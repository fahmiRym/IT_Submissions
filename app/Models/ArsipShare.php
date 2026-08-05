<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipShare extends Model
{
    protected $table = 'arsip_shares';

    protected $fillable = [
        'arsip_id',
        'target_type',  // 'user' | 'role'
        'user_id',
        'role',
        'shared_by',
        'note',
        'read_at',
        'noted_at',
        'note_content',
        'approvals_json',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'noted_at' => 'datetime',
        'approvals_json' => 'array',
    ];

    public function scopeForUser($q, $userId)        { return $q->where('target_type', 'user')->where('user_id', $userId); }
    public function scopeForRole($q, string $role)   { return $q->where('target_type', 'role')->where('role', $role); }

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    // ─── Status helpers ────────────────────────────────────────────
    public function isUnread(): bool     { return is_null($this->read_at); }
    public function isNoted(): bool      { return !is_null($this->noted_at); }
    public function approvals(): array   { return $this->approvals_json ?? []; }

    /** Cek apakah user sudah approve no_transaksi tertentu (case-insensitive trim). */
    public function hasApprovedTx(string $noTransaksi): bool
    {
        $needle = mb_strtolower(trim($noTransaksi));
        foreach ($this->approvals() as $ap) {
            if (mb_strtolower(trim($ap['no_transaksi'] ?? '')) === $needle) return true;
        }
        return false;
    }

    /**
     * Kumpulan approval untuk arsip tertentu, di-flatten per no_transaksi
     * dalam bentuk: ['MO/PF/26/08/65624' => [ ['name'=>..,'at'=>..], .. ], ...]
     * dipakai print draft untuk render watermark.
     */
    public static function approvalsByTx(int $arsipId): array
    {
        $out = [];
        static::query()
            ->where('arsip_id', $arsipId)
            ->whereNotNull('approvals_json')
            ->get(['id', 'approvals_json'])
            ->each(function ($s) use (&$out) {
                foreach ($s->approvals() as $ap) {
                    $tx = trim($ap['no_transaksi'] ?? '');
                    if ($tx === '') continue;
                    $out[$tx] ??= [];
                    $out[$tx][] = [
                        'name' => $ap['approved_name'] ?? '—',
                        'at'   => $ap['approved_at'] ?? null,
                    ];
                }
            });
        return $out;
    }
}
