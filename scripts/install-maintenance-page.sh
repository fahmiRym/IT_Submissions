#!/usr/bin/env bash
# ============================================================
# Install nginx fallback → maintenance.html untuk 502/503/504
# ============================================================
# Efek: kalau it_app (php-fpm) mati, nginx tidak return raw 502,
# tapi serve /maintenance.html yg cantik. User tidak liat error mentah.
#
# Kapan trigger:
#   - Container it_app di-stop (docker stop it_app)
#   - PHP-FPM crash
#   - Backend timeout > 60s
#   - php artisan down (Laravel 503) → di-render via view
#
# Yang tidak trigger (server total mati):
#   - it_nginx container juga mati → tidak ada yg serve
#   - Server Linux mati total → CF bakal show default CF page
#
# Usage di server 192.168.11.199 sbg root:
#   scp scripts/install-maintenance-page.sh root@192.168.11.199:/tmp/
#   ssh root@192.168.11.199 "sed -i 's/\r$//' /tmp/install-maintenance-page.sh && bash /tmp/install-maintenance-page.sh"
#
# Ini idempotent — aman dijalankan berulang.
# ============================================================

set -u

NGINX_CONTAINER="it_nginx"
DROPIN_PATH="/etc/nginx/conf.d/zz-maintenance.conf"

echo "================================================================"
echo " Install Maintenance Fallback Page → $NGINX_CONTAINER"
echo "================================================================"

# ---- 1) Verify containers ----
if ! docker ps --format '{{.Names}}' | grep -qx "$NGINX_CONTAINER"; then
    echo "ERROR: container '$NGINX_CONTAINER' tidak jalan."
    exit 1
fi

# ---- 2) Verify maintenance.html exists in public/ (mounted ke container) ----
echo ""
echo "--- 1) Cek public/maintenance.html di container ---"
if docker exec "$NGINX_CONTAINER" test -f /var/www/public/maintenance.html; then
    echo "  ✓ Ditemukan di /var/www/public/maintenance.html"
else
    echo "  ✗ Tidak ada! Pastikan sudah git pull + docker restart (bind-mount refresh)"
    echo "  Cek: docker exec $NGINX_CONTAINER ls -la /var/www/public/maintenance.html"
    exit 1
fi

# ---- 3) Tulis drop-in nginx config ----
echo ""
echo "--- 2) Tulis $DROPIN_PATH ---"

docker exec -i "$NGINX_CONTAINER" bash -c "cat > $DROPIN_PATH <<'NGINXEOF'
# ============================================================
# Auto-injected by install-maintenance-page.sh
# Fallback ke /maintenance.html kalau upstream (php-fpm) unreachable
# ATAU return error 5xx.
# ============================================================

# Global: intercept upstream errors di seluruh server block
# NB: nginx v1.14+ mendukung 'proxy_intercept_errors on' + 'fastcgi_intercept_errors on'
#     di server context — tapi lebih portable diaktifkan per location upstream.

# Custom error page pointing ke static HTML
error_page 502 503 504 = @maintenance;

location @maintenance {
    root /var/www/public;
    rewrite ^ /maintenance.html break;
    add_header Retry-After 60 always;
    add_header Cache-Control 'no-store, no-cache, must-revalidate' always;
    internal;
}

# Location eksplisit untuk /maintenance.html supaya CF bisa hit langsung
# (misal admin/IT mau preview: https://.../maintenance.html)
location = /maintenance.html {
    root /var/www/public;
    internal;
}
NGINXEOF
"

echo "  Content:"
docker exec "$NGINX_CONTAINER" cat "$DROPIN_PATH" | sed 's/^/    /'

# ---- 4) Pastikan main config aktifkan intercept_errors ----
echo ""
echo "--- 3) Cek fastcgi_intercept_errors on di default.conf ---"
DEFAULT_CONF="/etc/nginx/conf.d/default.conf"

if docker exec "$NGINX_CONTAINER" test -f "$DEFAULT_CONF"; then
    if docker exec "$NGINX_CONTAINER" grep -q "fastcgi_intercept_errors" "$DEFAULT_CONF"; then
        echo "  ✓ Sudah ada fastcgi_intercept_errors di $DEFAULT_CONF"
    else
        echo "  Belum ada — inject 'fastcgi_intercept_errors on;' ke location ~ \\.php\$..."
        # Sisipkan setelah 'fastcgi_pass' — ini pattern paling umum
        docker exec "$NGINX_CONTAINER" sed -i \
            '/fastcgi_pass/a\        fastcgi_intercept_errors on;\n        fastcgi_read_timeout 60s;\n        fastcgi_connect_timeout 5s;' \
            "$DEFAULT_CONF" || echo "  WARN: gagal inject via sed, cek manual."
        docker exec "$NGINX_CONTAINER" grep -n "fastcgi_intercept_errors\|fastcgi_pass" "$DEFAULT_CONF" | sed 's/^/    /'
    fi
else
    echo "  WARN: $DEFAULT_CONF tidak ada. Skip."
fi

# ---- 5) Test + reload nginx ----
echo ""
echo "--- 4) Test nginx config ---"
if docker exec "$NGINX_CONTAINER" nginx -t 2>&1 | tail -3; then
    echo ""
    echo "--- 5) Reload nginx ---"
    docker exec "$NGINX_CONTAINER" nginx -s reload && echo "  ✓ Reload OK"
else
    echo "  ERROR: config test gagal. Rollback..."
    docker exec "$NGINX_CONTAINER" rm -f "$DROPIN_PATH"
    exit 1
fi

# ---- 6) Smoke test ----
echo ""
echo "--- 6) Test langsung maintenance.html ---"
docker exec "$NGINX_CONTAINER" curl -s -o /dev/null -w "  Direct GET /maintenance.html: HTTP %{http_code}\n" http://localhost/maintenance.html || echo "  (curl tidak tersedia di container, skip)"

echo ""
echo "================================================================"
echo " MAINTENANCE PAGE TERPASANG"
echo ""
echo " CARA COBA:"
echo "   1. Stop container app: docker stop it_app"
echo "   2. Buka URL: https://lin-dev-it-sub.inkalum.com/"
echo "      → harusnya muncul halaman maintenance cantik (bukan 502 mentah)"
echo "   3. Nyalakan lagi:      docker start it_app"
echo ""
echo " ATAU pakai Laravel maintenance mode (server tetap up, cuma marked):"
echo "   docker exec it_app php artisan down --render='errors::503'"
echo "   docker exec it_app php artisan up   # untuk nyalakan lagi"
echo "================================================================"
