# DEVLOG — IT Submission (e_arsip)

Catatan kerja per sesi. Entri terbaru di atas.

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
