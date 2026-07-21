@echo off
REM ============================================================
REM Startup script — Laravel dev server multi-worker + Cloudflare tunnel
REM ============================================================
REM Fixes:
REM   - "Login gagal (Error 502)" di Android saat concurrent request
REM     (Android after-login burst: login → device-token → dashboard → notif)
REM   - Root cause: php artisan serve default single-threaded
REM     → cloudflared tunnel timeout kalau request queued > 30s
REM
REM Solution: PHP_CLI_SERVER_WORKERS=4 → PHP built-in server jadi 4-worker
REM   → handle ~4 concurrent request tanpa queue
REM
REM Cara pakai:
REM   1. Double-click file ini (atau jalankan dari cmd)
REM   2. Jendela artisan serve muncul → biarkan terbuka
REM   3. Cloudflared tunnel harus jalan terpisah (via Laragon / Windows service)
REM
REM Verifikasi:
REM   curl -X POST https://dev-it-sub.inkalum.com/api/login \
REM     -H "Accept: application/json" -H "Content-Type: application/json" \
REM     -d "{\"username\":\"test\",\"password\":\"x\"}"
REM   Harus balas 401 (bukan 502).
REM ============================================================

cd /d "%~dp0"

echo.
echo [1/3] Killing existing artisan serve on port 8003 (if any)...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":8003" ^| findstr "LISTENING"') do (
    echo   Killing PID %%a
    taskkill /F /PID %%a >nul 2>&1
)

timeout /t 2 /nobreak >nul

echo.
echo [2/3] Clearing Laravel caches...
php artisan optimize:clear

echo.
echo [3/3] Starting artisan serve with 4 workers on port 8003...
echo   URL: http://localhost:8003
echo   Tunnel: https://dev-it-sub.inkalum.com (via cloudflared)
echo.
echo   Ctrl+C untuk stop.
echo.

set PHP_CLI_SERVER_WORKERS=4
php artisan serve --host=0.0.0.0 --port=8003
