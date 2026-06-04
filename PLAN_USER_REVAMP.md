# PLAN — User Data Revamp (HR Excel → users + multi-pemohon)

Dokumen ini adalah blueprint kerja yang dieksekusi pada 2026-06-04.
Disimpan agar sesi Claude berikutnya bisa langsung melanjutkan.

---

## 0. Konteks

User mau menyatukan data karyawan HR (Excel: `employeeId, name, departmentName, workUnitName`) ke tabel `users` Laravel yang sudah ada (149 row). Lalu di submission (arsip) ingin bisa pilih **multi-pemohon by NIK/employeeId**.

**Constraint utama:** TIDAK boleh kehilangan user lama (banyak FK ke `arsips`, `arsip_approvals`, `notifications`, dll).

---

## 1. Strategi Migrasi

**Additive only, non-destructive:**
- Tambah kolom **nullable** ke `users` (`employee_id`, `work_unit_id`, `odoo_user_id`, `source`, `must_change_password`, `last_synced_at`).
- Tambah kolom `code` ke `units` (nullable).
- Tabel baru `users_staging` (workspace import) + `arsip_requesters` (pivot multi-pemohon).
- Flag `source`: `legacy` (default, data lama), `hr_import` (dari Excel), `manual` (admin tambah).
- User lama tanpa NIK tetap utuh; bisa dideaktivasi via flag `--deactivate-missing` (opsional).

---

## 2. Skema Database (yang baru / berubah)

### 2.1 `users` — kolom tambahan

| Kolom | Tipe | Null | Default | Catatan |
|---|---|---|---|---|
| employee_id | varchar(20) | YES | NULL | UNIQUE — NIK dari HR |
| work_unit_id | bigint | YES | NULL | FK → `units.id` (ON DELETE SET NULL) |
| odoo_user_id | int | YES | NULL | mapping res.users (fase Odoo) |
| source | enum | NO | `legacy` | `legacy / hr_import / manual` |
| must_change_password | boolean | NO | 0 | force password change at first login |
| last_synced_at | timestamp | YES | NULL | kapan terakhir update dari HR |

### 2.2 `units` — kolom tambahan

| Kolom | Tipe | Null | Catatan |
|---|---|---|---|
| code | varchar(20) | YES | kode unit (mis. `U3A`) |

### 2.3 `users_staging` — tabel baru (workspace import)

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint pk | |
| employee_id | varchar(20) | indexed |
| name | varchar(150) | |
| department_name | varchar(150) nullable | raw dari Excel |
| work_unit_name | varchar(150) nullable | raw dari Excel |
| matched_user_id | bigint nullable | FK → users (nullOnDelete) |
| match_method | enum | `exact_name / fuzzy_name / employee_id / manual / new` |
| match_score | tinyint nullable | 0..100 |
| status | enum | `pending / reviewed / applied / skipped` |
| notes | text nullable | |
| batch_id | varchar(40) | ULID — index |
| timestamps | | |

### 2.4 `arsip_requesters` — tabel baru (pivot multi-pemohon)

| Kolom | Tipe | Catatan |
|---|---|---|
| arsip_id | bigint | composite PK; FK → `arsips.id` (cascade) |
| user_id | bigint | composite PK; FK → `users.id` |
| employee_id | varchar(20) | **snapshot anti-rename** |
| name_snapshot | varchar(150) | snapshot |
| is_primary | boolean | true untuk pemohon utama (1 per arsip) |
| created_at | timestamp | |

Kolom lama `arsips.pemohon` (TEXT) tetap dipertahankan untuk backward compatibility data lama; submission BARU menggunakan pivot.

---

## 3. Alur Eksekusi (CLI workflow)

```bash
# 1) Backup dulu (manual, di luar artisan)
mysqldump -u root e_arsip > backup_2026-06-04.sql

# 2) Migrate (additive)
php artisan migrate
# → 4 migration baru: users cols, units.code, users_staging, arsip_requesters

# 3) Import Excel dari HR
php artisan users:import-excel /path/to/users_hr.xlsx --fresh
# → tulis ke users_staging dengan status=pending + batch_id

# 4) Auto-match
php artisan users:auto-match --batch=<batch_id_dari_step_3>
# Urutan match: employee_id → exact_name → fuzzy_name (similar_text ≥ 85)
# → users_staging.match_method + match_score terisi

# 5) (Opsional) Review hasil match
# Buat halaman /superadmin/users/import-review (belum dibuat sesi ini)
# Tampilkan: row dengan match_score < 100 + match_method=new untuk konfirmasi

# 6) Dry-run apply
php artisan users:apply-import --batch=<batch_id> --dry-run

# 7) Apply sungguhan
php artisan users:apply-import --batch=<batch_id>
# Atau dengan deactivate user legacy:
php artisan users:apply-import --batch=<batch_id> --deactivate-missing
```

**Rules saat apply:**
- Existing user (match found): UPDATE field yang masih **kosong** saja (`employee_id`, `work_unit_id`, `department_id`, `last_synced_at`). Field `name`, `email`, `password` TIDAK pernah ditimpa.
- New user: CREATE dengan:
  - `username = nik_<employee_id>` (collision suffix bila tabrakan)
  - `email = <employee_id>@placeholder.local` (admin edit nanti)
  - `password = bcrypt(<employee_id>)` (default = NIK)
  - `must_change_password = true`
  - `role = 'admin'` (= "pengaju" di terminologi lama)
  - `source = 'hr_import'`
- `--deactivate-missing`: user `source=legacy` + `employee_id IS NULL` + role bukan superadmin yang TIDAK ada di batch akan `is_active=0` (tidak dihapus; FK aman).

---

## 4. Multi-Pemohon (API contract)

### Search user (autocomplete)
```
GET /api/v1/users/search?q=4492
→ [{id, employee_id, name, department:{...}, work_unit:{...}}]
```

### Submit dengan multi-pemohon
```json
POST /api/v1/submissions
{
  ...form fields...,
  "requesters": [
    {"user_id": 12, "is_primary": true},
    {"user_id": 45, "is_primary": false},
    {"user_id": 78, "is_primary": false}
  ]
}
```

Server logic:
```
foreach r in request.requesters:
    user = User.findOrFail(r.user_id)
    ArsipRequester.create({
        arsip_id: submission.id,
        user_id: user.id,
        employee_id: user.employee_id,        # snapshot
        name_snapshot: user.name,             # snapshot
        is_primary: r.is_primary
    })
# Backward compat: isi arsips.pemohon (TEXT) = join nama
submission.pemohon = collect(requesters).pluck('name_snapshot').join(', ')
submission.save()
```

---

## 5. Status Eksekusi 2026-06-04

| Item | Status | File |
|---|---|---|
| Install maatwebsite/excel | ✅ | composer.json |
| Migration users (employee_id, work_unit_id, dll) | ✅ migrated | database/migrations/2026_06_04_100000 |
| Migration units.code | ✅ migrated | database/migrations/2026_06_04_100100 |
| Migration users_staging | ✅ migrated | database/migrations/2026_06_04_100200 |
| Migration arsip_requesters | ✅ migrated | database/migrations/2026_06_04_100300 |
| User model fillable + workUnit relation | ✅ | app/Models/User.php |
| Unit model fillable + users relation | ✅ | app/Models/Unit.php |
| UsersStaging model | ✅ | app/Models/UsersStaging.php |
| ArsipRequester model | ✅ | app/Models/ArsipRequester.php |
| UsersHrImport (ToModel + WithHeadingRow) | ✅ | app/Imports/UsersHrImport.php |
| `users:import-excel` command | ✅ | app/Console/Commands/ImportUsersExcelCommand.php |
| `users:auto-match` command | ✅ | app/Console/Commands/AutoMatchUsersCommand.php |
| `users:apply-import` command | ✅ | app/Console/Commands/ApplyUserImportCommand.php |
| Halaman review `/superadmin/users/import-review` | ⏳ TODO | belum dibuat |
| Endpoint `/api/users/search` (multi-pemohon picker) | ⏳ TODO | belum dibuat |
| Endpoint POST submission accept `requesters[]` | ⏳ TODO | belum dibuat |
| UI Form Create/Edit Arsip: pakai Tom-Select multi pemohon | ⏳ TODO | belum dibuat |
| Force change password page (first login) | ⏳ TODO | belum dibuat |

---

## 6. TODO Sesi Berikutnya

1. **Halaman review staging** — superadmin lihat hasil auto-match per batch, bisa override `matched_user_id`, ubah ke `status=reviewed`, lalu klik "Apply".
2. **Endpoint user search by NIK/nama** untuk autocomplete pemohon.
3. **Refactor form arsip _create / _edit (admin & superadmin)** ganti `<textarea name="pemohon">` jadi Tom-Select multi (lib: `tom-select` CDN).
4. **Refactor controller store/update** simpan ke `arsip_requesters` + tetap isi `arsips.pemohon` (backward compat).
5. **Middleware ForceChangePassword** untuk redirect user dengan `must_change_password=true` ke `/profile/change-password` setelah login.
6. **Seeder demo** untuk testing tanpa file Excel asli.

---

## 7. Rollback Plan (kalau perlu)

```bash
# Rollback urutan reverse
php artisan migrate:rollback --step=4

# Bila ada user baru yang sudah ter-create dan mau di-purge:
DELETE FROM users WHERE source = 'hr_import' AND created_at >= '2026-06-04';
# (FK arsip_requesters akan cascade-delete pivot row)
```

Semua perubahan additive sehingga rollback aman (tidak akan kehilangan data lama).

---

## 8. Verifikasi cepat sebelum produksi

```sql
-- Cek tidak ada duplikat employee_id
SELECT employee_id, COUNT(*) FROM users WHERE employee_id IS NOT NULL
GROUP BY employee_id HAVING COUNT(*) > 1;

-- Cek user legacy yang belum punya NIK (perlu mapping manual)
SELECT id, name, username, role FROM users
WHERE employee_id IS NULL AND is_active = 1;

-- Cek hasil import batch terbaru
SELECT match_method, COUNT(*) FROM users_staging
WHERE batch_id = '<batch_id>' GROUP BY match_method;
```
