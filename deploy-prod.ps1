# Deploy e_arsip ke Production Server (192.168.11.200)
# Strategi: stash local server changes -> pull -> pop stash (may conflict).
# Path PHP/composer di-resolve via login shell (bash -lc).

$ErrorActionPreference = 'Continue'

$PROD_HOST = '192.168.11.200'
$PROD_USER = 'root'
$PROD_PASS = 'bismillah@'
$projectDir = '/var/www'
$stamp = Get-Date -Format 'yyyyMMddHHmmss'

function Run-Ssh($title, $cmd) {
    Write-Host "==> $title" -ForegroundColor Cyan
    & plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "bash -lc `"$cmd`""
    Write-Host ''
}

Write-Host "==> Target: $PROD_USER@$PROD_HOST  dir: $projectDir" -ForegroundColor Green
Write-Host ''

Run-Ssh '1) Pre-deploy info (commit, php, composer)' `
    "cd $projectDir && git rev-parse --short HEAD && (which php || echo 'php NOT FOUND') && (which composer || echo 'composer NOT FOUND') && (test -f docker-compose.yml && echo 'docker-compose.yml: present' || echo 'docker-compose.yml: absent')"

Run-Ssh '2) Backup .env' `
    "cd $projectDir && cp .env .env.bak.$stamp && ls -la .env.bak.$stamp"

Run-Ssh '3) Stash local server changes (tracked + untracked)' `
    "cd $projectDir && git stash push --include-untracked -m 'pre-deploy-$stamp' && git stash list | head -5"

Run-Ssh '4) git pull --ff-only origin main' `
    "cd $projectDir && git fetch origin && git pull --ff-only origin main 2>&1"

Run-Ssh '5) Re-apply server stash (akan conflict utk file yang berubah di kedua sisi)' `
    "cd $projectDir && (git stash pop 2>&1 || echo 'STASH POP CONFLICTED - lihat git status')"

Run-Ssh '6) Status setelah pop' `
    "cd $projectDir && git status --short"

Run-Ssh '7) composer install (prod, no-dev)' `
    "cd $projectDir && composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -15"

Run-Ssh '8) php artisan migrate --force' `
    "cd $projectDir && php artisan migrate --force 2>&1"

Run-Ssh '9) Clear + rebuild cache' `
    "cd $projectDir && php artisan view:clear && php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache 2>&1"

Run-Ssh '10) Permission storage + bootstrap/cache' `
    "cd $projectDir && (chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R apache:apache storage bootstrap/cache 2>/dev/null || true) && chmod -R 775 storage bootstrap/cache && ls -ld storage bootstrap/cache"

Run-Ssh '11) Restart queue + reload php-fpm/nginx (best-effort)' `
    "cd $projectDir && php artisan queue:restart 2>&1; (systemctl reload php8.3-fpm 2>/dev/null || systemctl reload php8.2-fpm 2>/dev/null || systemctl reload php-fpm 2>/dev/null || true); (systemctl reload nginx 2>/dev/null || systemctl reload apache2 2>/dev/null || true); echo 'done'"

Run-Ssh '12) Verifikasi endpoint API (task C dari report)' `
    "cd $projectDir && php artisan route:list --path=api 2>&1 | grep -E 'arsip/dashboard|notifications/unread-count|api/arsip' | head -10"

Run-Ssh '13) migrate:status' `
    "cd $projectDir && php artisan migrate:status 2>&1 | tail -12"

Write-Host '==================== DEPLOY DONE ====================' -ForegroundColor Green
Write-Host 'KALAU step 5 conflict: SSH ke server, resolve manual:' -ForegroundColor Yellow
Write-Host '  ssh root@192.168.11.200'
Write-Host "  cd $projectDir"
Write-Host '  # edit file yang konflik (cek dgn: git status)'
Write-Host '  git add <file_resolved>'
Write-Host '  git stash drop  # buang stash setelah merge OK'
Write-Host ''
Write-Host 'Manual verification setelah deploy beres:'
Write-Host '  1. Trigger FCM notif dari Laravel -> Android (layar terkunci) -> heads-up + bunyi'
Write-Host '  2. tail -f /root/it_submissions/storage/logs/laravel.log'
Write-Host '  3. Kirim 3 notif berturut-turut -> device tidak nge-lag'
