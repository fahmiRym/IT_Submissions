# patches/prod-safe/ — Backport untuk PROD .200 (era April 2026)

Folder ini berisi **file backport** yang aman deploy ke prod `192.168.11.200` tanpa harus jalankan migration full 37 tabel/kolom baru.

## Kenapa perlu?

Prod `.200` stuck di git `147ff58 first commit` = **era April 2026**, sebelum era approval chain / TTD digital / delegasi / share / lampiran / multi-pemohon / role SPV/Kabag/Manager / APK versioning. Kode modern di `main` branch reference banyak model/table/kolom yang belum ada di prod → deploy naive = crash 500.

File di sini adalah **prod baseline + patch minimal** (bug fix + feature kritis) yang tidak butuh schema baru. Detail migrasi lengkap ke fitur modern ada di [`PROJECT_OVERVIEW.md`](../../PROJECT_OVERVIEW.md) seksi 15 (Staged Rollout).

## Isi

| File | Baseline source | Patch delta |
|---|---|---|
| `app/Models/Arsip.php` | prod `147ff58` (366 baris) | +`$appends = ['status_utama']` +method `getStatusUtamaAttribute()` |
| `app/Http/Controllers/Api/BarcodeController.php` | prod `147ff58` (104 baris) | Fix `accept_ba` tidak corrupt kolom `status` + validation `in:arsip,accept_ba` + idempoten check |

Impact: **fix bug Android** (status DALAM PROSES padahal sudah Done, Accept BA menghancurkan status utama). Zero risiko schema, zero migration.

## Cara pakai

### Deploy manual (SCP)

```powershell
# Dari laptop, cd ke repo root
pscp -pw 'bismillah@' patches\prod-safe\app\Models\Arsip.php `
    root@192.168.11.200:/root/it_submissions/app/Models/Arsip.php
pscp -pw 'bismillah@' patches\prod-safe\app\Http\Controllers\Api\BarcodeController.php `
    root@192.168.11.200:/root/it_submissions/app/Http/Controllers/Api/BarcodeController.php

# Backup dulu prod (recommended, taruh di `~/backups/`)
plink -pw 'bismillah@' root@192.168.11.200 "cp /root/it_submissions/app/Models/Arsip.php /root/backups/Arsip.php.bak.$(date +%Y%m%d)"

# Clear cache + graceful reload php-fpm (0 downtime)
plink -pw 'bismillah@' root@192.168.11.200 "
  docker exec it_app php artisan cache:clear && \
  docker exec it_app php artisan config:clear && \
  docker kill --signal=USR2 it_app
"
```

### Deploy via script

```powershell
.\patches\prod-safe\deploy-to-prod.ps1
```

## Rollback

```powershell
plink -pw 'bismillah@' root@192.168.11.200 "
  cp /root/backups/Arsip.php.bak.YYYYMMDD /root/it_submissions/app/Models/Arsip.php && \
  cp /root/backups/BarcodeController.php.bak.YYYYMMDD /root/it_submissions/app/Http/Controllers/Api/BarcodeController.php && \
  docker kill --signal=USR2 it_app
"
```

## Verify after deploy

```powershell
plink -pw 'bismillah@' root@192.168.11.200 "
  curl -s -o /dev/null -w 'HTTP %{http_code}\n' http://localhost/
  curl -s -o /dev/null -w 'HTTP %{http_code}\n' http://localhost/login
"
```

Expected: 302 (redirect ke login) + 200.

## Sunset (kapan hapus folder ini)

Hapus `patches/prod-safe/` setelah **Staged Rollout migrasi selesai** di prod (Batch A-G di [PROJECT_OVERVIEW.md seksi 15.9](../../PROJECT_OVERVIEW.md#159-kesimpulan-plan-migrasi)). Setelah prod schema equivalent dengan local, file `main` branch akan langsung compatible.
