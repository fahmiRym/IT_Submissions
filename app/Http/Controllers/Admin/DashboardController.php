<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Arsip;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $adminId = auth()->id();

        $query = Arsip::with(['department', 'manager', 'unit', 'adjustItems', 'mutasiItems', 'bundelItems'])
            ->where('admin_id', $adminId);

        // FILTER TANGGAL
        if ($request->filled('start_date')) {
            $query->whereDate('tgl_pengajuan', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tgl_pengajuan', '<=', $request->end_date);
        }

        // FILTER DEPARTEMEN
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // FILTER MANAGER
        if ($request->filled('manager_id')) {
            $query->where('manager_id', $request->manager_id);
        }

        // FILTER UNIT
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // FILTER KATEGORI
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // ================= BASIC STATS =================
        $total   = (clone $query)->count();
        $Review  = (clone $query)->where('ket_process', 'Review')->count();
        $process = (clone $query)->where('ket_process', 'Process')->count();
        $done    = (clone $query)->where('ket_process', 'Done')->count();
        $pending = (clone $query)->where('ket_process', 'Pending')->count();
        $partial = (clone $query)->where('ket_process', 'Partial Done')->count();
        $void    = (clone $query)->where('ket_process', 'Void')->count();

        // ================= STATUS CHART (ket_process) =================
        $statusChart = (clone $query)
            ->select('ket_process as status', DB::raw('COUNT(*) as total'))
            ->groupBy('ket_process')
            ->get();

        // ================= MONTHLY CHART =================
        $monthlyChart = (clone $query)
            ->selectRaw('MONTH(tgl_pengajuan) as bulan, COUNT(*) as total')
            ->whereNotNull('tgl_pengajuan')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // ================= TREND PER JENIS =================
        $trendByType = (clone $query)
            ->selectRaw('MONTH(tgl_pengajuan) as bulan, jenis_pengajuan, COUNT(*) as total')
            ->whereNotNull('tgl_pengajuan')
            ->groupBy('bulan', 'jenis_pengajuan')
            ->orderBy('bulan')
            ->get();

        // ================= JENIS PENGAJUAN STATS =================
        $adjustCount       = (clone $query)->where('jenis_pengajuan', 'Adjust')->count();
        $mutasiBilletCount = (clone $query)->where('jenis_pengajuan', 'Mutasi_Billet')->count();
        $mutasiProdukCount = (clone $query)->where('jenis_pengajuan', 'Mutasi_Produk')->count();
        $internalMemoCount = (clone $query)->where('jenis_pengajuan', 'Internal_Memo')->count();
        $bundelCount       = (clone $query)->where('jenis_pengajuan', 'Bundel')->count();
        $cancelCount       = (clone $query)->where('jenis_pengajuan', 'Cancel')->count();

        // ================= HISTORY =================
        $arsips = (clone $query)->latest()->paginate(10);

        return view('admin.dashboard.index', [
            // Basic stats
            'total'   => $total,
            'Review'  => $Review,
            'process' => $process,
            'done'    => $done,
            'pending' => $pending,
            'partial' => $partial,
            'void'    => $void,

            // Jenis Pengajuan Stats
            'adjustCount'       => $adjustCount,
            'mutasiBilletCount' => $mutasiBilletCount,
            'mutasiProdukCount' => $mutasiProdukCount,
            'internalMemoCount' => $internalMemoCount,
            'bundelCount'       => $bundelCount,
            'cancelCount'       => $cancelCount,

            // Charts
            'statusChart'   => $statusChart,
            'monthlyChart'  => $monthlyChart,
            'trendByType'   => $trendByType,

            // History
            'arsips' => $arsips,

            // Dropdowns
            'departments' => Department::orderBy('name')->get(),
            'managers'    => \App\Models\Manager::orderBy('name')->get(),
            'units'       => \App\Models\Unit::orderBy('name')->get(),
        ]);
    }
}
