#!/usr/bin/env bash
# ============================================================
# Hot-fix: naikkan PHP + Nginx upload limit tanpa rebuild image
# ============================================================
# Fix untuk error 413 PostTooLargeException saat upload APK
# via /superadmin/app-versions/{id}/upload-apk.
#
# Container yang di-touch:
#   - it_app   : tulis /usr/local/etc/php/conf.d/uploads.ini + reload php-fpm
#   - it_nginx : tulis client_max_body_size 250M + reload nginx
#
# Limit baru: 250M (APK biasanya 60-150MB, margin ke atas)
#
# CATATAN CLOUDFLARE:
#   - CF Free plan hard-limit body upload = 100 MB
#   - Kalau APK > 100 MB, harus BYPASS Cloudflare:
#     upload langsung ke http://192.168.11.199/ (via LAN/VPN)
#     ATAU upgrade CF ke Pro (500 MB) / Business (200 MB) / Enterprise (custom)
#
# Usage di server 192.168.11.199 sbg root:
#   scp scripts/fix-upload-size.sh root@192.168.11.199:/tmp/
#   ssh root@192.168.11.199 "sed -i 's/\r$//' /tmp/fix-upload-size.sh && bash /tmp/fix-upload-size.sh"
#
# File WAJIB LF line endings. Anti CRLF:
#   sed -i 's/\r$//' fix-upload-size.sh
# ============================================================

set -u

APP_CONTAINER="it_app"
NGINX_CONTAINER="it_nginx"
LIMIT="250M"
LIMIT_BYTES="262144000"  # 250M dalam bytes untuk nginx

echo "================================================================"
echo " HOT-FIX Upload Limit → $LIMIT"
echo "  App container   : $APP_CONTAINER"
echo "  Nginx container : $NGINX_CONTAINER"
echo "================================================================"

# ---- 1) Verify containers running ----
for c in "$APP_CONTAINER" "$NGINX_CONTAINER"; do
    if ! docker ps --format '{{.Names}}' | grep -qx "$c"; then
        echo "ERROR: container '$c' tidak jalan. Cek dgn 'docker ps'."
        exit 1
    fi
done

# ---- 2) Update PHP config di it_app ----
echo ""
echo "--- 1) Tulis php uploads.ini di $APP_CONTAINER ---"
docker exec -i "$APP_CONTAINER" bash -c "cat > /usr/local/etc/php/conf.d/uploads.ini <<'EOF'
upload_max_filesize = $LIMIT
post_max_size = $LIMIT
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
EOF
"

echo "  Verify:"
docker exec "$APP_CONTAINER" cat /usr/local/etc/php/conf.d/uploads.ini | sed 's/^/    /'

# ---- 3) Reload php-fpm (SIGUSR2 = graceful reload) ----
echo ""
echo "--- 2) Reload php-fpm ---"
# php-fpm master PID biasanya 1 di container. SIGUSR2 = graceful reload.
docker exec "$APP_CONTAINER" bash -c 'kill -USR2 1 2>/dev/null || pkill -USR2 php-fpm 2>/dev/null || (echo "SIGUSR2 gagal, restart container..." && exit 1)'

if [ $? -ne 0 ]; then
    echo "  Fallback: restart container $APP_CONTAINER..."
    docker restart "$APP_CONTAINER"
fi

sleep 2
echo "  PHP effective limits:"
docker exec "$APP_CONTAINER" php -r 'echo "    upload_max_filesize = " . ini_get("upload_max_filesize") . PHP_EOL; echo "    post_max_size       = " . ini_get("post_max_size") . PHP_EOL; echo "    memory_limit        = " . ini_get("memory_limit") . PHP_EOL;'

# ---- 4) Update nginx client_max_body_size ----
echo ""
echo "--- 3) Tulis nginx client_max_body_size di $NGINX_CONTAINER ---"

# Cari config file default nginx. Umumnya /etc/nginx/conf.d/*.conf atau /etc/nginx/sites-enabled/*
NGINX_CONF=""
for candidate in "/etc/nginx/conf.d/default.conf" "/etc/nginx/conf.d/app.conf" "/etc/nginx/sites-enabled/default"; do
    if docker exec "$NGINX_CONTAINER" test -f "$candidate" 2>/dev/null; then
        NGINX_CONF="$candidate"
        break
    fi
done

if [ -z "$NGINX_CONF" ]; then
    echo "  WARN: config nginx default tidak ketemu di lokasi standar."
    echo "  Cek manual: docker exec $NGINX_CONTAINER find /etc/nginx -name '*.conf'"
    echo "  Skip nginx patch — bikin override via /etc/nginx/conf.d/upload-size.conf"

    # Bikin drop-in include yg berlaku di http context
    docker exec -i "$NGINX_CONTAINER" bash -c "cat > /etc/nginx/conf.d/zz-upload-size.conf <<'EOF'
# Auto-injected by fix-upload-size.sh
client_max_body_size $LIMIT;
client_body_buffer_size 128k;
client_body_timeout 300s;
EOF
"
    echo "  Drop-in: /etc/nginx/conf.d/zz-upload-size.conf"
else
    echo "  Nginx config: $NGINX_CONF"

    # Kalau sudah ada client_max_body_size, replace. Kalau belum, sisipkan di server block.
    if docker exec "$NGINX_CONTAINER" grep -q "client_max_body_size" "$NGINX_CONF"; then
        docker exec "$NGINX_CONTAINER" sed -i "s/client_max_body_size[[:space:]]\+[^;]\+;/client_max_body_size $LIMIT;/" "$NGINX_CONF"
        echo "  Replaced existing client_max_body_size → $LIMIT"
    else
        # Sisipkan setelah baris 'server {'
        docker exec "$NGINX_CONTAINER" sed -i "/^\s*server\s*{/a\    client_max_body_size $LIMIT;\n    client_body_timeout 300s;" "$NGINX_CONF"
        echo "  Inserted client_max_body_size $LIMIT into $NGINX_CONF"
    fi

    echo "  Effective config:"
    docker exec "$NGINX_CONTAINER" grep -n "client_max_body_size\|client_body_timeout" "$NGINX_CONF" | sed 's/^/    /'
fi

# ---- 5) Reload nginx ----
echo ""
echo "--- 4) Reload nginx ---"
if docker exec "$NGINX_CONTAINER" nginx -t 2>&1 | tail -3; then
    docker exec "$NGINX_CONTAINER" nginx -s reload && echo "  ✓ Nginx reload OK"
else
    echo "  ERROR: nginx config test gagal. Skip reload."
    exit 1
fi

# ---- 6) Health check ----
echo ""
echo "--- 5) Health check ---"
sleep 2
curl -s -o /dev/null -w "  Local backend: HTTP %{http_code} in %{time_total}s\n" -m 5 http://localhost/api/mobile/version?app=itsubmissions

echo ""
echo "================================================================"
echo " HOT-FIX DONE"
echo ""
echo " Sekarang coba upload APK lagi via web UI."
echo " Limit efektif: $LIMIT (PHP + Nginx)"
echo ""
echo " KALAU MASIH 413 dari public URL (dev-it-sub.inkalum.com):"
echo "   → APK > 100 MB kena Cloudflare Free limit"
echo "   → Solusi: upload direct ke http://192.168.11.199/ dari LAN"
echo "     atau via jaringan VPN internal"
echo "================================================================"
