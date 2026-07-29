# PROJECT OVERVIEW — IT Submission / e-Arsip (PT Inkalum)

> Dokumen konsolidasi state proyek per **2026-07-28** untuk keperluan **rancang ulang / pengembangan lanjutan**.
> Sumber: analisa codebase lokal `c:\laragon\www\e_arsip` + DEVLOG.md (log kerja per-sesi).
> **Catatan**: State server prod `192.168.11.200` **tidak diverifikasi langsung** dalam dokumen ini (akses SSH plink diblokir auto-mode classifier). Per DEVLOG 2026-07-21 dev server `192.168.11.199` sudah full-sync ke commit `d3f1c2b`; prod diasumsikan mengikuti tapi **wajib verifikasi manual** sebelum re-arsitek.

---

## 1. RINGKASAN EKSEKUTIF

**Nama produk**: IT Submission (internal disebut e-arsip)
**Owner**: Departemen IT PT Inkalum
**Fungsi**: sentralisasi 6+ jenis pengajuan operasional (Cancel, Adjust, Mutasi Billet, Mutasi Produk, Internal Memo, Bundel, opsional Produk Baru) dengan **approval chain berjenjang + tanda tangan digital (SHA-256) + arsip otomatis dgn penomoran doc**.

**Klien**:
- **Web** (Bootstrap 5, jQuery, Chart.js) — role Admin / Accounting / SPV / Kabag / Manager / Superadmin
- **Android** (Kotlin, `ITSubmissions` project di `C:\Users\ADI EDP\AndroidStudioProjects\ITSubmissions\`) — role Admin / Superadmin only

**Skala**: 74 department, 24 unit, 11 manager, ~1666 user aktif (data 2026-07-14 tinker cek); ~700+ arsip transaksi historikal.

---

## 2. TECH STACK

| Layer | Teknologi |
|---|---|
| **Backend** | Laravel 12, PHP 8.2 (FPM in Docker) |
| **DB** | MySQL 8 (`inkalum_db` container) |
| **Auth Web** | Laravel session (cookie) + custom role middleware |
| **Auth API** | Laravel Sanctum token |
| **Frontend** | Blade, Bootstrap 5.3.3, jQuery 3.7.1, Alpine (per-view), Tom-Select, Chart.js 4.x |
| **PDF** | mPDF 8.3 + FPDF/FPDI (merged docs), Dompdf 3.1 |
| **QR** | endroid/qr-code v6 |
| **Excel** | maatwebsite/excel |
| **Push** | Firebase Cloud Messaging (`FcmController` + `DeviceToken` model) |
| **Storage** | Local disk symlinked `public/storage` → `storage/app/public/{apk,bukti_scan,lampiran,settings}` |
| **Container** | Docker Compose 6 layanan: `it_app` (php-fpm), `it_nginx`, `it_db`, `it_assistant_reverb`, `it_assistant_app`, `inkalum_db` |
| **Reverse proxy** | Cloudflare Tunnel (`dev-it-sub.inkalum.com`, `lin-dev-it-sub.inkalum.com`) |
| **Deploy** | Manual via SSH `scripts/*.ps1` + `docker exec` untuk composer/artisan (host PHP miss ext-gd) |

**Docker upload limit** (`docker/php/Dockerfile:26`): 250 MB / mem 512 M / max_exec 300 s.

---

## 3. DOMAIN BISNIS

### 3.1 Role & Jabatan
| role | jabatan default | Akses inti |
|---|---|---|
| `admin` | Staff | Submit pengajuan yang di-grant, view own + shared, TTD Pemohon |
| `accounting` | Accounting | Submit Adjust, view semua Adjust, TTD "Accounting" step |
| `spv` | SPV | Approver step SPV |
| `kabag` | Kabag | Approver step Kabag |
| `manager` | Manager | Approver step Manager |
| `superadmin` | IT | Bypass semua, final TTD "Departemen IT", CRUD master data, arsip sistem |

**Mobile-only roles**: `admin` + `superadmin` (spv/kabag/manager/accounting harus pakai web — enforcement di `Api\AuthController::login`).

### 3.2 Jenis Pengajuan
Terpusat di `RolePengajuanAccess::JENIS_LIST`:

| jenis (DB) | Label UI | Approval antar | Prefix No_Doc |
|---|---|---|---|
| `Cancel` | Cancel Dokumen | SPV → Kabag → Manager | `Cancelled No Doc : NNNN/MM/IT/YYYY` |
| `Adjust` | Adjust Stok | SPV → Kabag → Manager → **Accounting** | `DC/YYYY/MM/DD/NNNN` |
| `Mutasi_Billet` | Mutasi Billet | SPV → Kabag → Manager | `DCB/YYYY/MM/NNNNN` |
| `Mutasi_Produk` | Mutasi Produk | SPV → Kabag → Manager | `RPP/YYYY/MM/NNNN` |
| `Internal_Memo` | Internal Memo | SPV → Kabag → Manager | `IM/YYYY/MM/NNNN` |
| `Bundel` | Bundel Dokumen | SPV → Kabag → Manager | `BDL/YYYY/MM/NNNN` |
| `Produk_Baru` (opt feature-flag) | Produk Baru | — (langsung IT) | `PB/YYYY/MM/NNNN` |

Chain didefinisikan `ArsipApproval::rolesForJenis()`. Pemohon auto-approve saat submit; step terakhir selalu **Departemen IT** (any superadmin).

### 3.3 State Machine Arsip
Kolom: `status`, `ket_process`, `ba`, `arsip`.

- `status`: `Pending` → `Check` → `Process` → `Done` / `Reject` / `Void`
- Trigger `Done`: `Arsip::processArchiving()` — generate no_doc, set semua sub-state ke `Done`
- Trigger `Reject`: approver reject → status `Reject`, ket_process `Void`
- Accessor `status_utama` (di `Arsip.php:63`): `Reject/Void → DITOLAK`, `Done|no_doc → DONE`, else `DALAM PROSES`

---

## 4. DATA MODEL

### 4.1 Tabel Inti
```
arsips (core entity)
├── arsip_requesters      (M-pemohon pivot, is_primary flag)
├── arsip_adjust_items    (kode/nama/lot/lokasi/qty_in/qty_out/odoo/fisik)
├── arsip_mutasi_items    (type=asal|tujuan, qty, location)
├── arsip_bundel_items    (no_doc, qty, keterangan)
├── arsip_produk_baru_items (kode, barcode auto, tipe/kategori/satuan/status_approval)
├── arsip_tindakan_items  (tindakan_in/out + keterangan, sort_order — untuk catatan IT strict)
├── arsip_lampiran        (multi-file PDF, original_name, file_size, mime_type)
├── arsip_approvals       (chain: step_order, role_label, approver_id, delegated_from_id, status)
├── arsip_signatures      (TTD immutable: role_label unique per arsip, hash SHA-256)
├── arsip_shares          (Layer 2 share: target_type=user|role)
└── arsip_personal_notes  (private notes per user per arsip)

users (auth)
├── departments/units/managers (M-1)
├── work_unit_id → units (untuk unit tempat kerja karyawan, beda dari unit pengajuan)
├── delegate_to_id, delegate_active_from/until, delegate_reason  (TTD delegasi, 2026-07-14)
├── device_tokens (FCM push)
└── personal_access_tokens (Sanctum)

master:
- departments, units (dgn code), managers, locations, products, item_prices
- role_pengajuan_access (baseline akses per role × jenis)
- audit_logs (auto via HasAuditLogs trait)
- app_versions (Android auto-update, 2026-07-20)
- users_staging (HR sync)
- settings (key-value app config)
- notifications (in-app bell; role_target column)
```

### 4.2 Denormalisasi Sengaja
- `arsips.detail_barang` (JSON) — snapshot untuk UI cepat
- `arsip_*_items` — untuk query/reporting
- **Risiko**: dua sumber, harus sinkron via service. Sekarang sinkronisasi ada di controller (candidate extract → `ArsipDetailService`).

### 4.3 Migration Terbaru (referensi timeline)
| Tanggal | Migration | Efek |
|---|---|---|
| 2026-07-20 | `create_app_versions_table` | APK version registry |
| 2026-07-14 | `add_approval_delegation_fields` | Delegasi TTD (users + arsip_approvals + arsip_signatures) |
| 2026-06-22 | `create_arsip_personal_notes` | Personal notes per arsip per user |
| 2026-06-22 | `extend_arsip_shares_for_role_target` | Share by role (bukan hanya per user) |
| 2026-06-20 | `create_role_pengajuan_access` | **Refactor akses: per-user → per-role** |
| 2026-06-19 | `create_arsip_shares` | Layer 2 share ke user tertentu |
| 2026-06-19 | `create_item_prices_and_add_harga_to_items` | Master harga (Gate `view-price`) |
| 2026-06-19 | `add_spv_kabag_manager_roles_to_users_table` | 3 role baru selain admin/accounting/superadmin |
| 2026-06-06 | `create_arsip_lampiran_table` | Multi-file PDF attachment |
| 2026-06-04 | `create_submission_requesters_table` | Multi-pemohon pivot |
| 2026-06-04 | `create_users_staging_table` | Staging HR sync |
| 2026-05-29 | `create_approval_chain_tables` | Introduksi ArsipApproval chain |
| 2026-05-29 | `create_digital_signature_tables` | Introduksi ArsipSignature |

---

## 5. APPROVAL CHAIN

### 5.1 Alur Runtime
1. **Submit** (`Admin\ArsipController::store()`) → panggil `HandlesApproval::initApprovalChain()` → `ArsipApproval::generateFor($arsip, $approverMap)`:
   - Step 1: **Pemohon** — auto-approved + auto-TTD via `SignsArsip::applySignature()`
   - Step 2..N: role dari `rolesForJenis($jenis)` — **auto-substitusi delegasi** kalau approver punya `activeDelegate()` (chain-forward max depth 3)
   - Step terminal: **Departemen IT** (`approver_id=null`, di-claim oleh superadmin manapun)
2. **Approve** (web/API) → `HandlesApproval::approveArsip()`:
   - Guard: current step milik user (atau superadmin + FINAL_ROLE)
   - Update step: `status=approved`, `acted_by`, `acted_at`
   - Auto-TTD via `applySignature($arsip, $user, $roleLabel, $note, $ip, $delegatedFromId)`
   - Notify next approver (in-app + FCM)
   - Jika step terakhir → mark arsip `status=Done` + notify pengaju
3. **Reject** → `HandlesApproval::rejectArsip()`: `status=Reject`, `ket_process=Void`, notify pengaju dengan reason.

### 5.2 Delegasi TTD (2026-07-14)
- **Setup**: Superadmin di modal users pilih `delegate_to_id` + window `active_from..until` + `reason` (mis. "Cuti tahunan")
- **Guard**: no self-delegate, no loop A→B & B→A
- **Substitution otomatis** saat generate chain
- **Chain-forward** max depth 3 (A→B→C, endpoint C)
- **Snapshot per record**: `arsip_approvals.delegated_from_id` + `arsip_signatures.delegated_from_id`
- **Render**:
  - Draft PDF: badge kuning "↩ Mewakili {nama asli}"
  - Verify page: badge "DELEGASI"
- **Hash**: `delegated_from_id` masuk SHA-256 input → verifikasi tetap valid.

---

## 6. TANDA TANGAN DIGITAL

**Trait**: `SignsArsip` (source of truth: `app/Traits/SignsArsip.php`).

**Skema hash**:
```
SHA-256( arsip.id | user.id | roleLabel | signedAt-ISO |
         no_registrasi | delegatedFromId | config('app.key') )
```

**Storage**: `arsip_signatures` — kolom `hash` immutable. `signature_path` (PNG legacy) tidak dipakai lagi; TTD render sbg QR + metadata.

**Anti-double-sign**: unique constraint (`arsip_id`, `role_label`).

**Verifikasi publik**: `GET /verify/{token}` (token = UUID di `arsips.verify_token`, dijamin `ensureVerifyToken()`) → tampilkan sertifikat, semua tanda tangan, timeline approval, badge validity per hash re-compute.

---

## 7. ACCESS CONTROL — 3 LAYER

| Layer | Sumber | Cek di |
|---|---|---|
| **Layer 1 — Role-based baseline** | `role_pengajuan_access` (role × jenis) | `User::canAccessJenis()` + `User::accessibleJenis()` |
| **Layer 2 — Per-arsip share** | `arsip_shares` (target=user\|role) | `User::sharedArsips()` (query builder) + `Arsip::canBeEditedBy()` |
| **Layer 3 — Superadmin override** | Hardcode | Cek `$user->role === 'superadmin'` di guard |

**Rules tambahan**:
- Owner (`arsips.admin_id`) selalu bisa edit
- Accounting bisa edit semua `Adjust` (rule khusus, hardcode)
- **Gate `view-price`** (`AppServiceProvider::boot()`): accounting/superadmin OR user dept starts with "Accounting"/"Finance" OR = "IT"

---

## 8. CORE WORKFLOWS

### 8.1 Submit Pengajuan (Admin)
`admin.arsip.store` → `Admin\ArsipController::store()`:
1. Validate + `Arsip::generateNoRegistrasi($request)` (prefix `KODEDEPT-YYMMDD-KODEUNIT-NNN`)
2. Upload `bukti_scan` (PDF/JPG/PNG max 10 MB) ke `storage/app/public/bukti_scan/`
3. Hitung `total_qty_in/out` dari items
4. Simpan detail items per jenis (Adjust/Mutasi/Bundel = delete+insert; Produk Baru = **upsert** untuk preserve barcode)
5. `SyncsRequesters::syncArsipRequesters()` — pivot multi-pemohon
6. `HandlesApproval::initApprovalChain()` — generate chain + auto-TTD pemohon
7. Notify next approver (in-app + FCM push kalau user punya device_token)

### 8.2 Arsip Sistem (Superadmin trigger)
`superadmin.arsip.arsip-sistem` → `Superadmin\ArsipController::arsipSistem()` → `Arsip::processArchiving($id, $seq, $note)`:
1. Transaction:
2. Generate `no_registrasi` (kalau kosong) dari prefix dept/unit + sequence per hari per unit
3. Generate `no_doc` per jenis (prefix + sequence per tahun)
4. Set `status=Done, ba=Done, arsip=Done, ket_process=Done, updated_by=auth->id`
5. Append `catatan_it` dengan timestamp (bukan overwrite — preserve history)
6. Insert Notification untuk `admin_id` (pengaju)

### 8.3 Print / Show Document (PDF Merged)
`admin.arsip.show-document` → controller ambil arsip + lampiran → mPDF/FPDI merge:
- Halaman 1..N: `resources/views/print/arsip_draft.blade.php` (atau `arsip_draft_bundel.blade.php` untuk jenis Bundel)
- Halaman N+1..: setiap PDF di `arsip_lampiran` di-append via FPDI

### 8.4 Barcode Scan (Android)
`POST /api/arsip/scan` → `Api\BarcodeController::processScan()`:
- Input: `barcode` (dari kamera scanner)
- Cari arsip via no_registrasi/no_doc match
- Return detail JSON
- `updateStatus` — Android trigger `processArchiving()` remote

---

## 9. MOBILE (ANDROID) INTEGRATION

### 9.1 Endpoint API (semua di bawah `auth:sanctum` kecuali dicatat)
Referensi: [routes/api.php](routes/api.php)

| Method | Path | Fungsi |
|---|---|---|
| POST | `/api/login` | Login → return Sanctum token |
| GET | `/api/mobile/version` **(public)** | Splash cek versi APK |
| GET | `/api/mobile/versions` **(public)** | List semua app |
| POST | `/api/logout` | Revoke token + delete device_token |
| GET | `/api/arsip/dashboard` | Home stats |
| GET | `/api/arsip/master-data` | Dept/unit/manager/approver users/roles per jenis |
| POST | `/api/arsip/store` | Submit pengajuan dgn approvalChainJson opsional |
| GET | `/api/arsip` | List (query filter) |
| GET | `/api/arsip/{id}` | Detail enriched (`toApiDetailArray`) |
| GET | `/api/arsip/outstanding-ba` | List yg belum BA di-scan |
| POST | `/api/arsip/scan` | Barcode → arsip |
| POST | `/api/arsip/update-status` | Trigger archiving |
| GET | `/api/approvals` | Inbox pengajuan menunggu user |
| POST | `/api/arsip/{id}/approve` | Approve + auto-TTD |
| POST | `/api/arsip/{id}/reject` | Reject dgn note |
| POST | `/api/arsip/{id}/sign` | Manual TTD (Pemohon/Accounting) |
| GET | `/api/superadmin/server-stats` | Snapshot metrik server |
| GET | `/api/superadmin/server-stats/metrics` | Live sample (CPU/mem/disk) |
| GET | `/api/superadmin/activity-logs` | Audit log dgn filter |
| GET | `/api/superadmin/activity-logs/users` | Filter user options |
| POST | `/api/device-token` | Register FCM token |
| DELETE | `/api/device-token` | Unregister |
| POST | `/api/fcm/test` | Kirim push test |
| GET | `/api/notifications` | In-app list |
| GET | `/api/notifications/unread-count` | Badge |
| POST | `/api/notifications/{id}/read` | Mark read |
| POST | `/api/notifications/read-all` | Mark all |

### 9.2 Auto-Update APK
- Table: `app_versions` (app_slug, version_name, version_code, apk_path, apk_url_override, force_update, changelog, file_size, file_hash)
- Superadmin UI: `superadmin.app-versions.index` (form upsert + upload APK max 250 MB + audit log via `Log::info`)
- APK disimpan `storage/app/public/apk/{slug}-{version}-{code}.apk`
- Android splash panggil `/api/mobile/version?app=itsubmissions` sebelum login, prompt update jika `versionCode` server > local `BuildConfig.VERSION_CODE`.

### 9.3 Gap Android vs Web (untuk sync ke depan — dari DEVLOG 2026-07-27)
1. Android tidak kirim `tgl_pengajuan` → backend fallback `now()` (tidak bisa backdate)
2. Android kirim `pemohon` string (bukan `requesters[]` array id) → multi-pemohon picker belum ada
3. Cancel: Android kirim `kategori` di `detailJson`, backend expect top-level → cek payload
4. Bonus: Android punya **structured detail barang** + **custom approval chain per jenis** — improvement, biarkan

### 9.4 Menu Web-Only (di-hide di Android per 2026-07-27)
`Lokasi Fisik`, `User`, `Laporan`, `Setting`, `Profile` — belum implement native, sembunyikan lewat `visible=false` di `navigation_drawer.xml` + guard `applyRoleVisibility()`.

---

## 10. STRUKTUR CODEBASE

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/        (7 controller: Arsip, Dashboard, Profile, Notification, Price, ArsipShare, ArsipNote)
│   │   ├── Superadmin/   (14 controller: Arsip, Dashboard, User, Department, Unit, Manager, Location, Product, ActivityLog, ServerStat, Setting, Backup, Laporan, AppVersion, PengajuanAccess, Export, Profile, Notification)
│   │   ├── Api/          (9 controller: Auth, Barcode, ArsipApi, Approval, ServerStat, ActivityLog, AppVersion, Fcm, Notification)
│   │   ├── Auth/         (Login, AccountSetup — link_nik + force change password)
│   │   ├── VerificationController.php  (public /verify/{token})
│   │   ├── DashboardStatController.php (shared popup endpoint)
│   │   └── NotificationController.php  (common bell)
│   └── Middleware/       (RoleMiddleware, ForceChangePassword, EnsureLinkedToNik, TrustProxies)
├── Models/               (26 model — 10 arsip-related, 5 master, 4 support)
├── Traits/               (HandlesApproval, SignsArsip, SyncsRequesters, HasAuditLogs)
├── Services/             (ArsipLampiranService — hanya 1 service; area growth potensial)
├── Providers/            (AppServiceProvider: gate view-price, view composer global)
└── Helpers/              (ArsipFormatter.php — global autoload di composer.json)

resources/views/
├── layouts/              (app.blade + sidebar/admin,superadmin)
├── admin/                (arsip {index 1315L, _create 320L, _edit 286L, shared_inbox, _share_modal, _note_modal}, dashboard 968L, prices, profile)
├── superadmin/           (arsip {index 1570L, _view, _create, _edit, _arsip_sistem}, dashboard 1167L, app_versions, server_stats, backup, activity_logs, settings, pengajuan_access, products)
├── auth/                 (login, link_nik, change_password)
├── verify/               (show 420L, invalid)
├── partials/             (_pemohon_picker, _approver_select, _approval_timeline, _lampiran_modal, _dashboard_*)
├── print/                (arsip_draft 833L, arsip_draft_bundel, arsip_notes_attachment, lampiran_placeholder)
├── notifications/        (admin, superadmin, index)
├── vendor/pdfjs/         (PDF.js viewer)
└── laporan/              (index, pdf, pdf_viewer)

routes/
├── web.php  (auth + admin/{resource,prices,shared-inbox,approvals} + superadmin/{arsip,master,server-stats,app-versions,activity-logs,backup,pengajuan-access,settings})
├── api.php  (public /login + /mobile/version; sanctum {arsip crud, approvals, superadmin tools, fcm, notifications})
└── console.php

database/
├── migrations/           (67 migration — timeline 2026-01-06 sd 2026-07-20)
├── seeders/
└── factories/

docker/
└── php/Dockerfile        (PHP 8.2-fpm + ext-gd/bz2/zip + upload 250M)

docs/manual-book/         (6 SVG panduan end-user + README.md)
scripts/                  (deploy + fix-upload-size.sh + start-dev-tunnel.bat)
```

---

## 11. TECH DEBT & KANDIDAT REFAKTOR

Prioritas berdasarkan impact terhadap rancang-ulang.

### HIGH IMPACT
1. **God-object controllers**
   - `Admin\ArsipController` 878 baris, `Superadmin\ArsipController` 1020 baris
   - Isi: CRUD + approval init + signature + lampiran + detail items sync + tindakan IT
   - **Action**: extract → `Services/ArsipDetailService`, `Services/ArsipArchivingService`, `Http/Requests/ArsipStoreRequest+UpdateRequest`

2. **Duplikasi Admin ↔ Superadmin**
   - `saveDetailItems()`, `syncProdukBaruItems()`, sebagian besar `store()` logic ada di kedua tempat
   - View juga: `admin/arsip/_create.blade.php` vs `superadmin/arsip/_create.blade.php` nearly identical
   - **Action**: extract ke shared service + shared blade component

3. **View monolitik**
   - `admin/arsip/index.blade.php` 1315 baris (filter + stats + table + modals + 1200 baris JS)
   - `superadmin/dashboard/index.blade.php` 1167 baris
   - `print/arsip_draft.blade.php` 833 baris hardcode 5+ signature columns
   - **Action**: split ke `_filter/_stats/_table.blade.php` per section; ekstrak JS ke `public/js/arsip-index.js`

### MEDIUM IMPACT
4. **Hard-coded approval config**
   - `ArsipApproval::rolesForJenis()` — chain per jenis di code
   - **Action**: pindah ke tabel `approval_config` (jenis → array roles) supaya bisa di-manage superadmin tanpa deploy

5. **Legacy `signature_path` column**
   - `arsip_signatures.signature_path` NULL untuk semua signature baru (PNG specimen tidak dipakai lagi sejak refactor QR)
   - **Action**: hapus kolom di migration cleanup, hapus `hasSignature()`/`signatureUrl()` di `User.php`

6. **No Form Request classes**
   - Semua validasi inline di controller
   - **Action**: `php artisan make:request Arsip\StoreRequest UpdateRequest ApproveRequest`

7. **jQuery-heavy frontend**
   - AJAX pakai `$.ajax`, DOM manip pakai jQuery. Alpine sudah ada di beberapa view (mixed pattern)
   - **Action**: pilih satu (Alpine + Fetch API atau tetap jQuery); hapus legacy

### LOW IMPACT / DESIGN DECISION
8. **`detail_barang` JSON + rows** (dual source) — **acceptable if sync via service**
9. **Mobile-only auth restriction** — by design (approval tier enforcement)
10. **Auto-reload dashboard 60s** — bisa switch ke websocket (Reverb sudah ada di container `it_assistant_reverb`, belum dipakai untuk e-arsip)

---

## 12. DEPLOYMENT & OPS

### 12.1 Topologi
| Lokasi | Host | Peran | Status |
|---|---|---|---|
| Trikasa | 192.168.11.191 | (tunggal, belum diverifikasi 2026-06-04) | — |
| Inkasa | 192.168.11.199 | Development | Full-sync `d3f1c2b` per 2026-07-21 |
| Inkasa | 192.168.11.200 | **Production** | ✅ verified 2026-07-28 — **DRIFT jauh (lihat seksi 15)** |

**Path project**: `/root/it_submissions` (dev **DAN prod** — bukan `/var/www` seperti diperkirakan sebelumnya).
**App user aplikasi**: `fahmi` (superadmin), password default `bismillah`.

### 12.2 Container Docker
**Prod (`.200`) — 3 container running**:
- `it_app` (custom image `it_submissions-app`, PHP 8.2.30, Up 2 months)
- `it_nginx` (`nginx:alpine`, port 80)
- `it_db` (`mysql:8.0`)

**Dev (`.199`) — 6 container** (per DEVLOG 2026-07-21): tambah `it_assistant_reverb`, `it_assistant_app`, `inkalum_db`. Assistant + inkalum_db mungkin di server lain untuk prod.

**Note deploy**: host PHP tidak punya ext-gd → composer install harus `docker exec it_app composer install --no-dev --optimize-autoloader`. Migrasi `docker exec it_app php artisan migrate --force`.

### 12.3 Cloudflare Tunnel
- `dev-it-sub.inkalum.com` → `http://localhost:8003` (route via `ADI` laptop connector = Laragon)
- `lin-dev-it-sub.inkalum.com` → `http://192.168.11.199` (Linux dev, stabil)
- **Batasan CF Free plan**: body max 100 MB — APK > 100 MB harus bypass CF (direct LAN atau upgrade plan)

### 12.4 Deploy Script
- `deploy-dev.ps1` (belum auto-detect Docker; sekarang manual step-by-step)
- `scripts/fix-upload-size.sh` — hot-fix upload size tanpa rebuild image
- `start-dev-tunnel.bat` — Windows helper multi-worker `PHP_CLI_SERVER_WORKERS=4` (fix 502 burst)

---

## 13. REKOMENDASI RANCANG-ULANG

Kategori proposal, bukan komitmen. Pilih sesuai prioritas bisnis.

### A. Refactor Layered (medium-effort, high-value)
1. **Introduce Service Layer** untuk pisahkan business logic dari controller:
   - `ArsipSubmissionService` (store/update, delegate ke ArsipDetailService + HandlesApproval)
   - `ArsipDetailService` (single source of truth untuk detail items + JSON sync)
   - `ArsipArchivingService` (extract dari model static `processArchiving`)
   - `ArsipPdfService` (draft + merged document)
2. **Form Request classes** — validasi terpisah, reusable
3. **API Resource classes** — normalize response shape (sekarang campur `toArray()` + custom fields)
4. **Approval config ke DB** — bisa manage jenis + chain tanpa deploy

### B. Frontend Modernization (high-effort)
1. Adopsi **Livewire 3** atau **Inertia + Vue** untuk area kompleks (form pengajuan, dashboard) — hilangkan 1200-baris jQuery
2. Split monolitik view ke Blade component (`<x-arsip.filter>`, `<x-arsip.table>`)
3. Pisah asset build → Vite (sudah punya `vite.config.js`, tapi belum dipakai maksimal — banyak asset via CDN + inline)

### C. Real-time (medium-effort)
1. Pakai **Laravel Reverb** (container sudah running) untuk approval notification real-time (ganti polling 10s/30s)
2. Live-update inbox approver saat pengajuan baru masuk
3. Live-update dashboard tanpa reload 60s

### D. Test Coverage (currently 0)
1. Feature tests untuk approval flow (submit → approve × N → done, delegasi, reject)
2. Feature tests untuk API mobile (login, submit, approve)
3. Unit test untuk `SignsArsip::applySignature()` (hash consistency), `ArsipApproval::generateFor()` (chain + delegation)

### E. Observability
1. Sentry / Bugsnag integration untuk exception tracking (sekarang hanya `storage/logs/laravel.log`)
2. Prometheus/Grafana untuk server-stats endpoint (sudah ada JSON snapshot, tinggal expose sebagai metrics)
3. Structured logging untuk audit trail (bukan `Log::info` free-text)

### F. Security Hardening
1. Rotasi password root SSH → **key-based auth** (memory catatan sudah ada, belum dilakukan)
2. Hapus `public/phpinfo.php` (masih untracked di git status — leaks server config)
3. Sanctum token expiry (default: never expire — set 30 hari untuk mobile?)
4. Rate limiting API `/login` (throttle brute-force)

### G. Data & Cleanup
1. Drop `arsip_signatures.signature_path` (legacy)
2. Drop `users_staging` kalau HR sync sudah stabil
3. Archive old audit_logs (> 6 bulan) ke tabel terpisah
4. Uji strategi backup DB (endpoint `backup.export` ada, tapi tidak automated)

---

## 14. REFERENSI

- **DEVLOG.md** — log kerja per sesi (newest first)
- **docs/manual-book/** — 5 SVG panduan end-user (login, buat pengajuan, approval, tracking, arsip)
- **Memory index**: `C:\Users\ADI EDP\.claude\projects\c--laragon-www-e-arsip\memory\MEMORY.md`
- **Android project**: `C:\Users\ADI EDP\AndroidStudioProjects\ITSubmissions\`
- **Test URLs**:
  - Dev tunnel: `https://dev-it-sub.inkalum.com` / `https://lin-dev-it-sub.inkalum.com`
  - Local: `http://localhost:8003` (Laragon) atau `http://192.168.11.199` (dev Linux LAN)

---

## 15. STATE PROD TERVERIFIKASI (SSH 2026-07-28)

> **CRITICAL**: prod `.200` **DRIFT SANGAT JAUH** dari local/dev. Ini bukan sekadar "belum di-deploy update terbaru" — prod stuck di era **April 2026 (pra-approval-chain, pra-delegasi, pra-multi-pemohon)**. Rancang-ulang HARUS mempertimbangkan migrasi data existing 1586 arsip.

### 15.1 Server Identity
| Field | Nilai |
|---|---|
| Hostname | `it-submission` |
| OS | Linux 6.17.2-1-pve (PVE = Proxmox VE container) |
| Timezone | UTC (`Tue Jul 28 08:43:29 UTC 2026`) |
| Disk | 100 GB (3.8 GB used, 97 GB free — lega banget) |
| Project dir | `/root/it_submissions` (bukan `/var/www`) |
| Backup exist | `/root/it_submissions_backup_2026-02-16/` |

### 15.2 Docker Containers (3 saja — beda dari dev 6 container)
| Name | Image | Status |
|---|---|---|
| `it_app` | `it_submissions-app` (custom, PHP 8.2.30) | Up 2 months |
| `it_nginx` | `nginx:alpine` | Up 2 months, port 80 |
| `it_db` | `mysql:8.0` | Up 2 months |

**Note**: prod TIDAK punya `it_assistant_*` (ITAsistant belum aktif di prod) dan tidak punya `inkalum_db` (kemungkinan sudah di-container-kan terpisah atau di server lain).

### 15.3 Git State
| Field | Nilai |
|---|---|
| Branch | `main` |
| HEAD | `147ff58 first commit` |
| Behind origin/main | **8+ commits** (origin punya `6b9d019`, `4cf76ca`, `f1f4051`, `01c50d6`, `7131e8c`, `d3f1c2b`, `62e2ed4`, `a7816ee`) |
| Modified uncommitted | **19 file** (kombinasi controller + view + gitignore drift) |

**File uncommitted di prod (drift):**
```
M app/Http/Controllers/Superadmin/UserController.php
M app/Http/Middleware/RoleMiddleware.php
M bootstrap/cache/.gitignore
M resources/views/auth/login.blade.php
M resources/views/layouts/app.blade.php
M resources/views/users/create.blade.php
M resources/views/users/edit.blade.php
M resources/views/users/index.blade.php
M routes/web.php
+ 10 file .gitignore di storage/
```

**Historikal prod (last 8 commits)**: semua bernama "first commit" (kemungkinan force-push berulang dulu, riwayat prod tidak berlurus dgn local).

### 15.4 Migration Gap
| Metrik | Prod | Local |
|---|---|---|
| Total migration files | 30 | 67 |
| Migration ran | 30 (batch 20) | 67 |
| Gap | **-37 migration** | — |

**Migration terakhir yg ran di prod**: `2026_04_14_111647_create_locations_table` (batch 20).

**Migration YG BELUM DI PROD (semua fitur besar sejak Mei 2026):**
- ✗ `create_audit_logs_table` (2026-05-16) — audit trail
- ✗ `create_products_table` + `add_accounting_role_to_users` (2026-05-09) — role accounting
- ✗ `add_scan_ba_accounting_and_status_columns` (2026-05-09)
- ✗ `add_tindakan_and_catatan_it_to_arsips` + `create_arsip_tindakan_items` (2026-05-22)
- ✗ `create_device_tokens` (2026-05-23) — FCM push
- ✗ `create_arsip_produk_baru_items` + `add_produk_baru_to_jenis_pengajuan_enum` (2026-05-26)
- ✗ `create_digital_signature_tables` (2026-05-29) — **arsip_signatures**
- ✗ `create_approval_chain_tables` (2026-05-29) — **arsip_approvals**
- ✗ `add_employee_id_and_work_unit_to_users` (2026-06-04) — HR sync fields
- ✗ `add_code_to_units_table` (2026-06-04)
- ✗ `create_users_staging_table` (2026-06-04)
- ✗ `create_submission_requesters_table` (2026-06-04) — **arsip_requesters** (multi-pemohon)
- ✗ `create_arsip_lampiran_table` (2026-06-06) — **lampiran multi-file**
- ✗ `add_last_login_to_users` (2026-06-13)
- ✗ `add_spv_kabag_manager_roles_to_users_table` (2026-06-19) — **3 role baru**
- ✗ `strip_nik_prefix_from_existing_usernames` (2026-06-19)
- ✗ `reset_hr_import_passwords_to_role_default` (2026-06-19)
- ✗ `create_item_prices_and_add_harga_to_items` (2026-06-19)
- ✗ `create_user_pengajuan_access` (2026-06-19) — DEPRECATED (superseded)
- ✗ `create_arsip_shares` (2026-06-19) — **Layer 2 access**
- ✗ `create_role_pengajuan_access` (2026-06-20) — **role-based access control**
- ✗ `extend_arsip_shares_for_role_target` (2026-06-22)
- ✗ `create_arsip_personal_notes` (2026-06-22)
- ✗ `add_approval_delegation_fields` (2026-07-14) — **delegasi TTD**
- ✗ `create_app_versions_table` (2026-07-20) — **APK versioning**

### 15.5 Data Prod (LIVE)
| Metrik | Nilai |
|---|---|
| Total arsip | **1586** |
| Total user | **176** |
| Distribusi role | superadmin=6, admin=168, `user`=2 (role legacy, sudah tidak dipakai) |
| Jenis breakdown | Cancel: 1071 (67%) · Adjust: 164 · Internal_Memo: 198 · Mutasi_Produk: 42 · Bundel: 29 · Mutasi_Billet: 82 · Produk_Baru: 0 |

**Kritikal utk migrasi**:
- 176 user existing **belum ada yg berperan SPV/Kabag/Manager/Accounting** — approval chain harus di-backfill (siapa approve apa) sebelum enable.
- 1586 arsip existing **tidak punya**: `verify_token`, entry di `arsip_approvals`, `arsip_signatures`. Kalau feature diaktifkan, arsip lama harus di-mark as "legacy pre-approval" atau di-backfill dengan Pemohon-only chain.
- Kolom baru di `arsips` (`tindakan`, `catatan_it`, `scan_ba_accounting`, `scan_final`, `updated_by`) belum ada — akan default null after migration.

### 15.6 Konfigurasi Server (Runtime)
| Setting | Prod | Local Dockerfile |
|---|---|---|
| Nginx `client_max_body_size` | **50 MB** | tidak set |
| PHP `upload_max_filesize` | **2 MB** (DEFAULT) | 250 MB |
| PHP `post_max_size` | **8 MB** (DEFAULT) | 250 MB |
| PHP `memory_limit` | **128 MB** (DEFAULT) | 512 MB |
| PHP version | 8.2.30 | 8.2 (Dockerfile) |

**Impact**:
- Upload bukti_scan > 2 MB → gagal
- Bulk operation / PDF generation besar → OOM (Laravel log confirm: dompdf error `Allowed memory size of 134217728 bytes exhausted at Cpdf.php:6171`)
- Container `it_app` di prod dibangun dari image lama tanpa `uploads.ini` (`docker/php/Dockerfile` update baru di local, belum di-rebuild+push ke prod)

### 15.7 Log Analysis
- Log terakhir: **2026-06-29 21:16:36** (SSO login superadmin id=189). Berarti prod low-activity 30 hari terakhir (atau log rotate — perlu cek `storage/logs/`).
- Pattern error dominan Juni: **memory exhaust dompdf** (PDF gen gagal karena `memory_limit=128M`). Sebelum re-arsitek, fix konfigurasi PHP dulu.

### 15.8 Storage Usage
```
storage/app/public/bukti_scan  = 873 MB  (arsip scan PDF)
storage/app/public/settings    =  53 KB  (logo/branding)
Total storage                  = 873 MB
```
Belum ada folder `apk/` (fitur belum ada), belum ada `lampiran/` (fitur belum ada).

### 15.9 KESIMPULAN PLAN MIGRASI

Prod **tidak bisa langsung** di-deploy `git pull` + `migrate`. Perlu **staged rollout**:

**Stage 0 — Prep** (satu hari):
1. Backup DB penuh (`mysqldump inkalum_db > backup_20260728.sql`)
2. Backup folder `storage/` (873 MB → tar.gz)
3. Backup image container running (`docker save it_submissions-app > it_app_snapshot.tar`)
4. Commit 19 file uncommitted (atau stash + investigate — apa isinya)
5. Tune PHP config (uploads.ini) sebelum apapun — quick win, fix upload + OOM

**Stage 1 — Migration Sync** (staged, satu batch per jenis):
1. Merge origin/main ke prod branch (may need conflict resolve untuk 19 file drift)
2. Run migration bertahap:
   - Batch A: audit_logs + products + accounting role (2026-05-09..16) → minor
   - Batch B: digital_signature + approval_chain (2026-05-29) → **backfill arsip_signatures kosong utk 1586 arsip lama** (mark as pre-approval)
   - Batch C: spv/kabag/manager roles (2026-06-19) → **manual assignment role per user** (176 user, based on jabatan HR)
   - Batch D: arsip_shares + role_pengajuan_access (2026-06-19..20) → **seed default: setiap role dapat semua jenis awal**
   - Batch E: multi-pemohon + lampiran + personal notes (2026-06-04..22)
   - Batch F: delegation (2026-07-14) — opsional per user
   - Batch G: app_versions + APK upload (2026-07-20)

**Stage 2 — Feature Flag Rollout**:
- Deploy code baru tapi feature-gate approval chain (bisa disable via env)
- Aktifkan per department dulu (mis. Anodize) → uji seminggu → rollout
- Arsip lama tetap bisa dilihat tanpa chain (`hasApprovalChain()` return false → UI tampilkan "legacy")

**Stage 3 — Full Sync**:
- Rebuild `it_app` container dgn Dockerfile terbaru (upload_max 250M, memory 512M)
- Deploy container + full migration
- Health check semua endpoint

### 15.10 REKOMENDASI KEPUTUSAN

Karena drift prod 3+ bulan + data live 1586 arsip + user tergantung sistem harian, ada **3 opsi strategis**:

**Opsi A — Big Bang Migration** (risky, cepat, 2-3 hari):
- Down window 1-2 jam
- Run semua migration + deploy code + backfill data
- Risk: kalau ada bug di migration/logic, rollback ribet karena data sudah bermigrasi

**Opsi B — Staged Rollout** (aman, 2-3 minggu — **RECOMMENDED**):
- Ikuti Stage 0-3 di atas
- Tiap batch di-test di dev `.199` dulu, baru ke prod
- Feature flag mengontrol aktivasi per fitur

**Opsi C — Green-Blue Deploy** (paling aman, 1 bulan):
- Bikin container `it_app_v2` paralel dgn migration+code baru
- Route traffic 5% → 20% → 50% → 100% ke v2
- Rollback = flip nginx upstream

**Aku sarankan Opsi B**. Alasan: data prod live tapi low-activity (log terakhir sebulan lalu), risk absorbable. Big Bang overkill; Blue-Green over-engineered untuk 176 user.

---

## 16. ACTION ITEMS UNTUK VERIFIKASI PROD LANJUTAN

Snippet siap-copy untuk verifikasi tambahan (aku bisa jalankan dari sini kalau perlu, atau user run sendiri):

- [ ] SSH ke prod → `git log -1` cek commit HEAD; bandingkan dgn local `d3f1c2b`
- [ ] `docker ps` cek 6 container running, uptime, image tag
- [ ] `docker exec it_app php artisan migrate:status` — konfirmasi 67 migration sudah run
- [ ] Cek `arsips` count vs local dev (drift data)
- [ ] Cek storage disk: `du -sh /var/www/storage/app/public/{apk,bukti_scan,lampiran}` (growth planning)
- [ ] Verify CF tunnel prod endpoint respond 200
- [ ] Health check `/api/mobile/version` prod
- [ ] Cek `laravel.log` prod untuk error frequency 7 hari terakhir

Command siap-copy untuk user (jalankan sendiri di PowerShell):
```powershell
echo y | plink -ssh -pw 'bismillah@' root@192.168.11.200 @"
cd /var/www 2>/dev/null || cd /root/it_submissions
git log -1 --oneline
docker ps --format 'table {{.Names}}\t{{.Image}}\t{{.Status}}'
docker exec it_app php artisan migrate:status | tail -20
docker exec it_app php artisan tinker --execute='echo App\Models\Arsip::count();'
du -sh storage/app/public/apk storage/app/public/bukti_scan storage/app/public/lampiran 2>/dev/null
curl -s -o /dev/null -w '%{http_code}\n' http://localhost/api/mobile/version?app=itsubmissions
tail -30 storage/logs/laravel.log
"@
```
