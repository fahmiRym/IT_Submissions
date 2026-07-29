# Manual Book — IT Submission (e_arsip)

Panduan alur aplikasi untuk **end-user / staff**, disajikan dalam 5 gambar alur (SVG).

## Daftar Gambar

| No | File | Isi |
|----|------|-----|
| 1  | [01-login-dashboard.svg](01-login-dashboard.svg)   | Cara login + kenalan halaman awal per peran (Staff, SPV/Kabag/Manager, Accounting, IT) |
| 2  | [02-buat-pengajuan.svg](02-buat-pengajuan.svg)     | Membuat pengajuan baru: pilih jenis (Cancel/Adjust/Mutasi/Memo/Bundel), isi form, upload, submit |
| 3  | [03-alur-approval.svg](03-alur-approval.svg)       | Perjalanan pengajuan lewat SPV → Kabag → Manager → (Accounting) → Departemen IT + delegasi TTD |
| 4  | [04-notifikasi-tracking.svg](04-notifikasi-tracking.svg) | Kapan notif dikirim, di mana muncul (web bell + Android push), cara lacak status |
| 5  | [05-selesai-arsip.svg](05-selesai-arsip.svg)       | Setelah TTD final: nomor dokumen, download PDF, verifikasi QR, arsip permanen |

## Cara Membuka

- **Preview cepat**: klik file `.svg` di GitHub / VS Code — akan langsung ke-render.
- **Buka di browser**: double-click file → otomatis terbuka di Chrome/Edge/Firefox.
- **Cetak / masukkan Word**: drag file `.svg` ke Word / PowerPoint — resolusi tetap tajam saat di-zoom.
- **Convert ke PNG** (kalau butuh untuk WhatsApp / email image):
  - Web: [svgtopng.com](https://svgtopng.com) — upload SVG, download PNG
  - Windows: buka di Chrome → klik kanan → "Save image as..." → pilih PNG

## Untuk Kepentingan Cetak (A4 Portrait)

Semua SVG di-desain landscape ~1000×700 px. Untuk cetak A4:
1. Buka SVG di browser
2. Ctrl+P → Layout: **Landscape** → Fit to page
3. Hasil siap dibagikan

## Update

Kalau ada perubahan alur (jenis pengajuan baru, role baru, atau workflow), edit SVG langsung dengan text editor — semua koordinat & label ada di dalam file. Warna palet:

- Biru (`#2563eb`) — aksi user
- Ungu/Pink (`#7c3aed` / `#db2777`) — role approver
- Hijau (`#16a34a`) — selesai / sukses
- Oranye (`#d97706`) — menunggu / info penting
- Merah (`#dc2626`) — ditolak / jalur error
- Abu-abu (`#64748b`) — proses sistem otomatis
