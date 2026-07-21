<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\RolePengajuanAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Akses Pengajuan PER ROLE (baseline).
 * Untuk exception per-arsip, lihat ArsipShareController (share-per-arsip).
 */
class PengajuanAccessController extends Controller
{
    public function index()
    {
        $jenisList = RolePengajuanAccess::JENIS_LIST;
        $roleList  = RolePengajuanAccess::ROLE_LIST;

        // Bangun matrix: role => [jenis grants]
        $accessMap = RolePengajuanAccess::all()
            ->groupBy('role')
            ->map(fn ($g) => $g->pluck('jenis')->all());

        // Hitung user count per role (untuk info di UI)
        $userCounts = User::selectRaw('role, COUNT(*) as cnt')
            ->where('is_active', true)
            ->groupBy('role')
            ->pluck('cnt', 'role');

        return view('superadmin.pengajuan_access.index', compact(
            'jenisList', 'roleList', 'accessMap', 'userCounts'
        ));
    }

    /**
     * Bulk update akses untuk SEMUA role dalam satu submit.
     * Payload: { matrix: { role => [jenis, ...] } }
     */
    public function updateBulk(Request $request)
    {
        $request->validate([
            'matrix'       => 'array',
            'matrix.*'     => 'array',
            'matrix.*.*'   => 'string|in:Cancel,Adjust,Mutasi_Billet,Mutasi_Produk,Internal_Memo,Bundel',
        ]);

        $matrix    = $request->get('matrix', []);
        $validRoles = array_keys(RolePengajuanAccess::ROLE_LIST);

        DB::transaction(function () use ($matrix, $validRoles) {
            $now = now();
            foreach ($validRoles as $role) {
                $jenisGranted = array_unique($matrix[$role] ?? []);
                // Hapus semua grant lama untuk role ini
                RolePengajuanAccess::where('role', $role)->delete();
                if (empty($jenisGranted)) continue;
                $rows = array_map(fn ($j) => [
                    'role' => $role,
                    'jenis' => $j,
                    'updated_by' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $jenisGranted);
                RolePengajuanAccess::insert($rows);
            }
        });

        return back()->with('success', 'Matrix akses per role berhasil disimpan.');
    }

    /** Grant ALL jenis ke 1 role. */
    public function grantAll(string $role)
    {
        if (!array_key_exists($role, RolePengajuanAccess::ROLE_LIST)) abort(404);

        DB::transaction(function () use ($role) {
            RolePengajuanAccess::where('role', $role)->delete();
            $now = now();
            $rows = array_map(fn ($j) => [
                'role' => $role,
                'jenis' => $j,
                'updated_by' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ], array_keys(RolePengajuanAccess::JENIS_LIST));
            RolePengajuanAccess::insert($rows);
        });

        $label = RolePengajuanAccess::ROLE_LIST[$role]['label'] ?? $role;
        return back()->with('success', "Semua jenis pengajuan diberikan ke role {$label}.");
    }

    /** Revoke ALL jenis dari 1 role. */
    public function revokeAll(string $role)
    {
        if (!array_key_exists($role, RolePengajuanAccess::ROLE_LIST)) abort(404);
        RolePengajuanAccess::where('role', $role)->delete();
        $label = RolePengajuanAccess::ROLE_LIST[$role]['label'] ?? $role;
        return back()->with('success', "Semua akses pengajuan dicabut dari role {$label}.");
    }
}
