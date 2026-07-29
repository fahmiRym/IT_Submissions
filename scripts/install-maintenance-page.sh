#!/usr/bin/env bash
# ============================================================
# Install nginx fallback → maintenance.html untuk 502/503/504
# ============================================================
# Efek: kalau it_app (php-fpm) mati, nginx tidak return raw 502,
# tapi serve /maintenance.html yg cantik.
#
# Usage di server 192.168.11.199 sbg root:
#   bash /root/it_submissions/scripts/install-maintenance-page.sh
#
# Idempotent — aman dijalankan berulang. Auto-backup default.conf.
# ============================================================

set -u

NGINX_CONTAINER="it_nginx"
DEFAULT_CONF="/etc/nginx/conf.d/default.conf"
BACKUP="/etc/nginx/conf.d/default.conf.bak"

echo "================================================================"
echo " Install Maintenance Fallback → $NGINX_CONTAINER"
echo "================================================================"

# ---- 1) Verify containers ----
if ! docker ps --format '{{.Names}}' | grep -qx "$NGINX_CONTAINER"; then
    echo "ERROR: container '$NGINX_CONTAINER' tidak jalan."
    exit 1
fi

# ---- 2) Verify maintenance.html exists ----
echo ""
echo "--- 1) Cek public/maintenance.html di container ---"
if docker exec "$NGINX_CONTAINER" test -f /var/www/public/maintenance.html; then
    echo "  ✓ Ditemukan di /var/www/public/maintenance.html"
else
    echo "  ✗ Tidak ada! Pastikan git pull sudah lengkap."
    exit 1
fi

# ---- 3) Backup default.conf (kalau belum ada backup) ----
echo ""
echo "--- 2) Backup $DEFAULT_CONF ---"
if docker exec "$NGINX_CONTAINER" test -f "$BACKUP"; then
    echo "  Backup sudah ada di $BACKUP (skip)"
else
    docker exec "$NGINX_CONTAINER" sh -c "cp $DEFAULT_CONF $BACKUP" && \
        echo "  ✓ Backup dibuat: $BACKUP"
fi

# ---- 4) Cek apakah sudah pernah di-patch dgn versi terbaru ----
if docker exec "$NGINX_CONTAINER" grep -q "MAINTENANCE_PATCH_V2" "$DEFAULT_CONF" 2>/dev/null; then
    echo ""
    echo "--- 3) Sudah pernah di-patch V2 (marker MAINTENANCE_PATCH_V2 ditemukan). Skip write. ---"
else
    echo ""
    echo "--- 3) Tulis config baru dgn maintenance directives ---"

    # Pakai `cat > file` (bukan sed -i) → overwrite in-place, tidak butuh rename
    # (rename gagal di bind-mount dgn error 'Resource busy')
    docker exec -i "$NGINX_CONTAINER" sh -c "cat > $DEFAULT_CONF" <<'NGINXEOF'
# MAINTENANCE_PATCH_V2 — jangan hapus marker ini
server {
    listen 80;
    client_max_body_size 250M;
    index index.php index.html;
    server_name localhost;

    root /var/www/public;

    # Resolver Docker embedded DNS (127.0.0.11) supaya "app" hostname
    # ter-resolve fresh tiap request (tidak cached ke IP mati)
    resolver 127.0.0.11 valid=5s ipv6=off;
    resolver_timeout 2s;

    # ==== Fallback: kalau upstream php-fpm error → maintenance page ====
    error_page 502 503 504 = @maintenance;

    location @maintenance {
        root /var/www/public;
        rewrite ^ /maintenance.html break;
        # Pastikan browser TIDAK cache halaman maintenance (biar refresh
        # selalu hit server, dan pas app up → langsung dapat konten baru)
        add_header Cache-Control 'no-store, no-cache, must-revalidate, max-age=0' always;
        add_header Pragma 'no-cache' always;
        add_header Expires '0' always;
    }

    # Preview URL: https://<host>/maintenance.html (untuk cek design)
    location = /maintenance.html {
        root /var/www/public;
        add_header Cache-Control 'no-store, no-cache, must-revalidate, max-age=0';
    }
    # ==================================================================

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        # Intercept upstream errors → picu error_page di atas
        fastcgi_intercept_errors on;

        # FAST-FAIL saat upstream mati: total < 3s (bukan hang 5-10s)
        # → user recovery instan sesudah container up lagi
        fastcgi_connect_timeout 2s;
        fastcgi_read_timeout 60s;
        fastcgi_send_timeout 10s;
        fastcgi_next_upstream error timeout;
        fastcgi_next_upstream_tries 1;
        fastcgi_next_upstream_timeout 3s;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINXEOF

    echo "  ✓ Config baru ditulis"
fi

# ---- 5) Test config ----
echo ""
echo "--- 4) Test nginx config ---"
if ! docker exec "$NGINX_CONTAINER" nginx -t 2>&1 | tail -3; then
    echo "  ERROR: config test gagal. Rollback dari backup..."
    docker exec "$NGINX_CONTAINER" sh -c "cp $BACKUP $DEFAULT_CONF"
    exit 1
fi

# ---- 6) Reload nginx ----
echo ""
echo "--- 5) Reload nginx ---"
docker exec "$NGINX_CONTAINER" nginx -s reload && echo "  ✓ Reload OK"

# ---- 7) Smoke test ----
echo ""
echo "--- 6) Smoke test ---"
docker exec "$NGINX_CONTAINER" wget -qO- --tries=1 --timeout=3 http://localhost/maintenance.html 2>/dev/null | head -3 | sed 's/^/    /'
echo ""
echo "  Direct GET /maintenance.html status:"
docker exec "$NGINX_CONTAINER" sh -c "wget -q --spider --server-response http://localhost/maintenance.html 2>&1 | grep 'HTTP/'" | sed 's/^/    /'

# ---- 8) Restore info ----
echo ""
echo "================================================================"
echo " MAINTENANCE PAGE TERPASANG ✓"
echo ""
echo " CARA COBA (dari mesin lain / browser):"
echo "   1. docker stop it_app                    # simulate app down"
echo "   2. Buka https://lin-dev-it-sub.inkalum.com/"
echo "      → halaman maintenance cantik muncul (bukan 502 raw)"
echo "   3. docker start it_app                   # nyalakan lagi"
echo ""
echo " ALTERNATIF (server tetap up, Laravel down mode):"
echo "   docker exec it_app php artisan down     # Laravel serve errors/503.blade"
echo "   docker exec it_app php artisan up       # nyalakan lagi"
echo ""
echo " ROLLBACK (kembalikan config asli):"
echo "   docker exec $NGINX_CONTAINER cp $BACKUP $DEFAULT_CONF"
echo "   docker exec $NGINX_CONTAINER nginx -s reload"
echo "================================================================"
