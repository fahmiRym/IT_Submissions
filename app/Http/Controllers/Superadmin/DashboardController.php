<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Arsip;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getTopDeptsByType($type)
    {
        // Handle Mutasi Combined
        if ($type === 'Mutasi') {
            return Department::withCount(['arsips' => function($q) {
                    $q->whereIn('jenis_pengajuan', ['Mutasi_Billet', 'Mutasi_Produk']);
                }])
                ->orderByDesc('arsips_count')
                ->get()
                ->map(fn($d) => ['name' => $d->name, 'total' => $d->arsips_count]);
        }

        return Department::withCount(['arsips' => function($q) use ($type) {
                $q->where('jenis_pengajuan', $type);
            }])
            ->orderByDesc('arsips_count')
            ->get()
            ->map(fn($d) => ['name' => $d->name, 'total' => $d->arsips_count]);
    }

    public function index(Request $request)
    {
        $query = Arsip::with(['admin','department','unit','manager']);

        // FILTER TANGGAL
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('tgl_pengajuan', [
                $request->from,
                $request->to
            ]);
        }

        // FILTER DEPARTEMEN
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // FILTER JENIS PENGAJUAN
        if ($request->filled('jenis_pengajuan')) {
            $query->where('jenis_pengajuan', $request->jenis_pengajuan);
        }

        // ================= BASIC STATS =================
        // Total Arsip: Hanya yang status fisiknya 'Done' (Sudah diarsip)
        $totalArsip   = (clone $query)->where('arsip', 'Done')->count();
        $arsipDone    = (clone $query)->where('status','Done')->count();
        $arsipProcess = (clone $query)->where('status','Process')->count();

        $totalPengajuan = Arsip::count(); // Total seluruh pengajuan tanpa filter
        $totalDept = Department::count();
        $totalUser = User::count();

        // ================= JENIS PENGAJUAN STATS =================
        $adjustCount = (clone $query)->where('jenis_pengajuan', 'Adjust')->count();
        $mutasiBilletCount = (clone $query)->where('jenis_pengajuan', 'Mutasi_Billet')->count();
        $mutasiProdukCount = (clone $query)->where('jenis_pengajuan', 'Mutasi_Produk')->count();
        $internalMemoCount = (clone $query)->where('jenis_pengajuan', 'Internal_Memo')->count();
        $bundelCount = (clone $query)->where('jenis_pengajuan', 'Bundel')->count();
        $cancelCount = (clone $query)->where('jenis_pengajuan', 'Cancel')->count();

        // ================= BA & ARSIP STATUS =================
        $baDone = (clone $query)->where('ba', 'Done')->count();
        $baPending = (clone $query)->where('ba', 'Pending')->count();
        $arsipStatusDone = (clone $query)->where('arsip', 'Done')->count();
        $arsipStatusPending = (clone $query)->where('arsip', 'Pending')->count();

        // ================= STATUS CHART =================
        $statusChart = (clone $query)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        // ================= JENIS PENGAJUAN CHART =================
        $jenisPengajuanChart = (clone $query)
            ->select('jenis_pengajuan', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_pengajuan')
            ->get();

        // ================= DEPARTEMEN CHART (GLOBAL) =================
        $auditByDepartment = Department::leftJoin('arsips', function ($join) use ($request) {
                $join->on('departments.id','=','arsips.department_id');

                if ($request->filled('from') && $request->filled('to')) {
                    $join->whereBetween('arsips.tgl_pengajuan', [
                        $request->from,
                        $request->to
                    ]);
                }

                if ($request->filled('jenis_pengajuan')) {
                    $join->where('arsips.jenis_pengajuan', $request->jenis_pengajuan);
                }
            })
            ->select(
                'departments.id',
                'departments.name',
                DB::raw('COUNT(arsips.id) as total')
            )
            ->groupBy('departments.id','departments.name')
            ->orderByDesc('total')
            ->get();

        // ================= TREND PER JENIS (DIGROUP) =================
        $trendByType = (clone $query)
            ->selectRaw('MONTH(tgl_pengajuan) as bulan, jenis_pengajuan, COUNT(*) as total')
            ->groupBy('bulan', 'jenis_pengajuan')
            ->orderBy('bulan')
            ->get();

        // ================= DEPT STATS PER TYPE =================
        $deptCancel = $this->getTopDeptsByType('Cancel');
        $deptAdjust = $this->getTopDeptsByType('Adjust');
        $deptBundel = $this->getTopDeptsByType('Bundel');
        $deptMemo   = $this->getTopDeptsByType('Internal_Memo');
        $deptMutasi = $this->getTopDeptsByType('Mutasi');
        $deptMutasiProduk = $this->getTopDeptsByType('Mutasi_Produk');
        $deptMutasiBillet = $this->getTopDeptsByType('Mutasi_Billet');

        // ================= BULANAN =================
        $monthlyChart = (clone $query)
            ->selectRaw('MONTH(tgl_pengajuan) as bulan, COUNT(*) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // ================= HISTORY =================
        $latestArsip = (clone $query)
            ->latest('tgl_pengajuan')
            ->limit(10)
            ->get();

        // ================= VIEW =================
        return view('superadmin.dashboard.index', [
            'totalArsip'       => $totalArsip,
            'totalPengajuan'   => $totalPengajuan,
            'arsipDone'        => $arsipDone,
            'arsipProcess'     => $arsipProcess,
            'totalDept'        => $totalDept,
            'totalUser'        => $totalUser,
            
            // Jenis Pengajuan Stats
            'adjustCount'      => $adjustCount,
            'mutasiBilletCount'=> $mutasiBilletCount,
            'mutasiProdukCount'=> $mutasiProdukCount,
            'internalMemoCount'=> $internalMemoCount,
            'bundelCount'      => $bundelCount,
            'cancelCount'      => $cancelCount,
            
            // BA & Arsip Status
            'baDone'           => $baDone,
            'baPending'        => $baPending,
            'arsipStatusDone'  => $arsipStatusDone,
            'arsipStatusPending'=> $arsipStatusPending,
            
            // Charts
            'statusChart'      => $statusChart,
            'jenisPengajuanChart' => $jenisPengajuanChart,
            'auditByDepartment'=> $auditByDepartment,
            'trendByType'      => $trendByType,
            // 'trendByDept'      => $trendByDept, // Removed, replaced by specific per types
            'deptCancel'       => $deptCancel,
            'deptAdjust'       => $deptAdjust,
            'deptBundel'       => $deptBundel,
            'deptMemo'         => $deptMemo,
            'deptMutasi'       => $deptMutasi,
            'deptMutasiProduk' => $deptMutasiProduk,
            'deptMutasiBillet' => $deptMutasiBillet,

            // Ket Process Stats
            'ketPending'       => (clone $query)->where('ket_process', 'Pending')->count(),
            'ketProcess'       => (clone $query)->where('ket_process', 'Process')->count(),
            'ketDone'          => (clone $query)->where('ket_process', 'Done')->count(),
            'ketPartial'       => (clone $query)->where('ket_process', 'Partial Done')->count(),
            'ketReview'        => (clone $query)->where('ket_process', 'Review')->count(), // Assuming Review exists based on table logic

            'monthlyChart'     => $monthlyChart,
            
            // Others
            'latestArsip'      => $latestArsip,
            'departments'      => Department::orderBy('name')->get(),
        ]);
    }
}
