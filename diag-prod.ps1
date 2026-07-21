# Diagnostic e_arsip Production (192.168.11.200)

$ErrorActionPreference = 'Continue'
$PROD_HOST = '192.168.11.200'
$PROD_USER = 'root'
$PROD_PASS = 'bismillah@'
$projectDir = '/root/it_submissions'

function Run-Ssh($title, $cmd) {
    Write-Host "==> $title" -ForegroundColor Cyan
    & plink -ssh -batch -pw "$PROD_PASS" "$PROD_USER@$PROD_HOST" "bash -lc `"$cmd`""
    Write-Host ''
}

Run-Ssh '1) Laravel error log (50 baris terakhir)' `
    "cd $projectDir && tail -50 storage/logs/laravel.log 2>&1"

Run-Ssh '2) Git status (cek file conflict / stash) ' `
    "cd $projectDir && git status && echo '---' && git stash list"

Run-Ssh '3) Migrate status' `
    "cd $projectDir && php artisan migrate:status 2>&1 | tail -15"

Run-Ssh '4) Composer autoload check' `
    "cd $projectDir && ls -la vendor/autoload.php && ls vendor/maatwebsite 2>/dev/null && cat composer.json | head -30"

Run-Ssh '5) .env exists + APP_KEY set' `
    "cd $projectDir && ls -la .env && grep -E '^APP_KEY|^APP_ENV|^APP_DEBUG|^DB_DATABASE' .env"

Run-Ssh '6) Cek apakah file login.blade / app.blade ada merge marker' `
    "cd $projectDir && (grep -l '<<<<<<<\\|=======\\|>>>>>>>' resources/views/auth/login.blade.php resources/views/layouts/app.blade.php 2>/dev/null || echo 'NO merge markers in login/app blade')"

Run-Ssh '7) Permission check' `
    "cd $projectDir && ls -ld storage storage/logs storage/framework storage/framework/views bootstrap/cache"

Run-Ssh '8) Web server log (last 30)' `
    "(tail -30 /var/log/nginx/error.log 2>/dev/null || tail -30 /var/log/apache2/error.log 2>/dev/null || echo 'no nginx/apache log')"
