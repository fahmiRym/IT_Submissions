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

        /* ================= SEARCH ================= */
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($x) use ($q) {
                $x->where('no_doc', 'like', "%{$q}%")
                    ->orWhere('no_transaksi', 'like', "%{$q}%")
                    ->orWhere('no_registrasi', 'like', "%{$q}%")
                    ->orWhereHas('admin', fn($a) => $a->where('name', 'like', "%{$q}%"));
            });
        }

        /* ================= FILTER ================= */
        // 1. Departemen
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 2. Kategori (Error Type)
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // 3. Jenis Pengajuan (Support both 'jenis' and 'jenis_pengajuan' params)
        $jenis = $request->get('jenis') ?? $request->get('jenis_pengajuan');
        if ($jenis) {
            $query->where('jenis_pengajuan', $jenis);
        }

        // 4. Status Process (Review, Process, Done, Pending, Void)
        if ($request->filled('ket_process')) {
            $query->where('ket_process', $request->ket_process);
        }

        // 5. Date Range (Tgl Pengajuan)
        if ($request->filled('start_date')) {
            $query->whereDate('tgl_pengajuan', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tgl_pengajuan', '<=', $request->end_date);
        }

        // 6. User (Pengaju)
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // 7. BA Status (Khusus dashboard click)
        if ($request->filled('ba')) {
            $query->where('ba', $request->ba);
        }

        // 8. Arsip Status (Khusus dashboard click)
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
        $arsips = $query
            ->paginate($perPage)
            ->withQueryString();

        /* ================= STATS ================= */
        // Base query for stats (should respect current non-status filters if needed, but usually stats are broad)
        // For now, let's keep stats somewhat independent or just filtered by 'jenis' as before to not confuse the user with disappearing stats.
        // However, if the user requested "Filter", usually they want to see stats for that filter? 
        // Let's stick to the previous pattern of filtering stats only by 'jenis' to show "Global Context" of that type.
        
        $statsQuery = Arsip::query();
        if ($jenis) {
            $statsQuery->where('jenis_pengajuan', $jenis);
        }

        // Clone for counts
        $total    = (clone $statsQuery)->count();
        $review   = (clone $statsQuery)->where('ket_process', 'Review')->count();
        $process  = (clone $statsQuery)->where('ket_process', 'Process')->count();
        $done     = (clone $statsQuery)->where('ket_process', 'Done')->count();
        $pending  = (clone $statsQuery)->where('ket_process', 'Pending')->count();
        $void     = (clone $statsQuery)->where('ket_process', 'Void')->count();

        $stats = [
            'total'   => $total,
            'Review'  => $review,
            'Process' => $process,
            'Done'    => $done,
            'Pending' => $pending,
            'Void'    => $void,
            'ba_pending' => (clone $statsQuery)->where('ba', 'Pending')->count(),
            'ba_process' => (clone $statsQuery)->where('ba', 'Process')->count(),
            'ba_done'    => (clone $statsQuery)->where('ba', 'Done')->count(),
            'arsip_pending' => (clone $statsQuery)->where('arsip', 'Pending')->count(),
            'arsip_done'    => (clone $statsQuery)->where('arsip', 'Done')->count(),
        ];

        return view('superadmin.arsip.index', [
            'arsips'      => $arsips,
            'departments' => Department::orderBy('name')->get(),
            'managers'    => Manager::orderBy('name')->get(),
            'units'       => Unit::orderBy('name')->get(),
            'users'       => User::where('role', 'admin')->orderBy('name')->get(),
            'superadmins' => User::where('role', 'superadmin')->orderBy('name')->get(),
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
                'tgl_pengajuan'   => $request->tgl_pengajuan,
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

        // 🔒 CEK STATUS FINAL
        if ($arsip->status === 'Done') {
            return back()->withErrors(['status' => 'Dokumen ini sudah diarsipkan.']);
        }

        // --- MULAI TRANSAKSI DATABASE ---
        // Kita gunakan DB Transaction agar jika ada error saat insert item, status arsip tidak berubah jadi Done
        try {
            DB::beginTransaction();

            $now = Carbon::now();

            /**
             * 1️⃣ GENERATE NO REGISTRASI
             */
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
                ->orderBy('id', 'desc')
                ->first();

            $newSeq = $lastArsip ? str_pad((int)substr($lastArsip->no_registrasi, -3) + 1, 3, '0', STR_PAD_LEFT) : '001';
            $noRegistrasiFix = $prefixReg . $newSeq;

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

            switch ($arsip->jenis_pengajuan) {
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
                    $prefixDoc = 'CANCEL';
                    $padding   = 4;
                    $useDay    = false;
                    break;
                default:
                    $prefixDoc = strtoupper(substr($arsip->jenis_pengajuan, 0, 3));
                    $padding   = 4;
                    $useDay    = false;
                    break;
            }

            // --- USE MANUAL SEQUENCE OR AUTO ID ---
            $rawSeq = $request->sequence_number ?: $arsip->id;
            $seqDoc = str_pad($rawSeq, $padding, '0', STR_PAD_LEFT);

            if ($arsip->jenis_pengajuan === 'Cancel') {
                $finalNoDoc = "Cancelled No Doc : {$seqDoc}/{$bulan}/{$kodeDept}/{$tahun}";
            } else {
                if ($useDay) {
                    $finalNoDoc = "{$prefixDoc}/{$tahun}/{$bulan}/{$hari}/{$seqDoc}";
                } else {
                    $finalNoDoc = "{$prefixDoc}/{$tahun}/{$bulan}/{$seqDoc}";
                }
            }

            $noDoc[] = $finalNoDoc;

            if ($arsip->no_transaksi) {
                $lines = preg_split("/\r\n|\n|\r/", $arsip->no_transaksi);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!$line) continue;
                    if (preg_match('/^(BPB-|SJF-|BPBI-|SJ-|RTR-BPB)/', $line)) {
                        $noDoc[] = $line;
                    }
                }
            }

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
     * UPDATE – REVIEW SUPERADMIN
     * ===============================
     */
    public function update(Request $request, Arsip $arsip)
    {


        $request->validate([
            'status'      => 'required|in:Check,Process,Pending,Done,Reject,Void',
            'ket_process' => 'nullable|in:Review,Process,Done,Pending,Void',
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

            // 3. Update Header Arsip
            $arsip->update(array_merge(
                ['status' => $request->status],
                $map[$request->status] ?? [],
                [
                    'admin_id'        => $request->user_id,
                    'tgl_pengajuan'   => $request->tgl_pengajuan,
                    'tgl_arsip'       => $request->tgl_arsip,
                    'department_id'   => $request->department_id,
                    'manager_id'      => $request->manager_id,
                    'unit_id'         => $request->unit_id,
                    'no_transaksi'    => $request->no_transaksi,
                    'kategori'        => $request->kategori ?? 'None',
                    'ba'              => $request->ba,
                    'arsip'           => $request->arsip,
                    'ket_process'     => $request->ket_process, 
                    'jenis_pengajuan' => $request->jenis_pengajuan ?? $arsip->jenis_pengajuan,
                    'pemohon'         => $request->pemohon,
                    'target_qty'      => $request->target_qty ?? $arsip->target_qty,
                    'keterangan'      => $request->keterangan ?? $arsip->keterangan,
                    'sub_jenis'       => $request->sub_jenis ?? $arsip->sub_jenis,
                    'detail_barang'   => $rawItems, 
                    'total_qty_in'    => $totalIn,
                    'total_qty_out'   => $totalOut,
                    'bukti_scan'      => $filename,
                ]
            ));

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
}