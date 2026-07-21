#!/usr/bin/env bash
# Deploy e_arsip — Docker-aware (it_app container).
# Usage: bash deploy.sh /root/it_submissions
# File ini WAJIB LF line endings. Anti CRLF: sed -i 's/\r$//' /tmp/deploy.sh sebelum jalan.

set -u

PROJECT_DIR="${1:-/root/it_submissions}"
STAMP="$(date +%Y%m%d%H%M%S)"

if [ ! -d "$PROJECT_DIR" ]; then
    echo "ERROR: $PROJECT_DIR tidak ada."
    exit 1
fi

cd "$PROJECT_DIR" || exit 1

# Deteksi docker compose (v2 plugin "docker compose" vs legacy "docker-compose")
DC=""
if docker compose version >/dev/null 2>&1; then
    DC="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
    DC="docker-compose"
fi

APP_CONTAINER="it_app"
USE_DOCKER=0
if [ -n "$DC" ] && [ -f docker-compose.yml ]; then
    if docker ps --format '{{.Names}}' | grep -q "^${APP_CONTAINER}$"; then
        USE_DOCKER=1
    fi
fi

# Wrapper untuk eksekusi PHP / composer
if [ "$USE_DOCKER" -eq 1 ]; then
    EXEC_PHP="docker exec -i $APP_CONTAINER php"
    EXEC_COMP="docker exec -i $APP_CONTAINER composer"
else
    PHP_BIN=""
    for c in php8.3 php8.2 php8.1 php; do
        [ -x "/usr/bin/$c" ] && PHP_BIN="/usr/bin/$c" && break
        [ -x "/usr/local/bin/$c" ] && PHP_BIN="/usr/local/bin/$c" && break
    done
    EXEC_PHP="$PHP_BIN"
    EXEC_COMP="$(command -v composer 2>/dev/null)"
fi

echo "================================================================"
echo " DEPLOY e_arsip"
echo "  PROJECT_DIR : $PROJECT_DIR"
echo "  Docker mode : $USE_DOCKER (container: $APP_CONTAINER)"
echo "  PHP runner  : $EXEC_PHP"
echo "  Composer    : $EXEC_COMP"
echo "  Stamp       : $STAMP"
echo "================================================================"

# ---------- 1) Pre-deploy ----------
echo ""
echo "--- 1) Pre-deploy info ---"
git rev-parse --short HEAD 2>&1
[ -f .env ] && echo ".env: present" || echo ".env: MISSING"

# ---------- 2) Fix 500: SESSION_DRIVER=file ----------
echo ""
echo "--- 2) SESSION_DRIVER=file (fix MySQL deadlock di session GC) ---"
if [ -f .env ]; then
    cp .env .env.bak.$STAMP
    if grep -q '^SESSION_DRIVER=' .env; then
        sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
    else
        echo 'SESSION_DRIVER=file' >> .env
    fi
    grep ^SESSION_DRIVER .env
    mkdir -p storage/framework/sessions
fi

# ---------- 3) Stash local changes ----------
echo ""
echo "--- 3) Stash local server changes ---"
git stash push --include-untracked -m "pre-deploy-$STAMP" 2>&1 | head -5
git stash list | head -3

# ---------- 4) Git pull ----------
echo ""
echo "--- 4) git pull --ff-only origin main ---"
git fetch origin 2>&1 | tail -5
git pull --ff-only origin main 2>&1 | tail -10

# ---------- 5) Re-apply stash ----------
echo ""
echo "--- 5) Re-apply server stash ---"
git stash pop 2>&1 | head -20 || echo "STASH POP CONFLICTED — resolve manual!"
echo ""
echo "Status post-pop:"
git status --short

# ---------- 6) composer install (di container) ----------
echo ""
echo "--- 6) composer install ---"
if [ -n "$EXEC_COMP" ]; then
    $EXEC_COMP install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -10
else
    echo "SKIPPED: composer tidak ditemukan (USE_DOCKER=$USE_DOCKER)"
fi

# ---------- 7) migrate ----------
echo ""
echo "--- 7) php artisan migrate --force ---"
$EXEC_PHP artisan migrate --force 2>&1

# ---------- 8) clear + rebuild cache ----------
echo ""
echo "--- 8) clear + rebuild cache ---"
$EXEC_PHP artisan view:clear 2>&1 | tail -1
$EXEC_PHP artisan config:clear 2>&1 | tail -1
$EXEC_PHP artisan route:clear 2>&1 | tail -1
$EXEC_PHP artisan cache:clear 2>&1 | tail -1
$EXEC_PHP artisan config:cache 2>&1 | tail -1
$EXEC_PHP artisan route:cache 2>&1 | tail -1
$EXEC_PHP artisan view:cache 2>&1 | tail -1

# ---------- 9) Permission di host ----------
echo ""
echo "--- 9) permission storage + bootstrap/cache ---"
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null \
    || chown -R 33:33 storage bootstrap/cache 2>/dev/null
chmod -R 775 storage bootstrap/cache 2>/dev/null
ls -ld storage storage/framework storage/framework/sessions bootstrap/cache

# ---------- 10) queue:restart ----------
echo ""
echo "--- 10) queue:restart ---"
$EXEC_PHP artisan queue:restart 2>&1 | tail -2

# ---------- 11) Migrate status ----------
echo ""
echo "--- 11) migrate:status (last 15) ---"
$EXEC_PHP artisan migrate:status 2>&1 | tail -15

# ---------- 12) API route check ----------
echo ""
echo "--- 12) API route check ---"
$EXEC_PHP artisan route:list --path=api 2>&1 | grep -E 'arsip/dashboard|notifications/unread-count|api/arsip' | head -10

echo ""
echo "================================================================"
echo " DEPLOY DONE"
echo "  - tail -f $PROJECT_DIR/storage/logs/laravel.log"
echo "  - Test FCM notif Android"
echo "================================================================"
