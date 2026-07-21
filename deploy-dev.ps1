# Deploy e_arsip ke Dev Server (192.168.11.199)

$ErrorActionPreference = 'Continue'

$DEV_HOST = '192.168.11.199'
$DEV_USER = 'root'
$DEV_PASS = 'bismillah@'
$projectDir = '/root/it_submissions'
$stamp = Get-Date -Format 'yyyyMMddHHmmss'

function Run-Ssh($title, $cmd) {
    Write-Host "==> $title" -ForegroundColor Cyan
    & plink -ssh -batch -pw "$DEV_PASS" "$DEV_USER@$DEV_HOST" "bash -lc `"$cmd`""
    Write-Host ''
}

Write-Host "==> Target: $DEV_USER@$DEV_HOST  dir: $projectDir" -ForegroundColor Green
Write-Host ''

Run-Ssh '1) Pre-deploy info' `
    "cd $projectDir && git rev-parse --short HEAD && (which php || echo 'php NOT FOUND') && (which composer || echo 'composer NOT FOUND')"

Run-Ssh '2) Backup .env' `
    "cd $projectDir && cp .env .env.bak.$stamp && ls -la .env.bak.$stamp"

Run-Ssh '3) Stash local server changes' `
    "cd $projectDir && git stash push --include-untracked -m 'pre-deploy-$stamp' && git stash list | head -5"

Run-Ssh '4) git pull --ff-only' `
    "cd $projectDir && git fetch origin && git pull --ff-only origin main 2>&1"

Run-Ssh '5) Re-apply server stash' `
    "cd $projectDir && (git stash pop 2>&1 || echo 'STASH POP CONFLICTED')"

Run-Ssh '6) Status setelah pop' `
    "cd $projectDir && git status --short"

Run-Ssh '7) composer install' `
    "cd $projectDir && composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -15"

Run-Ssh '8) migrate --force' `
    "cd $projectDir && php artisan migrate --force 2>&1"

Run-Ssh '9) Clear + rebuild cache' `
    "cd $projectDir && php artisan view:clear && php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache 2>&1"

Run-Ssh '10) Permission' `
    "cd $projectDir && (chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true) && chmod -R 775 storage bootstrap/cache"

Run-Ssh '11) migrate:status' `
    "cd $projectDir && php artisan migrate:status 2>&1 | tail -12"

Write-Host '==================== DEPLOY DEV DONE ====================' -ForegroundColor Green
