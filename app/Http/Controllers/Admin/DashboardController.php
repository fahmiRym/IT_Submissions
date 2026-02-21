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

        $query = Arsip::with(['department', 'adjustItems', 'mutasiItems', 'bundelItems'])
            ->where('admin_id', $adminId);

        // ================= BASIC STATS =================
        $total   = (clone $query)->count();
        $Review  = (clone $query)->where('ket_process', 'Review')->count();
        $process = (clone $query)->where('ket_process', 'Process')->count();
        $done    = (clone $query)->where('ket_process', 'Done')->count();
        $pending = (clone $query)->where('ket_process', 'Pending')->count();
        $partial = (clone $query)->where('ket_process', 'Partial Done')->count();

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
        $arsips = Arsip::with(['department', 'adjustItems', 'mutasiItems', 'bundelItems'])
            ->where('admin_id', $adminId)
            ->latest()
            ->paginate(10);

        return view('admin.dashboard.index', [
            // Basic stats
            'total'   => $total,
            'Review'  => $Review,
            'process' => $process,
            'done'    => $done,
            'pending' => $pending,
            'partial' => $partial,

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
        ]);
    }
}
