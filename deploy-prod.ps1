# ============================================================
# Deploy e_arsip ke PRODUCTION Server via SSH KEY
# ============================================================
# EXTRA CAUTION untuk prod: dobel konfirmasi sebelum jalan.
#
# Prerequisite:
#   1. .env.deploy sudah di-setup (lihat .env.deploy.example)
#   2. SSH key sudah di-install ke prod server:
#      ssh-copy-id -i .deploy_keys/deploy_key.pub root@192.168.11.200
#   3. Kosongkan PROD_FALLBACK_PASSWORD setelah key aktif
#
# Usage: .\deploy-prod.ps1
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

$PROD_HOST    = $cfg['PROD_HOST']
$PROD_USER    = $cfg['PROD_USER']
$PROD_KEY     = Join-Path $PSScriptRoot $cfg['PROD_SSH_KEY']
$projectDir   = $cfg['PROD_PROJECT_DIR']
$dockerFile   = $cfg['DOCKER_COMPOSE_FILE']
$dockerSvc    = $cfg['DOCKER_SERVICES']
$fallbackPwd  = $cfg['PROD_FALLBACK_PASSWORD']
$stamp        = Get-Date -Format 'yyyyMMddHHmmss'

# ---- Prod confirmation ----
Write-Host ''
Write-Host '========================================================' -ForegroundColor Red
Write-Host '  DEPLOY PRODUCTION SERVER ' -ForegroundColor Red
Write-Host "  Target: $PROD_USER@$PROD_HOST" -ForegroundColor Red
Write-Host "  Dir:    $projectDir" -ForegroundColor Red
Write-Host '========================================================' -ForegroundColor Red
Write-Host ''
$confirm = Read-Host "Ketik 'DEPLOY PROD' untuk confirm (case-sensitive)"
if ($confirm -cne 'DEPLOY PROD') {
    Write-Host "Deploy dibatalkan." -ForegroundColor Yellow
    exit 0
}

# ---- Auth method detection ----
$useKey = (Test-Path $PROD_KEY) -and [string]::IsNullOrEmpty($fallbackPwd)
if ($useKey) {
    Write-Host "[AUTH] SSH KEY ($PROD_KEY)" -ForegroundColor Green
} elseif ($fallbackPwd) {
    Write-Host "[AUTH] PASSWORD fallback (setup SSH key ASAP!)" -ForegroundColor Yellow
} else {
    Write-Host "ERROR: Tidak ada SSH key + tidak ada fallback password." -ForegroundColor Red
    exit 1
}

# ---- SSH runner ----
function Run-Ssh($title, $cmd) {
    Write-Host "==> $title" -ForegroundColor Cyan
    if ($useKey) {
        & ssh -i "$PROD_KEY" -o StrictHostKeyChecking=no -o BatchMode=yes "$PROD_USER@$PROD_HOST" "bash -lc `"$cmd`""
    } else {
        & plink -ssh -batch -pw "$fallbackPwd" "$PROD_USER@$PROD_HOST" "bash -lc `"$cmd`""
    }
    Write-Host ''
}

# ============================================================
# DEPLOY STEPS
# ============================================================

Run-Ssh '1) Pre-deploy info' `
    "cd $projectDir && git rev-parse --short HEAD && (which php || echo 'php NOT FOUND') && (which composer || echo 'composer NOT FOUND') && (which docker || echo 'docker NOT INSTALLED')"

Run-Ssh '2) Backup .env + DB dump' `
    "cd $projectDir && cp .env .env.bak.$stamp && ls -la .env.bak.$stamp"

Run-Ssh '3) Stash local server changes' `
    "cd $projectDir && git stash push --include-untracked -m 'pre-deploy-$stamp' && git stash list | head -5"

Run-Ssh '4) git pull --ff-only' `
    "cd $projectDir && git fetch origin && git pull --ff-only origin main 2>&1"

Run-Ssh '5) Re-apply server stash' `
    "cd $projectDir && (git stash pop 2>&1 || echo 'STASH POP CONFLICTED')"

Run-Ssh '6) Status setelah pop' `
    "cd $projectDir && git status --short"

Run-Ssh '7) composer install (no-dev)' `
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
# DOCKER RESTART
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

Write-Host '==================== DEPLOY PROD DONE ====================' -ForegroundColor Green
Write-Host "Timestamp: $stamp" -ForegroundColor DarkGray
