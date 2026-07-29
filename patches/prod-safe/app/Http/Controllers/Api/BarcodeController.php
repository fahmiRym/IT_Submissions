<?php

/**
 * ============================================================================
 * PROD-SAFE BACKPORT — IT Submission (prod .200 baseline: git 147ff58)
 * ----------------------------------------------------------------------------
 * Sumber baseline: `/root/it_submissions/app/Http/Controllers/Api/BarcodeController.php`
 *                  di prod per 2026-07-29 (verified via SSH).
 *
 * PATCH DIAPPLY dibanding baseline:
 * 1. `updateStatus` validation: tambah `|in:arsip,accept_ba` — reject action
 *    di luar 2 mode ini (sebelumnya string bebas → bug tulis "accept_ba" ke
 *    kolom `status`, menghancurkan enum + accessor status_utama).
 * 2. `updateStatus` action=accept_ba: set kolom `ba='Done'` saja. TIDAK
 *    corrupt kolom `status` dgn string "accept_ba" (bug fix utama).
 * 3. Idempoten check: kalau ba sudah Done, return 200 tanpa error.
 *
 * TIDAK di-porting (biar backward-compat dgn schema prod era April 2026):
 * - Fallback barcode Produk Baru via `ArsipProdukBaruItem` (class + table
 *   belum di prod)
 * - Set `updated_by` di update (kolom `updated_by` belum di prod)
 * - Field `scan_ba_accounting_url` + `scan_final_url` di response processScan
 *   (kolom belum di prod)
 * - Param `note` di request updateStatus (butuh kolom `catatan_it` di
 *   `processArchiving`)
 *
 * DEPLOY: SCP file ini ke `/root/it_submissions/app/Http/Controllers/Api/BarcodeController.php`
 *         → `docker exec it_app php artisan cache:clear`
 *         → `docker kill --signal=USR2 it_app` (graceful reload)
 *
 * HAPUS FILE INI setelah migrasi full sudah dijalankan di prod
 * (Staged Rollout — lihat PROJECT_OVERVIEW.md seksi 15.9).
 * ============================================================================
 */

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
                'message' => 'Arsip dengan barcode ' . $barcode . ' tidak ditemukan'
            ], 404);
        }

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
            'id'     => 'required|exists:arsips,id',
            'status' => 'required|string|in:arsip,accept_ba',
        ]);

        try {
            $action = strtolower($request->status);

            if ($action === 'arsip') {
                // Archive Now → generate No Dokumen, set semua status ke Done
                $arsip = Arsip::processArchiving($request->id);
                $message = 'Arsip berhasil diproses (No Doc: ' . $arsip->no_doc . ')';

            } elseif ($action === 'accept_ba') {
                // Accept BA → hanya set kolom ba = Done. JANGAN corrupt kolom status utama.
                $arsip = Arsip::findOrFail($request->id);

                if ($arsip->ba === 'Done') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Berita Acara sudah Done sebelumnya',
                        'data'    => $arsip->fresh(),
                    ], 200);
                }

                $arsip->update(['ba' => 'Done']);
                $message = 'Berita Acara berhasil di-accept (BA: Done)';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $arsip->fresh(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }
}
