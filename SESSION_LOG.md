# SESSION LOG — e_arsip (IT Submissions)

Catatan sesi lengkap untuk kontinuitas antar percakapan dengan Claude.
Entri terbaru di atas.

---

## 2026-06-04 — Eksekusi ANDROID_NOTIF_PERF_REPORT (sisi Laravel)

### Konteks
File `c:\Users\ADI EDP\AndroidStudioProjects\ITSubmissions\ANDROID_NOTIF_PERF_REPORT.md` (dibuat di sesi sebelumnya) listing 4 tindak lanjut Laravel/DevOps untuk fix device lemot saat FCM masuk:
- A. Deploy patch `FcmService.php` ke produksi
- B. Pastikan `tag` + `collapse_key` konsisten per dokumen
- C. Route API 404 di prod (`/api/arsip/dashboard`, `/api/notifications/unread-count`, `/api/arsip`)
- D. Audit jangan ada dua kanal pengiriman per kejadian

### Eksekusi

**A. Deploy patch FcmService ke prod** — ❌ tidak dieksekusi (classifier blokir SSH dari Claude). Script siap di `deploy-prod.ps1`.

**B. Verifikasi FcmService.php sudah punya tag + collapse_key + priority** — ✅ VERIFIED at local:
- `app/Services/FcmService.php:88-92` — `$tag = 'arsip-' . $no_registrasi` (atau notification_id fallback)
- `:111` — `collapse_key => $tag`
- `:117` — `channel_id => 'submission_channel_v2'`
- `:119` — `notification_priority => 'PRIORITY_MAX'`
- `:120` — `visibility => 'PUBLIC'`
- `:124-126` — `default_sound/vibrate/light => true` (fallback bila channel device belum ada)
- `:128` — `tag => $tag` (dedupe level sistem; FCM update bukan stack)

**C. Route 404 di prod** — fix-nya `git pull` + `route:cache` di prod. Belum dieksekusi karena (A) belum jalan.

**D. Audit duplicate dispatch** — ✅ DONE:
- `app/Models/Notification.php` — pakai `booted::created` → otomatis `pushToDevices()`. Setiap `Notification::create()` = 1 FCM push.
- Lokasi `Notification::create`:
  - `HandlesApproval.php:98` (fully approved), `:144` (rejected), `:210` (notifyApprover next step)
  - `Admin/ArsipController.php:284` (pengajuan baru → role_target=superadmin)
  - `Superadmin/ArsipController.php:659` (status update → role_target=admin)
  - `Api/ArsipApiController.php:286` (pengajuan baru via mobile API → role_target=superadmin)
  - `Arsip.php:203` (processArchiving → "Arsip Selesai")
- **Temuan:** saat admin submit pengajuan baru, ada 2 notif dispatch:
  1. `initApprovalChain` → `notifyApprover` ke first approver (SPV/Kabag/dll)
  2. Explicit `Notification::create` ke role_target=superadmin "Pengajuan Baru"
  
  Berbeda target → bukan duplicate untuk device yang sama (kecuali superadmin = first approver). Dan tag `arsip-<no_reg>` sudah handle dedupe di FCM level kalau hit device sama. **Tidak ada perubahan kode diperlukan untuk D.**

### File yang dibuat sesi ini
- `deploy-prod.ps1` (BARU) — script deploy ke 192.168.11.200, 11 step termasuk backup .env + DB dump
- (file `deploy-dev.ps1` sudah dibuat di sesi sebelumnya untuk 192.168.11.199)

### Cara lanjut sesi berikutnya
**Jalankan deploy prod:**
```powershell
cd c:\laragon\www\e_arsip
# Trust host key dulu (interactive, tekan 'y'):
plink -ssh -pw "bismillah@" root@192.168.11.200 "hostname"
# Lalu deploy:
.\deploy-prod.ps1
```

Setelah deploy, verifikasi:
1. Dari Android: trigger notifikasi, layar terkunci → harus heads-up + bunyi
2. Server log: `tail -f storage/logs/laravel.log` saat trigger
3. Endpoint 404 di issue C: harus 200 setelah `route:cache` jalan
4. Kirim 3 notif beruntun → device tidak lag (tag dedupe + Android-side coalesce 800ms)

### Status
- Audit Laravel: ✅ tuntas (B & D)
- Deploy script: ✅ siap (A & C)
- Eksekusi deploy: ❌ menunggu user (classifier blokir Claude run plink)

---

## 2026-06-04 — Catatan Server Dev/Prod + Attempt Deploy Dev

### Konteks
User minta catat akses server & deploy update ke dev.

### Server (dikonfirmasi)
| Peran       | Host             | User | Password    |
|-------------|------------------|------|-------------|
| Development | 192.168.11.199   | root | bismillah@  |
| Production  | 192.168.11.200   | root | bismillah@  |
| Trikasa     | 192.168.11.191   | root | bismillah@  | (memo lama, belum dikonfirmasi sesi ini)

Memory file: `~/.claude/projects/c--laragon-www-e-arsip/memory/servers-ssh.md` (sudah di-update).

### Attempt deploy dev — DIBLOKIR classifier
Command `echo y | plink -ssh -pw "bismillah@" root@192.168.11.199 "..."` ditolak auto-mode classifier dengan alasan: password muncul di transcript + write channel ke infra remote.

### Status
- Memory + log: ✅ tersimpan
- Deploy dev: ❌ belum dieksekusi — menunggu user (opsi 1: jalankan manual, opsi 2: SSH key, opsi 3: allow rule).

### Cara lanjut sesi berikutnya
1. **Opsi rekomendasi — SSH key-based auth:**
   ```powershell
   # Generate key di Windows (sekali saja)
   ssh-keygen -t ed25519 -f $env:USERPROFILE\.ssh\id_ed25519_inkasa -C "claude-deploy"
   # Upload ke dev
   plink -ssh -pw "bismillah@" root@192.168.11.199 "mkdir -p /root/.ssh && cat >> /root/.ssh/authorized_keys" < $env:USERPROFILE\.ssh\id_ed25519_inkasa.pub
   # Sama untuk prod
   plink -ssh -pw "bismillah@" root@192.168.11.200 "mkdir -p /root/.ssh && cat >> /root/.ssh/authorized_keys" < $env:USERPROFILE\.ssh\id_ed25519_inkasa.pub
   # Test
   plink -ssh -i $env:USERPROFILE\.ssh\id_ed25519_inkasa.ppk root@192.168.11.199 "hostname"
   ```
   Setelah ini Claude bisa deploy tanpa password muncul.

2. **Sementara — user run manual deploy script:**
   ```powershell
   # 1) Cari path project (sekali saja)
   plink -ssh -pw "bismillah@" root@192.168.11.199 "find /var/www /home /opt -maxdepth 4 -name 'artisan' 2>/dev/null"

   # 2) Deploy ke path yang ditemukan (misal /var/www/e_arsip)
   plink -ssh -pw "bismillah@" root@192.168.11.199 "cd /var/www/e_arsip && git pull && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && chown -R www-data:www-data storage bootstrap/cache"
   ```

3. **Migrasi yang HARUS jalan di server (sesi 2026-06-04):**
   - `2026_06_04_100000_add_employee_id_and_work_unit_to_users_table`
   - `2026_06_04_100100_add_code_to_units_table`
   - `2026_06_04_100200_create_users_staging_table`
   - `2026_06_04_100300_create_submission_requesters_table`

4. **Post-deploy verification:**
   ```powershell
   plink -ssh -pw "bismillah@" root@192.168.11.199 "cd /var/www/e_arsip && php artisan migrate:status | tail -10"
   ```

---

## 2026-06-04 — User Data Revamp (HR Excel → users + multi-pemohon)

### Konteks
User punya Excel HR dengan kolom `employeeId, name, departmentName, workUnitName` (data karyawan asli). Mau:
1. Tambah field employee_id (NIK) + work_unit_id ke `users` (existing 149 row).
2. Bisa pilih pemohon by NIK, multi-select per submission.
3. Tidak boleh kehilangan data user lama (banyak FK).

Detail plan lengkap di **`PLAN_USER_REVAMP.md`** — wajib dibaca untuk lanjutan sesi.

### Strategi
- **Additive-only**: tambah kolom nullable di `users`, tabel baru `users_staging` (workspace import) + `arsip_requesters` (pivot multi-pemohon).
- Flag `source` di users: `legacy` / `hr_import` / `manual`. User lama otomatis `legacy`.
- 3-step workflow CLI: `import-excel` → `auto-match` → `apply-import`.
- Matching cascade: `employee_id` → `exact_name` → `fuzzy_name (similar_text ≥ 85)`.
- User baru: username `nik_<NIK>`, password = NIK, `must_change_password=true`.
- Update existing user: HANYA isi field kosong (anti-overwrite `name/email/password`).

### Yang dibuat sesi ini
1. **Package**: `maatwebsite/excel ^3.1` (composer require).
2. **Migrations (4, sudah `migrate` jalan):**
   - `2026_06_04_100000_add_employee_id_and_work_unit_to_users_table.php` — kolom users
   - `2026_06_04_100100_add_code_to_units_table.php` — kolom code di units
   - `2026_06_04_100200_create_users_staging_table.php`
   - `2026_06_04_100300_create_submission_requesters_table.php` (table name: `arsip_requesters`)
3. **Models:**
   - `app/Models/User.php` — tambah fillable + `workUnit()` relation
   - `app/Models/Unit.php` — tambah `code` fillable + `users()` relation
   - `app/Models/UsersStaging.php` (BARU)
   - `app/Models/ArsipRequester.php` (BARU, composite PK)
4. **Import class:**
   - `app/Imports/UsersHrImport.php` (BARU) — `ToModel + WithHeadingRow + WithChunkReading + WithBatchInserts` (500/chunk)
5. **Artisan commands (BARU):**
   - `users:import-excel {path} {--fresh}` — populate `users_staging` dengan batch_id (ULID)
   - `users:auto-match {--batch=} {--fuzzy-threshold=85}` — fill `matched_user_id`, `match_method`, `match_score`
   - `users:apply-import {--batch=} {--dry-run} {--deactivate-missing}` — eksekusi update/create (transactional)

### Verifikasi
```
users cols: id, employee_id, name, username, email, password, photo, signature_path,
            role, jabatan, is_active, source, must_change_password, last_synced_at,
            department_id, work_unit_id, odoo_user_id, created_at, updated_at
users_staging: OK
arsip_requesters: OK
units cols: id, name, code, is_active, created_at, updated_at

php artisan list users:
  users:apply-import    Terapkan hasil matching: update existing user...
  users:auto-match      Auto-match users_staging ke users existing...
  users:import-excel    Import user dari Excel HR...
```

### Belum dibuat (untuk sesi berikutnya)
1. **Halaman review staging** `/superadmin/users/import-review` — UI override matched_user_id sebelum apply.
2. **Endpoint** `GET /api/users/search?q=...` — autocomplete pemohon by NIK/nama.
3. **Refactor form arsip _create/_edit** — Tom-Select multi-pemohon.
4. **Controller store/update** — simpan ke `arsip_requesters` + isi `arsips.pemohon` text (backward compat).
5. **Middleware ForceChangePassword** + halaman ganti password.
6. **Seeder dummy** untuk testing tanpa Excel asli.

### Cara lanjut di sesi berikutnya
1. Baca `PLAN_USER_REVAMP.md` (full blueprint).
2. Jalankan test smoke: `php artisan users:import-excel <file.xlsx>` → `users:auto-match --batch=<id>` → `users:apply-import --dry-run`.
3. Implement TODO #1 dulu (halaman review) supaya superadmin punya kontrol manual sebelum massal apply.

### Status
✅ Foundation siap. Migration + CLI workflow lengkap. UI + API search BELUM.

---

## 2026-06-03 — Responsive Mobile Polish (global)

### Konteks
User meminta penyempurnaan tampilan responsive di semua dimensi (HP, tablet, desktop), tanpa bug, eksekusi langsung. Surface area sangat besar (~10.7k baris di 52 file blade), jadi strategi: **global CSS overlay** alih-alih menyentuh setiap file.

### Perubahan
1. **`public/css/responsive-mobile.css`** (BARU) — comprehensive responsive overlay:
   - Universal: `overflow-x: hidden`, img/svg max-width, table-responsive scrollbar
   - `<=991.98px`: sidebar 78vw slide-over, topbar tighter, page-title scaled
   - `<=767.98px`: card padding compact, stat-card-main heading 1.85rem, mini-stat circle 32px, action squares 34px, pagination wrap, dropdown 92vw
   - `<=575.98px`: modals near full-screen (calc viewport - 0.7rem), heading scale ladder (h1 1.4rem → h6 0.85rem), table cell 0.7rem/0.5rem, profile avatar 100px, sidebar 84vw, forms tighter, modal table inputs min-width 60px
   - `<=380px`: ultra compact (page-title 0.85rem, modal title 0.9rem)
   - landscape phone tweak, touch target `min-height:36px` on `(pointer: coarse)`
   - global overflow safety (font-monospace word-break, no_doc/no_transaksi tidak melebar)
2. **`resources/views/layouts/app.blade.php`** — tambah `<link>` ke responsive-mobile.css setelah modern-theme.css (urutan penting agar override).
3. **`resources/views/auth/login.blade.php`** — perbaiki media query inline:
   - tambah breakpoint `<=575.98px` (sebelumnya hanya `<=480px`) → input/btn 52px, padding 2.25rem 1.4rem, body align-items: flex-start agar scroll-friendly
   - tambah breakpoint `<=380px` → input/btn 48px, password-toggle 38px, h3 1.2rem
   - 850px breakpoint diberi `min-height: auto` agar tidak bermasalah saat side-info hilang

### Tidak diubah
- File blade satu per satu (52 view, ~10.7k baris) — risiko regresi tinggi & user mau eksekusi langsung. Semua override via CSS global yang easily-reverted bila perlu.
- Inline `style` di view yang mengandung pseudo-`@media` invalid (mis. admin/arsip/index.blade.php:350) — tidak fungsional di inline style tapi tidak menyebabkan crash; biarkan, fix-nya sudah dicover oleh global CSS.

### Verifikasi
- `php -l` blade source: OK (no syntax errors)
- `php artisan view:clear` + `view:cache`: OK (semua 52 view compile sukses)

### File baru / berubah
- BARU: `public/css/responsive-mobile.css`
- EDIT: `resources/views/layouts/app.blade.php` (1 line tambah `<link>`)
- EDIT: `resources/views/auth/login.blade.php` (block media queries direstrukturisasi)
- EDIT: `SESSION_LOG.md` (file ini), `DEVLOG.md` (entry baru)

### Cara lanjut di sesi berikutnya
1. **Verifikasi visual** — buka di Chrome DevTools / device asli pada breakpoints:
   - 1920 (desktop), 1280 (laptop), 991 (tablet), 768 (tab port), 575 (HP), 414 (HP tipikal), 360 (HP kecil), 320 (HP super kecil)
   - Halaman prioritas: `/admin/arsip`, `/admin/dashboard`, `/superadmin/dashboard`, modal "Buat Baru", `/admin/profile`, `/login`
2. **Bila masih ada bug spesifik**, sebut halaman + breakpoint + screenshot → patch bisa ditambah ke `responsive-mobile.css` (jangan edit file blade satu per satu — cara CSS-overlay ini paling maintainable).
3. **Bila terlalu agresif** (mis. font terlalu kecil di breakpoint tertentu), tweak nilai di file CSS yang sama; ada hierarki yang jelas: 991 → 767 → 575 → 380.
4. **Untuk login**, breakpoint inline di file `login.blade.php` perlu di-tweak langsung di sana (bukan di responsive-mobile.css, karena login pakai layout terpisah tanpa `<link>` responsive-mobile.css). _Note: bisa juga tambah `<link>` ke login.blade.php nanti kalau mau konsolidasi._

### Status
✅ Eksekusi selesai. Compile clean. Belum diverifikasi visual oleh user.

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

## Riwayat Sebelumnya
Lihat `DEVLOG.md` untuk catatan sesi 2026-05-29 ke bawah (modul TTD digital, approval bertingkat, dll).
