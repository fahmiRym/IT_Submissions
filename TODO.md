# TODO - Fitur Add per Baris Tindakan IT (In/Out)

- [x] Tambah kolom & FK pada migration `arsip_tindakan_items` (per-baris: IN/OUT + keterangan + sort_order)
- [ ] Tambah Model `ArsipTindakanItem` + relasi di `app/Models/Arsip.php`
- [ ] Update form edit superadmin: kolom IN/OUT pakai input array per baris + tombol tambah/hapus
- [ ] Update loading edit (AJAX) untuk mengisi baris tindakan dari DB
- [ ] Update controller `store()` dan `update()` untuk menyimpan & menghapus relasi `arsip_tindakan_items`
- [ ] Update print draft: tampilkan tabel TINDAKAN dengan looping per baris
- [ ] Jalankan `php artisan migrate` dan quick test

