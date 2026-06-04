<?php

namespace App\Models;

use App\Services\FcmService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'arsip_id',
        'title',
        'message',
        'role_target',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];

    /**
     * Setiap notifikasi yang dibuat otomatis dikirim juga sebagai
     * push notification FCM ke perangkat penerima (jika ada token).
     */
    protected static function booted(): void
    {
        static::created(function (Notification $notification) {
            try {
                $notification->pushToDevices();
            } catch (\Throwable $e) {
                // Jangan sampai gagal push mengganggu alur utama (simpan data dll).
                Log::error('FCM: gagal push dari Notification: ' . $e->getMessage());
            }
        });
    }

    /**
     * Kirim push ke perangkat penerima sesuai target notifikasi.
     */
    public function pushToDevices(): void
    {
        $fcm = app(FcmService::class);

        if (! $fcm->isConfigured()) {
            return;
        }

        $data = [
            'type' => 'notification',
            'notification_id' => (string) $this->id,
            'arsip_id' => $this->arsip_id ? (string) $this->arsip_id : '',
            'no_registrasi' => optional($this->arsip)->no_registrasi ?? '',
        ];

        $title = $this->title ?: 'Notifikasi';
        $body = $this->message ?: '';

        foreach ($this->recipientUserIds() as $userId) {
            $fcm->sendToUser($userId, $title, $body, $data);
        }
    }

    /**
     * Tentukan user penerima push.
     *
     * - role_target 'superadmin' : semua user superadmin (kotak bersama)
     * - selain itu, jika ada user_id : user tersebut
     * - fallback role_target lain : semua user dengan role tsb
     */
    private function recipientUserIds(): array
    {
        if ($this->role_target === 'superadmin') {
            return User::where('role', 'superadmin')->pluck('id')->all();
        }

        if ($this->user_id) {
            return [$this->user_id];
        }

        if ($this->role_target) {
            return User::where('role', $this->role_target)->pluck('id')->all();
        }

        return [];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }
}
