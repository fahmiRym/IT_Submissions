<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller; // ✅ WAJIB
use App\Models\Arsip;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function pdf(Request $request)
    {
        $arsips = Arsip::with('user','department')->get();

        // ===== RINGKASAN =====
        $totalArsip = Arsip::count();
        $done = Arsip::where('status','Done')->count();
        $open = Arsip::where('status','!=','Done')->count();

        $persenDone = $totalArsip ? round(($done/$totalArsip)*100,1) : 0;
        $persenOpen = $totalArsip ? round(($open/$totalArsip)*100,1) : 0;

        $topDepartment = Arsip::selectRaw('department_id, COUNT(*) as total')
            ->with('department')
            ->groupBy('department_id')
            ->orderByDesc('total')
            ->first();

        $topUser = Arsip::selectRaw('user_id, COUNT(*) as total')
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        // ===== DATA GRAFIK =====
        $byStatus = Arsip::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total','status');

        $byDept = Arsip::selectRaw('department_id, COUNT(*) as total')
            ->with('department')
            ->groupBy('department_id')
            ->get()
            ->mapWithKeys(fn ($d) => [
                $d->department->name ?? 'Unknown' => $d->total
            ]);

        // ===== QUICKCHART IMAGE =====
        $statusChartUrl = 'https://quickchart.io/chart?c='.urlencode(json_encode([
            'type'=>'pie',
            'data'=>[
                'labels'=>$byStatus->keys()->values(),
                'datasets'=>[['data'=>$byStatus->values()->values()]]
            ]
        ]));

        $deptChartUrl = 'https://quickchart.io/chart?c='.urlencode(json_encode([
            'type'=>'bar',
            'data'=>[
                'labels'=>$byDept->keys()->values(),
                'datasets'=>[['label'=>'Jumlah Arsip','data'=>$byDept->values()->values()]]
            ]
        ]));

        $pdf = Pdf::loadView('exports.arsip-pdf', compact(
            'arsips',
            'statusChartUrl',
            'deptChartUrl',
            'totalArsip',
            'done',
            'open',
            'persenDone',
            'persenOpen',
            'topDepartment',
            'topUser'
        ))->setPaper('A4','portrait');

        return $pdf->download('laporan-arsip.pdf');
    }

    // ===== EXPORT CSV (Excel-safe) =====
    public function csv()
    {
        $fileName = 'laporan-arsip.csv';

        $arsips = Arsip::with('user','department')->get();

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $callback = function() use ($arsips) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tanggal','User','Departemen','Status']);

            foreach ($arsips as $a) {
                fputcsv($file, [
                    $a->tgl_pengajuan,
                    $a->user->name ?? '-',
                    $a->department->name ?? '-',
                    $a->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
