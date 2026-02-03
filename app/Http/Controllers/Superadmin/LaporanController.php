<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\Arsip;
use App\Models\Department;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    private function getFilteredArsips(Request $request)
    {
        $query = Arsip::with(['admin', 'department', 'unit', 'manager']); // Eager load relations

        if ($request->from && $request->to) {
            $query->whereBetween('tgl_pengajuan', [$request->from, $request->to]);
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        return $query->latest()->get();
    }

    public function index(Request $request)
    {
        $arsips = $this->getFilteredArsips($request);

        // Statistics for Cards (Keep logic here or move if needed for PDF too, but usually PDF just needs the list)
        // ... (We can keep separate stats query or reuse if needed, but for now let's keep index logic simple)
        // Re-calcuating stats based on current filter might be heavy if done twice, 
        // but for now let's just stick to the requested structure.
        
        // Actually, to avoid breaking existing stats logic which aggregates on DB, 
        // I will keep the specific stats queries in index() as they group by ID.
        // But for the main list, I use the helper.

        $byUser = Arsip::selectRaw('admin_id, COUNT(*) as total')
            ->with('admin')
            ->groupBy('admin_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $byDepartment = Arsip::selectRaw('department_id, COUNT(*) as total')
            ->with('department')
            ->groupBy('department_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $departments = Department::orderBy('name')->get();

        return view('laporan.index', compact(
            'arsips',
            'byUser',
            'byDepartment',
            'departments'
        ));
    }


    public function printPdf(Request $request)
    {
        $arsips = $this->getFilteredArsips($request);
        $departmentName = $request->department_id ? Department::find($request->department_id)?->name : 'Semua Departemen';
        $filterDate = ($request->from && $request->to) ? "$request->from s/d $request->to" : 'Semua Tanggal';

        // 1. ANALISIS KINERJA & Department (Activity Focus)
        // Group by Dept (Activity)
        $departments = Department::orderBy('name')->get();
        $pivotData = $departments->map(function($dept) use ($arsips) {
            $deptArsips = $arsips->where('department_id', $dept->id);
            return (object) [
                'name' => $dept->name,
                'cancel' => $deptArsips->where('jenis_pengajuan', 'Cancel')->count(),
                'adjust' => $deptArsips->where('jenis_pengajuan', 'Adjust')->count(),
                'mutasi' => $deptArsips->whereIn('jenis_pengajuan', ['Mutasi_Billet', 'Mutasi_Produk'])->count(),
                'bundel' => $deptArsips->where('jenis_pengajuan', 'Bundel')->count(),
                'memo'   => $deptArsips->where('jenis_pengajuan', 'Internal_Memo')->count(),
                'total'  => $deptArsips->count()
            ];
        })->filter(fn($d) => $d->total > 0)->sortByDesc('total')->values();

        $topDept = $pivotData->first();
        $topDeptName = $topDept ? $topDept->name : '-';
        $topDeptCount = $topDept ? $topDept->total : 0;

        // 2. ANALISIS KESALAHAN (Cancel Focus)
        // User definition: "Error" = Making a 'Cancel' request.
        $total = $arsips->count();
        $cancels = $arsips->where('jenis_pengajuan', 'Cancel'); 
        $totalCancel = $cancels->count();
        $cancelRate = $total > 0 ? round(($totalCancel / $total) * 100, 1) : 0;

        // Find Who Makes Mistakes (Cancels)
        $allCancelers = $cancels->groupBy('admin_id')
            ->map(fn($group) => [
                'name' => $group->first()->admin->name ?? 'Unknown',
                'dept' => $group->first()->department->name ?? '-',
                'count' => $group->count()
            ])
            ->sortByDesc('count')
            ->values();
        
        // Data for Chart (Top 5)
        $topCancelers = $allCancelers->take(5);
        
        // Data for Table (Top 20)
        $userCancelList = $allCancelers->take(20);
        
        $topCanceler = $topCancelers->first();
        $topCancelerName = $topCanceler['name'] ?? '-';
        $topCancelerDept = $topCanceler['dept'] ?? '';
        $topCancelerCount = $topCanceler['count'] ?? 0;
        
        $topCancelerDisplay = $topCancelerName . ($topCancelerDept ? " ($topCancelerDept)" : "");

        // Conclusion Text
        $conclusion = "Total aktivitas pengajuan: <strong>$total dokumen</strong>. "
            . "Departemen paling aktif adalah <strong>$topDeptName</strong> ($topDeptCount dokumen).<br>"
            . "Tingkat pembatalan (Cancel) dokumen: <strong>$cancelRate%</strong> ($totalCancel dokumen). "
            . "Pengaju dengan frekuensi cancel tertinggi: <strong>$topCancelerDisplay</strong> ($topCancelerCount dokumen).";

        // CHART 1: TOP 5 DEPARTMENTS (Activity)
        $barConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $pivotData->take(5)->pluck('name'),
                'datasets' => [[
                    'label' => 'Total',
                    'data' => $pivotData->take(5)->pluck('total'),
                    'backgroundColor' => '#10b981', // Emerald
                ]]
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false], 'title' => ['display' => true, 'text' => 'Top 5 Dept Aktif'], 'datalabels' => ['display' => true, 'anchor' => 'end', 'align' => 'top']]
            ]
        ];
        $chartBarUrl = 'https://quickchart.io/chart?width=350&height=200&c=' . urlencode(json_encode($barConfig));

        // CHART 2: TOP CANCELERS (Users)
        $barCancelConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $topCancelers->pluck('name'),
                'datasets' => [[
                    'label' => 'Jumlah Cancel',
                    'data' => $topCancelers->pluck('count'),
                    'backgroundColor' => '#ef4444', // Red for Cancel
                ]]
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false], 'title' => ['display' => true, 'text' => 'Top 5 Pengaju Cancel Terbanyak'], 'datalabels' => ['display' => true, 'color' => 'black', 'anchor' => 'end', 'align' => 'top']]
            ]
        ];
        $chartPieUrl = 'https://quickchart.io/chart?width=350&height=200&c=' . urlencode(json_encode($barCancelConfig)); // Still reusing var name

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf', compact(
            'pivotData', 'departmentName', 'filterDate', 'conclusion', 'chartBarUrl', 'chartPieUrl', 'userCancelList'
        ))->setPaper('a4', 'landscape');
        
        return $pdf->stream('laporan-analisis-error.pdf');
    }
}
