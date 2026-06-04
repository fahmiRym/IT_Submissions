# Deploy e_arsip ke Dev Server (192.168.11.199)
# Jalankan: .\deploy-dev.ps1
# Atau dari PowerShell: powershell -ExecutionPolicy Bypass -File .\deploy-dev.ps1

$ErrorActionPreference = 'Continue'

$DEV_HOST = '192.168.11.199'
$DEV_USER = 'root'
$DEV_PASS = 'bismillah@'

# Trust host key sekali
$null = & cmd /c "echo y | plink -ssh -pw `"$DEV_PASS`" $DEV_USER@$DEV_HOST `"echo OK`" 2>&1"

Write-Host '==> 1) Lokasi project di server' -ForegroundColor Cyan
$projectPath = & plink -ssh -batch -pw "$DEV_PASS" "$DEV_USER@$DEV_HOST" "find /var/www /home /opt -maxdepth 4 -name 'artisan' -type f 2>/dev/null | head -1"
$projectPath = ($projectPath | Out-String).Trim()

if (-not $projectPath) {
    Write-Host 'GAGAL: artisan tidak ditemukan di server. Edit script ini dan set `$projectPath manual.' -ForegroundColor Red
    exit 1
}

$projectDir = Split-Path $projectPath -Parent
Write-Host "    project dir: $projectDir" -ForegroundColor Green

Write-Host '==> 2) Pre-deploy snapshot (git rev + migrate status)' -ForegroundColor Cyan
& plink -ssh -batch -pw "$DEV_PASS" "$DEV_USER@$DEV_HOST" "cd $projectDir && git rev-parse --short HEAD && php artisan migrate:status 2>&1 | tail -8"

Write-Host '==> 3) git pull' -ForegroundColor Cyan
& plink -ssh -batch -pw "$DEV_PASS" "$DEV_USER@$DEV_HOST" "cd $projectDir && git pull --ff-only 2>&1"

Write-Host '==> 4) composer install' -ForegroundColor Cyan
& plink -ssh -batch -pw "$DEV_PASS" "$DEV_USER@$DEV_HOST" "cd $projectDir && composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -15"

Write-Host '==> 5) migrate' -ForegroundColor Cyan
& plink -ssh -batch -pw "$DEV_PASS" "$DEV_USER@$DEV_HOST" "cd $projectDir && php artisan migrate --force 2>&1"

Write-Host '==> 6) clear + recache' -ForegroundColor Cyan
& plink -ssh -batch -pw "$DEV_PASS" "$DEV_USER@$DEV_HOST" "cd $projectDir && php artisan view:clear && php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache 2>&1"

Write-Host '==> 7) permission' -ForegroundColor Cyan
& plink -ssh -batch -pw "$DEV_PASS" "$DEV_USER@$DEV_HOST" "cd $projectDir && (chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R apache:apache storage bootstrap/cache 2>/dev/null || true) && chmod -R 775 storage bootstrap/cache 2>&1"

Write-Host '==> 8) verifikasi: migrate:status terakhir' -ForegroundColor Cyan
& plink -ssh -batch -pw "$DEV_PASS" "$DEV_USER@$DEV_HOST" "cd $projectDir && php artisan migrate:status 2>&1 | tail -10"

Write-Host ''
Write-Host 'DEPLOY DEV SELESAI.' -ForegroundColor Green
