<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;

/**
 * Shared endpoint untuk popup tabel pengajuan dari dashboard.
 * Filter via query string: status, jenis, kategori, dept, manager, unit, start_date, end_date.
 * Scope ke user current sesuai role (admin/accounting/superadmin).
 */
class DashboardStatController extends Controller
{
    public function popup(Request $request)
    {
        $user = auth()->user();

        $q = Arsip::query()->with([
            'department:id,name',
            'unit:id,name',
            'manager:id,name',
            'admin:id,name',
        ]);

        // ── Scope by role ──
        if ($user->role === 'accounting') {
            $q->where(function ($w) {
                $w->where('admin_id', auth()->id())
                  ->orWhere('jenis_pengajuan', 'Adjust');
            });
        } elseif ($user->role !== 'superadmin') {
            // Non-accounting non-superadmin: hanya milik sendiri ATAU yg di-share
            $sharedIds = $user->sharedArsips()->pluck('arsips.id')->all();
            $q->where(function ($w) use ($sharedIds) {
                $w->where('admin_id', auth()->id());
                if (!empty($sharedIds)) {
                    $w->orWhereIn('id', $sharedIds);
                }
            });
        }

        // ── Filters dari query string ──
        $filters = [
            'status' => $request->get('status'),
            'jenis' => $request->get('jenis'),
            'kategori' => $request->get('kategori'),
            'department_id' => $request->get('department_id'),
            'manager_id' => $request->get('manager_id'),
            'unit_id' => $request->get('unit_id'),
        ];

        if (!empty($filters['status']))         $q->where('ket_process', $filters['status']);
        if (!empty($filters['jenis']))          $q->where('jenis_pengajuan', $filters['jenis']);
        if (!empty($filters['kategori']))       $q->where('kategori', $filters['kategori']);
        if (!empty($filters['department_id']))  $q->where('department_id', $filters['department_id']);
        if (!empty($filters['manager_id']))     $q->where('manager_id', $filters['manager_id']);
        if (!empty($filters['unit_id']))        $q->where('unit_id', $filters['unit_id']);

        if ($request->filled('start_date')) {
            $q->whereDate('tgl_pengajuan', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $q->whereDate('tgl_pengajuan', '<=', $request->end_date);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $q->where(function ($w) use ($search) {
                $w->where('no_registrasi', 'like', "%{$search}%")
                  ->orWhere('no_doc', 'like', "%{$search}%")
                  ->orWhere('no_transaksi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $arsips = $q->latest('tgl_pengajuan')->paginate(10);

        $payload = [
            'total' => $arsips->total(),
            'current_page' => $arsips->currentPage(),
            'last_page' => $arsips->lastPage(),
            'per_page' => $arsips->perPage(),
            'rows' => $arsips->getCollection()->map(fn ($a) => [
                'id' => $a->id,
                'no_registrasi' => $a->no_registrasi,
                'jenis_pengajuan' => $a->jenis_pengajuan,
                'kategori' => $a->kategori,
                'tgl_pengajuan' => optional($a->tgl_pengajuan)->format('d/m/Y') ?? '—',
                'tgl_arsip' => optional($a->tgl_arsip)->format('d/m/Y'),
                'department' => optional($a->department)->name,
                'unit' => optional($a->unit)->name,
                'manager' => optional($a->manager)->name,
                'admin' => optional($a->admin)->name,
                'status' => $a->ket_process,
                'keterangan' => mb_substr((string) $a->keterangan, 0, 80),
            ]),
        ];

        return response()->json($payload);
    }
}
