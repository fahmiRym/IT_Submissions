<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipApproval extends Model
{
    protected $table = 'arsip_approvals';

    protected $fillable = [
        'arsip_id',
        'step_order',
        'role_label',
        'approver_id',
        'status',
        'note',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    public const FINAL_ROLE = 'Departemen IT';

    /**
     * Jumlah pengajuan yang TAHAP AKTIF-nya menunggu user ini.
     */
    public static function pendingCountFor($user): int
    {
        $q = self::where('status', 'pending');
        if ($user->role === 'superadmin') {
            $q->where(fn($x) => $x->where('approver_id', $user->id)->orWhere('role_label', self::FINAL_ROLE));
        } else {
            $q->where('approver_id', $user->id);
        }
        $arsipIds = $q->pluck('arsip_id')->unique();

        $count = 0;
        foreach (Arsip::whereIn('id', $arsipIds)->with('approvals')->get() as $a) {
            $cur = $a->currentApproval();
            if (!$cur) {
                continue;
            }
            if ($user->role === 'superadmin') {
                if ((int) $cur->approver_id === (int) $user->id || $cur->role_label === self::FINAL_ROLE) {
                    $count++;
                }
            } elseif ((int) $cur->approver_id === (int) $user->id) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Peran approver antara (sebelum IT) berdasarkan jenis pengajuan.
     * Pemohon = auto (pengaju), Departemen IT = tahap final (any superadmin).
     */
    public static function rolesForJenis(string $jenis): array
    {
        return match ($jenis) {
            'Adjust'        => ['SPV', 'Kabag', 'Manager', 'Accounting'],
            'Produk_Baru'   => [], // langsung diproses IT
            default         => ['SPV', 'Kabag', 'Manager'], // Cancel, Mutasi_*, Internal_Memo, Bundel
        };
    }

    /**
     * Bangun ulang rantai approval untuk satu pengajuan.
     * $approverMap = ['SPV' => userId, 'Kabag' => userId, ...]
     */
    public static function generateFor(Arsip $arsip, array $approverMap): void
    {
        self::where('arsip_id', $arsip->id)->delete();

        $order = 1;

        // 1) Pemohon — otomatis disetujui oleh pengaju saat submit
        self::create([
            'arsip_id'    => $arsip->id,
            'step_order'  => $order++,
            'role_label'  => 'Pemohon',
            'approver_id' => $arsip->admin_id,
            'status'      => 'approved',
            'acted_by'    => $arsip->admin_id,
            'acted_at'    => now(),
        ]);

        // 2) Approver antara sesuai jenis (hanya yg dipilih)
        foreach (self::rolesForJenis($arsip->jenis_pengajuan) as $role) {
            $uid = $approverMap[$role] ?? null;
            if (!$uid) {
                continue; // lewati peran yg tidak diisi
            }
            self::create([
                'arsip_id'    => $arsip->id,
                'step_order'  => $order++,
                'role_label'  => $role,
                'approver_id' => $uid,
                'status'      => 'pending',
            ]);
        }

        // 3) Tahap final: Departemen IT (any superadmin)
        self::create([
            'arsip_id'    => $arsip->id,
            'step_order'  => $order++,
            'role_label'  => self::FINAL_ROLE,
            'approver_id' => null,
            'status'      => 'pending',
        ]);
    }
}
