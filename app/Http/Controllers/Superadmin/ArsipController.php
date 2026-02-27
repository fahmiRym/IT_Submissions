<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Arsip;
use App\Models\Department;
use App\Models\Manager;
use App\Models\Unit;
use App\Models\User;
use App\Models\Notification;
use App\Models\Product;           // Tambahan
use App\Models\ArsipMutasiItem;   // Tambahan
use App\Models\ArsipAdjustItem; // Tambahan
use App\Models\ArsipBundelItem; // Tambahan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Tambahan untuk Transaction
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ArsipController extends Controller
{
    /**
     * ===============================
     * INDEX – LIST + FILTER + SEARCH
     * ===============================
     */
    public function index(Request $request)
    {
        $query = Arsip::with(['admin', 'department', 'manager', 'unit', 'adjustItems', 'mutasiItems', 'bundelItems']);

        /* ================= FILTER LOGIC ================= */
        // Use a helper array to store filters that should apply to stats too
        $filters = [
            'q'               => $request->q,
            'department_id'   => $request->department_id,
            'manager_id'      => $request->manager_id,
            'unit_id'         => $request->unit_id,
            'kategori'        => $request->kategori,
            'jenis_pengajuan' => $request->jenis_pengajuan ?? $request->jenis,
            'admin_id'        => $request->admin_id ?? $request->user_id,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
        ];

        foreach ($filters as $key => $value) {
            if (empty($value)) continue;

            if ($key === 'q') {
                $query->where(function ($x) use ($value) {
                    $x->where('no_doc', 'like', "%{$value}%")
                        ->orWhere('no_transaksi', 'like', "%{$value}%")
                        ->orWhere('no_registrasi', 'like', "%{$value}%")
                        ->orWhereHas('admin', fn($a) => $a->where('name', 'like', "%{$value}%"));
                });
            } elseif ($key === 'start_date') {
                $query->whereDate('tgl_pengajuan', '>=', $value);
            } elseif ($key === 'end_date') {
                $query->whereDate('tgl_pengajuan', '<=', $value);
            } else {
                $query->where($key, $value);
            }
        }

        // Filters that should NOT apply to stats (or should be handled per-stat)
        if ($request->filled('ket_process')) {
            $query->where('ket_process', $request->ket_process);
        }
        if ($request->filled('ba')) {
            $query->where('ba', $request->ba);
        }
        if ($request->filled('arsip')) {
            $query->where('arsip', $request->arsip);
        }

        /* ================= SORT ================= */
        $allowedSort = ['id', 'tgl_pengajuan', 'tgl_arsip', 'no_registrasi', 'ket_process', 'department_id', 'admin_id'];
        $sort = in_array($request->get('sort'), $allowedSort) ? $request->get('sort') : 'id';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        /* ================= DATA ================= */
        $perPage = $request->input('per_page', 10);
        $arsips = $query->paginate($perPage)->withQueryString();

        /* ================= STATS (Dynamic) ================= */
        // Stats should respect all filters EXCEPT status-specific ones
        $statsQuery = Arsip::query();
        foreach ($filters as $key => $value) {
            if (empty($value)) continue;

            if ($key === 'q') {
                $statsQuery->where(function ($x) use ($value) {
                    $x->where('no_doc', 'like', "%{$value}%")
                        ->orWhere('no_transaksi', 'like', "%{$value}%")
                        ->orWhere('no_registrasi', 'like', "%{$value}%")
                        ->orWhereHas('admin', fn($a) => $a->where('name', 'like', "%{$value}%"));
                });
            } elseif ($key === 'start_date') {
                $statsQuery->whereDate('tgl_pengajuan', '>=', $value);
            } elseif ($key === 'end_date') {
                $statsQuery->whereDate('tgl_pengajuan', '<=', $value);
            } else {
                $statsQuery->where($key, $value);
            }
        }

        $stats = [
            'total'         => (clone $statsQuery)->count(),
            'Review'        => (clone $statsQuery)->where('ket_process', 'Review')->count(),
            'Process'       => (clone $statsQuery)->where('ket_process', 'Process')->count(),
            'Done'          => (clone $statsQuery)->where('ket_process', 'Done')->count(),
            'Pending'       => (clone $statsQuery)->where('ket_process', 'Pending')->count(),
            'Void'          => (clone $statsQuery)->where('ket_process', 'Void')->count(),
            'ba_pending'    => (clone $statsQuery)->where('ba', 'Pending')->count(),
            'ba_process'    => (clone $statsQuery)->where('ba', 'Process')->count(),
            'ba_done'       => (clone $statsQuery)->where('ba', 'Done')->count(),
            'arsip_pending' => (clone $statsQuery)->where('arsip', 'Pending')->count(),
            'arsip_process' => (clone $statsQuery)->where('arsip', 'Process')->count(),
            'arsip_done'    => (clone $statsQuery)->where('arsip', 'Done')->count(),
        ];

        return view('superadmin.arsip.index', [
            'arsips'      => $arsips,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'managers'    => Manager::where('is_active', true)->orderBy('name')->get(),
            'units'       => Unit::where('is_active', true)->orderBy('name')->get(),
            'users'       => User::where('role', 'admin')->where('is_active', true)->orderBy('name')->get(),
            'superadmins' => User::where('role', 'superadmin')->where('is_active', true)->orderBy('name')->get(),
            'sort'        => $sort,
            'dir'         => $dir,
            'stats'       => $stats
        ]);
    }

    /**
     * ===============================
     * STORE – CREATE NEW ARSIP
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'tgl_pengajuan'   => 'required|date',
            'user_id'         => 'required|exists:users,id',
            'department_id'   => 'required|exists:departments,id',
            'manager_id'      => 'required|exists:managers,id',
            'unit_id'         => 'required|exists:units,id',
            'jenis_pengajuan' => 'required|string|max:30',
            'status'          => 'required|in:Check,Process,Done,Reject,Void',
            'bukti_scan'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $filename = null;
            if ($request->hasFile('bukti_scan')) {
                $file = $request->file('bukti_scan');
                $cleanName = preg_replace('/[^A-Za-z0-9\.\_\-]/', '_', $file->getClientOriginalName());
                $filename = time() . '_' . $cleanName;
                $file->storeAs('bukti_scan', $filename, ['disk' => 'public', 'visibility' => 'public']);
            }

            // Hitung total qty
            $totalIn = 0;
            $totalOut = 0;

            // Helper to sum qty from array structure
            $calculateQty = function($items, $type) {
                if(!is_array($items)) return 0;
                return collect($items)->sum(function($item) use ($type) {
                     if($type === 'in') return $item['qty_in'] ?? ($item['qty'] ?? 0);
                     if($type === 'out') return $item['qty_out'] ?? ($item['qty'] ?? 0);
                     return 0;
                });
            };

            $rawItems = $request->input('detail_barang', []);

            if ($request->jenis_pengajuan == 'Adjust') {
                $totalIn  = collect($rawItems['adjust'] ?? [])->sum('qty_in');
                $totalOut = collect($rawItems['adjust'] ?? [])->sum('qty_out');
            } elseif(str_contains($request->jenis_pengajuan, 'Mutasi')) {
                 // Mutasi Asal = Out, Mutasi Tujuan = In
                 $totalOut = collect($rawItems['mutasi_asal'] ?? [])->sum('qty');
                 $totalIn  = collect($rawItems['mutasi_tujuan'] ?? [])->sum('qty');
            } elseif($request->jenis_pengajuan == 'Bundel') {
                 $totalIn = collect($rawItems['bundel'] ?? [])->sum('qty');
            }

            $arsip = Arsip::create([
                'no_registrasi'   => Arsip::generateNoRegistrasi($request),
                'tgl_pengajuan'   => $request->tgl_pengajuan ? \Carbon\Carbon::parse($request->tgl_pengajuan)->setTimeFrom(now()) : now(),
                'tgl_arsip'       => $request->tgl_arsip,
                'admin_id'        => $request->user_id,
                'superadmin_id'   => auth()->id(),
                'department_id'   => $request->department_id,
                'manager_id'      => $request->manager_id,
                'unit_id'         => $request->unit_id,
                'kategori'        => $request->kategori ?: 'None', // Handle empty string
                'pemohon'         => $request->pemohon,
                'keterangan'      => $request->keterangan,
                'no_doc'          => $request->no_doc,
                'no_transaksi'    => $request->no_transaksi,
                'jenis_pengajuan' => $request->jenis_pengajuan,
                'target_qty'      => $request->target_qty,
                'catatan_tambahan'=> $request->catatan_tambahan,
                'sub_jenis'       => $request->sub_jenis,
                'detail_barang'   => $rawItems, // Backup JSON
                'total_qty_in'    => $totalIn,
                'total_qty_out'   => $totalOut,
                'status'          => $request->status,
                'bukti_scan'      => $filename,
                'ba'              => $request->ba ?? ($request->status == 'Done' ? 'Done' : 'Process'),
                'arsip'           => $request->arsip ?? ($request->status == 'Done' ? 'Done' : 'Pending'),
                'ket_process'     => $request->ket_process ?? 'Review',
            ]);

            // SIMPAN DETIL KE TABLE RELASI
            $this->saveDetailItems($arsip, $rawItems);

            DB::commit();

            return redirect()->route('superadmin.arsip.index')->with('success', 'Data arsip berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }


    /**
     * ===============================
     * EDIT (AJAX SUPPORT)
     * ===============================
     */
    public function edit($id)
    {
        $arsip = Arsip::with([
            'admin', 'department', 'manager', 'unit',
            'adjustItems', 'mutasiItems', 'bundelItems'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $arsip
        ]);
    }

    /**
     * ===============================
     * ARSIP SISTEM (LOGIC FIX)
     * ===============================
     */
    public function arsipSistem(Request $request, $id)
    {
        // 1. Load Arsip
        $arsip = Arsip::with(['department', 'unit'])->findOrFail($id);

        // 🔒 CEK STATUS FINAL - Hanya blokir jika SUDAH PUNYA No Dokumen
        if ($arsip->status === 'Done' && !empty($arsip->no_doc)) {
            return back()->withErrors(['status' => 'Dokumen ini sudah diarsipkan dengan No Doc: ' . $arsip->no_doc]);
        }

        // --- MULAI TRANSAKSI DATABASE ---
        // Kita gunakan DB Transaction agar jika ada error saat insert item, status arsip tidak berubah jadi Done
        try {
            DB::beginTransaction();

            $now = Carbon::now();

            /**
             * 1️⃣ NO REGISTRASI
             * Hanya generate baru jika BELUM ADA. Jangan overwrite yang sudah ada.
             */
            if (!empty($arsip->no_registrasi)) {
                // Sudah punya no_registrasi → pakai yang lama, jangan diubah
                $noRegistrasiFix = $arsip->no_registrasi;
            } else {
                // Belum punya → generate baru
                $deptObj = $arsip->department;
                $unitObj = $arsip->unit;

                $kodeDept = $deptObj->code ?? strtoupper(substr($deptObj->name, 0, 3));
                $tglCode  = $now->format('ymd');

                if (!empty($unitObj->code)) {
                    $kodeUnit = $unitObj->code;
                } else {
                    $kodeUnit = str_replace(['Unit ', 'Unit', ' '], ['U', 'U', ''], $unitObj->name);
                }

                $prefixReg = "{$kodeDept}-{$tglCode}-{$kodeUnit}-";
                $lastArsip = Arsip::where('no_registrasi', 'like', $prefixReg . '%')
                    ->where('id', '!=', $id)
                    ->orderBy('no_registrasi', 'desc')
                    ->first();

                if ($lastArsip) {
                    $parts = explode('-', $lastArsip->no_registrasi);
                    $lastSegment = end($parts);
                    $newSeq = is_numeric($lastSegment) ? (int)$lastSegment + 1 : 1;
                } else {
                    $newSeq = 1;
                }

                $noRegistrasiFix = $prefixReg . str_pad($newSeq, 3, '0', STR_PAD_LEFT);
            }

            /**
             * 2️⃣ GENERATE NO DOC (SWITCH CASE)
             */
            $noDoc = [];
            $tahun = $now->format('Y');
            $bulan = $now->format('m');
            $hari  = $now->format('d');

            $prefixDoc = 'DOC';
            $padding   = 4;
            $useDay    = false;

            switch (trim(str_replace(' ', '_', $arsip->jenis_pengajuan))) {
                case 'Mutasi_Produk':
                    $prefixDoc = 'RPP';
                    $padding   = 4;
                    $useDay    = false;
                    break;
                case 'Mutasi_Billet':
                    $prefixDoc = 'DCB';
                    $padding   = 5;
                    $useDay    = false;
                    break;
                case 'Adjust':
                    $prefixDoc = 'DC';
                    $padding   = 4;
                    $useDay    = true;
                    break;
                case 'Internal_Memo':
                    $prefixDoc = 'IM';
                    $padding   = 4;
                    $useDay    = false;
                    break;
                case 'Bundel':
                    $prefixDoc = 'BDL';
                    $padding   = 4;
                    $useDay    = false;
                    break;
                case 'Cancel':
                case 'Cancelled':
                    $prefixDoc = 'CANCEL';
                    $padding   = 4;
                    $useDay    = false;
                    break;
                default:
                    $prefixDoc = strtoupper(substr(str_replace(' ', '', $arsip->jenis_pengajuan), 0, 3));
                    $padding   = 4;
                    $useDay    = false;
                    break;
            }

            // --- LOGIKA SEQUENCE OTOMATIS (Mencari nomor urut TERBESAR dari No Doc) ---
            $rawSeq = $request->sequence_number;

            if (!$rawSeq) {
                $normalizedJenis = trim(str_replace(' ', '_', $arsip->jenis_pengajuan));

                // Ambil SEMUA record jenis yang sama yang sudah punya no_doc di tahun ini
                $allDocs = Arsip::where('jenis_pengajuan', $arsip->jenis_pengajuan)
                    ->whereNotNull('no_doc')
                    ->where('no_doc', 'like', "%{$tahun}%")
                    ->pluck('no_doc');

                $maxNumber = 0;

                foreach ($allDocs as $docStr) {
                    if ($normalizedJenis === 'Cancel' || $normalizedJenis === 'Cancelled') {
                        // Format: "Cancelled No Doc : 0025/02/IT/2026"
                        // Ambil angka tepat setelah ": "
                        if (preg_match('/Cancelled No Doc\s*:\s*(\d+)/', $docStr, $m)) {
                            $maxNumber = max($maxNumber, (int)$m[1]);
                        }
                    } else {
                        // Format: "PREFIX/TAHUN/BULAN/NNNN" atau "PREFIX/TAHUN/BULAN/HARI/NNNN"
                        // Nomor urut selalu merupakan segmen TERAKHIR
                        $parts = explode('/', $docStr);
                        $lastSegment = end($parts);
                        if (is_numeric($lastSegment)) {
                            $maxNumber = max($maxNumber, (int)$lastSegment);
                        }
                    }
                }

                $rawSeq = $maxNumber + 1;
            }

            $seqDoc = str_pad($rawSeq, $padding, '0', STR_PAD_LEFT);
            $normalizedJenisForDoc = trim(str_replace(' ', '_', $arsip->jenis_pengajuan));

            if ($normalizedJenisForDoc === 'Cancel' || $normalizedJenisForDoc === 'Cancelled') {
                $finalNoDoc = "Cancelled No Doc : {$seqDoc}/{$bulan}/IT/{$tahun}";
            } else {
                if ($useDay) {
                    $finalNoDoc = "{$prefixDoc}/{$tahun}/{$bulan}/{$hari}/{$seqDoc}";
                } else {
                    $finalNoDoc = "{$prefixDoc}/{$tahun}/{$bulan}/{$seqDoc}";
                }
            }

            $noDoc = [$finalNoDoc];

            /**
             * 3️⃣ UPDATE FINAL & NOTIFIKASI
             */
            $arsip->update([
                'no_registrasi' => $noRegistrasiFix,
                'no_doc'        => implode("\n", $noDoc),
                'tgl_arsip'     => $now,
                'status'        => 'Done',
                'ba'            => 'Done',
                'arsip'         => 'Done',
                'ket_process'   => 'Done',
            ]);

            Notification::create([
                'user_id'     => $arsip->admin_id,
                'arsip_id'    => $arsip->id,
                'title'       => 'Arsip Selesai',
                'message'     => "Dokumen telah diarsipkan.\nNo Reg: {$noRegistrasiFix}\nNo Doc: {$finalNoDoc}",
                'role_target' => 'admin',
            ]);

            DB::commit(); // Simpan semua perubahan jika sukses

            if ($request->wantsJson() || $request->ajax()) {
                $isCancel = in_array(trim($arsip->jenis_pengajuan), ['Cancel', 'Cancelled']);

                $noTransaksiSub = '';
                $copyAllText    = '';

                if ($isCancel) {
                    // Filter Sub Transaksi (buang baris yang diawali MO atau PO)
                    $indukPrefixes  = ['MO', 'PO'];
                    $noTransaksiRaw = $arsip->no_transaksi ?? '';
                    $allLines       = preg_split('/\r\n|\n|\r/', $noTransaksiRaw);
                    $subLines       = array_filter($allLines, function ($line) use ($indukPrefixes) {
                        $trimmed = trim($line);
                        if ($trimmed === '') return false;
                        foreach ($indukPrefixes as $prefix) {
                            if (str_starts_with($trimmed, $prefix)) return false;
                        }
                        return true;
                    });
                    $noTransaksiSub = implode("\n", array_values($subLines));

                    // Gabungan siap copy: No Doc + newline + sub transaksi
                    $copyAllText = $finalNoDoc;
                    if ($noTransaksiSub !== '') {
                        $copyAllText .= "\n" . $noTransaksiSub;
                    }
                }

                return response()->json([
                    'status'           => 'success',
                    'no_doc'           => $finalNoDoc,
                    'no_registrasi'    => $noRegistrasiFix,
                    'jenis_pengajuan'  => $arsip->jenis_pengajuan,
                    'no_transaksi_sub' => $noTransaksiSub,
                    'copy_all_text'    => $copyAllText,
                    'message'          => "Berhasil diarsipkan!",
                ]);
            }

            return redirect()
                ->route('superadmin.arsip.index')
                ->with('success', "Berhasil Arsip! No Doc: {$finalNoDoc}");

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua jika ada error
            return back()->withErrors(['error' => 'Gagal melakukan arsip sistem: ' . $e->getMessage()]);
        }
    }

    /**
     * ===============================
     * CLEANUP STORAGE - DELETE SCAN FILES
     * ===============================
     */
    public function cleanupStorage(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        try {
            // Cari arsip yang memiliki file bukti_scan dalam range tanggal
            $arsips = Arsip::whereNotNull('bukti_scan')
                ->whereDate('tgl_pengajuan', '>=', $request->start_date)
                ->whereDate('tgl_pengajuan', '<=', $request->end_date)
                ->get();

            if ($arsips->isEmpty()) {
                return back()->with('error', 'Tidak ada file scan yang ditemukan dalam rentang tanggal tersebut.');
            }

            $count = 0;
            foreach ($arsips as $arsip) {
                if ($arsip->bukti_scan) {
                    $filePath = 'bukti_scan/' . $arsip->bukti_scan;
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                    
                    // Update field database jadi NULL agar tidak ada link 'mati'
                    $arsip->update(['bukti_scan' => null]);
                    $count++;
                }
            }

            return back()->with('success', "Berhasil membersihkan {$count} file scan dari sistem.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membersihkan storage: ' . $e->getMessage());
        }
    }

    /**
     * ===============================
     * UPDATE – REVIEW SUPERADMIN
     * ===============================
     */
    public function update(Request $request, Arsip $arsip)
    {


        $request->validate([
            'status'      => 'required|in:Check,Process,Pending,Done,Reject,Void',
            'ket_process' => 'nullable|in:Review,Process,Done,Pending,Void,Partial Done',
            'ba'          => 'nullable',
            'arsip'       => 'nullable',
            'bukti_scan'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $filename = $arsip->bukti_scan;
            if ($request->hasFile('bukti_scan')) {
                $file = $request->file('bukti_scan');
                $cleanName = preg_replace('/[^A-Za-z0-9\.\_\-]/', '_', $file->getClientOriginalName());
                $filename = time() . '_' . $cleanName;
                $file->storeAs('bukti_scan', $filename, ['disk' => 'public', 'visibility' => 'public']);
            }

            // MAP STATUS UTAMA
            $map = [
                'Check'   => ['ba' => 'Done', 'arsip' => 'Pending', 'ket_process' => 'Review'],
                'Process' => ['ba' => 'Done', 'arsip' => 'Pending', 'ket_process' => 'Process'],
                'Pending' => ['ket_process' => 'Pending'],
                'Done'    => ['ba' => 'Done', 'arsip' => 'Done', 'ket_process' => 'Done'],
                'Reject'  => ['ba' => 'Void', 'arsip' => 'None', 'ket_process' => 'Void'],
                'Void'    => ['ba' => 'Void', 'arsip' => 'None', 'ket_process' => 'Void'],
            ];

            // 1. Ambil Data Detail dari Request
            $rawItems = $request->detail_barang ?: [];

            // 2. Hitung Ulang Total Qty (Mutasi, Adjust, Bundel)
            $totalIn  = 0;
            $totalOut = 0;

            if (!empty($rawItems['mutasi_asal']))   $totalOut += collect($rawItems['mutasi_asal'])->sum('qty');
            if (!empty($rawItems['mutasi_tujuan'])) $totalIn  += collect($rawItems['mutasi_tujuan'])->sum('qty');
            if (!empty($rawItems['bundel']))        $totalOut += collect($rawItems['bundel'])->sum('qty');
            if (!empty($rawItems['adjust'])) {
                $totalIn  += collect($rawItems['adjust'])->sum('qty_in');
                $totalOut += collect($rawItems['adjust'])->sum('qty_out');
            }

            // 3. Siapkan Data Update & Automation
            $updateData = [
                'admin_id'        => $request->user_id,
                'no_registrasi'   => $request->no_registrasi ?? $arsip->no_registrasi,
                'tgl_pengajuan'   => $request->tgl_pengajuan ? \Carbon\Carbon::parse($request->tgl_pengajuan)->setTimeFrom(now()) : $arsip->tgl_pengajuan,
                'tgl_arsip'       => $request->tgl_arsip,
                'department_id'   => $request->department_id,
                'manager_id'      => $request->manager_id,
                'unit_id'         => $request->unit_id,
                'no_doc'          => $request->no_doc,
                'no_transaksi'    => $request->no_transaksi,
                'kategori'        => $request->kategori ?? 'None',
                'jenis_pengajuan' => $request->jenis_pengajuan ?? $arsip->jenis_pengajuan,
                'pemohon'         => $request->pemohon,
                'target_qty'      => $request->target_qty ?? $arsip->target_qty,
                'keterangan'      => $request->keterangan ?? $arsip->keterangan,
                'sub_jenis'       => $request->sub_jenis ?? $arsip->sub_jenis,
                'status'          => $request->status,
                'bukti_scan'      => $filename,
                'detail_barang'   => $rawItems,
                'total_qty_in'    => $totalIn,
                'total_qty_out'   => $totalOut,
            ];

            // Masukkan Automasi Status (Default)
            if (isset($map[$request->status])) {
                $updateData = array_merge($updateData, $map[$request->status]);
            }

            // IJINKAN OVERRIDE MANUAL (Jika form diisi secara spesifik & tidak kosong)
            if ($request->filled('ba'))          $updateData['ba'] = $request->ba;
            if ($request->filled('arsip'))       $updateData['arsip'] = $request->arsip;
            if ($request->filled('ket_process')) $updateData['ket_process'] = $request->ket_process;

            // JIKA STATUS DI EDIT MENJADI DONE, TAPI NO DOC KOSONG -> BERI PERINGATAN ATAU TETAPKAN
            // Catatan: Sebaiknya No Doc hanya digenerate lewat tombol Arsip Sistem agar terkontrol.
            
            $arsip->update($updateData);

            // 4. Sinkronisasi Tabel Relasi (Hapus Lama, Buat Baru)
            ArsipMutasiItem::where('arsip_id', $arsip->id)->delete();
            ArsipAdjustItem::where('arsip_id', $arsip->id)->delete();
            ArsipBundelItem::where('arsip_id', $arsip->id)->delete();

            $this->saveDetailItems($arsip, $rawItems);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }

        // NOTIFIKASI CUSTOM (Menggunakan Keterangan Proses sesuai permintaan user)
        $title = 'Update Tahap Pengajuan';
        $message = "Pengajuan Anda sekarang berada di tahap: {$arsip->ket_process}";

        if ($arsip->ket_process == 'Void') {
            $title = 'Pengajuan Dibatalkan / Void';
            $message = 'Pengajuan dokumen Anda telah dibatalkan (Void).';
        } elseif ($arsip->ket_process == 'Done') {
            $title = 'Pengajuan Selesai';
            $message = 'Selamat, pengajuan dokumen Anda telah selesai (Done).';
        } elseif ($arsip->ket_process == 'Partial Done') {
            $title = 'Pengajuan Selesai Sebagian';
            $message = 'Pengajuan Anda telah selesai sebagian (Partial Done).';
        } elseif ($arsip->ket_process == 'Pending') {
            $title = 'Pengajuan Ditunda (Pending)';
            $message = 'Pengajuan Anda sedang dipending atau memerlukan revisi.';
        } elseif ($arsip->ket_process == 'Review') {
            $title = 'Pengajuan Sedang Diulas';
            $message = 'Pengajuan Anda sedang dalam tahap review oleh Superadmin.';
        }

        Notification::create([
            'user_id'     => $arsip->admin_id,
            'arsip_id'    => $arsip->id,
            'title'       => $title,
            'message'     => $message,
            'role_target' => 'admin',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Status arsip berhasil diperbarui']);
        }
        
        return back()->with('success', 'Status arsip berhasil diperbarui');
    }

    public function destroy(Arsip $arsip)
    {
        $arsip->delete();
        return redirect()->route('superadmin.arsip.index')->with('success', 'Data arsip berhasil dihapus');
    }

    /**
     * ===============================
     * HELPER: SAVE DETAIL ITEMS
     * ===============================
     */
    private function saveDetailItems($arsip, $data)
    {
        // 1. MUTASI
        $saveMutasi = function($items, $type) use ($arsip) {
            if (!is_array($items)) return;
            foreach ($items as $item) {
                if (empty($item['no_doc']) && empty($item['nama_produk']) && empty($item['product_code'])) continue;
                
                ArsipMutasiItem::create([
                    'arsip_id'     => $arsip->id,
                    'type'         => $type,
                    'product_code' => $item['product_code'] ?? $item['no_doc'] ?? null,
                    'product_name' => $item['nama_produk'] ?? $item['no_doc'] ?? '-',
                    'qty'          => $item['qty'] ?? 0,
                    'lot'          => $item['lot'] ?? $item['keterangan'] ?? null,
                    'panjang'      => $item['panjang'] ?? null,
                    'location'     => $item['location'] ?? null,
                ]);
            }
        };

        if (!empty($data['mutasi_asal']))   $saveMutasi($data['mutasi_asal'], 'asal');
        if (!empty($data['mutasi_tujuan'])) $saveMutasi($data['mutasi_tujuan'], 'tujuan');

        // 2. BUNDEL
        if (!empty($data['bundel']) && is_array($data['bundel'])) {
            foreach ($data['bundel'] as $item) {
                if (empty($item['no_doc'])) continue;
                ArsipBundelItem::create([
                    'arsip_id'   => $arsip->id,
                    'no_doc'     => $item['no_doc'],
                    'qty'        => $item['qty'] ?? 1,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }
        }

        // 3. ADJUST
        if (!empty($data['adjust']) && is_array($data['adjust'])) {
            foreach ($data['adjust'] as $item) {
                // Cek minimal ada nama barang/kode
                $identifier = $item['nama_produk'] ?? $item['no_doc'] ?? $item['product_code'] ?? null;
                if (!$identifier) continue;

                ArsipAdjustItem::create([
                    'arsip_id'     => $arsip->id,
                    'product_code' => $item['product_code'] ?? null,
                    'product_name' => $item['nama_produk'] ?? $item['no_doc'] ?? '-', 
                    'qty_in'       => $item['qty_in'] ?? 0,
                    'qty_out'      => $item['qty_out'] ?? 0,
                    'lot'          => $item['lot'] ?? $item['keterangan'] ?? null,
                ]);
            }
        }
    }

    /**
     * ===============================
     * PRINT DRAFT
     * ===============================
     */
    public function printDraft($id)
    {
        $arsip = Arsip::with(['department', 'unit', 'admin'])->findOrFail($id);
        return view('print.arsip_draft', compact('arsip'));
    }
}