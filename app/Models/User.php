<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;


    protected $fillable = [
        'employee_id',
        'name',
        'username',
        'email',
        'password',
        'photo',
        'signature_path',
        'role',
        'jabatan',
        'department_id',
        'work_unit_id',
        'odoo_user_id',
        'is_active',
        'source',
        'must_change_password',
        'last_synced_at',
        'last_login_at',
        'last_login_ip',
        'login_count',
        'delegate_to_id',
        'delegate_active_from',
        'delegate_active_until',
        'delegate_reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'last_synced_at' => 'datetime',
        'last_login_at' => 'datetime',
        'delegate_active_from' => 'date',
        'delegate_active_until' => 'date',
    ];

    protected $hidden = ['password'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Akses pengajuan sekarang di level ROLE, bukan user.
     * Per-user override sudah di-drop. Untuk pengecualian individual,
     * pakai per-arsip share (lihat sharedArsips()).
     */
    public function accessibleJenis(): array
    {
        if ($this->role === 'superadmin') {
            return array_keys(RolePengajuanAccess::JENIS_LIST);
        }
        return RolePengajuanAccess::jenisForRole($this->role);
    }

    public function canAccessJenis(string $jenis): bool
    {
        if ($this->role === 'superadmin') return true;
        return RolePengajuanAccess::roleHasJenis($this->role, $jenis);
    }

    /**
     * Arsip yang di-share ke user ini (Layer 2) — gabungan share via user_id
     * langsung MAUPUN via role. Return query builder Arsip.
     */
    public function sharedArsips()
    {
        $userId = $this->id;
        $role = $this->role;
        return Arsip::query()
            ->whereExists(function ($q) use ($userId, $role) {
                $q->select(\DB::raw(1))
                  ->from('arsip_shares')
                  ->whereColumn('arsip_shares.arsip_id', 'arsips.id')
                  ->where(function ($w) use ($userId, $role) {
                      $w->where(function ($x) use ($userId) {
                          $x->where('arsip_shares.target_type', 'user')
                            ->where('arsip_shares.user_id', $userId);
                      })->orWhere(function ($x) use ($role) {
                          $x->where('arsip_shares.target_type', 'role')
                            ->where('arsip_shares.role', $role);
                      });
                  });
            });
    }

    /**
     * Boleh melihat HARGA / NILAI Rupiah pada arsip.
     * Aturan tunggal — sync dengan Gate 'view-price' di AppServiceProvider.
     */
    public function canViewPrice(): bool
    {
        if (in_array($this->role, ['accounting', 'superadmin'], true)) {
            return true;
        }
        $dept = strtolower((string) optional($this->department)->name);
        if ($dept === '') return false;
        return str_starts_with($dept, 'accounting')
            || str_starts_with($dept, 'finance')
            || $dept === 'it'
            || str_starts_with($dept, 'it ');
    }

    public function workUnit()
    {
        return $this->belongsTo(Unit::class, 'work_unit_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public static function getJabatanOptions(): array
    {
        return ['Staff', 'SPV', 'Kabag', 'Manager', 'Accounting', 'IT'];
    }

    public function approvalsAssigned()
    {
        return $this->hasMany(ArsipApproval::class, 'approver_id');
    }

    // ─── DELEGASI TTD ─────────────────────────────────────────────
    // User yg ditunjuk sbg delegasi (kalau saya cuti/dinas, TTD-nya otomatis diforward ke sini).
    public function delegateTo()
    {
        return $this->belongsTo(User::class, 'delegate_to_id');
    }

    // Kebalikannya: siapa saja yg mendelegasikan TTD-nya ke user ini.
    public function delegatedFromUsers()
    {
        return $this->hasMany(User::class, 'delegate_to_id');
    }

    /**
     * Return User yg AKTIF menjadi delegasi user ini pada tanggal $on (default: today).
     * Return null bila:
     *   - user tidak set delegate_to_id, ATAU
     *   - tanggal window belum mulai / sudah lewat, ATAU
     *   - delegate user sendiri sudah tidak aktif.
     * Chain-forward: kalau delegate juga sedang delegasi (misal chain A→B→C), ikuti sampai
     * terminal (protection dgn max-depth 3 supaya tidak infinite loop).
     */
    public function activeDelegate(?\Carbon\Carbon $on = null, int $depth = 0): ?self
    {
        $on = $on ?? now();
        if (!$this->delegate_to_id || $depth >= 3) return null;

        // Window check
        if ($this->delegate_active_from && $on->lt($this->delegate_active_from)) return null;
        if ($this->delegate_active_until && $on->gt($this->delegate_active_until)) return null;

        $delegate = $this->delegateTo()->first();
        if (!$delegate || !$delegate->is_active) return null;

        // Chain forward: kalau delegate juga sedang delegasi → follow.
        $next = $delegate->activeDelegate($on, $depth + 1);
        return $next ?: $delegate;
    }

    public function isDelegatingNow(?\Carbon\Carbon $on = null): bool
    {
        return $this->activeDelegate($on) !== null;
    }

    public function hasSignature(): bool
    {
        return !empty($this->signature_path)
            && file_exists(public_path('signatures/' . $this->signature_path));
    }

    public function signatureUrl(): ?string
    {
        return $this->signature_path ? asset('signatures/' . $this->signature_path) : null;
    }
}
