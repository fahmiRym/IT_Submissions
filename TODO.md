# TODO - Responsive Layout Fix

## Step 1
- Audit & stabilkan layout responsif di:
  - `resources/views/layouts/app.blade.php`
  - `public/css/modern-theme.css`

## Step 2
- Pastikan saat mobile/tablet:
  - `content` selalu full-width (tanpa margin-left tersisa)
  - topbar tidak memicu overflow

## Step 3
- Fine-tune ukuran & spacing komponen form/tabel yang terdampak (terutama di index arsip) agar tidak terlalu besar di HP/tablet.
  - Sudah dilakukan via `public/css/modern-theme.css` (breakpoints 991.98px, 768px, 576px)

## Step 4
- Testing manual di ukuran:
  - HP 360x640
  - Tablet 768x1024
  - Laptop 1280x720

## Step 5
- Fine-tuning tambahan untuk HP (≤576px) pada `public/css/modern-theme.css` agar tabel/form & modal lebih rapat, dan mencegah horizontal overflow/geser pada `superadmin` (terutama bagian pengajuan).




