<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Arsip;
use App\Models\Department;
use App\Models\Unit;
use App\Models\Manager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ArsipApiController extends Controller
{
    /**
     * API untuk Dashboard Utama Android
     */
    public function getDashboard(Request $request)
    {
        // 1. Statistik Berdasarkan Status Utama (Card Atas)
        $totalPengajuan = Arsip::count(); // Total Semua Pengajuan
        $totalArsip = Arsip::where('status', 'Done')->count(); // Total Pengajuan Terarsip
        $arsipDone = Arsip::where('arsip', 'Done')->count(); // Pengajuan Selesai (Finalized)
        $arsipProcess = Arsip::where('arsip', 'Pending')->count(); // Dinamika Proses (On Going)

        // 2. Statistik Berdasarkan Keterangan Proses (Pipeline Bawah)
        $ketPending = Arsip::where('ket_process', 'Pending')->count();
        $ketReview = Arsip::where('ket_process', 'Review')->count();
        $ketProcess = Arsip::where('ket_process', 'Process')->count();
        $ketDone = Arsip::where('ket_process', 'Done')->count();

        // Riwayat Pengajuan Terakhir (5 Data Terakhir)
        $recentArsips = Arsip::with(['department', 'unit'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($arsip) {
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
                return $arsip;
            });

        return response()->json([
            'success' => true,
            'message' => 'Data Dashboard Berhasil Diambil',
            'data' => [
                'user' => [
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role ?? 'admin',
                ],
                'statistics' => [
                    // Sesuai dengan penamaan di DashboardFragment.kt Android
                    'totalPengajuan' => $totalPengajuan,
                    'totalArsip' => $totalArsip,
                    'arsipDone' => $arsipDone,
                    'arsipProcess' => $arsipProcess,
                    'ketPending' => $ketPending,
                    'ketReview' => $ketReview,
                    'ketProcess' => $ketProcess,
                    'ketDone' => $ketDone,
                ],
                'recent_pengajuan' => $recentArsips
            ]
        ], 200);
    }

    /**
     * API untuk mendapatkan data master (Department, Unit, Manager)
     * Digunakan oleh Android untuk mengisi Dropdown form pengajuan.
     */
    public function getMasterData()
    {
        $departments = Department::where('is_active', true)->get(['id', 'name', 'code']);
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);
        $managers = Manager::where('is_active', true)->get(['id', 'name']);

        $jenisPengajuan = [
            'Adjust',
            'Mutasi Billet',
            'Mutasi Produk',
            'Internal Memo',
            'Bundel',
            'Cancel'
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data Master Berhasil Diambil',
            'data' => [
                'departments' => $departments,
                'units' => $units,
                'managers' => $managers,
                'jenis_pengajuan' => $jenisPengajuan,
            ]
        ], 200);
    }

    /**
     * API untuk menyimpan Pengajuan Arsip dari Android
     */
    public function storePengajuan(Request $request)
    {
        // Validasi Dasar
        $request->validate([
            'jenis_pengajuan' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'unit_id' => 'required|exists:units,id',
            'manager_id' => 'nullable|exists:managers,id',
            'keterangan' => 'nullable|string',
            'bukti_scan' => 'nullable|file|mimes:pdf|max:10240', // Maks 10MB
        ]);

        try {
            // Generate No Registrasi otomatis menggunakan fungsi yang ada di Model
            $noRegistrasi = Arsip::generateNoRegistrasi((object) [
                'department_id' => $request->department_id,
                'unit_id' => $request->unit_id
            ]);

            // Handle Upload File (Bukti Scan dari Kamera HP/File Picker)
            $filename = null;
            if ($request->hasFile('bukti_scan')) {
                $file = $request->file('bukti_scan');
                $extension = $file->getClientOriginalExtension();
                $filename = str_replace([' ', '/', '\\'], '_', $noRegistrasi) . '-' . time() . '.' . $extension;

                // Simpan ke storage/app/public/bukti_scan
                $file->storeAs('public/bukti_scan', $filename);
            }

            // Simpan Ke Database
            $arsip = Arsip::create([
                'no_registrasi' => $noRegistrasi,
                'jenis_pengajuan' => $request->jenis_pengajuan,
                'keterangan' => $request->keterangan,
                'department_id' => $request->department_id,
                'unit_id' => $request->unit_id,
                'manager_id' => $request->manager_id,
                'tgl_pengajuan' => now(),
                'status' => 'Pending', // Status awal pengajuan
                'ba' => 'Pending',
                'arsip' => 'Pending',
                'ket_process' => 'Pending',
                'admin_id' => $request->user()->id, // Diisi id admin yang login di Android
                'bukti_scan' => $filename,
            ]);

            // Tambahkan URL lengkap untuk hasil respon
            if ($arsip->bukti_scan) {
                $ext = pathinfo($arsip->bukti_scan, PATHINFO_EXTENSION);
                if (strtolower($ext) === 'pdf') {
                    $arsip->bukti_scan_url = route('pdf.viewer', ['filename' => $arsip->bukti_scan]);
                } else {
                    $arsip->bukti_scan_url = asset('storage/bukti_scan/' . $arsip->bukti_scan);
                }
            } else {
                $arsip->bukti_scan_url = null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan Arsip Berhasil Disimpan',
                'data' => $arsip
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengajuan: ' . $e->getMessage()
            ], 500);
        }
    }
}
