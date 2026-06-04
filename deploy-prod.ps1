# Deploy e_arsip ke Production Server (192.168.11.200)
# Jalankan: .\deploy-prod.ps1

$ErrorActionPreference = 'Continue'

$PROD_HOST = '192.168.11.200'
$PROD_USER = 'root'
$PROD_PASS = 'bismillah@'

# Trust host key sekali (bila pertama kali konek dari mesin ini)
$null = & cmd /c "echo y | plink -ssh -pw `"$PROD_PASS`" $PROD_USER@$PROD_HOST `"echo OK`" 2>&1"

Write-Host '==> 1) Lokasi project di server' -ForegroundColor Cyan
$projectPath = & plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "find /var/www /home /opt -maxdepth 4 -name 'artisan' -type f 2>/dev/null | head -1"
$projectPath = ($projectPath | Out-String).Trim()

if (-not $projectPath) {
    Write-Host 'GAGAL: artisan tidak ditemukan di server.' -ForegroundColor Red
    Write-Host 'Edit script ini dan set $projectPath manual.' -ForegroundColor Red
    exit 1
}

$projectDir = Split-Path $projectPath -Parent
Write-Host "    project dir: $projectDir" -ForegroundColor Green

Write-Host '==> 2) Pre-deploy snapshot' -ForegroundColor Cyan
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && git rev-parse --short HEAD && php artisan --version"

Write-Host '==> 3) Backup .env' -ForegroundColor Cyan
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && cp .env .env.bak.$stamp && ls -la .env.bak.$stamp"

Write-Host '==> 4) git pull (FF only)' -ForegroundColor Cyan
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && git status --short && git pull --ff-only 2>&1"

Write-Host '==> 5) composer install (production)' -ForegroundColor Cyan
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -15"

Write-Host '==> 6) migrate --force' -ForegroundColor Cyan
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && php artisan migrate --force 2>&1"

Write-Host '==> 7) Clear + rebuild cache' -ForegroundColor Cyan
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && php artisan view:clear && php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache 2>&1"

Write-Host '==> 8) Fix permission' -ForegroundColor Cyan
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && (chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chown -R apache:apache storage bootstrap/cache 2>/dev/null || true) && chmod -R 775 storage bootstrap/cache 2>&1"

Write-Host '==> 9) Restart queue/horizon' -ForegroundColor Cyan
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && php artisan queue:restart 2>&1"

Write-Host '==> 10) Verifikasi endpoint API yang sebelumnya 404' -ForegroundColor Cyan
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && php artisan route:list --path=api 2>&1 | grep -E 'api/arsip|notifications/unread-count' | head -10"

Write-Host '==> 11) migrate:status' -ForegroundColor Cyan
& plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "cd $projectDir && php artisan migrate:status 2>&1 | tail -10"

Write-Host ''
Write-Host 'DEPLOY PROD SELESAI.' -ForegroundColor Green
Write-Host ''
Write-Host 'POST-DEPLOY MANUAL VERIFICATION:' -ForegroundColor Yellow
Write-Host '  1. POST /api/fcm/test dari device dengan layar terkunci - harus heads-up + bunyi + getar'
Write-Host '  2. tail -f storage/logs/laravel.log saat trigger notif - tidak ada error FCM'
Write-Host '  3. Kirim 3 notif beruntun - device tidak boleh nge-lag (FcmService sudah pakai tag+collapse_key)'
