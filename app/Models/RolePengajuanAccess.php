<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePengajuanAccess extends Model
{
    protected $table = 'role_pengajuan_access';

    protected $fillable = [
        'role',
        'jenis',
        'updated_by',
    ];

    /** Semua jenis pengajuan yang valid (single source of truth). */
    public const JENIS_LIST = [
        'Cancel'        => ['label' => 'Cancel',         'icon' => 'bi-trash3-fill',                'color' => '#dc2626'],
        'Adjust'        => ['label' => 'Adjustment',     'icon' => 'bi-sliders2-vertical',          'color' => '#0891b2'],
        'Mutasi_Billet' => ['label' => 'Mutasi Billet',  'icon' => 'bi-arrow-repeat',               'color' => '#4f46e5'],
        'Mutasi_Produk' => ['label' => 'Mutasi Produk',  'icon' => 'bi-box-fill',                   'color' => '#059669'],
        'Internal_Memo' => ['label' => 'Internal Memo',  'icon' => 'bi-file-earmark-richtext-fill', 'color' => '#d97706'],
        'Bundel'        => ['label' => 'Bundel',         'icon' => 'bi-collection-fill',            'color' => '#be185d'],
    ];

    /** Role yang bisa di-set akses-nya (superadmin selalu bypass). */
    public const ROLE_LIST = [
        'admin'      => ['label' => 'Admin',              'icon' => 'bi-person-fill-gear',  'color' => '#0ea5e9'],
        'accounting' => ['label' => 'Accounting',         'icon' => 'bi-calculator-fill',   'color' => '#f59e0b'],
        'spv'        => ['label' => 'Supervisor',         'icon' => 'bi-person-badge',      'color' => '#06b6d4'],
        'kabag'      => ['label' => 'Kepala Bagian',      'icon' => 'bi-diagram-3-fill',    'color' => '#d97706'],
        'manager'    => ['label' => 'Manager',            'icon' => 'bi-briefcase-fill',    'color' => '#8b5cf6'],
    ];

    /** Cek apakah role boleh akses jenis tertentu (with simple request-level cache). */
    public static function roleHasJenis(string $role, string $jenis): bool
    {
        static $cache = [];
        $key = $role . '|' . $jenis;
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = static::where('role', $role)->where('jenis', $jenis)->exists();
        }
        return $cache[$key];
    }

    /** Daftar jenis yang di-grant ke role tertentu. */
    public static function jenisForRole(string $role): array
    {
        static $cache = [];
        if (!array_key_exists($role, $cache)) {
            $cache[$role] = static::where('role', $role)->pluck('jenis')->all();
        }
        return $cache[$role];
    }
}
