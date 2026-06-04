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

        $relations = [
            'department',
            'unit',
            'admin',
            'superadmin',
            'adjustItems',
            'mutasiItems',
            'bundelItems',
            'produkBaruItems',
        ];

        // 1) Cari berdasarkan No Registrasi (barcode arsip)
        $arsip = Arsip::with($relations)
            ->where('no_registrasi', $barcode)
            ->first();

        // 2) Fallback: barcode item Produk Baru (PB########)
        if (!$arsip) {
            $produkItem = \App\Models\ArsipProdukBaruItem::where('barcode', $barcode)->first();
            if ($produkItem) {
                $arsip = Arsip::with($relations)->find($produkItem->arsip_id);
            }
        }

        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Arsip dengan barcode ' . $barcode . ' tidak ditemukan'
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

        // Tambahkan URL lengkap untuk semua lampiran agar Android bisa akses langsung
        $fileUrl = function (?string $filename) {
            if (!$filename) {
                return null;
            }
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            return $ext === 'pdf'
                ? route('pdf.viewer', ['filename' => $filename])
                : asset('storage/bukti_scan/' . $filename);
        };

        $arsip->bukti_scan_url = $fileUrl($arsip->bukti_scan);
        $arsip->scan_ba_accounting_url = $fileUrl($arsip->scan_ba_accounting);
        $arsip->scan_final_url = $fileUrl($arsip->scan_final);

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

        try {
            // Jika status dari mobile adalah "arsip", jalankan logika Arsip Sistem (Generate No Doc)
            if (strtolower($request->status) === 'arsip') {
                $arsip = Arsip::processArchiving($request->id);
                $message = 'Status arsip berhasil diperbarui menjadi Done (No Doc: ' . $arsip->no_doc . ')';
            } else {
                // Untuk status lain (jika ada), update manual
                $arsip = Arsip::findOrFail($request->id);
                $arsip->update([
                    'status' => $request->status,
                    'tgl_arsip' => now(),
                ]);
                $message = 'Status arsip berhasil diperbarui menjadi ' . $request->status;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $arsip
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }
}
