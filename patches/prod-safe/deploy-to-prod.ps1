#Requires -Version 5.1
<#
.SYNOPSIS
    Deploy patches/prod-safe/ ke server prod 192.168.11.200 dengan backup +
    graceful reload. Zero downtime.

.DESCRIPTION
    Untuk detail latar belakang lihat patches/prod-safe/README.md.

    Langkah:
      1. Backup 2 file prod ke /root/backups/*.bak.YYYYMMDD
      2. SCP 2 file patch (Arsip.php + BarcodeController.php) via pscp
      3. Verify PHP syntax di container
      4. Clear cache Laravel (view/config)
      5. Graceful reload php-fpm via docker kill --signal=USR2 (0 downtime)
      6. Health check GET / dan GET /login

.NOTES
    Wajib: plink + pscp di PATH (bawaan PuTTY package).
    Password prod: hard-coded 'bismillah@' — sesuai memory servers-ssh.
    User dapat ganti ke SSH key auth via `deploy-prod.ps1` (script utama repo).
#>

$ErrorActionPreference = 'Stop'

$ProdHost = 'root@192.168.11.200'
$ProdPwd = 'bismillah@'
$ProdDir = '/root/it_submissions'
$BackupDir = '/root/backups'
$DateStamp = Get-Date -Format 'yyyyMMdd_HHmmss'

$RepoRoot = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent

# 2 file backport prod-safe (butuh syntax lint PHP)
$Patches = @(
    @{
        Local  = "$RepoRoot\patches\prod-safe\app\Models\Arsip.php"
        Remote = "$ProdDir/app/Models/Arsip.php"
        Name   = 'Arsip.php'
        IsPhp  = $true
    },
    @{
        Local  = "$RepoRoot\patches\prod-safe\app\Http\Controllers\Api\BarcodeController.php"
        Remote = "$ProdDir/app/Http/Controllers/Api/BarcodeController.php"
        Name   = 'BarcodeController.php'
        IsPhp  = $true
    }
)

# 3 file maintenance page — pure static, deploy langsung dari repo root
# (tidak butuh backport, tidak butuh syntax lint)
$StaticFiles = @(
    @{
        Local  = "$RepoRoot\public\maintenance.html"
        Remote = "$ProdDir/public/maintenance.html"
        Name   = 'maintenance.html'
    },
    @{
        Local  = "$RepoRoot\resources\views\errors\503.blade.php"
        Remote = "$ProdDir/resources/views/errors/503.blade.php"
        Name   = '503.blade.php'
    },
    @{
        Local  = "$RepoRoot\scripts\install-maintenance-page.sh"
        Remote = "$ProdDir/scripts/install-maintenance-page.sh"
        Name   = 'install-maintenance-page.sh'
    }
)

function Invoke-Plink {
    param([string]$Cmd)
    & plink -ssh -batch -pw $ProdPwd $ProdHost $Cmd
    if ($LASTEXITCODE -ne 0) { throw "plink failed: $Cmd" }
}

function Invoke-Pscp {
    param([string]$Local, [string]$Remote)
    & pscp -pw $ProdPwd -batch $Local "${ProdHost}:${Remote}"
    if ($LASTEXITCODE -ne 0) { throw "pscp failed: $Local -> $Remote" }
}

Write-Host "=== 1. Verify local files exist + PHP syntax OK ===" -ForegroundColor Cyan
foreach ($p in $Patches) {
    if (-not (Test-Path $p.Local)) { throw "Missing: $($p.Local)" }
    $lint = & php -l $p.Local
    if ($LASTEXITCODE -ne 0) { throw "PHP lint failed: $($p.Local)`n$lint" }
    Write-Host "  OK $($p.Name)" -ForegroundColor Green
}
foreach ($s in $StaticFiles) {
    if (-not (Test-Path $s.Local)) { throw "Missing: $($s.Local)" }
    Write-Host "  OK $($s.Name)" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== 2. Backup prod files ===" -ForegroundColor Cyan
Invoke-Plink "mkdir -p $BackupDir"
foreach ($p in $Patches) {
    $bakName = "$($p.Name).bak.$DateStamp"
    Invoke-Plink "cp $($p.Remote) $BackupDir/$bakName"
    Write-Host "  Backed up: $BackupDir/$bakName" -ForegroundColor Green
}
foreach ($s in $StaticFiles) {
    $bakName = "$($s.Name).bak.$DateStamp"
    Invoke-Plink "test -f $($s.Remote) && cp $($s.Remote) $BackupDir/$bakName || echo '  (belum ada di prod, skip backup)'"
}

Write-Host ""
Write-Host "=== 3. SCP file ke prod ===" -ForegroundColor Cyan
foreach ($p in $Patches) {
    Invoke-Pscp $p.Local $p.Remote
    Write-Host "  Uploaded: $($p.Name)" -ForegroundColor Green
}
foreach ($s in $StaticFiles) {
    # Ensure parent dir exists
    $parentDir = $s.Remote.Substring(0, $s.Remote.LastIndexOf('/'))
    Invoke-Plink "mkdir -p $parentDir"
    Invoke-Pscp $s.Local $s.Remote
    Write-Host "  Uploaded: $($s.Name)" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== 4. Verify PHP syntax on server ===" -ForegroundColor Cyan
foreach ($p in $Patches) {
    Invoke-Plink "docker exec it_app php -l /var/www/$($p.Remote.Replace("$ProdDir/", ''))"
}

Write-Host ""
Write-Host "=== 5. Clear cache + graceful reload php-fpm ===" -ForegroundColor Cyan
Invoke-Plink "docker exec it_app php artisan cache:clear"
Invoke-Plink "docker exec it_app php artisan config:clear"
Invoke-Plink "docker kill --signal=USR2 it_app"
Start-Sleep -Seconds 2

Write-Host ""
Write-Host "=== 6. Health check ===" -ForegroundColor Cyan
Invoke-Plink "curl -s -o /dev/null -w 'GET / HTTP %{http_code} in %{time_total}s\n' http://localhost/"
Invoke-Plink "curl -s -o /dev/null -w 'GET /login HTTP %{http_code} in %{time_total}s\n' http://localhost/login"

Write-Host ""
Write-Host "DEPLOY SUCCESS. Backup di $BackupDir/*.bak.$DateStamp" -ForegroundColor Green
Write-Host "Rollback: cp $BackupDir/Arsip.php.bak.$DateStamp $ProdDir/app/Models/Arsip.php && docker kill --signal=USR2 it_app" -ForegroundColor Yellow
