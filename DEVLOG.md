# DEVLOG — IT Submission (e_arsip)

Catatan kerja per sesi. Entri terbaru di atas.

---

## 2026-07-20 (lanjutan) — Superadmin UI: Kelola APK Android

**Konteks:** setelah endpoint public `/api/mobile/version` siap, tambah UI supaya superadmin bisa manage versi + upload APK tanpa harus tinker manual.

### Routes (4 baru)

| Method | URI | Handler |
|--------|-----|---------|
| GET | `superadmin/app-versions` | `AppVersionController@index` — list + form |
| POST | `superadmin/app-versions` | `@upsert` — create-or-update by app_slug |
| POST | `superadmin/app-versions/{id}/upload-apk` | `@uploadApk` — multipart upload APK, auto sha256 |
| DELETE | `superadmin/app-versions/{id}` | `@destroy` — hapus row + file APK |

### View features (`superadmin/app_versions/index.blade.php`)

- Left panel — **Form tambah/update** (upsert by `app_slug`): slug, name, version_name, version_code, apk_url_override, force_update toggle, changelog textarea
- Right panel — **List app terdaftar** dgn per-row: badge version, force_update indicator, download link APK, file size + hash preview, changelog card, inline upload form (choose file + button), delete
- Footer info card — dokumentasi endpoint `/api/mobile/version` untuk Android team

### Upload APK flow (`uploadApk()`)

1. Validate MIME type `application/vnd.android.package-archive` OR `application/octet-stream` (Firefox kadang label sbg octet-stream)
2. Max size 200 MB
3. Hapus APK lama kalau ada (`Storage::disk('public')->delete()`)
4. Store dgn filename `{slug}-{version_name}-{version_code}.apk` di `storage/app/public/apk/`
5. Auto-compute `file_size` + `sha256` hash
6. Reset `apk_url_override` (path lokal jadi source of truth)
7. Audit log

### Audit trail

`AppVersionController::audit()` — write ke `laravel.log` via `Log::info()` dgn payload `{actor_id, actor_name, app_slug, version, extra}`. Actions: `app_version.create`, `app_version.update`, `app_version.upload_apk`, `app_version.delete`. Simple + tidak butuh tabel baru (bisa di-migrasi ke `audit_logs` nanti kalau perlu queryable).

### Setup infra

- `storage/app/public/apk/` directory dibuat otomatis
- `public/storage` symlink verified ada (kalau tidak, `php artisan storage:link`)

### Sidebar menu

Tambah item di [layouts/sidebar/superadmin.blade.php](resources/views/layouts/sidebar/superadmin.blade.php) — icon `bi-android2` (hijau), label "Kelola APK Android", di bawah "Statistik Server".

### File created (2) + edited (3)

**Created:**
- `app/Http/Controllers/Superadmin/AppVersionController.php`
- `resources/views/superadmin/app_versions/index.blade.php`

**Edited:**
- `routes/web.php` — 4 route baru di grup superadmin
- `resources/views/layouts/sidebar/superadmin.blade.php` — menu Kelola APK Android
- `storage/app/public/apk/` — dir baru

### Verifikasi
- Route list: 4 route terdaftar ✓
- View compile OK ✓
- Controller syntax OK ✓
- GET `/superadmin/app-versions` → 302 (redirect ke login — expected, butuh auth) ✓
- GET `/api/mobile/version?app=itsubmissions` → 200 JSON masih return correct data ✓

### Cara pakai (superadmin flow)

1. Buka **Sidebar → Kelola APK Android**
2. **Tambah app baru** (mis. `itapproval`, `itasistant`, dst) via form kiri
3. **Update versi**: isi form dgn `app_slug` yang sama → auto-upsert
4. **Upload APK**: di card app, pilih file `.apk` → tombol Upload → auto-hitung size + sha256 + set path
5. Kalau APK di CDN eksternal, isi field `apk_url_override` (kosongkan setelah upload lokal supaya override tidak konflik)

### TODO next

- (Opsional) Extract audit ke tabel `audit_logs` supaya queryable via Superadmin → Activity Logs
- (Opsional) Preview icon APK (perlu library `androguard` PHP wrapper atau parse manual)
- Android team implement `CheckUpdateActivity` port dari ITAsistant

---

## 2026-07-20 — Auto-update APK Android (public endpoint /api/mobile/version)

**Konteks:** user minta "MDM" untuk ITSubmissions — setelah klarifikasi, maksudnya **auto-update APK terbaru** (bukan Device Owner / kiosk). Pattern sama seperti ITAsistant yg sudah punya `CheckUpdateActivity`.

### Data model — `app_versions` table

Satu row per Android app yg di-manage (mis. `itsubmissions`, `itapproval`, `itasistant`):

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `app_slug` | string(40) unique | slug identifier |
| `app_name` | string(100) | nama tampil |
| `latest_version` | string(20) | semver mis. `1.2.3` |
| `version_code` | int | Android `versionCode` — comparator utama |
| `apk_path` | string nullable | relative path di `storage/app/public/apk/` |
| `apk_url_override` | string nullable | URL absolut kalau APK di CDN eksternal |
| `force_update` | bool | wajib update atau tidak |
| `changelog` | text nullable | release notes |
| `file_size`, `file_hash` | int, string | optional untuk verify integrity |
| `uploaded_by` | FK users | audit |

### Endpoint terdaftar (2, public — no auth)

Public karena Android splash panggil SEBELUM login (user belum punya token).

| Method | URI | Handler | Purpose |
|--------|-----|---------|---------|
| GET | `/api/mobile/version?app=itsubmissions` | `AppVersionController@show` | Info versi terbaru untuk 1 app |
| GET | `/api/mobile/versions` | `AppVersionController@index` | List semua app registered |

### Response shape

```json
{
  "success": true,
  "app_slug": "itsubmissions",
  "app_name": "IT Submissions",
  "latest_version": "1.0.0",
  "version_code": 1,
  "apk_url": "https://dev-it-sub.inkalum.com/storage/apk/itsubmissions-1.0.0.apk",
  "force_update": false,
  "changelog": "Initial release",
  "file_size": null,
  "file_hash": null,
  "updated_at": "2026-07-20T10:59:19+07:00"
}
```

### Model helper

`AppVersion::getApkUrlAttribute()` — accessor yg prioritas: `apk_url_override` (kalau di CDN) > `apk_path` (build via `Storage::url()`) > null.

### Seed awal

2 row di-seed: `itsubmissions` v1.0.0, `itapproval` v1.0.0 (kompatibel dgn dua Android project).

### File created (3)

- `database/migrations/2026_07_20_100000_create_app_versions_table.php`
- `app/Models/AppVersion.php`
- `app/Http/Controllers/Api/AppVersionController.php`
- `routes/api.php` — 2 route baru dalam public group

### Untuk Android team

**Contract sudah siap** — port pattern dari ITAsistant `CheckUpdateActivity.kt` + `VersionDto.kt`:

1. Buat `VersionResponse.kt` DTO dengan field: `latest_version, version_code, apk_url, force_update, changelog`
2. Retrofit method: `@GET("api/mobile/version") fun getAppVersion(@Query("app") slug: String = "itsubmissions"): Call<VersionResponse>`
3. `SplashActivity.onCreate()`: fetch version → compare `body.versionCode > BuildConfig.VERSION_CODE` → prompt update
4. `CheckUpdateActivity`: DownloadManager download APK → BroadcastReceiver `ACTION_DOWNLOAD_COMPLETE` → `PackageInstaller` OR `FileProvider` intent → user tap install
5. Manifest: `<uses-permission android:name="android.permission.REQUEST_INSTALL_PACKAGES" />` + FileProvider config
6. Kalau `force_update=true` → dialog non-dismissible sampai user install

### TODO next

- Superadmin UI: upload APK + form manage versions (sekarang manual via tinker)
- Optional: audit log setiap kali versi ditambah/diubah
- Optional: signature verify di client (hash sha256 dari `file_hash`)

---

## 2026-07-15 (lanjutan #2) — Root cause 502 sebenarnya: artisan serve single-thread → multi-worker fix

**Follow-up dari 502 issue sebelumnya.** Fix `departments.code` menyelesaikan crash SQL, TAPI user report tetap "Login gagal (Error 502)" saat login dev.

### Investigasi mendalam

**Fakta:**
1. Dev URL = `https://dev-it-sub.inkalum.com` = **Cloudflare tunnel → localhost:8003** (bukan server terpisah — pakai Laragon local via tunnel)
2. Probe langsung ke tunnel 5x → semua HTTP 401 (server sehat)
3. User dapat 502 sporadic saat login
4. `php artisan serve` = **single-threaded** default (PHP built-in server hanya proses 1 request at a time)

**Test simulasi Android burst** (5 concurrent request via tunnel):
- Single-thread: linear 0.5s → 1.0s → 1.5s → 2.0s → 2.5s (kalau ada slowdown → tunnel timeout 30s → 502)
- Setelah fix multi-worker: 0.55s / 0.82s / 1.11s / 1.52s / 2.40s (pola 4-worker, 4 process paralel + 1 queued)

### Root cause

`php artisan serve` default single-threaded → Android after-login burst:
1. `POST /api/login` → dapat token
2. `POST /api/device-token` → register FCM
3. `GET /api/arsip/master-data` → populate spinners
4. `GET /api/arsip/dashboard` → home
5. `GET /api/notifications/unread-count` → badge

Kalau semua datang bersamaan → request 3-5 queued di server → tunnel Cloudflare tunggu response > 30s → **502 gateway timeout** dilempar ke Android → Toast "Login gagal (Error 502)".

### Fix: PHP_CLI_SERVER_WORKERS env var

PHP 7.4+ built-in server support multi-worker via env var:
```bash
PHP_CLI_SERVER_WORKERS=4 php artisan serve --host=0.0.0.0 --port=8003
```
→ 4 worker paralel, handle ~4 concurrent request tanpa queue.

### Alternatif yang di-explore & di-tolak

- **Opsi A (Laragon Apache vhost)**: Apache Laragon pakai PHP 8.1, tapi project butuh PHP ≥ 8.2 (composer platform check). Perlu install PHP 8.2 ke Laragon + switch module = kompleks + affects project Laragon lain.
- **Opsi C (php-fpm on Windows)**: overkill untuk dev.
- **Opsi B (env var multi-worker)** = **CHOSEN** — 1 baris config, no dependency, langsung jalan.

### File created (1)

- `start-dev-tunnel.bat` — Windows batch script yg:
  1. Kill existing artisan serve di port 8003
  2. Clear Laravel caches
  3. Start artisan serve dgn `PHP_CLI_SERVER_WORKERS=4`

  User cukup double-click file untuk restart dev server multi-worker (recommend jadikan shortcut di desktop).

### Verifikasi
- localhost:8003 GET / → 302 ✓
- Tunnel POST /api/login 5x paralel → semua 401 (bukan 502) ✓
- Time distribution linear per-worker → confirm 4-worker aktif

### Untuk Android team

**502 error saat login dev seharusnya sekarang HILANG.** Test ulang dari device — kalau masih 502, kirim logcat + timestamp exact supaya bisa cross-check dgn Laravel log.

**Bonus recommendation untuk dev workflow:**
- Windows startup: taruh shortcut `start-dev-tunnel.bat` di `%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\` supaya auto-start saat boot
- Alternatif: setup Windows Task Scheduler trigger "At log on" run `start-dev-tunnel.bat`

---

## 2026-07-15 (lanjutan) — Fix 502 mobile setelah login + konfirmasi Fase 1 approver flow

### Bug fix: 502 di mobile setelah login sukses

**Report Android:** login OK di API, tapi 502 muncul begitu credential valid dikirim. Bukan crash di `AuthController::login` — crash di call BERIKUTNYA yg Android auto-trigger setelah login.

**Diagnosis** (`tail storage/logs/laravel.log`):
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'code' in 'field list'
at ArsipApiController.php:141 (getMasterData method)
```

**Root cause:** Line 141 `Department::where('is_active', true)->get(['id', 'name', 'code'])` — tabel `departments` **tidak punya kolom `code`** (verified via `Schema::getColumnListing`). Hanya `units` yg punya `code`. Query gagal → SQL exception → Apache/nginx return 502 (dev logs error, prod bisa return 500 tergantung config).

Android app langsung call `getMasterData` setelah `AuthController::login` sukses (untuk populate spinners dept/unit/manager). Crash di `getMasterData` = perceived sebagai "502 tepat setelah login".

**Fix:** hapus `'code'` dari select departments di line 141.

### Verifikasi
```
success=1
departments=74 units=24 managers=11 approver_users=1666
first dept: {"id":1,"name":"Anodize"}
first unit: {"id":1,"name":"Unit 1","code":null}
```

### Fase 1 Approver Flow — CONFIRMED support langsung ke IT (dokumentasi)

**Keputusan produk:** Fase 1 mobile users tidak pilih approver chain — pengajuan dari HP langsung Pemohon → Departemen IT (bypass SPV/Kabag/Manager). Fase 2 nanti kalau butuh scale-up baru aktifkan picker.

**Backend zero-change needed** — struktur sudah support keduanya via `ArsipApproval::generateFor()` line 121-124:
```php
foreach (self::rolesForJenis(...) as $role) {
    $uid = $approverMap[$role] ?? null;
    if (!$uid) continue;   // ← Fase 1: skip step, tidak error
    // ... create step ...
}
```

Kalau Android kirim `approvers` map kosong (Fase 1) → hanya Pemohon + FINAL_ROLE (Departemen IT) yg dibuat. Kalau isi (Fase 2) → chain penuh dibangun. **Zero migration atau code change** untuk switch antara Fase 1 ↔ Fase 2.

**Data pendukung Fase 2 sudah tersedia** di `GET /api/arsip/master-data` (verified):
- `approver_users[]` — list user aktif dgn `{id, name, jabatan, role}` (1666 user)
- `approval_roles{Adjust: [SPV,Kabag,Manager,Accounting], Produk_Baru: [], default: [SPV,Kabag,Manager]}`

Android tinggal build picker UI kalau Fase 2 aktif — payload backend sudah siap.

### File edited (1)
- `app/Http/Controllers/Api/ArsipApiController.php` — hapus `'code'` dari `Department::get(...)`

### Sanity + cache
- `php artisan optimize:clear` — cleared config/cache/compiled/events/routes/views
- Syntax check 8 file (4 approval + 4 API baru) → semua OK

---

## 2026-07-15 — Sprint 2 partial: Server Stats + Activity Logs API (superadmin only)

**Konteks:** Android sprint 2 request — Server Stats + Activity Logs endpoints untuk mobile dashboard. Manajemen DB di-skip (tidak cocok mobile).

### Endpoint terdaftar (4 baru)

| Method | URI | Handler | Guard |
|--------|-----|---------|-------|
| GET | `/api/superadmin/server-stats` | `ServerStatApiController@apiSnapshot` | superadmin |
| GET | `/api/superadmin/server-stats/metrics` | `ServerStatApiController@apiMetrics` | superadmin |
| GET | `/api/superadmin/activity-logs` | `ActivityLogApiController@index` | superadmin |
| GET | `/api/superadmin/activity-logs/users` | `ActivityLogApiController@users` | superadmin |

### Approach

**Server Stats:** `Api\ServerStatApiController` **extends** `Superadmin\ServerStatController` supaya reuse SEMUA 22 helper protected (formatBytes, getCpuCount, getTableBreakdown, getRecentTraffic, dst) — ZERO duplikasi 200+ baris logic. Refactor 22 helper `private → protected` di web controller (accessor scope only, tidak break web). Snapshot response terstruktur nested (disk/memory/cpu/database as objects) beda dari web flat shape, lebih mobile-friendly.

**Activity Logs:** controller baru dgn transform helper `transformLog()` yg build `changes[]` array `[{field, old, new}]` dari `old_values`/`new_values` diff — Android tinggal render list changes tanpa parsing JSON manual. Include filter (q, user_id, action, from, to) + pagination meta.

### Verifikasi tinker
```
server-stats: success=1 health=90 php=8.2.12
  disk=250.99 GB/476.23 GB (52.7%)
  db_size_mb=2.98 pending_approvals=61
  alerts=1 tables=10
metrics: ts=13:49:48 disk=52.7% mem=8.59% cpu=0%
activity-logs: success=1 total=90 page=1/5
  first: action=created user=Novi arsip=ANO-260714-U3B-001 changes=21
```

### File created (2) + edited (2)
- `app/Http/Controllers/Api/ServerStatApiController.php` (NEW)
- `app/Http/Controllers/Api/ActivityLogApiController.php` (NEW)
- `app/Http/Controllers/Superadmin/ServerStatController.php` — 22 helper visibility `private → protected`
- `routes/api.php` — 4 route baru dalam grup `Route::prefix('superadmin')`

### TODO
- Laporan endpoint (Sprint 2 lanjutan)
- P1 sprint: delegation `/api/me/delegation`, lampiran CRUD, personal notes, show-document merged PDF, verify QR JSON

---

## 2026-07-14 (lanjutan #2) — API contract alignment (fix mismatch dgn Android DTOs)

**Konteks:** setelah rilis endpoint P0 approval, cek Android side ternyata sudah preemptively siap semua (Sprint 1 100% ter-implement). Audit response shape → ada 5 mismatch dgn Android DTOs.

### Mismatch & fix

| # | Field Android expect | Backend awal | Fix backend |
|---|---|---|---|
| 1 | `ApprovalInboxItem.department` = object `{id, name}` | string "Extrusion" | render `{id, name, code}` object |
| 2 | `ApprovalStep.step` (Int) | kirim `step_order` saja | kirim BOTH `step` + `step_order` |
| 3 | `ApprovalStep.delegated_from` = String? (nama) | object `{id, name}` | flatten ke string nama |
| 4 | `DetailDokumenResponse.success + data` | approve/reject/sign hanya `{status, message}` | tambah `success:true` + `data:<enriched arsip>` |
| 5 | `getMyApprovals` — `success` field | `{status, count, data}` | tambah `success:true` di top-level |

### Refactor DRY: extract `Arsip::toApiDetailArray($user)` helper di model

Fix #4 tadinya perlu duplikasi enrichment logic ke 4 tempat (`ArsipApiController::show`, `HandlesApproval::approveArsip`, `rejectArsip`, `SignsArsip::signArsip`). Extract helper di model:
```php
public function toApiDetailArray($user = null): array {
    // eager-load relasi + toArray + enrichment (is_fully_approved, current_step, actions_available)
}
```
Semua 4 endpoint sekarang panggil helper ini → response payload IDENTIK. Android tinggal `displayData(response.body.data)` di callback approve/reject/sign → auto-refresh UI, ZERO extra network call.

### Efek untuk Android

- **Zero code change** — semua DTOs Android sudah benar sejak awal
- **Save 1 network round-trip per action** — response approve/reject/sign include `data` full arsip

### Verifikasi tinker

```
GET /api/approvals:
  success=1, count=1, department object (name="Die History"),
  unit object (name="Unit 4A"), current_step.step=2, current_step.delegated_from=null (string)

GET /api/arsip/725:
  success=1, has_data=Y, current_step.step=2, actions_available.can_sign_self=false
```

### File edited (4)
- `app/Traits/HandlesApproval.php` — `transformArsipForApi()` restructure + `approveArsip`/`rejectArsip` return format
- `app/Traits/SignsArsip.php` — `signArsip()` return format
- `app/Models/Arsip.php` — `toApiDetailArray($user)` helper (single source of truth untuk detail payload API)
- `app/Http/Controllers/Api/ArsipApiController.php` — `show()` pakai helper (DRY, ~70 baris → ~15 baris)

---

## 2026-07-14 (lanjutan) — API P0 untuk Android: Approval Flow endpoints

**Konteks:** roadmap Android Sprint 1 — 5 endpoint P0 supaya user bisa approve/reject/TTD dari HP tanpa harus buka web.

### Approach: single source of truth (trait) — bukan duplikasi logic

Alih-alih copy-paste logic ke API controller, saya extend `HandlesApproval` trait supaya support `$request->wantsJson()` → satu method (`myApprovals`, `approveArsip`, `rejectArsip`) melayani BOTH web (view/redirect) dan API (JSON) tanpa duplikasi. `SignsArsip::signArsip()` sudah punya `wantsJson()` check dari sebelumnya (anti double-sign).

`Api\ApprovalApiController` cuma `use \App\Traits\HandlesApproval + SignsArsip;` — zero method body-nya. Routes langsung mount ke trait methods.

### Endpoint terdaftar

| Method | URI | Handler | Function |
|--------|-----|---------|----------|
| GET | `/api/approvals` | `myApprovals()` | Inbox pengajuan yg tahap aktifnya menunggu user login |
| POST | `/api/arsip/{id}/approve` | `approveArsip()` | Setujui + auto-TTD sesuai role step (dgn delegation propagation) |
| POST | `/api/arsip/{id}/reject` | `rejectArsip()` | Tolak (body: `note`), arsip → Reject/Void |
| POST | `/api/arsip/{id}/sign` | `signArsip()` | TTD self (Pemohon / Accounting Adjust) |
| GET | `/api/arsip/{id}` (enriched) | `show()` | Detail + approvals[] + signatures[] + delegation + `actions_available` |

### Response contract

**GET /api/approvals** — return list compact untuk inbox card:
```json
{
  "status": "success",
  "count": 3,
  "data": [{
    "id": 708, "no_registrasi": "...", "jenis_pengajuan": "Cancel",
    "pemohon": "Novi", "department": "Extrusion", "unit": "Unit 3B",
    "tgl_pengajuan": "2026-07-10", "status": "Check", "ket_process": "Review",
    "current_step": {
      "step_order": 2, "role_label": "SPV", "is_mine": true,
      "delegated_from": { "id": 12, "name": "Budi Kabag" }
    },
    "approvals_count": 4, "signatures_count": 1
  }]
}
```

**POST /api/arsip/{id}/approve** — return next-step info:
```json
{
  "status": "success",
  "message": "Tahap SPV berhasil disetujui & ditandatangani.",
  "is_fully_approved": false,
  "next_step": { "step_order": 3, "role_label": "Kabag", "approver_id": 15 }
}
```

**POST /api/arsip/{id}/reject** — return updated status:
```json
{
  "status": "success",
  "message": "Pengajuan ditolak pada tahap SPV.",
  "arsip": { "id": 708, "status": "Reject", "ket_process": "Void" }
}
```

**GET /api/arsip/{id}** (enriched) — tambahan field di response `data`:
- `is_fully_approved` (bool)
- `approval_started` (bool)
- `verify_url` (URL absolut ke `/verify/{token}`)
- `current_step` — object berisi `step_order`, `role_label`, `approver`, `delegated_from`, `is_mine`
- `actions_available` — boolean guide untuk UI Android (`can_approve`, `can_reject`, `can_sign_self`, `self_sign_role`)
- `approvals[].delegated_from` — nested user info kalau step didelegasikan
- `signatures[].delegated_from` — sama untuk TTD record
- `lampirans[]` — untuk section lampiran (P1 belum diimplement tapi data sudah ada)
- `requesters[]` — multi-pemohon

### Verifikasi
Tinker sanity test:
- `GET /api/approvals` (user Dana) → 200, 1 pengajuan pending, `current_step.role_label=SPV`, `is_mine=true` ✓
- `GET /api/arsip/725` (pemohon Novi) → 200, enriched dgn `approvals=5, signatures=1, actions_available.can_sign_self=false` (karena Pemohon sudah auto-TTD saat submit — anti double-sign works) ✓

### Anti-forgery + delegation propagation

`SignsArsip::applySignature()` sekarang menerima `$delegatedFromId` param dan include di SHA-256 hash (bersama `config('app.key')` pepper). Berarti kalau step di-delegasikan (auto atau superadmin override), signature record menyimpan info wakil-dari + hash-nya berbeda dari signature non-delegasi → verify page tetap validate correctly.

### File edited (4) + created (1)

**Created:**
- `app/Http/Controllers/Api/ApprovalApiController.php` — thin controller yg cuma `use` trait

**Edited:**
- `app/Traits/HandlesApproval.php` — extract `myApprovalsData()` helper, tambah `transformArsipForApi()` compact serializer, tambah `wantsJson()` check di `myApprovals`, `approveArsip`, `rejectArsip`
- `app/Http/Controllers/Api/ArsipApiController.php` — `$detailRelations` +`lampirans` + `approvals.delegatedFrom` + `signatures.delegatedFrom` + `requesters.user`. `show()` merge `is_fully_approved` + `current_step` + `actions_available` ke response. Tambah helper `determineSelfSignRole()`.
- `routes/api.php` — 4 route baru di dalam `auth:sanctum` group

### TODO Sprint 1 sisa
- **Android side**: konsumsi 5 endpoint ini + build UI Inbox screen + Approve/Reject sheet + biometric gate
- **Backend polish**: bikin `ApiResource` (Laravel API Resource class) untuk normalize response shape — sekarang campur `$arsip->toArray()` + custom fields, agak berantakan
- **Next sprint (P1)**: delegation endpoints, lampiran CRUD, show-document merged PDF, personal notes CRUD, verify-QR JSON

---

## 2026-07-14 — Delegasi TTD (Kabag → SPV, Manager → Assistant)

**Konteks:** User minta support kasus delegasi TTD — mis. Kabag cuti diwakilkan SPV, Manager diwakilkan assistant. Solusi hybrid: persistent user profile + snapshot per-approval.

### Data model

**Migration** `2026_07_14_100000_add_approval_delegation_fields.php`:
1. `users`:
   - `delegate_to_id` (nullable FK users) — user pengganti
   - `delegate_active_from` (date, nullable) — start window
   - `delegate_active_until` (date, nullable) — end window
   - `delegate_reason` (string 200, nullable) — mis. "Cuti tahunan"
2. `arsip_approvals.delegated_from_id` (nullable FK users) — kalau step ini di-forward dari approver original
3. `arsip_signatures.delegated_from_id` (nullable FK users) — snapshot delegasi di record TTD

### Flow

1. **Superadmin set delegasi** via users index modal (button ↩ per row): pilih user pengganti + window + alasan. Guard: tidak boleh self-delegate, deteksi loop A→B & B→A.
2. **Pengaju submit pengajuan baru** → `ArsipApproval::generateFor()` — untuk tiap approver_id yg dipilih, cek `activeDelegate()`. Bila ada → substitute + set `delegated_from_id` = original id. Chain-forward supported (max depth 3, mis. A→B→C, akhir ke C).
3. **Approver TTD** → `HandlesApproval::approveArsip()` propagate `$step->delegated_from_id` ke `applySignature()` (kalau superadmin override tahap orang lain, juga di-treat sbg delegasi). `SignsArsip::applySignature()` simpan `delegated_from_id` di `arsip_signatures` + include di hash SHA-256 (anti-forgery).
4. **Render draft** (print/arsip_draft + arsip_draft_bundel): kotak signature tampilkan badge kuning "↩ Mewakili {nama asli}" di bawah timestamp. Style `.sig-delegate` bg #fef3c7, font 6.5px italic.
5. **Verify page** (`verify.show`): primary sig card + list TTD tampilkan badge "DELEGASI" + baris "TTD sbg wakil dari <b>{nama}</b> ({jabatan})".
6. **Approval timeline** (`_approval_timeline`): step delegasi dapat badge warning "WAKIL DARI {nama}".
7. **Hash update** (`VerificationController` + `SignsArsip`): formula hash sekarang include `delegated_from_id` + `config('app.key')` pepper. TTD lama tetap valid selama data tidak berubah.

### Files edited (11) + created (1)

**Created:**
- `database/migrations/2026_07_14_100000_add_approval_delegation_fields.php`

**Edited:**
- `app/Models/User.php` — fillable+casts+relations `delegateTo`/`delegatedFromUsers`, helper `activeDelegate()` (chain-follow max depth 3) + `isDelegatingNow()`.
- `app/Models/ArsipApproval.php` — fillable+relation `delegatedFrom()`+`isDelegated()`. `generateFor()` auto-substitute delegate.
- `app/Models/ArsipSignature.php` — fillable+relation `delegatedFrom()`+`isDelegated()`.
- `app/Traits/SignsArsip.php` — `applySignature()` extra param `$delegatedFromId`, include di hash + save.
- `app/Traits/HandlesApproval.php` — `approveArsip()` propagate `delegated_from_id` dari step ke signature; superadmin-override juga di-treat delegasi.
- `app/Http/Controllers/VerificationController.php` — eager-load `delegatedFrom` + include `delegated_from_id` + pepper di hash re-verify.
- `app/Http/Controllers/Superadmin/UserController.php` — `setDelegate()` + `clearDelegate()` methods, validasi self+loop.
- `routes/web.php` — routes `superadmin.users.set-delegate` (POST) + `clear-delegate` (DELETE).
- `resources/views/users/index.blade.php` — button ↩ per row (indigo/kuning bila aktif dgn dot hijau) + modal set/clear delegasi.
- `resources/views/print/arsip_draft.blade.php` — `$renderSig` tampilkan `.sig-delegate`, CSS baru, eager-load `signatures.delegatedFrom`.
- `resources/views/print/arsip_draft_bundel.blade.php` — sama.
- `resources/views/verify/show.blade.php` — badge DELEGASI + baris wakil-dari di primary card & sig list.
- `resources/views/partials/_approval_timeline.blade.php` — badge "WAKIL DARI {nama}" tiap step.

### Verifikasi
- Migration OK, semua kolom baru muncul.
- Tinker: `activeDelegate()` return delegate user saat window aktif; return null di luar window.
- View cache re-cache clean, tidak ada error compile.
- Routes terdaftar: `superadmin.users.set-delegate`, `superadmin.users.clear-delegate`.

### TODO sesi berikutnya
- E2E test: pengaju submit → assigned ke Kabag (yg sedang delegasi ke SPV) → SPV lihat notifikasi + boleh approve → draft render badge delegasi.
- Opsional: notifikasi ke user asli kalau delegasi baru dipakai ("Kabag Budi: SPV Fulan telah men-TTD pengajuan X sebagai wakil Anda").
- Opsional: user profile page — tambah section "Delegasi TTD" supaya user bisa set sendiri (tidak harus lewat Superadmin).

---

## 2026-06-26 (lanjutan) — Cache busting: SERVICE_VERSION + no-cache headers

User report follow-up: "tidak muncul saat di generate show dokumen, hanya draft dan note personal, lampirannya tidak ikut tampil" — meski fix sebelumnya sudah merge correctly.

### Investigasi

Tinker test ulang membuktikan service code WORK:
- arsip 721 (2 lampiran clean): 3 pages (1 draft + 2 lampiran) ✓
- arsip 722 (1 lampiran encrypted): 2 pages (1 draft + 1 placeholder cover) ✓
- arsip 723 (1 lampiran encrypted + 3 notes): 3 pages (1 draft + 1 placeholder + 1 notes) ✓

Root cause: **browser cache + stale disk cache**. Cache header sebelumnya `Cache-Control: private, max-age=300` (5 min). User test setelah fix dalam window 5 menit dari test sebelumnya → browser serve PDF lama dari memory cache.

Plus: disk cache di `storage/app/pdf_cache/arsip_*.pdf` pakai cache key dari data arsip saja (tidak include versi service). Kalau code service berubah tapi data arsip tetap → cache key sama → PDF lama yg ke-return.

### Yang dieksekusi

**A. Service versioning** (`ArsipLampiranService.php`)
- Tambah `const SERVICE_VERSION = 2;`
- `buildCacheKey()` include `'v' . self::SERVICE_VERSION` di awal parts array.
- Setiap kali ada perubahan signifikan di flow (tambah cover page, ubah urutan append, dll), bump SERVICE_VERSION → semua cache lama auto-invalidate (key berbeda).

**B. Tight no-cache headers** (`streamFromCache`)
```php
'Cache-Control' => 'private, no-cache, must-revalidate',
'Pragma' => 'no-cache',
'Expires' => '0',
'ETag' => md5_file($cachePath),
'X-Service-Version' => self::SERVICE_VERSION,
```

Browser sekarang WAJIB revalidate setiap request. ETag = MD5 file → kalau PDF tidak berubah, server bisa return 304 (efficient). Kalau PDF baru di-generate (cache miss / version bump), browser dapat content baru.

### Verifikasi
- PDF cache di-flush: `rm -f storage/app/pdf_cache/*.pdf`
- Tinker re-test 3 arsip: semua hasil sesuai expected page count.

### TODO sesi berikutnya
- User hard-refresh browser (Ctrl+Shift+R) untuk clear browser cache sebelum test.
- Verifikasi visual: buka Show Document untuk arsip dgn lampiran → halaman placeholder kuning "LAMPIRAN TERLAMPIR" muncul setelah draft.

---

## 2026-06-26 — Fix Show Document: lampiran encrypted di-skip → cover-page fallback + try-decrypt

**User report:** "untuk show document nya lampirannya tidak muncul" — lampiran PDF tidak muncul di merged PDF Show Document.

### Root cause

Tinker test pada arsip ID 722 (1 lampiran, file ada di disk, ukuran 62KB):
```
PDF header: %PDF-1.6
FPDI ERROR: This PDF document is encrypted and cannot be processed with FPDI.
```

Lampiran-nya **terenkripsi** (security flag, bahkan tanpa password). FPDI v2 free **tidak support PDF terenkripsi**. Di kode lama, exception ini di-`catch (PdfReaderException) { continue; }` → silent skip → lampiran tidak muncul di output, user kira hilang.

Sumber encrypted PDF: typical dari export Word/Office, scanner driver, PDF dari Google Drive/OneDrive, dll. Punya bit security walaupun bisa dibuka tanpa password.

### Solusi (3-tier fallback)

**Tier 1: FPDI langsung** — kalau PDF kompatibel, append normal.

**Tier 2: Shell decrypt** — bila FPDI throw "encrypted/security", coba decrypt via shell:
1. `qpdf --decrypt $in $out` (paling reliable)
2. `gswin64c` / `gswin32c` / `gs -sDEVICE=pdfwrite ...` (ghostscript fallback)
3. Deteksi `where`/`which` lebih dulu — kalau tool tidak ada di server, skip ke tier 3.

Output file di-cleanup setelah merge.

**Tier 3: Cover page placeholder** — render halaman A4 via dompdf (new view `print/lampiran_placeholder.blade.php`) berisi:
- Badge "LAMPIRAN TERLAMPIR" kuning
- Original filename + ukuran + hash sha256 + tgl upload + keterangan
- Reason box merah: "PDF terenkripsi/proteksi sehingga tidak dapat di-merge inline"
- Hint biru: cara download manual + save ulang tanpa proteksi

User TETAP melihat lampiran muncul di merged PDF (sebagai cover page), bukan hilang silent.

### Verifikasi
Tinker test arsip 722 (1 lampiran encrypted):
- BEFORE: merged PDF = 1 page (draft saja)
- AFTER: merged PDF = 2 page (draft + cover placeholder utk lampiran encrypted)

### File yang berubah (2 + 1 baru)
- `app/Services/ArsipLampiranService.php` — refactor: `appendLampiranSafely()` orchestrator, `tryDecryptPdf()` shell-tool wrapper, `appendPlaceholder()` cover renderer
- `resources/views/print/lampiran_placeholder.blade.php` (NEW) — cover template
- `resources/views/print/arsip_draft.blade.php` — fix deprecation `trim(null)` → cast `trim((string) $arsip->keterangan)` dll (PHP 8.1+ no longer accepts null in `trim()`)

### TODO sesi berikutnya / prod deploy
- **Install qpdf di server prod** (Trikasa/Inkasa) supaya tier-2 decrypt jalan: `apt install qpdf` (Linux). Setelah itu lampiran encrypted akan auto-decrypt inline, bukan cover page.
- Tambah validasi di upload (admin/superadmin `uploadLampiran`): warn user kalau PDF terenkripsi, sarankan re-save tanpa proteksi sebelum upload. (Opsional — placeholder cover sudah cukup informatif.)

---

## 2026-06-25 (lanjutan #7) — Main draft breathing room + Bundle force 3/page via wrapper

User feedback:
1. **Main draft Show Document**: footer "Dicetak pada... — IT Submissions" terlalu mepet ke TTD validation strip di atasnya. Mau breathing room.
2. **Bundle Show Document**: masih kacau — render hanya 2 form/page bukan 3, walaupun harusnya muat. 6 form → 3 page bukan 2.

### A. Main draft: wrap naik 5mm utk breathing room ke _print_footer

Sebelum: `.footer-section-wrap { bottom: 5mm }`. `_print_footer` fixed `bottom: 4mm` dari page. Wrap content sampai ke `bottom: 5mm` container, kalau wrap ~50mm tinggi → wrap top ≈ 227mm. _print_footer di 293mm. Tapi visual user lihat mepet (mungkin wrap rendering lebih tinggi karena ttd-validation strip wrap ke 2 baris).

Sekarang: `bottom: 5mm` → `bottom: 10mm`. Wrap naik 5mm → gap dgn _print_footer bertambah 5mm.

Compensate budget:
- `BUDGET_RULED` 26 → **24** (lose 2 ruled-line, save 12mm)
- Adjust `tindakanLines` masing-masing turun 1 (10→9, 9→8, 8→7, 7→6, 6→5, 5→4). `keteranganLines` 6→5 untuk 0-2 item, dst.

Net: wrap naik 5mm, save 12mm dari ruled budget → 7mm extra buffer di bawah ruled & atas signature.

### B. Bundle: force 3 form per page via `.bundle-page` wrapper

Sebelum: tiap `.form-block` di-loop datar dgn `page-break-inside: avoid`. dompdf cenderung push form ke page baru kalau total tinggi 3 form mendekati page-height → 2 form/page only.

Sekarang: wrap setiap 3 form ke dalam `<div class="bundle-page">`. CSS:
```css
.bundle-page + .bundle-page { page-break-before: always; }
```

Effect: paksa page-break ANTARA grup 3-form, bukan antara form individu. Lebih deterministik. dompdf wajib mulai page baru setiap `.bundle-page` baru.

Loop structure:
```blade
@for ($p = 0; $p < $pageCount; $p++)
    <div class="bundle-page">
        @for ($idx = 0; $idx < 3; $idx++)
            ...form-block...
        @endfor
    </div>
@endfor
```

$pageCount = ceil($displayFormCount / 3). $displayFormCount selalu kelipatan 3 (existing logic `max(3, ceil($totalChunks / 3) * 3)`), jadi tiap page diisi tepat 3 form.

Plus: `signature-table td { overflow: hidden; }` — kalau ada signer name + ts + role label semua tampil + content > 18mm, di-clip supaya td tidak expand.

Cut-line condition di-update: muncul kalau `$idx < 2` (antar form dlm satu page), bukan setiap form non-terakhir.

`page-break-inside: avoid` di form-block dihapus karena `.bundle-page` sekarang yg handle pagination.

### File edited (2)
- `resources/views/print/arsip_draft.blade.php` (wrap bottom 5→10mm, BUDGET 26→24, Adjust budget rebalance −1)
- `resources/views/print/arsip_draft_bundel.blade.php` (bundle-page wrapper + page-break, sig td overflow:hidden)

---

## 2026-06-25 (lanjutan #6) — Bundle: SELALU tampilkan role label (Departemen IT/Manager Production) + signer name di bawah

User feedback dari hasil lanjutan #5: setelah doc di-TTD digital, label role "Departemen IT" / "Manager Production" hilang — diganti dgn signer name (mis. "FAHMI"). User: "jangan hilangkan Department IT nya".

### Yang dieksekusi

`$renderSig($sig, $roleLabel)` di-refactor → dua slot terpisah:
- **Role label** (selalu tampil, underlined bold, 10px): "DEPARTEMEN IT" / "MANAGER PRODUCTION" / nama pemohon.
- **Signer name** (hanya saat signed & beda dari role label, italic 7.5px): nama orang yang TTD digital (mis. FAHMI sbg signer dari role IT).
- **Timestamp** (hanya saat signed, italic 6.5px).

Logic: signer name di-skip kalau identik dgn role label (mis. case Pemohon dimana role label = nama pemohon = signer name).

Layout final per kotak signature:
```
[YANG MENGETAHUI]            ← section title
[QR / wet-sign space]         ← TTD anchor
[DEPARTEMEN IT]               ← role label (selalu, underlined)
FAHMI                         ← signer name (italic, kalau signed & beda)
25/06/2026 13:16 WIB          ← timestamp (kalau signed)
[( TTD & Nama Jelas )]
```

### CSS baru
- `.sig-signer { font-size: 7.5px; color: #1e293b; font-style: italic; font-weight: 600; }`

### File edited (1)
- `resources/views/print/arsip_draft_bundel.blade.php`

---

## 2026-06-25 (lanjutan #5) — Bundle: revert posisi QR/TTD → kembali di ATAS nama

Salah interpretasi feedback "(lanjutan #3 → #4)": user bilang "ttd ada diatas nama bukan dibawahnya" — saya kira itu keluhan, jadi pindah TTD ke bawah nama. Sekarang user clarify: "seharusnya barcode ada diatas nama/label departemen IT, manager Produksi" → barcode/QR memang harus di ATAS, di-anchor sebagai TTD digital di atas nama (konvensi formal Indonesia).

### Yang dieksekusi (`renderSig` helper di-revert urutan)
Sebelum (lanjutan #4):
```
[NAMA underlined]
[sig-stamp: QR + timestamp]
```
Sekarang:
```
[sig-stamp: QR atau empty wet-sign space]   ← QR/TTD anchor di atas
[NAMA underlined]                            ← nama jelas di bawah
[timestamp (kalau signed)]
```

Fallback (belum TTD digital): `sig-stamp` tetap kosong dgn `min-height: 9mm` → kasih ruang vertical buat wet-sign manual, lalu nama besar underlined di bawahnya. Konsisten antara digital (QR di atas) dan wet-sign (space di atas).

CSS adjust: `.sig-stamp` tambah `padding-top: 1mm`; `.sig-name` `margin: 1mm 0 0` (was `1px 0 1mm`).

### File edited (1)
- `resources/views/print/arsip_draft_bundel.blade.php`

---

## 2026-06-25 (lanjutan #4) — Bundle: NAMA on top + TTD below + fix overflow form 3

**Feedback user dari render lanjutan #3:**
1. "ttd ada diatas nama bukan dibawahnya" — di kotak signature, area TTD muncul di atas nama (margin-top: 6mm pada `.sig-name` membuat name terdorong ke bawah). User mau NAMA di atas, TTD area / QR di bawahnya.
2. "terpotong" — Form ke-3 dari bundle tidak muat di A4 (overflow ke page 2 menampilkan header tabel "BERAT STD ISI" lagi).

### Estimasi overflow

A4 portrait = 297mm. `@page margin: 6mm 8mm` → usable 285mm tinggi.
Per form-block (sebelum fix):
- header table ~22mm
- date row ~6mm
- main table (header 12mm + 5 baris × 6mm) ~42mm
- signature table 22mm
- form-footer 5mm
- cut-line + margin 5mm
- **Total ≈ 102mm**

3 form × 102mm = **306mm** > 285mm → **overflow 21mm** (≈ form 3 terpotong di area signature).

### Yang dieksekusi

**A. Reorder signature: NAMA di atas, TTD/QR di bawah** (`renderSig` helper)
Sebelum:
```
[sig-stamp: QR + name + ts]   ← QR di atas, name di bawah
```
Sekarang:
```
[sig-name: NAMA underlined]    ← nama dulu
[sig-stamp: QR + ts]            ← TTD area di bawah
```
Untuk fallback (belum TTD): hanya nama besar underlined, sig-stamp area kosong dgn `min-height: 9mm` untuk wet-sign space (jika dicetak fisik).

**B. Shrink semua dimensi supaya 3 form muat A4** (target 91mm/form × 3 = 273mm ≤ 285mm):
- `@page` margin: 6mm 8mm → **5mm 7mm** (gain 2mm tinggi)
- header-title: 14px → 13px
- header-sub: 8px → 7.5px; header-meta-value: 10px → 9.5px
- info-table td padding: 2px → 1.5px, font 8.5px → 8px
- date-row: tambah `font-size: 9.5px`
- main-table td: padding 3px → 2px, height 15px → 12px, tambah `font-size: 9px`
- main-table th: font 8.5px → 8px
- signature-table td: height 22mm → **18mm**
- sig-title: 9px → 8.5px
- sig-stamp img: 38px → 34px
- sig-ts: 7px → 6.5px
- sig-note: 7.5px → 7px, margin-top 2px → 1mm
- form-footer: 8.5px → 7.5px, padding 2mm/1mm → 1mm both, line-height 1.2
- cut-line: margin 2mm → 1mm, height: 1px
- form-block: margin-bottom 2mm → 1mm, +`page-break-inside: avoid`

Total per form sekarang ~92mm. 3 × 92 = 276mm ≤ 285mm. ✓ Fits.

### File edited (1)
- `resources/views/print/arsip_draft_bundel.blade.php`

---

## 2026-06-25 (lanjutan #3) — Bundle TTD underline + per-form footer + Adjust rebalance budget

User feedback dari hasil render:
1. Bundle signature nama (Pemohon/Manager/IT) tidak ke-underline seperti style asli — user mau spt screenshot lama: "Happy" bold underlined besar.
2. Footer "Dicetak pada..." cuma sekali per A4 page → user mau **3 kali per A4** (1 per form, 3 form = 3 footer).
3. Untuk Adjust, TINDAKAN section "terlalu ke atas" — nempel langsung di bawah tabel items + CATATAN (cuma 2 ruled-line filler). Mau ditarik ke bawah.

### A. Bundle signature underline (`print/arsip_draft_bundel.blade.php`)

`.sig-name` sebelumnya: 8.5px, no underline (style modern minimalis). Sekarang:
- Default fallback (belum TTD digital): font **10px bold UNDERLINED** dgn `text-underline-offset: 2px`, `letter-spacing: 0.3px`, `margin-top: 6mm` (kasih ruang di atas spt area wet-sign).
- Saat sudah ada TTD digital (QR muncul): pakai variant `.sig-name.has-qr` → 8.5px tanpa underline (QR jadi anchor utama, nama support).

Helper `$renderSig($sig, $fallbackName)` di-tweak: fallback no-sig sekarang langsung render `<div class="sig-name">NAMA</div>` (tanpa placeholder text), dgn QR variant pakai class `.has-qr`.

### B. Per-form footer di bundle

`@include('partials._print_footer')` global dihapus (itu yg bikin footer cuma di bottom A4 dgn `position: fixed`). Sekarang setiap `.form-block` punya footer inline `.form-footer` di bawahnya:

```
Dicetak pada <tgl>, <jam> oleh <user> ~ IT Submissions ~
```

Style: italic, 8.5px, centered, padding 2mm. Hasilnya 3 form per A4 = 3 footer (di bawah masing-masing form, di atas cut-line). `printedFooterDate` + `printedFooterUser` di-resolve sekali di awal supaya konsisten.

### C. Adjust TINDAKAN rebalance (`print/arsip_draft.blade.php`)

Sebelum: `$keteranganLines = 2` (hard-coded untuk Adjust). Hasilnya CATATAN filler cuma 2 ruled-line (12mm) → TINDAKAN section langsung muncul di posisi ~92mm dari atas → terlalu nempel ke items table.

Sekarang: rebalance keterangan ↔ tindakan secara dinamis per item count:
- 0-2 item: `keterangan=6, tindakan=10` (was 2/14)
- 3-4: `keterangan=5, tindakan=9`
- 5-6: `keterangan=4, tindakan=8`
- 7-8: `keterangan=3, tindakan=8`
- 9-11: `keterangan=3, tindakan=7`
- 12-14: `keterangan=2, tindakan=6`
- 15+: `keterangan=1, tindakan=5`

Total ruled-line tetap sama (≈16 → ≈6 sesuai item density), tapi distribusi bergeser → CATATAN ruled lebih banyak → TINDAKAN section turun ~25mm.

### File edited (2)
- `resources/views/print/arsip_draft_bundel.blade.php` (underline + per-form footer)
- `resources/views/print/arsip_draft.blade.php` (Adjust budget rebalance)

### Catatan / TODO
- User juga sebut "tindakan IT tidak keluar" — kemungkinan data `tindakan_it_rows` belum tersimpan / belum dirender saat doc baru. Perlu verifikasi flow save Tindakan IT di superadmin update + render di printDraft. Defer ke sesi berikutnya kalau setelah test masih kosong.

---

## 2026-06-25 (lanjutan #2) — Revert 2-col no_transaksi → kembali single-stack rapi

User reject hasil 2-column rendering: "malah jadi seperti itu, garis itu jangan diubah ubah, agar tertata rapi". Masalah visual:
1. **Stagger**: group lines beda jumlah per group (3, 4, 6, 5) → kolom kiri & kanan tidak sejajar, garis baseline pecah-pecah.
2. **Text rendered RED** di sel-sel tabel kolom (kemungkinan PDF viewer auto-link pattern slash `/` di "MO/R-PC/..." → di-highlight merah).

User mental model awal: setiap group simetris 2-baris (MO + INK). Real data: group bervariasi 2-6 baris → 2-kolom layout pecah.

### Yang dieksekusi
- **Revert ke single-column stack** untuk no_transaksi. Tiap line dirender sbg `.ruled-line` berurutan top-to-bottom. Antar group dipisah satu `.ruled-line` kosong (jaga baseline tetap konsisten 22px grid, garis tidak skip).
- **Tambah `color: #000` eksplisit** di tiap `.ruled-line` no_transaksi → cegah PDF viewer auto-color slash pattern jadi merah.
- **Update `usedRuledLines` counter**: 1 (label) + sum(lines per group) + (n_groups - 1) (separator) — match dgn yg dirender.

### File edited (1)
- `resources/views/print/arsip_draft.blade.php`

---

## 2026-06-25 (lanjutan) — Cancel Draft 2-col + Pagination Unlimited + Produk Baru Toggle

**3 request user:**
1. Cancel draft: kalau No. Transaksi punya banyak group induk, **render side-by-side 2 kolom**, jangan ditumpuk vertikal (boros space).
2. Pagination admin/superadmin arsip index: tambah opsi **"Unlimited"**.
3. **Toggle on/off fitur Produk Baru** + dashboard/sidebar/form/popup mengikuti state-nya.

### A. Cancel draft — 2-kolom no_transaksi (`print/arsip_draft.blade.php`)

Sebelum: setiap baris no_transaksi (mis. `MO/PF/...` + `INK/PR/...`) dirender stack vertikal `.ruled-line`. Untuk 4 group × 2 baris = 8 baris vertikal → makan ruang besar.

Sekarang: bila `count($trxGroups) > 1` → render via `<table>` 2 kolom 50/50, column-first split-half. Group 1..ceil(n/2) di kiri, sisanya di kanan. Tiap group di-separasi spacer 6px. Hasilnya: 4 group cuma butuh 2 row × 2 baris/group = 4 baris vertikal (half).

`usedRuledLines` counter ikut di-adjust: bila multi-group → counted sebagai `ceil(total/2) + 1` (label) bukan `total`. Budget calc tetap akurat → tidak overflow.

### B. Pagination "Unlimited" (admin + superadmin arsip index)

- View: tambah `<option value="all">Unlimited</option>` di `#perPageSelect`.
- Controller: parse `per_page`. Bila `'all'` → set `$perPage = 99999` (tetap pakai `paginate()` supaya UI links tidak break). Else cast int.

### C. Feature flag "Produk Baru" — toggle on/off + cascade

**Setting baru:** `produk_baru_enabled` (default `'1'`). Disetel via Pengaturan Aplikasi (Superadmin only).

**Wiring:**
1. `AppServiceProvider`: view composer share `$produkBaruEnabled` (boolean) ke semua view.
2. `superadmin/settings/index.blade.php`: tambah card toggle switch dgn label state ("AKTIF" hijau / "DINONAKTIFKAN SEMENTARA" merah). Hidden `value=0` untuk handle unchecked-state submission.
3. `superadmin/SettingController@update`: validate `produk_baru_enabled in:0,1`, write ke settings table.
4. **Form create** (admin & superadmin `_create.blade.php`): `<option value="Produk_Baru">` di dropdown `jenis_pengajuan` di-wrap `@if(!empty($produkBaruEnabled))`. Edit form tetap punya option (untuk display data lama).
5. **Filter dropdown** (admin & superadmin `arsip/index.blade.php`): jenis list `Produk_Baru` ikut wrap conditional.
6. **Dashboard admin**: section "Pengajuan Produk Baru" 3-card wrap `@if`. Bila disabled → tampilkan **alert kuning** "Fitur dinonaktifkan sementara". Card "PRODUK BARU" di "Statistik per Jenis" juga hide.
7. **Dashboard superadmin**: sama, plus alert dgn link langsung ke `superadmin.settings.index`.
8. **Backend guard** (admin & superadmin store + API `storePengajuan`): tolak request `jenis_pengajuan = Produk_Baru` bila setting `produk_baru_enabled !== '1'`. Web: `back()` + flash error. API: 403 JSON.

**Sidebar:** menu Produk Baru sudah ter-comment manual sejak sebelum sesi ini di kedua sidebar. Tidak diubah (sudah hide by default). Bisa di-restore wrap `@if($produkBaruEnabled)` di sesi berikutnya kalau perlu auto-show saat enabled.

### Verifikasi
- `php artisan view:clear && view:cache` → semua compile OK.
- PDF cache dibersihkan.
- Tinker / smoke test: tidak dilakukan (UI-driven feature, perlu manual test).

### File edited (12)
- `resources/views/print/arsip_draft.blade.php` (2-col no_transaksi)
- `resources/views/admin/arsip/index.blade.php` (pagination "all" opt + filter conditional)
- `resources/views/superadmin/arsip/index.blade.php` (sama)
- `app/Http/Controllers/Admin/ArsipController.php` (perPage parse + Produk_Baru guard)
- `app/Http/Controllers/Superadmin/ArsipController.php` (sama)
- `app/Http/Controllers/Api/ArsipApiController.php` (Produk_Baru guard 403)
- `app/Providers/AppServiceProvider.php` (share `$produkBaruEnabled`)
- `app/Http/Controllers/Superadmin/SettingController.php` (toggle field)
- `resources/views/superadmin/settings/index.blade.php` (toggle UI + JS state-swap)
- `resources/views/admin/arsip/_create.blade.php` (Produk_Baru option conditional)
- `resources/views/superadmin/arsip/_create.blade.php` (sama)
- `resources/views/admin/dashboard/index.blade.php` (3-card + statistik conditional + alert)
- `resources/views/superadmin/dashboard/index.blade.php` (sama)

### TODO sesi berikutnya
- (Opsional) Restore sidebar menu "Daftar Produk Baru" wrap `@if($produkBaruEnabled)` supaya auto-show saat enabled.
- (Opsional) Mobile/Android API: serialize state `produk_baru_enabled` di response `getMasterData` supaya Android client juga bisa hide opsi.
- Test manual: toggle off → submit form Produk_Baru harus dapat alert error; toggle on kembali → option muncul lagi.

---

## 2026-06-25 — Fix Overflow Page 2 Draft (BUDGET_RULED revert + page-break guards)

**Konteks:** user kirim 6 screenshot PDF hasil cetak draft. Visual:
- Adjust 17 items, Adjust 3 items, Mutasi Billet, Mutasi Produk, Internal Memo, Bundel.
- Per-line garis keterangan **muncul** ✓ (fix sesi sebelumnya jalan).
- Tapi: signature header "Diketahui Oleh," / "Tin..." muncul ter-potong di bawah footer "Dicetak pada..." pada **5 dari 6** screenshot. Artinya konten overflow ke halaman 2.

**Root cause:** sesi sebelumnya saya bump `BUDGET_RULED` 28→32 (non-Adjust) dan `tindakanLines` Adjust 12→16 untuk fill empty space. Hasilnya: jumlah ruled-line yang dirender bertambah → total tinggi konten flow > (277mm − 5mm bottom − ~46mm signature) = 226mm available. Signature anchored absolute, overlap dgn konten → dompdf paginate ke page 2 dan re-render signature table di sana.

Estimasi (Internal Memo dgn keterangan 1 baris + no_transaksi 1 baris, BUDGET=32):
- Header 13mm + Info 32mm + keterangan ruled (17 baris × 5.83mm) + TINDAKAN heading 9mm + tindakan ruled (15 × 5.83mm) = **240mm**.
- Available untuk content flow ≈ 226mm → **overflow 14mm** → spill ke page 2.

### Yang dieksekusi

1. **Revert `BUDGET_RULED` 32→26** (lebih konservatif dari original 28, kasih buffer 15mm di bawah ruled). Untuk IM contoh di atas: 13+32+(11×5.83)+9+(15×5.83)=205mm ≤ 226mm. ✓
2. **Reduce Adjust tindakanLines** sedikit (16→14 untuk 0-2 item; 14→12; 12→10; 11→9; 10→8; 8→6; 7→5) — kompromi antara fill-area dan anti-overflow.
3. **Tambah hard constraint anti-overflow di body**: `html, body { height: 297mm; max-height: 297mm; overflow: hidden }`. Konten yg over di-clip oleh body bukan oleh page-break.
4. **Tambah `page-break-inside/before/after: avoid`** di `.footer-section-wrap` + `.print-container`. Memberi tahu dompdf jangan break inside elemen-elemen ini.

### Verifikasi
- `php artisan view:clear && view:cache` → OK.
- `storage/app/pdf_cache/*.pdf` dibersihkan.
- Visual verification: pending — user re-render Show Document untuk konfirmasi halaman 2 hilang.

### Observasi tambahan dari screenshot
- "TANGGGERANG" (3G) pada blok signature → ini dari `Setting::get('kota_ba')` di DB. Bukan bug code; user setting. Tidak diubah.
- Bundel template render bagus: 3 form per A4, header dgn QR, signature 3 box. Nama "HAPPY/MANAGER PRODUCTION/DEPARTEMEN IT" muncul karena ini fallback bila `signatureFor()` return null (doc belum TTD digital). Ekspektasi: setelah doc disign (Pemohon → Manager → Dept IT via approval flow), kotak signature akan menampilkan QR + nama + timestamp.

### File edited (1)
- `resources/views/print/arsip_draft.blade.php`

### TODO sesi berikutnya
- User test render semua jenis (Cancel, Adjust, Mutasi_Billet, Mutasi_Produk, Internal_Memo) → semua harus 1 halaman.
- Bila masih ada gap kosong di TINDAKAN section Adjust (terutama 0-2 item), bump tindakanLines kembali +1-2 sambil tetap monitor overflow.
- Bila ada jenis spesifik yg overflow lagi, kemungkinan `usedRuledLines` counter (yg pakai `ceil(strlen/90)` untuk keterangan) underestimate baris yg long-wrap di multi-line. Bisa tighten dengan strlen/70 atau /60.

---

## 2026-06-24 (lanjutan #2) — Per-line Border Keterangan + Bundel TTD Digital + TINDAKAN Budget

**Konteks:** user lapor 4 isu dari screenshot:
1. "DESKRIPSI PERMASALAHAN" + keterangan Mutasi_Billet (Produk/LOT/Panjang/Jumlah/Note) → **tidak ada garis per baris**.
2. TINDAKAN section (Adjust empty) → **masih ada space kosong** antara ruled lines & blok TANGGGERANG signature.
3. `arsip_draft_bundel.blade.php` → **terpotong** + **belum ada approval digital** (masih hardcoded `MANAGER PRODUCTION` / `EDP`).
4. Klik tombol Edit → data tidak muncul di modal.

### A. Per-line border-bottom untuk keterangan multi-line (`arsip_draft.blade.php`)

Sebelum: `<div class="ruled-content">{{ trim($arsip->keterangan) }}</div>` → satu blok dengan satu border-bottom di bawah. Multi-line text tidak punya garis per baris.

Sekarang: split via `preg_split('/\r\n|\r|\n/', ...)` dan render tiap baris sebagai `<div class="ruled-line">...</div>` (border-bottom per baris, height 22px). Sama treatment untuk `arsip->tindakan` dan `arsip->catatan_it` di TINDAKAN section (prefix "TINDAKAN: " / "CATATAN IT: " ditempel di baris pertama saja).

### B. TINDAKAN ruled-lines budget di-bump

Adjust dengan ≤2 item: ruled tindakan 12 → **16** baris. Counts naik bertahap (3:14, 5:12, 7:11, 9:10, 12:8, 15+:7). Non-Adjust `BUDGET_RULED` 28 → **32**. Penyebab gap kosong sebelumnya: arsitektur signature absolute-bottom + content flow di atas → kalau content tinggi total < (container − signature), ada whitespace di tengah. Kalkulasi: container 277mm − padding 18mm = 259mm tersedia; signature ~41mm dianchor di bawah → 218mm utk content. Adjust 0-item: header 50px + info 120px + adjust table 100px + ruled 12×22 = 264px + tindakan table 70px → ~689px ≈ 182mm. Sisa 36mm → ~6 ruled-line. Karena itu bump tindakanLines +4.

### C. Bundel template ditulis ulang (`arsip_draft_bundel.blade.php`)

Sebelum (banyak masalah):
- Hardcoded "MANAGER PRODUCTION" / "EDP" sebagai nama TTD (tidak ada link ke `arsip_signatures`).
- Mini QR dirender via `qrcodejs` CDN (JS) → **blank di dompdf** (dompdf tidak execute JS).
- Font `Inter` via Google Font CDN → dompdf juga tidak fetch (`isRemoteEnabled: false`), fallback ke serif.
- `.signature` pakai `display: flex` → tidak reliable di dompdf, alignment bisa pecah.
- `.wrapper { height: 88mm }` fixed → konten (header 33mm + date 5mm + main-table ~40mm + signature 30mm = 108mm) **terpotong**.

Sekarang:
- Font: `DejaVu Sans` (konsisten dgn `arsip_draft.blade.php` & dompdf-friendly).
- QR Verifikasi **server-rendered** via `QrSignatureService::renderDocumentQrDataUri($arsip, 120)` per form (no JS).
- TTD digital: `$arsip->signatureFor('Pemohon')` → YANG MEMBUAT, `signatureFor('Manager')` → YANG MENYETUJUI, `signatureFor('Departemen IT')` → YANG MENGETAHUI. Tiap kotak render QR signature + nama + timestamp WIB (atau placeholder pending bila belum TTD). Helper `$renderSig($sig, $fallbackName)` reusable + fallback ke nama default kalau TTD belum ada.
- Signature pakai `<table class="signature-table">` (bukan flex) — dompdf reliable.
- `.wrapper` tanpa fixed height → content flow natural; cut-line ✄ di antara form (tiap 3 form per A4).
- Meta-bar lama (Printed date / User / no_reg) dihapus → redundan dgn `_print_footer`.
- Drop CDN `qrcodejs` + script init `<script>` di akhir body.

### D. Edit modal "data tidak muncul" — **ROOT CAUSE FOUND & FIXED**

**Reproduksi**: user lapor terjadi di superadmin. Awalnya dikira JS error.

**Investigasi**:
- Route `superadmin.arsip.edit` ter-resolve OK (HTTP 200, payload `data` dgn 51 keys).
- Eager-loads lengkap (`adjustItems`, `mutasiItems`, ..., `tindakanItems`, `requesters.user`).
- Endpoint tinker test → response shape sehat, semua field ada.
- Field IDs JS vs `_edit.blade.php` → seharusnya cocok.

**ROOT CAUSE**: `superadmin/arsip/_edit.blade.php` punya **2 elemen `<textarea>` dengan `id="editKeterangan"` yang sama**:
- Line 181 (lama): di dalam `#sectionAdjustExtraEdit` ("Deskripsi Masalah" — Adjust-only, default `d-none`).
- Line 314 (lama, sekarang 317): di section utama "Keterangan" — visible untuk semua jenis.

HTML tidak boleh punya duplicate ID. jQuery `$('#editKeterangan').val(data.keterangan)` hanya set ke elemen PERTAMA di DOM (yang ada di section Adjust hidden). Visible textarea utama dapat string kosong → user lihat field keterangan empty padahal datanya ada di textarea yang ke-hide.

**Fix**: hapus `<textarea id="editKeterangan">` duplikat di section `#sectionAdjustExtraEdit`. Section sekarang berisi info-banner kecil: "Deskripsi Masalah Adjustment dapat diisi di kolom Keterangan di bawah — konten akan muncul sebagai CATATAN pada output draft." (info-style, tidak ada form input). Section tetap toggle visible bila jenis=Adjust agar user paham hubungannya.

**File edited**: `resources/views/superadmin/arsip/_edit.blade.php`.

**Catatan**: Admin `_edit.blade.php` punya 1 `editKeterangan` saja (clean). Issue hanya di superadmin.

### Verifikasi
- `php artisan view:clear && view:cache` → semua blade compile clean.
- `storage/app/pdf_cache/*.pdf` dibersihkan (next Show Document akan re-render dgn template baru).

### File edited (2)
- `resources/views/print/arsip_draft.blade.php`
- `resources/views/print/arsip_draft_bundel.blade.php` (rewrite)

### TODO sesi berikutnya
- Verifikasi visual: render PDF Mutasi_Billet, Internal_Memo, Cancel (multi-line keterangan) → garis per baris harus muncul.
- Verifikasi visual: render PDF Adjust 0/1/2-item → gap di TINDAKAN harus hilang (ruled fill ke bawah).
- Verifikasi visual: render PDF Bundel — QR verifikasi muncul per form, signature 3 box (Pemohon/Manager/IT) render dgn QR + nama + tgl bila sudah TTD, atau fallback nama bila belum.
- Edit modal: minta user buka console & kirim error JS untuk diagnosis.

---

## 2026-06-24 (lanjutan) — Cleanup Layout Draft Print (refactor CSS + buang dead code)

**Konteks:** user lapor "tata letak draft masih berantakan". Layout arsitektur (signature absolute-bottom + ruled budget adaptive) dari sesi pagi sudah verified 1-page. Yang berantakan: kode template-nya — banyak inline-style, dead-code, dan struktur HTML rusak.

**Yang dibenahi di `resources/views/print/arsip_draft.blade.php`:**

1. **Bug HTML — stray `</div>`**: ada `</div>{{-- /.doc-section --}}` di akhir (sisa refactor lama) yang tidak match opening tag manapun → bikin DOM tree miring. **Dihapus.**
2. **Dead code dibuang**:
   - `<script src=".../qrcode.min.js">` CDN — tidak dipakai (QR sekarang server-rendered via `QrSignatureService`).
   - Block `<script>` di akhir body yg manggil `new QRCode(...)` untuk elemen `#qrcode`/`#regQrcode` yang sudah tidak ada → JS error silent.
   - Block META BAR yg di-comment (printed date/user/no_reg) — sudah pindah ke `_print_footer`.
   - `<hr class="separator">` yang di-comment.
   - Class `.ruled` yg di-define tapi tidak pernah dipakai (hanya `.ruled-line` & `.ruled-content`).
   - `display: block` redundan di `.print-container`.
   - Block `@media print` yang re-deklarasi `.print-container` / `.footer-section-wrap` dengan nilai yang sama dgn screen → buang, simpan hanya `print-color-adjust` + `.no-print`.
3. **Inline-style → CSS class** (cleanliness):
   - Header: tambah class `.no-doc-line`, `.qr-label.verify`, `.qr-label.reg`.
   - Info-table: class `.label/.colon/.value/.desc-row`.
   - Section heading: `.section-title`, `.section-note`, `.section-catatan`.
   - Signature stamp: `.sig-stamp`, `.sig-name`, `.sig-ts`, `.sig-pending`, `.sig-role`, `.sig-hint`.
   - Footer date: `.footer-place-date`.
   - TTD validation footer: `.ttd-validation`, `.ttd-text`, `.ttd-token`, `.ttd-check` (sebelumnya satu div dengan 7 inline rules).
   - Trx ruled rows: `.trx-label`, `.trx-line`.
   - Main table sel kiri: `.cell-left` (ganti `style="text-align:left"` repeating).
4. **Background tipis di header tabel** (`#f7f7f7` di `.main-table th` dan `.signature-table th`) — sedikit kontras, tetap subtle untuk dompdf.
5. **Compact level styles disinkronkan**: level 1/2/3 sekarang juga override `.ruled-line` line-height (sebelumnya cuma override `.ruled`, kelas yg tidak dipakai). Level 2/3 dapat `height: 18/16px` agar ruled line ikut shrink bareng text.
6. **`body { line-height: 1.35 }`** ditambah supaya tipografi info-table/keterangan tidak terlalu rapat.
7. **Hapus `<div>` pembungkus ganda di footer** (sebelumnya `footer-section-wrap > footer-section > date-div + signature-table + ...`). Sekarang flat: `footer-section-wrap` langsung berisi date + signature-table + notes.
8. **`$renderSig` helper** dirapikan: pakai class `.sig-stamp` (sebelumnya inline `style="text-align:center;line-height:1;"` + inline rules tiap baris). Output HTML lebih pendek, easier to maintain.

**Tidak diubah** (sudah verified sesi pagi):
- `height: 277mm` + `padding: 13mm 12mm 5mm 12mm`.
- `footer-section-wrap` `position: absolute; bottom: 5mm` + `background:#fff; z-index:100`.
- Ruled-lines budget calculation (`$BUDGET_RULED = 28`, tindakan 60%, adaptive untuk Adjust).
- Field/data yg di-render (semua signature roles, QR, watermark VOID/REJECT, dst).

**Verifikasi:**
- `php artisan view:clear && php artisan view:cache` → "Blade templates cached successfully" (no compile error).
- `storage/app/pdf_cache/*.pdf` dibersihkan agar Show Document re-render dengan template baru.

**Belum dikerjakan / TODO sesi berikutnya:**
- Verifikasi visual semua 7 jenis (Cancel berbagai variasi, Internal_Memo, Mutasi_Billet, Mutasi_Produk, Adjust 1-item & 14-items) masih 1-page setelah cleanup. Arsitektur tidak berubah jadi expected OK, tapi belum tes visual PDF.
- Refactor sejenis untuk `arsip_draft_bundel.blade.php` (kemungkinan masih punya inline-style/dead-code yg sama).

**File edited:** `resources/views/print/arsip_draft.blade.php` (1 file).

---

## 2026-06-24 — Layout Draft Print: Signature Absolute-Bottom + Adaptive Ruled Lines + Anti Double-Sign

**Masalah utama yang diselesaikan dalam 1 sesi panjang:**

### A. Layout `arsip_draft.blade.php` — Signature menempel bawah, ruled lines adapt

Sebelumnya draft pakai `display: flex` (tidak reliable di dompdf) → kadang overflow ke 2 halaman, kadang signature di tengah halaman dengan gap kosong.

**Solusi final:**
- `.print-container { height: 277mm; position: relative; padding: 13mm 12mm 5mm 12mm; overflow: hidden; }`
- `.footer-section-wrap { position: absolute; left/right: 12mm; bottom: 5mm; background: #fff; z-index: 100; padding-top: 4mm; margin-top: -2mm; }` — signature dianchor di bawah container
- Garis ruled `keteranganLines` + `tindakanLines` di-adaptasi via budget calculation supaya mengisi area antara content dan signature
- `BUDGET_RULED = 28` (total ruled budget non-Adjust); `tindakanLines` ambil 60% dari sisa setelah trx+items+keterangan terhitung
- Untuk Adjust, `tindakanLines` selalu generous: 12 (≤2 items) sampai 6 (≥15 items)

**Bug yang juga diperbaiki:**
- **Multi-line keterangan undercounted** — `ceil(strlen/90)` underestimate kalau ada `\n` eksplisit. Fix: split per line via `preg_split('/\r\n|\r|\n/', $keterangan)`, hitung tiap baris. Mengembalikan Mutasi_Billet (keterangan 7 baris: Produk:/LOT:/Panjang:/Jumlah:/Note:) dari 2 halaman → 1 halaman.
- **Garis ruled overlap dengan "TANGGGERANG, ..."** — signature wrap kasih `background: #fff` + `z-index: 100` → masking garis ruled yang bleed dari content flow di atas.
- **Lokasi UPPERCASE** — `strtoupper($kotaBa)` + CSS `text-transform: uppercase`.

### B. Anti Double-Sign (Pop-up alert)

User minta cegah TTD digital 2x untuk role yang sama.

- **Backend** ([app/Traits/SignsArsip.php](app/Traits/SignsArsip.php)): sebelum `applySignature()`, cek `ArsipSignature::where('arsip_id', ...)->where('role_label', ...)->first()`. Kalau ada → flash warning + 409 JSON. (Sebelumnya: `updateOrCreate` silent-overwrite.)
- **Frontend admin** ([resources/views/admin/arsip/index.blade.php](resources/views/admin/arsip/index.blade.php)): per row hitung `$existingSig = $a->signatures->firstWhere('role_label', $myRoleLabel)`. Kalau ada, tombol pen→check (warna hijau), klik = `alert('⚠️ Dokumen sudah Anda tanda tangani sebagai [Role] pada [tgl]...')` + `return false`. Kalau belum, normal confirm dialog.
- **Frontend superadmin** ([resources/views/superadmin/arsip/index.blade.php](resources/views/superadmin/arsip/index.blade.php)): treatment sama untuk role "Departemen IT".

### C. Sub-issue terkait yang juga difix dalam sesi ini

- **Show-document blank page 1** — root cause: `.print-container { display: flex; min-height: 285mm; padding: 14/18mm }` membuat dompdf render 2 page (page 1 blank-ish, page 2 content). Fixed dengan pendekatan baru di atas.
- **Personal notes section overflowing draft** — keluarin dari draft, jadi lampiran terpisah ([resources/views/print/arsip_notes_attachment.blade.php](resources/views/print/arsip_notes_attachment.blade.php)) yang di-append di akhir merged PDF via `ArsipLampiranService::streamMergedPdf()`. Cache key tambah `personal_notes` timestamp.

**File-file utama yang berubah:**
- `resources/views/print/arsip_draft.blade.php` — layout container + ruled-lines logic
- `resources/views/print/arsip_draft_bundel.blade.php` — buang personal notes section (jadi lampiran terpisah)
- `resources/views/print/arsip_notes_attachment.blade.php` (NEW) — page lampiran catatan
- `app/Services/ArsipLampiranService.php` — append notes attachment di merged PDF + cache key include notes timestamp
- `app/Traits/SignsArsip.php` — anti double-sign guard
- `app/Http/Controllers/Admin/ArsipNoteController.php` — bust cache saat note CUD
- `resources/views/admin/arsip/index.blade.php` + `resources/views/superadmin/arsip/index.blade.php` — sign button conditional alert
- `resources/views/partials/_print_footer.blade.php` — restore `position: fixed`

**Verifikasi:** 7 jenis test (Cancel berbagai variasi, Internal_Memo, Mutasi_Billet, Mutasi_Produk, Adjust 1-item & 14-items) semua **1 halaman**. PDF cache di-nuke.

---

## 2026-06-10 — Hilangkan Watermark TTD Digital + Cleanup Footer Draft

**Fix paket di `print/arsip_draft.blade.php`:**
1. Watermark "TERTANDATANGANI DIGITAL" + "DONE" dihapus (dompdf render `border: 12px double` sebagai blok pink solid besar di tengah dokumen). Sisanya (VOID, REJECT) tetap tapi tipis (`rgba(...,0.10)`, tanpa border).
2. **Cleanup duplicate footer**: hapus meta-bar lama (Printed date / User / no_registrasi) — REDUNDAN dengan partial `_print_footer` ("Dicetak pada ... — IT Submissions").
3. **Compress signature box**: td 78→70px, min-height div 80→60px, QR 55→48px, font nama 8→7.5px.
4. **TINDAKAN**: default `keteranganLines`/`tindakanLines` 5→3, drop `flex-grow:1`, margin-bottom 14→8px.
5. `doc-footer-note` + TTD validation dashed line: font 8.5→7.5px (kompak).

**File edited:** `resources/views/print/arsip_draft.blade.php` (1 file).

**Verifikasi:** `view:cache` OK · PDF cache dihapus.

**Deploy:** `git push` + di server `git pull && php artisan view:cache && rm -f storage/app/pdf_cache/*.pdf`.

---

## 2026-06-09 — Fix Lampiran View Redirect Login (Auth-Protected Stream)

**Masalah:** Klik tombol View di modal Kelola Lampiran → redirect ke `/login`. Penyebab: `/storage/lampiran/...` direct link bergantung pada `php artisan storage:link` yang belum jalan di prod → middleware auth catch → redirect.

**Solusi:** Pakai controller endpoint auth-protected yang stream file langsung via filesystem path (bypass HTTP storage).

**Route baru** (admin + superadmin): `GET /{role}/arsip/{arsip}/lampiran/{lampiran}/view` → `viewLampiran()` method: find arsip + lampiran, `response()->file($absPath, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline'])`.

**listLampiran JSON** sekarang return `route('...view-lampiran')` bukan `publicUrl()`. Modal JS tidak perlu diubah.

**File edited:** 2 controller (admin/superadmin) + `routes/web.php`.

**Verifikasi:** route resolve OK · view:cache OK.

---

## 2026-06-08 (lanjutan #3) — Revert Aggressive Compress + Smart 3-Level Auto-Scale Draft

User lapor draft jadi "amburadul" karena kompresi sebelumnya agresif untuk semua kasus. **REVERT** ke layout original rapi A4 + tambah **smart `$compactLevel` 0/1/2/3** yang aktif HANYA saat items banyak:
- 0 (≤6): rapi default
- 1 (7-10): sedikit padat
- 2 (11-16): padat, font 9.5px
- 3 (>16): sangat padat, font 8.5px

`$keteranganLines`/`$tindakanLines` 5 → 2 progressive berdasar count.

Restore values: `@page` 14/11/14/11mm, `print-container min-height: 92vh`, sig-td 88px, QR sig 55×55, font tag/title kembali normal.

**File edited:** `resources/views/print/arsip_draft.blade.php`.

**Belum diselesaikan (deferred):** Lampiran direct link `/storage/lampiran/...` → redirect login. Penyebab: `php artisan storage:link` belum jalan di prod ATAU middleware. User minta fokus Show Document dulu.

---

## 2026-06-08 (lanjutan #2) — FPDI Backend + Draft 1-Page + Footer Minimal

**Fix paket:**
1. **Error `Class FPDF not found`** di prod saat Show Document → `composer require setasign/fpdf ^1.9` (backend untuk fpdi yang sebelumnya tidak ikut ter-install).
2. **Footer cetakan** disederhanakan ke 1-baris italic tengah `Dicetak pada {tgl}, {jam} oleh {user} — IT Submissions` (gradient pill style lama dihapus).
3. **Draft kompres 1 halaman A4**: `@page` margin 12/9/14/9, `print-container min-height: auto`, signature td 78px + padding 3, default `keteranganLines`/`tindakanLines` 6→3, TINDAKAN tanpa flex-grow, QR signature 58→48px + hapus baris hash (sekarang scan QR untuk verifikasi), signature td min-height 88→68px. Hasil: draft fit 1 page, lampiran otomatis dari page 2.

**File edited:** `composer.json` (+ setasign/fpdf), `partials/_print_footer.blade.php`, `print/arsip_draft.blade.php`.

**Verifikasi:** `view:cache` OK · `setasign\Fpdi\Fpdi` instantiable OK · cache PDF dibersihkan.

**Deploy:** `git push` + di server `composer install --no-dev` + `rm -f storage/app/pdf_cache/*.pdf`.

---

## 2026-06-08 (lanjutan) — Show Document Speed-Up + QR-Only TTD Digital

**A. Show Document jadi cepat:**
- **File cache** di `storage/app/pdf_cache/arsip_{id}_{hash}.pdf` — hash dari `updated_at` arsip+lampirans+signatures+approvals. Cache HIT = stream instant (~100ms), MISS = render + simpan. Auto-invalidate saat upload lampiran via `storeMany()`.
- **dompdf options**: `isRemoteEnabled: false` (no Google Font fetch), `isJavascriptEnabled: false`, `defaultFont: 'DejaVu Sans'`. Draft template juga ganti font ke DejaVu (4 occurrences).

**B. TTD Digital QR-only (no specimen):**
- Refactor `SignsArsip` + `HandlesApproval`: drop `hasSignature()` check, `applySignature()` SKIP snapshot image. Hash include `config('app.key')` pepper.
- BARU: `app/Services/QrSignatureService.php` — 3 method (`renderSignatureQrDataUri`, `renderDocumentQrDataUri`, `renderTextQrDataUri`). Pakai `endroid/qr-code ^6.0` (composer install).
- Draft template: kotak TTD render QR 58×58 + nama + tanggal + hash short. Header QR (verify + no_registrasi) pakai server-render (sebelumnya pakai qrcodejs CDN = BLANK di PDF karena dompdf tidak execute JS).
- Profile partial `_signature_specimen.blade.php` REWRITE jadi info card "QR-Based, tidak perlu upload gambar".

**File baru:** `app/Services/QrSignatureService.php`.

**Verifikasi:** `view:cache` OK · `php -l` 4 file OK · QR test PNG base64 OK.

---

## 2026-06-08 — Fix Font Dir + Pagination Bug + Server Stats Inovasi

**3 fix paket:**
1. **dompdf font dir**: bikin folder `storage/fonts/` (+ `.gitkeep`) — fix `fopen failed` saat render Show Document. Config `font_dir`/`enable_remote` sudah benar, hanya folder yang absent.
2. **Pagination bug** (chevron raksasa di activity-logs): Laravel 11 default pakai Tailwind pagination, project pakai Bootstrap 5. Tambah `Paginator::useBootstrapFive()` di `AppServiceProvider::boot()` → semua `links()` global pakai Bootstrap 5 markup. Polish CSS pagination (gradient indigo active, hover indigo-soft, rounded 8px).
3. **Server Stats** rewrite: controller tambah `metrics()` JSON endpoint untuk live poll (5s) + data lengkap (CPU load + cores, memory + limit, uptime via /proc/uptime, table breakdown TOP 10, storage breakdown, top users, traffic 14 hari, queue health, PHP extensions). View pakai Chart.js (dual-line live CPU/Mem + bar traffic), 4 KPI gradient cards (indigo CPU, cyan Mem, green Disk, orange DB), status dot pulse OPERATIONAL/HIGH UTILIZATION, dan tabel/storage/top users/software/queue/extensions card.

**Route baru:** `GET /superadmin/server-stats/metrics` → `superadmin.server-stats.metrics`.

**Verifikasi:** `view:cache` OK · `php -l` OK · route resolve OK.

**Detail penuh + TODO:** `SESSION_LOG.md`.

---

## 2026-06-06 — Multi-PDF Lampiran + Show Document (Merged) + Cert-Style Verify

**3 paket:**
1. **Multi-PDF lampiran**: tabel baru `arsip_lampiran` (file_path, file_hash sha256, mime_type, page_count, sort_order). Model `ArsipLampiran` + relation `Arsip::lampirans()`. Modal `_lampiran_modal` rewrite: list tersimpan + form upload `multiple accept=".pdf"` + AJAX list/delete.
2. **Show Document** (Draft + Lampiran → 1 PDF): pakai `setasign/fpdi` + `barryvdh/laravel-dompdf`. Service `ArsipLampiranService::streamMergedPdf()` render draft via dompdf, lalu FPDI append seluruh halaman draft + tiap lampiran. Stream inline `application/pdf`. Eye-menu di kolom aksi sekarang 3 item: **Show Document** (hijau, ikon PDF), Print Draft saja (biru), Kelola Lampiran (ungu).
3. **Cert-style verify page** ala Makarya One: cert card 84px hijau patch-check + 6 row (Ditandatangani oleh / Jabatan-role / Level Approval / Tanggal TTD / Hash gradient pink / Dokumen). Doc summary card + seluruh TTD + alur persetujuan (color-coded per step).

**Routes baru** (admin + superadmin, masing-masing 4): `upload-lampiran`, `list-lampiran`, `delete-lampiran`, `show-document`.

**File baru:** migration `2026_06_06_100000_create_arsip_lampiran_table.php`, `app/Models/ArsipLampiran.php`, `app/Services/ArsipLampiranService.php`.

**Verifikasi:** `migrate` OK · `view:cache` OK · 8 route resolve OK · `php -l` OK.

**Detail penuh + TODO (auto-rotate, dup-hash check):** `SESSION_LOG.md`.

---

## 2026-06-05 — IT Submissions Footer (Cetakan) + Eye-Menu (Print Draft + Upload Lampiran)

**A. Footer cetakan** — `partials/_print_footer.blade.php` (BARU): position-fixed bottom-0 dengan logo + brand `IT Submissions` (indigo) + tagline + stamp `Generated ...`. Di-include di 4 file print/export: `print/arsip_draft.blade.php`, `print/arsip_draft_bundel.blade.php`, `exports/arsip-pdf.blade.php`, `laporan/pdf.blade.php`.

**B. Eye-dropdown menu** — ganti tombol Print di kolom aksi (admin & superadmin arsip index) dengan icon `bi-eye-fill` yang membuka dropdown 2 item: (1) **Print Draft** (link external print-draft), (2) **Upload Lampiran** (modal `#modalLampiran`).

**C. Modal upload lampiran** — `partials/_lampiran_modal.blade.php` (BARU): header gradient indigo→violet, file input dengan live preview card (auto-icon PDF/JPG, name + size, clear btn), keterangan optional. Route baru `POST /{admin,superadmin}/arsip/{id}/upload-lampiran`, controller method `uploadLampiran()` (validasi PDF/JPG/PNG max 10MB → store ke `bukti_scan/`, filename `LAMP_{no_reg}_{ts}_{name}`).

**Helper**: CSS rule `.dropdown-toggle-no-caret::after { display:none }` di `adjust-theme.css`.

**Verifikasi:** `view:cache` OK · `php -l` OK · route resolve OK.

---

## 2026-06-05 — Sidebar + Topbar Premium Overhaul

**File baru:** `public/css/premium-sidebar-topbar.css` (~360 baris) — loaded setelah modern-theme.css.

**Sidebar:** background gradient halus, header dengan radial deco, nav-link hover → translateX + glow + indicator bar 3px kiri, active → gradient indigo→violet + shimmer overlay, sub-menu compact dengan border indigo. Badge pulse animation 2.2s, footer profile card dengan shimmer-on-hover + **online dot hijau pulse**.

**Topbar:** glassmorphism backdrop blur 18px saturate 180%, **search bar Ctrl+K** dengan kbd hint kanan (Enter → redirect ke arsip index `?q=`), **live clock pill** (refresh 30s), notif bell gradient + dropdown header indigo→violet, profile button gradient + **online dot pulse**, mobile hamburger gradient indigo. Responsive: search shrink ≤991px, hidden ≤767px.

**Markup di `layouts/app.blade.php`**: tambah search + meta strip di tengah (d-none d-md-flex), `topbar-profile-online` span, 2 JS function (clock + Ctrl+K binding).

**Verifikasi:** `php artisan view:cache` OK.

**Detail penuh + ide pengembangan (dark mode, search history, presence):** lihat `SESSION_LOG.md`.

---

## 2026-06-05 — Adjust Kolom Refactor + Multi-Pemohon Picker + Dashboard Inovasi

**3 paket besar sekaligus:**

1. **Adjust kolom direstrukturisasi** (8 file): Kode|Nama|Lot|Lokasi|Odoo|Fisik|**Selisih(auto)**|**Adjus(IN/OUT, auto)**. Sumber-tunggal JS row di `partials/_adjust_row_template.blade.php` → `window.buildAdjustRow()`. Hidden qty_in/qty_out auto-filled dari (Fisik - Odoo). Backward-compat: model tidak diubah.
2. **Multi-pemohon picker** dengan Tom-Select (CDN): `partials/_pemohon_picker.blade.php`, route `/users/search` (filter by NIK/name + work_unit/dept). Pivot `arsip_requesters` di-sync via trait `App\Traits\SyncsRequesters`. Backward-compat: `arsips.pemohon` text diisi join-name. 4 form (admin/superadmin × create/edit) wired + JS edit preload pakai `refreshPemohonPicker(presets)`.
3. **Dashboard inovasi**: `partials/_dashboard_hero.blade.php` (gradient indigo→purple, greeting time-aware, live clock, KPI today/week/month, quick-action pills) + `partials/_dashboard_innovation.blade.php` (3 card: Adjustment Odoo Sync · Approval Velocity gauge · Approval Inbox + Recent Activity feed). Include di admin & superadmin dashboard sebelum filter.

**File baru:** `app/Traits/SyncsRequesters.php`, `partials/_adjust_row_template.blade.php`, `partials/_pemohon_picker.blade.php`, `partials/_dashboard_hero.blade.php`, `partials/_dashboard_innovation.blade.php`.

**Verifikasi:** `php artisan view:cache` OK · `php -l` semua controller/trait OK · route `users.search` OK.

**Detail penuh + TODO:** lihat `SESSION_LOG.md`.

---

## 2026-06-05 — Adjust Form Inovasi (banner Odoo + live totals + auto-calc + approver flow)

**Tujuan:** Adjust adalah jenis pengajuan istimewa (extra Accounting step + Odoo sync). Form CRUD-nya disesuaikan & dipercantik untuk komunikasikan ini ke user secara visual.

**Yang dibuat:**
- **`partials/_approver_select.blade.php`** — redesign: 6-step visual flow timeline (Pemohon → SPV → Kabag → Manager → Accounting → Dept. IT). Saat jenis=Adjust: card border merah + badge `ADJUSTMENT FLOW` + step Accounting filled-highlight + hint "wajib lewat Accounting".
- **`partials/_adjust_header.blade.php`** (BARU) — premium gradient cyan banner: judul `STOCK ADJUSTMENT`, badge `SYNC ODOO` + `VIA ACCOUNTING`, tombol `Auto-Calc`.
- **`partials/_adjust_footer.blade.php`** (BARU) — live totals: ITEMS, TOTAL IN, TOTAL OUT, NET ADJUSTMENT.
- **`public/css/adjust-theme.css`** (BARU) — gradient banner + stat cards + row color-code (pos/neg/zero) + input bg tint per kolom.
- **`public/js/adjust-enhancer.js`** (BARU) — live recalc (event delegation + MutationObserver), Auto-Calc dari `(Fisik − Odoo)`: positif → qty_in, negatif → qty_out.
- **`layouts/app.blade.php`** — link CSS + script (filemtime cache-busting).
- **4 form files** (admin & superadmin × create & edit) — wrap sectionAdjust dgn `@include _adjust_header` + `@include _adjust_footer`.

**Behavior baru:** pilih jenis=Adjust → banner cyan muncul, approver card jadi tema merah dgn step Accounting highlighted, isi Odoo+Fisik → Auto-Calc isi qty_in/out otomatis dari selisih, totals live setiap input, row diwarnai pos/neg/netral.

**Verifikasi:** `php artisan view:cache` OK semua blade compile.

**Detail lengkap + TODO sesi berikutnya:** lihat `SESSION_LOG.md`.

---

## 2026-06-04 — User Data Revamp (HR Excel → users + multi-pemohon)

**Tujuan:** Integrasi data HR (Excel `employeeId, name, departmentName, workUnitName`) ke `users` existing (149 row) **tanpa kehilangan data lama**. Plus pivot multi-pemohon di arsip.

**Strategi:** additive-only — tambah kolom nullable + 2 tabel baru, flag `source` (legacy/hr_import/manual), 3-step CLI workflow.

**Yang dibuat:**
- `composer require maatwebsite/excel ^3.1`
- 4 migration (sudah migrate jalan):
  - users: `+employee_id, +work_unit_id, +odoo_user_id, +source, +must_change_password, +last_synced_at`
  - units: `+code`
  - `users_staging` (workspace import + matching result)
  - `arsip_requesters` (pivot composite PK, snapshot employee_id + name)
- Models: `UsersStaging`, `ArsipRequester` (BARU); `User`, `Unit` (update fillable + relation)
- `App\Imports\UsersHrImport` — Maatwebsite ToModel + WithHeadingRow (normalize lowercase header → toleran kapitalisasi excel)
- 3 Artisan command:
  - `users:import-excel {path} [--fresh]` — populate staging dengan batch_id ULID
  - `users:auto-match [--batch=] [--fuzzy-threshold=85]` — cascade match: employee_id → exact_name → fuzzy (similar_text)
  - `users:apply-import [--batch=] [--dry-run] [--deactivate-missing]` — transactional update/create

**Aman by design:**
- Update existing user HANYA isi field kosong (`employee_id`, `work_unit_id`, `department_id`) — `name/email/password` tidak pernah ditimpa.
- `--deactivate-missing` hanya set `is_active=0` (tidak DELETE; FK ke arsips/approvals tetap valid).
- `--dry-run` rollback transaksi sebelum commit.

**Blueprint lengkap:** `PLAN_USER_REVAMP.md` (8 section: konteks, strategi, schema, alur, multi-pemohon API, status, TODO sesi berikutnya, rollback, verifikasi).

**Belum dibuat:** halaman review `/superadmin/users/import-review`, endpoint `/api/users/search`, refactor form arsip pakai Tom-Select multi-pemohon, middleware ForceChangePassword.

---

## 2026-06-03 — Responsive Mobile Polish (global)

**Konteks:** User minta penyempurnaan tampilan responsive di semua dimensi (HP/tablet/desktop). Surface area besar (~10.7k baris di 52 file blade) → strategi global CSS overlay, bukan edit per file.

**Perubahan:**
- **BARU `public/css/responsive-mobile.css`** — comprehensive responsive overlay:
  - safety net global: `overflow-x: hidden`, img/svg max-width, table-responsive scrollbar tipis
  - `<=991.98px`: sidebar slide-over 78vw, topbar tighter, page-title scaled
  - `<=767.98px`: card padding compact, stat-card hero scaled (1.85rem), mini-stat circle 32px, action squares 34px, dropdown 92vw
  - `<=575.98px`: modal near full-screen (`calc(100vw - 0.7rem)`), heading scale ladder, table cell 0.7rem/0.5rem, profile avatar 100px, sidebar 84vw, form padding tighter, modal table inputs `min-width: 60px`
  - `<=380px`: ultra compact (page-title 0.85rem, modal title 0.9rem)
  - touch target `min-height: 36px` di `(pointer: coarse)`
  - global overflow safety (font-monospace word-break, no_doc/no_transaksi tidak melebar)
- **EDIT `resources/views/layouts/app.blade.php`** — tambah `<link>` ke responsive-mobile.css setelah modern-theme.css (urutan penting agar override).
- **EDIT `resources/views/auth/login.blade.php`** — tambah breakpoint `<=575.98px` + `<=380px` (sebelumnya hanya `<=480px`); 850px diberi `min-height: auto`.

**Tidak diubah:** file blade satu per satu (risiko regresi tinggi). Semua override via CSS global = mudah revert dengan menghapus 1 file + 1 baris `<link>`.

**Verifikasi:** `php -l` blade source: OK · `php artisan view:clear` + `view:cache`: OK (52 view compile clean).

**Lanjutan:** lihat `SESSION_LOG.md` untuk daftar halaman + breakpoint yang perlu di-verifikasi visual.

---

## 2026-06-02 — FCM heads-up/sound fix (FcmService)

**Masalah:** notifikasi FCM masuk tapi silent/tanpa heads-up di sebagian device (terutama saat channel `submission_channel_v2` belum sempat ter-create — FCM jatuh ke `fcm_fallback_notification_channel` yang silent).

**Perubahan `app/Services/FcmService.php` (blok `android.notification` di `sendOne()`):**
- `notification_priority: PRIORITY_MAX` — pemicu heads-up bawaan device.
- `visibility: PUBLIC` — tampil utuh di lock screen.
- `default_sound`, `default_vibrate_timings`, `default_light_settings` = `true` — fallback bunyi/getar/LED bila channel tujuan belum ada.
- `tag: arsip-<no_registrasi>` + `collapse_key` — dedupe level sistem saat bg auto-render.
- `title`/`body` diduplikasi di `android.notification` (defensive, konsisten dgn root notification).

**Tidak diubah:** strategi hybrid (notification + data + priority high) dipertahankan; `channel_id` tetap `submission_channel_v2`; sisi Android tidak disentuh — kontrak data tetap kompatibel.

**File baru:** `LARAVEL_FCM_REPORT.md` — analisis akar penyebab + tabel field + checklist verifikasi.

**Verifikasi pasca-deploy:** `php artisan config:clear`, lalu `POST /api/fcm/test` dengan layar terkunci → harus heads-up + bunyi + getar. Jika masih silent → cek setting channel `submission_channel_v2` di system settings device.

---

## 2026-05-29 — Hapus UI upload `bukti_scan` & `scan_ba_accounting`, sisakan `scan_final` (IT)

Alur sudah full digital lewat TTD + QR, jadi UI scan fisik dirampingkan:
- **Admin _create + _edit**: hilangkan input upload "Bukti Scan" + `linkBuktiSaatIni`.
- **Superadmin _create + _edit**: hilangkan input "Upload Bukti Scan" + panel "Scan BA Accounting" + `linkBuktiSaatIni`. **Scan Final (IT) tetap**.
- **Admin index**: action area dirapikan — group Print/View tinggal **Print**; hilangkan tombol "View Bukti Scan", "Upload BA" accounting, "View Scan BA"; modal `modalReuploadBA` + JS `openModalReuploadBA` dihapus.
- **Superadmin index**: action — hilangkan tombol "View" bukti; `editArsip` JS dibersihkan dari `linkBuktiSaatIni` + `scan_ba_accounting`.
- **Dashboard Superadmin** (kolom Berkas): chip `SCAN` & `BA` dihapus; **chip `FINAL` (IT) tetap**.

Kolom DB `bukti_scan`, `scan_ba_accounting` **tidak didrop** (data lama tetap aman, route reuploadBaScan tetap ada untuk kompatibilitas, hanya UI yang hilang). Semua view ter-compile clean; view cache dibersihkan.

---

## 2026-05-29 — Draft: posisi QR — Verifikasi di kiri, No Registrasi di kanan

Pisahkan dua QR dari satu wrapper kanan menjadi kiri-kanan: `qr-wrapper` kiri (70px) berisi **QR Verifikasi** + label "SCAN VERIFIKASI"; `qr-wrapper` kanan (70px) berisi **QR No Registrasi** + label no_reg. Title tetap di tengah. compile draft OK.

---

## 2026-05-29 — Draft: No Registrasi pakai QR (bukan CODE128)

Ganti barcode CODE128 di header kanan atas dengan **QR Code** untuk no_registrasi (seperti sebelumnya). JsBarcode CDN dilepas dari draft (tidak dipakai lagi). Sekarang header punya 2 QR sejajar vertikal: **QR verifikasi (atas)** + **QR No Registrasi (bawah, 56×56)** + label no_reg di bawahnya. `view:clear` + compile draft OK.

---

## 2026-05-29 — Draft: tambah Barcode No Registrasi, TTD jadi timestamp saja

- Header kanan atas kini punya DUA elemen: **QR verifikasi** (di atas) + **Barcode CODE128 untuk No Registrasi** (di bawah, separator dashed) + label no_reg di bawah barcode. `qr-wrapper` dilebarkan ke 110px.
- JsBarcode (CDN) di-load + init di `DOMContentLoaded` (CODE128, height 28, displayValue false → no_reg ditulis manual di bawah).
- `renderSig()` kotak TTD: hilangkan label "✓ TTD Digital", tampilkan **timestamp saja** (`d/m/Y H:i WIB`, italic, abu-abu).
- Verifikasi: `view:clear` + compileString OK.

---

## 2026-05-29 — Draft dokumen: penegasan TTD digital + QR verifikasi

Draft sudah memuat TTD digital + QR (sejak modul TTD). Tambah penegasan:
- **QR pojok kanan atas** kini berlabel "SCAN VERIFIKASI" + 8-char token `verify_token`; mengarah ke `/verify/{token}` (daftar TTD + alur approval).
- **Watermark `TERTANDATANGANI DIGITAL`** (biru, kelas `watermark-digital`) muncul saat dokumen fully approved + ada TTD (override DONE biasa).
- **Footer "validasi TTD digital"** otomatis muncul bila ada TTD: list peran yg sudah TTD + 12-char token utk fallback verifikasi.

Tetap: tiap kotak (Pemohon/SPV/Kabag/Manager/Accounting/Departemen IT) menampilkan gambar specimen + nama + "✓ TTD Digital tgl" saat sudah ditandatangani.

Verifikasi: `view:clear` + compileString draft OK.

---

## 2026-05-29 — Modal Edit disesuaikan dgn alur approval

**Alur (ringkas):** Pengaju submit + pilih approver → TTD Pemohon auto → SPV → Kabag → Manager → (Accounting utk Adjust) → Departemen IT (final) → Done. Tiap approver "Setujui & TTD" di menu Persetujuan; edit terkunci begitu approval berjalan.

**Controller:**
- `edit()` (Admin & Superadmin): JSON kini sertakan `approval_started` (bool) & `approval_map` (role→approver_id), plus relasi `approvals.approver`.
- `update()` (Admin & Superadmin): bangun ulang chain via `initApprovalChain` HANYA bila `!approvalStarted()` dan ada input `approvers` (aman: hanya Pemohon approved + step pending yang ditata ulang; mendukung perubahan jenis/approver sebelum jalan).

**View modal edit (admin & superadmin `_edit.blade.php`):**
- Tambah panel "Alur Persetujuan": `#editApprovalTimeline` (diisi JS) + include `partials._approver_select` (jenisSelectId=`editJenisPengajuan`) + `#editApprovalNote`.
- JS `renderApprovalEdit(data)` (di kedua index): render timeline, preselect approver dari `approval_map`, trigger toggle per jenis, dan **disable approver + tampilkan kunci** bila `approval_started`.

**Verifikasi:** PHP lint clean; semua blade ter-compile (compileString) tanpa error; view cache dibersihkan.

---

## 2026-05-29 — Badge counter approval pending di sidebar

- `ArsipApproval::pendingCountFor($user)` — hitung pengajuan yang TAHAP AKTIF-nya menunggu user (superadmin: tahap IT-final/ditugaskan; lainnya: approver_id = user).
- `AppServiceProvider` view composer global `*` kini share `$pendingApprovalCount` utk semua view saat login.
- Sidebar admin ("Persetujuan Saya") & superadmin ("Persetujuan (Final IT)") tampilkan badge merah jumlah pending (sembunyi bila 0).
- Lint clean; smoke test `pendingCountFor` OK.

---

## 2026-05-29 — Approval Bertingkat (Opsi A, approver per pengajuan)

**Konteks:** Lanjutan TTD digital. User pilih Opsi A (semua approver = akun login) dengan penentuan approver **per pengajuan** (pengaju memilih user tiap tahap saat submit). Rantai: Pemohon → SPV → Kabag → Manager → (Accounting, khusus Adjust) → Departemen IT (final, any superadmin). Produk_Baru langsung Pemohon → IT.

**Migrasi `2026_05_29_110000_create_approval_chain_tables.php`:** `users.jabatan` (filter approver); tabel `arsip_approvals` (arsip_id, step_order, role_label, approver_id nullable [null=IT], status pending/approved/rejected, note, acted_by, acted_at; index arsip+step).

**Model:** `ArsipApproval` (baru): `rolesForJenis($jenis)` + `generateFor($arsip,$map)` (Pemohon auto-approved, role antara sesuai jenis & yg dipilih, IT final). `Arsip`: `approvals()`, `currentApproval()` (step pending paling awal & berurutan; null bila rejected/selesai), `isFullyApproved()`, `approvalStarted()`. `User`: +`jabatan` fillable, `getJabatanOptions()`, `approvalsAssigned()`.

**Trait `SignsArsip`:** di-refactor → `applySignature($arsip,$user,$roleLabel,...)` reusable (snapshot specimen + hash + ensureVerifyToken + updateOrCreate signature).

**Trait `HandlesApproval` (baru, dipakai Admin/Superadmin/API ArsipController):**
- `initApprovalChain($arsip,$map,$pengaju)` — generate chain + stempel TTD Pemohon (bila ada specimen) + notif approver pertama.
- `approveArsip($id)` — validasi giliran (`canActOnStep`: approver ditugaskan / superadmin override) + butuh specimen → `applySignature(role tahap)` + tandai approved + notif tahap berikut; bila tahap terakhir (IT) → arsip `Done`. Transaksional.
- `rejectArsip($id)` — tandai rejected, arsip `Reject`/`ket_process Void`, notif pengaju.
- `myApprovals()` — daftar pengajuan yg tahap aktifnya menunggu user (superadmin lihat tahap IT-final + yg ditugaskan).

**Store integration:** Admin & Superadmin `store()` dan API `storePengajuan()` panggil `initApprovalChain` dari `approvers` (map role→user_id; API juga baca `detail_barang_json.approvers`).

**Routes:** `{admin,superadmin}` → `GET approvals`, `POST arsip/{id}/approve`, `POST arsip/{id}/reject`.

**View:**
- `partials/_approver_select.blade.php` (BARU): dropdown approver SPV/Kabag/Manager/Accounting; JS toggle per jenis (Accounting hanya Adjust; Produk_Baru sembunyi; field tersembunyi di-`disabled` agar tak ter-submit). Di-include di create form admin (`jenisPengajuanTambahAdmin`) & superadmin (`jenisPengajuanTambah`).
- `partials/_approval_timeline.blade.php` (BARU): timeline status tiap tahap (Disetujui/Menunggu/Ditolak + nama + tgl + alasan).
- `approvals/index.blade.php` (BARU): "Persetujuan Saya" — kartu pengajuan + timeline + tombol **Setujui & TTD** / **Tolak** (peringatan bila belum punya specimen). Link sidebar admin ("Persetujuan Saya") & superadmin ("Persetujuan (Final IT)").
- `print/arsip_draft.blade.php`: kotak SPV/Kabag/Manager kini ikut distempel specimen (selain Pemohon/Accounting/IT).
- `verify/show.blade.php`: tambah Alur Persetujuan (timeline).
- Admin index: edit dikunci juga saat `approvalStarted()` (eager-load `approvals`).

**API:** `getMasterData` +`approver_users` & `approval_roles` (peran per jenis); `detailRelations` +`signatures`,`approvals.approver` (show/store ikut bawa).

**Verifikasi:** semua PHP lint clean; routes terdaftar; E2E tinker: Cancel → `Pemohon>SPV>Kabag>Manager>Departemen IT`, current pasca-Pemohon=SPV, setelah semua approve `fullyApproved=Y`; Produk_Baru → `Pemohon>Departemen IT`.

**Catatan lanjutan:** approver dipilih dari semua user aktif (filter via `jabatan` opsional — perlu set jabatan user). Superadmin bisa override tahap mana pun. Belum: badge counter di sidebar, reminder/eskalasi, dan integrasi CA resmi.

---

## 2026-05-29 — Modul Tanda Tangan Digital (specimen + stempel PDF + QR verifikasi)

**Konteks:** User pilih kembangkan TTD digital (dari 2 opsi: skema approval bertingkat vs modul TTD). Ini fondasi paperless; approval bertingkat (SPV/Kabag/Manager) menyusul. Arsip sistem IT tetap fisik.

**Migrasi `2026_05_29_100000_create_digital_signature_tables.php`:**
- `users.signature_path` (specimen TTD), `arsips.verify_token` (uuid unik, utk QR), tabel `arsip_signatures` (arsip_id, user_id, role_label, signer_name, signature_path snapshot, hash sha256, note, ip, signed_at; unique [arsip_id, role_label]).

**Model:** `ArsipSignature` (baru, +`signatureUrl()`); `User` fillable +email/photo/signature_path, +`hasSignature()`/`signatureUrl()`; `Arsip` +`signatures()`, +`ensureVerifyToken()`, +`signatureFor($role)`, fillable +verify_token.

**Trait baru:**
- `HandlesSignature` → `updateSignature()` simpan specimen dari canvas base64 ATAU file upload ke `public/signatures`, + hapus. Dipakai Admin & Superadmin ProfileController.
- `SignsArsip` → `signArsip()` snapshot specimen + hitung hash sha256(id|user|role|signed_at|no_reg) + `ensureVerifyToken` + `updateOrCreate` by (arsip,role). Role→label: superadmin=Departemen IT, accounting=Accounting, lainnya=Pemohon. Otorisasi: non-superadmin hanya doc miliknya (accounting boleh Adjust). Dipakai kedua ArsipController.

**Routes (`web.php`):** `POST {admin,superadmin}/profile/signature`; `POST {admin,superadmin}/arsip/{id}/sign`; publik `GET /verify/{token}` → `VerificationController@show` (re-hitung & cek hash tiap TTD, tampil VALID/TIDAK VALID).

**View:**
- `partials/_signature_specimen.blade.php` (BARU): canvas **signature_pad** (CDN) + tab upload + preview + hapus. Di-include di profil admin & superadmin (kirim `action` route masing-masing).
- `print/arsip_draft.blade.php`: kotak TTD Pemohon/Accounting/Departemen IT kini render gambar specimen + nama + "✓ TTD Digital tgl"; QR kini mengarah ke `route('verify.show', verify_token)`.
- `verify/show.blade.php` + `verify/invalid.blade.php` (BARU): halaman publik verifikasi (info dokumen + daftar TTD + badge valid).
- Tombol **TTD** di index admin (Pemohon/Accounting, doc sendiri) & superadmin (Departemen IT).
- `printDraft` (kedua controller): eager-load `signatures.user` + `ensureVerifyToken()`.

**Verifikasi:** semua PHP lint clean; routes terdaftar; E2E tinker: `hasSignature=Y`, verify_token uuid tergenerate, hash re-check `sigValid=Y`.

**Catatan lanjutan:** SPV/Kabag/Manager belum jadi user login → kotaknya masih manual; approval bertingkat sekuensial = pengembangan berikutnya (skema `arsip_approvals`). Belum ada integrasi CA resmi (Peruri/PrivyID) — `arsip_signatures` sudah siap menampung bila perlu legal-binding.

---

## 2026-05-29 — API Android disesuaikan (Produk Baru, barcode, scan_final, lokasi)

**Konteks percakapan:** User minta API disesuaikan agar kompatibel & siap dikembangkan di Android Studio (mengikuti fitur baru). Eksekusi langsung. Juga: berkas relasi accounting (Scan BA) hanya untuk Adjustment — sudah dibatasi di view admin index, superadmin edit modal (toggle JS by jenis), dan dashboard superadmin.

**`app/Http/Controllers/Api/ArsipApiController.php` (ditulis ulang):**
- Helper `fileUrl()` & `appendFileUrls()` → tiap respons arsip kini punya `bukti_scan_url`, `scan_ba_accounting_url`, `scan_final_url`.
- Helper `normalizeJenis()` → ubah "Mutasi Billet"/"Produk Baru"/"Internal Memo" jadi underscore (cocok ENUM). **Fix bug laten:** sebelumnya kirim jenis berspasi dari Android akan gagal/ke-truncate.
- `getMasterData()`: tambah `jenis_pengajuan_options` (value+label), `locations`, dan `produk_baru` (tipe/kategori/satuan/status_approval) — sumber dari model statis. `jenis_pengajuan` (label list) kini termasuk "Produk Baru".
- `storePengajuan()`: simpan jenis ter-normalisasi; tambah field `pemohon`; status awal `Check`/`ket_process Review`; `generateNoRegistrasi($request)` (kini unit_id ikut); produk_baru item tersimpan + `status_approval` + `updated_by` (barcode auto via model). Respons memuat relasi lengkap + URL file.
- `show($id)` (BARU): detail satu pengajuan + semua item (incl `produkBaruItems.barcode`) + URL file. Route `GET /api/arsip/{id}` (whereNumber).
- `getDashboard()`: tambah stats `produkBaruTotal/Done/Waiting`; recent kini sertakan `produkBaruItems` + URL file.
- `index()` & `getOutstandingBA()`: transform koleksi → URL file tertempel; index dukung `?mine=1` (pengajuan milik user login) & cari `no_doc`. `storeDetailItems()` Adjust kini ikut `odoo/fisik/keterangan_in/out`.

**`app/Http/Controllers/Api/BarcodeController.php`:** `processScan()` cari by No Registrasi, fallback by **barcode item Produk Baru** (`PB########`) → ambil arsip induk; tambah relasi `produkBaruItems` + URL `scan_ba_accounting`/`scan_final`.

**`routes/api.php`:** `GET /api/arsip/{id}` → `show` (numeric only, ditaruh setelah route spesifik).

**Verifikasi:** semua API controller lint clean; `route:list` benar (dashboard/master-data/outstanding-ba/{id} tidak bentrok krn `whereNumber`); sumber opsi master OK (tipe=3, kategori=50, satuan=41, lokasi=33); normalize "Produk Baru"→"Produk_Baru".

**Catatan utk Android:** pakai `jenis_pengajuan_options[].value` saat submit; payload item dikirim via `detail_barang_json` (string JSON) dgn key: `adjust[]`, `mutasi.asal[]/tujuan[]`, `bundel[]`, `produk_baru[]`. Endpoint detail: `GET /api/arsip/{id}`.

---

## 2026-05-29 — Produk Baru tanpa draft + Barcode + Detail/Log, & Lock edit saat Done

**Konteks percakapan:** User minta kembangkan Produk Baru: tidak perlu draft dokumen (cukup form yg diproses superadmin), tambah barcode, tanggal dibuat, log perubahan, last modify. Lalu tambahan: untuk role non-superadmin, sembunyikan tombol Edit saat status sudah Done. (Sesi sebelumnya 28/5 sempat catat kredensial server & fix `sort_order` overflow tindakan IT.)

**Migrasi baru:**
- `2026_05_29_000000_add_barcode_to_arsip_produk_baru_items_table.php` — `barcode` (varchar 64, unique, nullable) + `updated_by` (nullable). Sudah migrate.

**Model `ArsipProdukBaruItem`:**
- `booted()`: event `created` → auto-set `barcode = 'PB' . str_pad(id,8,'0')` via `saveQuietly()`.
- fillable +`barcode`, +`updated_by`; relasi `editor()` → User.

**Controller (Admin + Superadmin `ArsipController`):**
- **Refactor penting:** Produk Baru TIDAK lagi delete+recreate. Dibuatkan `syncProdukBaruItems($arsip, $items)` = upsert by hidden `id`: update row lama (barcode & created_at lestari), create row baru (auto barcode), hapus row yg hilang dari form. Dipanggil di `store()` & `update()` kedua controller. Baris produk_baru dihapus dari `saveDetailItems()` & dari blok delete generik.
- `printDraft()`: jika `Produk_Baru` → redirect balik dgn pesan "tidak punya draft" (kedua controller).
- Method baru `produkDetail($id)` (kedua controller): JSON berisi meta arsip (no_reg, pengaju, dept/unit, status, created_at, updated_at, editor), items (incl barcode + created/updated + editor), dan `logs` dari `audit_logs` (action, user, changes, tanggal). Admin: ada security check role.
- Form rows bawa hidden `id` + `barcode` agar upsert & barcode stabil.

**Routes** (`routes/web.php`): `GET admin/arsip/{id}/produk-detail` & `GET superadmin/arsip/{id}/produk-detail`.

**View:**
- `resources/views/partials/_produk_detail_modal.blade.php` (BARU) — modal detail; render barcode pakai **JsBarcode** (CDN, CODE128); tampil tgl dibuat, last modify+editor, timeline log perubahan. Di-include di kedua index dgn `['detailBase' => url('.../arsip')]`.
- Kedua index: tombol Print Draft + View diganti tombol **Detail** (`.btn-detail-produk`, ikon `bi-upc-scan`) khusus baris `Produk_Baru`.
- DETAIL DOKUMEN cell (kedua index): tampil badge barcode hitam (`bi-upc`).
- **Admin index — lock edit:** guard edit diberi `@else` → badge gembok (`bi-lock-fill`, "Sudah final oleh Superadmin") saat `status ∈ [Done,Reject,Void]` atau `ket_process ∈ [Done,Void]`. (Server-side sudah diblok di `update()` admin sejak awal; superadmin tetap bisa edit.)

**Verifikasi:** tinker — barcode auto `PB00000008`, setelah `update()` barcode & created_at tetap sama (Y). Routes terdaftar. Semua PHP lint clean. `route:clear` + `view:clear` dijalankan.

**Catatan lanjutan / belum:** belum ada barcode di dashboard; belum ada scan-barcode lookup utk Produk Baru; admin `_view` modal lama tetap dipakai utk jenis lain.

---

## 2026-05-26 — Fix ENUM Produk_Baru + Dashboard Stats

**Bug ditemukan:** submit Produk_Baru gagal silent — kolom `arsips.jenis_pengajuan` ternyata `ENUM('Cancel','Adjust','Mutasi_Billet','Mutasi_Produk','Internal_Memo','Bundel')` (warisan dari migrasi `2026_01_14_073708_update_enums_in_arsips_table.php`). Insert otomatis di-truncate jadi default `Cancel` (atau gagal SQL strict). Tabel `arsip_produk_baru_items` ikut kosong karena identifier lewat tapi parent arsip salah jenis.

**Fix:**
- Migrasi `2026_05_26_000003_add_produk_baru_to_jenis_pengajuan_enum.php` — `ALTER TABLE` tambah `'Produk_Baru'` ke ENUM. `down()` kembalikan ENUM lama (& migrasi data baris `Produk_Baru` → `Internal_Memo` sebelum drop).
- Verified: insert via tinker dgn `jenis_pengajuan = 'Produk_Baru'` + `ArsipProdukBaruItem::create(...)` sukses.

**Dashboard Admin** ([app/Http/Controllers/Admin/DashboardController.php](app/Http/Controllers/Admin/DashboardController.php) + [resources/views/admin/dashboard/index.blade.php](resources/views/admin/dashboard/index.blade.php)):
- Eager-load `produkBaruItems`.
- Stats baru: `produkBaruCount`, `produkBaruDone`, `produkBaruWaiting`.
- Section "Pengajuan Produk Baru" — 3 kartu (Total gradient ungu, Done hijau, Waiting List kuning), masing-masing link ke list ter-filter.
- Tambah `PRODUK BARU` di "Statistik per Jenis Pengajuan" + warna badge ungu `#a855f7` pada match table.

**Dashboard Superadmin** ([app/Http/Controllers/Superadmin/DashboardController.php](app/Http/Controllers/Superadmin/DashboardController.php) + [resources/views/superadmin/dashboard/index.blade.php](resources/views/superadmin/dashboard/index.blade.php)):
- Sama: stats + 3 kartu summary Produk Baru.
- `deptProdukBaru` via `getTopDeptsByType('Produk_Baru')` (siap dipakai chart bila perlu).
- Tambah `PRODUK BARU` di kategori "Statistik per Jenis Pengajuan".

---

## 2026-05-26 — Fitur Pengajuan Produk Baru

**Tujuan:** jenis pengajuan baru `Produk_Baru` untuk request produk Odoo baru (Stockable / Service / Consumable) lengkap dgn kategori & satuan.

**Migrasi baru:**
- `2026_05_26_000002_create_arsip_produk_baru_items_table.php` — tabel `arsip_produk_baru_items` (product_code, product_name, tipe_produk, kategori, satuan, status_approval [Done/Waiting List], keterangan).

**Model baru/diubah:**
- `app/Models/ArsipProdukBaruItem.php` — fillable + static `getTipeOptions()` / `getKategoriOptions()` (~50 opsi sesuai master Odoo) / `getSatuanOptions()` / `getStatusApprovalOptions()`.
- `app/Models/Arsip.php` — relasi `produkBaruItems()`, helper `isProdukBaru()`, `processArchiving` mapping `Produk_Baru` → prefix `PB/YYYY/MM/SEQ`.

**Controller diubah:**
- Admin & Superadmin `ArsipController` — eager-load `produkBaruItems` di `index/edit/printDraft`; `saveDetailItems()` simpan rows dari `$data['produk_baru']`; `update()` purge `ArsipProdukBaruItem` lama sebelum sync. Admin `store()` ikut backup ke `detail_barang` JSON.

**View diubah:**
- Admin + Superadmin form Create + Edit: dropdown jenis pengajuan +`Pengajuan Produk Baru`; section table dgn kolom Kode/Nama/Tipe/Kategori/Satuan/Status; JS row builder reusable (`buildProdukBaruRow`) + `addProdukBaruRowEdit`.
- Dynamic show/hide JS pada `jenisPengajuanTambahAdmin` / `#jenisPengajuanTambah` / `#editJenisPengajuan`.
- `editArsip` AJAX → populate `produk_baru_items` ke wrapper edit.
- Filter jenis_pengajuan di index admin & superadmin: tambah Produk_Baru.
- DETAIL DOKUMEN cell admin & superadmin index: tampilkan ringkasan item produk baru (kode/nama + badge kategori/satuan + status).
- `resources/views/print/arsip_draft.blade.php` — judul "BERITA ACARA PENGAJUAN PRODUK BARU"; tabel daftar item Produk Baru.

**Catatan:**
- Tidak ada perubahan kolom `arsips` — pakai `jenis_pengajuan` yg sudah `string(30)` free-text.
- `status_approval` per-item (Done / Waiting List) — beda dari `ket_process` arsip (alur Review/Process/Done).
- Kategori & satuan dihardcode di model agar konsisten dengan master Odoo yg diberikan user; bila berubah, edit `getKategoriOptions()` / `getSatuanOptions()`.

---

## 2026-05-26 — Lokasi pada Adjustment, Scan Final IT, Dashboard Superadmin

**Tujuan:** 
1. Tambah field **Lokasi** di item Adjustment (input user, muncul di draft cetak).
2. Tambah **Scan Final** (eksekusi Tim IT / Superadmin) di samping Bukti Scan awal & Scan BA Accounting.
3. Dashboard Superadmin: tampilkan **Lot** + indikator berkas (Scan / BA Accounting / Final) di tabel "Riwayat Pengajuan Terbaru".

**Migrasi baru:**
- `2026_05_26_000000_add_location_to_arsip_adjust_items_table.php` — kolom `location` (string 150, nullable) di `arsip_adjust_items`.
- `2026_05_26_000001_add_scan_final_to_arsips_table.php` — kolom `scan_final` (string, nullable) di `arsips`.

**Model diubah:**
- `app/Models/ArsipAdjustItem.php` — fillable +`location`; tambah static `getLocations()` (pakai `Location` master, konsisten dengan `ArsipMutasiItem`).
- `app/Models/Arsip.php` — fillable +`scan_final`.

**Controller diubah:**
- `app/Http/Controllers/Admin/ArsipController.php` — `saveDetailItems()` simpan `location` untuk adjust.
- `app/Http/Controllers/Superadmin/ArsipController.php` — `saveDetailItems()` simpan `location`; `update()` validate + handle upload `scan_final` (PDF maks 10MB) dengan prefix file `FINAL_<no_reg>_<timestamp>_<name>`.
- `app/Http/Controllers/Superadmin/DashboardController.php` — eager-load `adjustItems`, `mutasiItems` untuk `latestArsip`.

**View diubah:**
- Admin & Superadmin form Create + Edit Adjust: kolom Lokasi (dropdown dari `Location` master).
- `resources/views/print/arsip_draft.blade.php` — tabel adjust dapat kolom **LOKASI**.
- `resources/views/superadmin/arsip/_edit.blade.php` — panel "Scan BA Accounting" (read-only link) & input upload **Scan Final**.
- `resources/views/superadmin/arsip/index.blade.php` — JS `editArsip` set link `scan_ba_accounting` & `scan_final`; `addAdjustRowEdit` + `btnAddAdjust` dukung field `location`.
- `resources/views/superadmin/dashboard/index.blade.php` — tabel Riwayat Pengajuan: tambah kolom **Lot** (badge, max 3 + counter) & **Berkas** (chip SCAN / BA / FINAL link ke preview).
- `routes/web.php` — `/pdf-viewer` lookup arsip juga via `scan_final`.

**Catatan:**
- Field `location` di adjust dipakai key yang sama dgn mutasi (`detail_barang[adjust][i][location]`), aman terhadap controller existing.
- Belum ada migrasi untuk menambah upload `scan_final` di flow Admin/Accounting (sesuai requirement: eksekusi oleh Superadmin / Tim IT).
- Lot di dashboard ambil dari `adjustItems` (prioritas) atau `mutasiItems` (fallback). Jenis lain (Bundel / Cancel / Memo) tidak punya lot.

---

## 2026-05-23 — Firebase Cloud Messaging (FCM) untuk notifikasi Android

**Tujuan:** push notification ke HP Android via FCM, terhubung ke sistem notifikasi DB yang sudah ada.

**Pendekatan:** FCM HTTP v1 API + Service Account (OAuth2, JWT ditandatangani openssl). Tanpa paket composer tambahan.

**File baru:**
- `database/migrations/2026_05_23_000000_create_device_tokens_table.php` — tabel `device_tokens` (sudah migrate)
- `app/Models/DeviceToken.php`
- `app/Services/FcmService.php` — kirim push, cache OAuth token 55 mnt, auto-hapus token invalid
- `app/Http/Controllers/Api/FcmController.php` — store/destroy/test token
- `app/Http/Controllers/Api/NotificationController.php` — list/unread/read notifikasi in-app
- `storage/app/firebase/` — folder kredensial (+ `.gitignore`, `README.md`)

**File diubah:**
- `app/Models/Notification.php` — event `created` → otomatis push FCM
- `app/Models/User.php` — relasi `deviceTokens()`
- `routes/api.php`, `config/services.php`, `.env(.example)`
- `app/Http/Controllers/Api/AuthController.php` — logout hapus token
- `app/Http/Controllers/Api/ArsipApiController.php` — pengajuan Android notif ke superadmin

**Endpoint API baru (auth:sanctum):**
`POST/DELETE /api/device-token`, `POST /api/fcm/test`, `GET /api/notifications`,
`GET /api/notifications/unread-count`, `POST /api/notifications/{id}/read`, `POST /api/notifications/read-all`.

**Data payload FCM:** `type`, `notification_id`, `arsip_id`. Title/body via blok `notification`. Channel Android: `e_arsip_default`.

**TODO manual:** taruh `firebase-credentials.json` di `storage/app/firebase/`, lalu `php artisan config:clear`.

**Android:** `MyFirebaseMessagingService.kt` dibenahi (token dikirim ke `/api/device-token` saat refresh, handling payload dirapikan).
