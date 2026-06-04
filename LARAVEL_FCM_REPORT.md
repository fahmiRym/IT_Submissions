# Laporan: Notifikasi heads-up "bawaan device" tidak muncul

Tanggal: 2026-06-02
Repo: e_arsip (Laravel) ⇄ ITSubmissions (Android)
File yang dimodifikasi: `app/Services/FcmService.php`

## Ringkasan masalah

Setelah refactor FCM sebelumnya, payload push sudah hybrid (`notification` + `data`),
tetapi notif heads-up bawaan device (yang muncul di atas layar / lock screen)
TIDAK selalu muncul lagi. Yang muncul kadang hanya icon kecil di status bar
(silent), atau bahkan tidak muncul sama sekali di MIUI/OEM lain.

## Akar penyebab dari sisi Laravel

Sebelum patch, `android.notification` hanya berisi `channel_id`:

```php
'android' => [
    'priority' => 'high',
    'ttl' => '600s',
    'notification' => [
        'channel_id' => 'submission_channel_v2',
    ],
],
```

Konsekuensi:

1. **`notification_priority` tidak di-set** → FCM SDK menentukan importance
   hanya dari channel di device. Bila channel `submission_channel_v2` belum
   pernah ter-create (app baru install & belum pernah dibuka, atau MIUI bersihkan
   data), FCM fallback ke `fcm_fallback_notification_channel` yang **silent
   tanpa heads-up**.
2. **Tidak ada `default_sound` / `default_vibrate_timings`** → di kondisi
   fallback channel, tidak ada bunyi maupun getar.
3. **Tidak ada `visibility`** → di lock screen MIUI/Pixel dengan privacy setting
   default, isi notif disembunyikan dan heads-up di-collapse.
4. **Tidak ada `tag`** → notif untuk `no_registrasi` yang sama menumpuk di
   shade alih-alih meng-update; pada Android sisi klien sudah dedupe via
   `notifId = noRegistrasi.hashCode()`, tapi dedupe itu hanya jalan saat
   `onMessageReceived` jalan (foreground). Untuk background (auto-render
   FCM SDK), dedupe harus dilakukan via `android.notification.tag`.
5. **Title/body hanya di root `notification`** → secara spek FCM v1 boleh,
   tapi defensive practice-nya adalah duplikasi ke `android.notification.title/body`
   agar auto-render Android konsisten.

## Patch yang diterapkan

Lihat `app/Services/FcmService.php::sendOne()`. Penambahan di blok
`android.notification`:

| Field                          | Nilai                                  | Tujuan                                                              |
| ------------------------------ | -------------------------------------- | ------------------------------------------------------------------- |
| `title`, `body`                | duplikasi dari root                    | Auto-render Android konsisten meski root `notification` di-tweak    |
| `notification_priority`        | `PRIORITY_MAX`                         | **Pemicu heads-up "bawaan device"** di atas layar                   |
| `visibility`                   | `PUBLIC`                               | Tampil utuh di lock screen                                          |
| `default_sound`                | `true`                                 | Fallback bunyi bila channel target tidak tersedia di device         |
| `default_vibrate_timings`      | `true`                                 | Fallback getar                                                      |
| `default_light_settings`       | `true`                                 | Fallback LED                                                        |
| `tag`                          | `arsip-<no_registrasi>`                | Dedupe level sistem (bg auto-render)                                |
| `sticky`                       | `false`                                | Boleh di-swipe                                                      |
| `event_time`                   | RFC3339 now                            | Timestamp benar saat sistem render                                  |
| (root) `android.collapse_key`  | sama dengan tag                        | FCM tidak antri banyak pesan basi untuk dokumen yang sama           |

Tidak ada perubahan kontrak data (Android tetap baca `data.title`,
`data.message`, `data.no_registrasi` seperti sebelumnya).

## Yang TIDAK diubah dan kenapa

- **Strategi hybrid (notification + data) dipertahankan.** Alternatifnya
  data-only akan memaksa `onMessageReceived` dipanggil sehingga custom layout
  selalu dipakai, tapi di MIUI device yang sudah swipe-up clean, service FCM
  bisa mati → pesan data-only HILANG. Hybrid lebih tahan banting.
- **`channel_id` tetap `submission_channel_v2`.** Sesuai dengan Android
  `NotificationHelper.CHANNEL_ID` dan `AndroidManifest` meta-data
  `default_notification_channel_id`. Mengubahnya akan membuat suara/vibrasi custom
  hilang.
- **`priority: 'high'` di `android` block tetap.** Inilah priority transport
  FCM (delivery), berbeda dengan `notification_priority` (importance visual).
  Keduanya wajib di-set untuk heads-up reliabel.

## Verifikasi yang harus dilakukan

1. `php artisan config:clear` di server (kalau di-deploy).
2. Trigger test push: login Android → tunggu submit pengajuan atau hit
   `POST /api/fcm/test`.
3. **Background test**: kunci layar device, kirim notif → harus muncul heads-up
   di atas lock screen dengan bunyi + getar.
4. **Foreground test**: app dibuka, kirim notif → `onMessageReceived` tetap
   render custom layout indigo via `NotificationHelper`.
5. Cek `storage/logs/laravel.log` — tidak boleh ada `FCM: gagal kirim push`
   dengan `INVALID_ARGUMENT` (kalau ada, payload schema salah).

## Yang masih perlu dicek di sisi Android / Device (di luar Laravel)

Bila setelah patch ini heads-up tetap tidak muncul, bukan kode Laravel-nya:

- **POST_NOTIFICATIONS permission** (Android 13+) — pastikan di-grant saat
  pertama buka.
- **MIUI Autostart + Battery saver** di Settings → Apps → IT Submission.
- **DND mode / Focus mode** sedang aktif.
- **Pengaturan channel `submission_channel_v2` di system settings** — user
  bisa men-disable channel ini setelah dapat satu notif (toggle "Pop on screen").
- **`fcm_fallback_notification_channel` di pengaturan sistem app** — kalau ada
  artinya app pernah menerima FCM sebelum `NotificationHelper` membuat channel
  utama. Solusi sudah ada di Android side: channel di-create di `init {}` block
  `NotificationHelper`.
