<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Arsip;

class BarcodeController extends Controller
{
    public function processScan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $barcode = trim($request->barcode);

        // Cari arsip berdasarkan no_registrasi beserta relasinya
        $arsip = Arsip::with([
            'department', 
            'unit', 
            'admin', 
            'superadmin',
            'adjustItems',
            'mutasiItems',
            'bundelItems'
        ])
        ->where('no_registrasi', $barcode)
        ->first();

        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Arsip dengan barcode '.$barcode.' tidak ditemukan'
            ], 404);
        }

        // ==========================================
        // Jika butuh UPDATE STATUS setelah discan
        // ==========================================
        // if ($arsip->status !== 'Sudah Diarsip') {
        //     $arsip->update(['status' => 'Sudah Diarsip', 'tgl_arsip' => now()]);
        //     $message = 'Arsip berhasil diproses menjadi Sudah Diarsip';
        // } else {
        //     $message = 'Data arsip ditemukan (Sudah Diarsip sebelumnya)';
        // }

        // Tambahkan URL lengkap untuk lampiran agar Android bisa akses langsung
        // Tambahkan URL lengkap untuk lampiran agar Android bisa akses langsung
        if ($arsip->bukti_scan) {
            $extension = pathinfo($arsip->bukti_scan, PATHINFO_EXTENSION);
            if (strtolower($extension) === 'pdf') {
                $arsip->bukti_scan_url = route('pdf.viewer', ['filename' => $arsip->bukti_scan]);
            } else {
                $arsip->bukti_scan_url = asset('storage/bukti_scan/' . $arsip->bukti_scan);
            }
        } else {
            $arsip->bukti_scan_url = null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Data arsip ditemukan',
            'data' => $arsip
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:arsips,id',
            'status' => 'required|string'
        ]);

        $arsip = Arsip::find($request->id);

        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Arsip tidak ditemukan'
            ], 404);
        }

        // Map status "arsip" dari mobile ke "Sudah Diarsip" pada database
        $newStatus = $request->status;
        if (strtolower($newStatus) === 'arsip') {
            $newStatus = 'Sudah Diarsip';
        }

        $arsip->update([
            'status' => $newStatus,
            'tgl_arsip' => now(),
            'arsip' => 'Done' // Asumsi: mengubah trackernya juga menjadi Done
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status arsip berhasil diperbarui menjadi ' . $newStatus,
            'data' => $arsip
        ], 200);
    }
}
