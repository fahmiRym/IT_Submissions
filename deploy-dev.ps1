# ============================================================
# Deploy e_arsip ke Dev Server via SSH KEY (no password in file)
# ============================================================
# Prerequisite:
#   1. Copy `.env.deploy.example` -> `.env.deploy` (gitignored)
#   2. Isi credential asli di `.env.deploy`
#   3. Install public key ke server (1x saja):
#      ssh-copy-id -i .deploy_keys/deploy_key.pub root@192.168.11.199
#      (butuh password sekali, setelah itu key auth aktif)
#   4. Kosongkan DEV_FALLBACK_PASSWORD di `.env.deploy` setelah key jalan
#
# Usage: .\deploy-dev.ps1
# ============================================================

$ErrorActionPreference = 'Continue'

# ---- Load .env.deploy ----
$envFile = Join-Path $PSScriptRoot '.env.deploy'
if (!(Test-Path $envFile)) {
    Write-Host "ERROR: File .env.deploy tidak ada. Copy dari .env.deploy.example dulu." -ForegroundColor Red
    exit 1
}
$cfg = @{}
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^\s*#') { return }
    if ($_ -match '^\s*$') { return }
    if ($_ -match '^([^=]+)=(.*)$') { $cfg[$matches[1].Trim()] = $matches[2].Trim() }
}

$DEV_HOST     = $cfg['DEV_HOST']
$DEV_USER     = $cfg['DEV_USER']
$DEV_KEY      = Join-Path $PSScriptRoot $cfg['DEV_SSH_KEY']
$projectDir   = $cfg['DEV_PROJECT_DIR']
$dockerFile   = $cfg['DOCKER_COMPOSE_FILE']
$dockerSvc    = $cfg['DOCKER_SERVICES']
$fallbackPwd  = $cfg['DEV_FALLBACK_PASSWORD']
$stamp        = Get-Date -Format 'yyyyMMddHHmmss'

# ---- Auth method detection ----
$useKey = (Test-Path $DEV_KEY) -and [string]::IsNullOrEmpty($fallbackPwd)
if ($useKey) {
    Write-Host "[AUTH] SSH KEY ($DEV_KEY)" -ForegroundColor Green
} elseif ($fallbackPwd) {
    Write-Host "[AUTH] PASSWORD fallback (setup SSH key untuk production)" -ForegroundColor Yellow
} else {
    Write-Host "ERROR: Tidak ada SSH key valid + tidak ada fallback password. Cek .env.deploy." -ForegroundColor Red
    exit 1
}

Write-Host "==> Target: $DEV_USER@$DEV_HOST  dir: $projectDir" -ForegroundColor Cyan
Write-Host ''

# ---- SSH runner ----
function Run-Ssh($title, $cmd) {
    Write-Host "==> $title" -ForegroundColor Cyan
    if ($useKey) {
        & ssh -i "$DEV_KEY" -o StrictHostKeyChecking=no -o BatchMode=yes "$DEV_USER@$DEV_HOST" "bash -lc `"$cmd`""
    } else {
        & plink -ssh -batch -pw "$fallbackPwd" "$DEV_USER@$DEV_HOST" "bash -lc `"$cmd`""
    }
    Write-Host ''
}

# ============================================================
# DEPLOY STEPS
# ============================================================

Run-Ssh '1) Pre-deploy info' `
    "cd $projectDir && git rev-parse --short HEAD && (which php || echo 'php NOT FOUND') && (which composer || echo 'composer NOT FOUND') && (which docker || echo 'docker NOT INSTALLED')"

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

# ============================================================
# DOCKER RESTART (baru)
# ============================================================

if ($dockerFile) {
    if ($dockerSvc) {
        $svcList = $dockerSvc -replace ',', ' '
        Run-Ssh "12) Restart Docker services: $dockerSvc" `
            "cd $projectDir && (docker compose -f $dockerFile restart $svcList 2>&1 || docker-compose -f $dockerFile restart $svcList 2>&1) | tail -15"
    } else {
        Run-Ssh '12) Restart ALL Docker services di compose' `
            "cd $projectDir && (docker compose -f $dockerFile restart 2>&1 || docker-compose -f $dockerFile restart 2>&1) | tail -15"
    }

    Run-Ssh '13) Docker ps setelah restart' `
        "docker ps --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}' 2>&1 | head -15"
} else {
    Write-Host "==> Docker restart SKIPPED (DOCKER_COMPOSE_FILE kosong di .env.deploy)" -ForegroundColor Yellow
    Write-Host ''
}

# ============================================================
# HEALTH CHECK
# ============================================================

Run-Ssh '14) Health check' `
    "curl -s -o /dev/null -w 'GET /api/mobile/version: HTTP %{http_code}\n' http://localhost/api/mobile/version?app=itsubmissions 2>&1"

Write-Host '==================== DEPLOY DEV DONE ====================' -ForegroundColor Green
Write-Host "Timestamp: $stamp" -ForegroundColor DarkGray
