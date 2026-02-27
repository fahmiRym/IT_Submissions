<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// IMPORT MODEL
use App\Models\Arsip;
use App\Models\ArsipMutasiItem;
use App\Models\ArsipAdjustItem;
use App\Models\ArsipBundelItem;
use App\Models\Unit;
use App\Models\Department;
use App\Models\Manager;
use App\Models\Product;
use App\Models\Notification;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        $query = Arsip::with(['department', 'manager', 'unit', 'adjustItems', 'mutasiItems', 'bundelItems'])
                        ->where('admin_id', auth()->id());

        /* ================= FILTER LOGIC ================= */
        $filters = [
            'q'               => $request->q,
            'department_id'   => $request->department_id,
            'kategori'        => $request->kategori,
            'jenis_pengajuan' => $request->jenis_pengajuan ?? $request->jenis,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
        ];

        foreach ($filters as $key => $value) {
            if (empty($value)) continue;

            if ($key === 'q') {
                $query->where(function ($x) use ($value) {
                    $x->where('no_doc', 'like', "%{$value}%")
                        ->orWhere('no_transaksi', 'like', "%{$value}%")
                        ->orWhere('no_registrasi', 'like', "%{$value}%");
                });
            } elseif ($key === 'start_date') {
                $query->whereDate('tgl_pengajuan', '>=', $value);
            } elseif ($key === 'end_date') {
                $query->whereDate('tgl_pengajuan', '<=', $value);
            } else {
                $query->where($key, $value);
            }
        }

        if ($request->filled('manager_id')) {
            $query->where('manager_id', $request->manager_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('ket_process')) {
            $query->where('ket_process', $request->ket_process);
        }

        /* ================= SORT ================= */
        $allowedSort = ['id', 'tgl_pengajuan', 'no_registrasi', 'ket_process', 'department_id'];
        $sort = in_array($request->get('sort'), $allowedSort) ? $request->get('sort') : 'id';
        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        /* ================= DATA ================= */
        $perPage = $request->input('per_page', 10);
        $arsips = $query->paginate($perPage)->withQueryString();

        /* ================= STATS (Dynamic) ================= */
        $statsQuery = Arsip::where('admin_id', auth()->id());
        foreach ($filters as $key => $value) {
            if (empty($value)) continue;

            if ($key === 'q') {
                $statsQuery->where(function ($x) use ($value) {
                    $x->where('no_doc', 'like', "%{$value}%")
                        ->orWhere('no_transaksi', 'like', "%{$value}%")
                        ->orWhere('no_registrasi', 'like', "%{$value}%");
                });
            } elseif ($key === 'start_date') {
                $statsQuery->whereDate('tgl_pengajuan', '>=', $value);
            } elseif ($key === 'end_date') {
                $statsQuery->whereDate('tgl_pengajuan', '<=', $value);
            } else {
                $statsQuery->where($key, $value);
            }
        }

        if ($request->filled('manager_id')) {
            $statsQuery->where('manager_id', $request->manager_id);
        }

        if ($request->filled('unit_id')) {
            $statsQuery->where('unit_id', $request->unit_id);
        }

        $stats = [
            'total'   => (clone $statsQuery)->count(),
            'Review'  => (clone $statsQuery)->where('ket_process', 'Review')->count(),
            'Process' => (clone $statsQuery)->where('ket_process', 'Process')->count(),
            'Done'    => (clone $statsQuery)->where('ket_process', 'Done')->count(),
            'Pending'       => (clone $statsQuery)->where('ket_process', 'Pending')->count(),
            'Void'          => (clone $statsQuery)->where('ket_process', 'Void')->count(),
            'ba_pending'    => (clone $statsQuery)->where('ba', 'Pending')->count(),
            'ba_process'    => (clone $statsQuery)->where('ba', 'Process')->count(),
            'ba_done'       => (clone $statsQuery)->where('ba', 'Done')->count(),
            'arsip_pending' => (clone $statsQuery)->where('arsip', 'Pending')->count(),
            'arsip_process' => (clone $statsQuery)->where('arsip', 'Process')->count(),
            'arsip_done'    => (clone $statsQuery)->where('arsip', 'Done')->count(),
        ];

        return view('admin.arsip.index', [
            'arsips'      => $arsips,
            'units'       => Unit::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'managers'    => Manager::where('is_active', true)->orderBy('name')->get(),
            'sort'        => $sort,
            'dir'         => $dir,
            'stats'       => $stats
        ]);
    }

    // ========================================================================
    // FUNGSI INI YANG DIPANGGIL AJAX SAAT TOMBOL EDIT DIKLIK
    // ========================================================================
    public function edit($id)
    {
        // 1. Ambil data arsip beserta semua item detailnya
        $arsip = Arsip::with([
            'adjustItems', 
            'mutasiItems', 
            'bundelItems',
            'department',
            'unit', 
            'manager'   
        ])->findOrFail($id);

        // 2. Kembalikan JSON agar bisa dibaca JavaScript
        return response()->json([
            'status' => 'success',
            'data'   => $arsip
        ]);
    }

    public function store(Request $request)
    {
        // ... (LOGIKA STORE ANDA SUDAH BAGUS, SAYA PERTAHANKAN) ...
        // ... (Saya ringkas disini agar fokus ke perbaikan Edit/Update) ...

        $validator = Validator::make($request->all(), [
            'jenis_pengajuan' => 'required',
            'department_id'   => 'required',
            'unit_id'         => 'required',
            'manager_id'      => 'required',
            'tgl_pengajuan'   => 'nullable|date',
            'bukti_scan'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Mohon lengkapi formulir.');
        }

        DB::beginTransaction();
        try {
            // A. UPLOAD FILE
            $filename = null;
            if ($request->hasFile('bukti_scan')) {
                $file = $request->file('bukti_scan');
                $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $filename = time() . '_' . $cleanName;
                $file->storeAs('bukti_scan', $filename, 'public');
            }

            // B. GENERATE NO REGISTRASI (Gunakan Logic Central di Model)
            $noRegistrasiFix = Arsip::generateNoRegistrasi($request);

            // C. HITUNG TOTAL
            $totalIn  = 0;
            $totalOut = 0;

            if ($request->has('mutasi_asal')) $totalOut += collect($request->mutasi_asal)->sum('qty');
            if ($request->has('mutasi_tujuan')) $totalIn += collect($request->mutasi_tujuan)->sum('qty');
            if ($request->has('bundel')) $totalOut += collect($request->bundel)->sum('qty');
            if ($request->has('adjust')) {
                $totalIn  += collect($request->adjust)->sum('qty_in');
                $totalOut += collect($request->adjust)->sum('qty_out');
            }

            // D. SIMPAN HEADER
            $arsip = Arsip::create([
                'tgl_pengajuan'   => $request->tgl_pengajuan ? \Illuminate\Support\Carbon::parse($request->tgl_pengajuan)->setTimeFrom(now()) : now(),
                'admin_id'        => auth()->id(),
                'department_id'   => $request->department_id,
                'unit_id'         => $request->unit_id,
                'manager_id'      => $request->manager_id,
                'no_registrasi'   => $noRegistrasiFix,
                'no_transaksi'    => $request->no_transaksi,
                'jenis_pengajuan' => $request->jenis_pengajuan,
                'kategori'        => $request->kategori ?? 'None',
                'pemohon'         => $request->pemohon,
                'keterangan'      => $request->keterangan,
                'detail_barang'   => $request->only(['mutasi_asal', 'mutasi_tujuan', 'bundel', 'adjust']),
                'total_qty_in'    => $totalIn,
                'total_qty_out'   => $totalOut,
                'bukti_scan'      => $filename,
                'status'          => 'Check',
                'ket_process'     => 'Review',
                'ba'              => 'Pending',
                'arsip'           => 'Pending',
            ]);

            // E. SIMPAN DETAIL (Gunakan Helper Lokal)
            $this->saveDetailItems($arsip, $request->all());

            // F. NOTIFIKASI KE SUPERADMIN
            // Cari salah satu user superadmin untuk mengisi user_id (karena constraint NOT NULL)
            $superadmin = \App\Models\User::where('role', 'superadmin')->first();
            $targetId = $superadmin ? $superadmin->id : 1; // Fallback ID 1 jika tidak ada

            Notification::create([
                'user_id'     => $targetId, 
                'role_target' => 'superadmin',
                'arsip_id'    => $arsip->id,
                'title'       => 'Pengajuan Baru',
                'message'     => 'Pengajuan No: ' . $noRegistrasiFix . ' (' . $request->jenis_pengajuan . ') baru dibuat oleh ' . auth()->user()->name,
                'is_read'     => false,
            ]);

            DB::commit();
            return redirect()->route('admin.arsip.index')->with('success', 'Berhasil! No Reg: ' . $noRegistrasiFix);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ========================================================================
    // PERBAIKAN LOGIKA UPDATE (EDIT DATA)
    // ========================================================================
    public function update(Request $request, $id)
    {
        $arsip = Arsip::findOrFail($id);

        if (in_array($arsip->status, ['Done', 'Reject', 'Void']) || in_array($arsip->ket_process, ['Done', 'Void'])) {
             if($request->ajax()) return response()->json(['message' => 'Data sudah selesai (Done) dan tidak bisa diubah'], 403);
             return back()->with('error', 'Data sudah selesai diproses.');
        }

        DB::beginTransaction();
        try {
            // 1. Update File Jika Ada
            if ($request->hasFile('bukti_scan')) {
                // Hapus file lama
                if ($arsip->bukti_scan) {
                    Storage::delete('public/bukti_scan/' . $arsip->bukti_scan);
                }
                // Upload baru
                $file = $request->file('bukti_scan');
                $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $filename = time() . '_' . $cleanName;
                $file->storeAs('bukti_scan', $filename, 'public');
                
                $arsip->bukti_scan = $filename;
            }

            // 2. Ambil Data Items dari Form Edit (Gunakan key 'detail_barang' agar match dengan JS)
            $dataToProcess = $request->input('detail_barang', []); 
            
            // 3. Hitung Ulang Total Qty
            $totalIn  = 0;
            $totalOut = 0;

            if (!empty($dataToProcess['mutasi_asal'])) $totalOut += collect($dataToProcess['mutasi_asal'])->sum('qty');
            if (!empty($dataToProcess['mutasi_tujuan'])) $totalIn += collect($dataToProcess['mutasi_tujuan'])->sum('qty');
            if (!empty($dataToProcess['bundel'])) $totalOut += collect($dataToProcess['bundel'])->sum('qty');
            if (!empty($dataToProcess['adjust'])) {
                $totalIn  += collect($dataToProcess['adjust'])->sum('qty_in');
                $totalOut += collect($dataToProcess['adjust'])->sum('qty_out');
            }

            // 4. Update Header Arsip
            $arsip->update([
                'department_id'   => $request->department_id,
                'manager_id'      => $request->manager_id,
                'unit_id'         => $request->unit_id,
                'jenis_pengajuan' => $request->jenis_pengajuan, // Update jenis juga jika berubah
                'no_transaksi'    => $request->no_transaksi,
                'kategori'        => $request->kategori,
                'pemohon'         => $request->pemohon,
                'keterangan'      => $request->keterangan,
                'total_qty_in'    => $totalIn,
                'total_qty_out'   => $totalOut,
                // Update JSON backup juga
                'detail_barang'   => $dataToProcess,
            ]);

            // 5. REFRESH DETAIL ITEM (Hapus Lama, Buat Baru)
            // Ini cara paling aman untuk edit data "One-to-Many" agar sinkron
            ArsipMutasiItem::where('arsip_id', $arsip->id)->delete();
            ArsipAdjustItem::where('arsip_id', $arsip->id)->delete();
            ArsipBundelItem::where('arsip_id', $arsip->id)->delete();

            // Simpan Item Baru
            $this->saveDetailItems($arsip, $dataToProcess);

            DB::commit();

            if($request->ajax()) {
                return response()->json(['status' => 'success', 'message' => 'Data berhasil diupdate']);
            }
            return back()->with('success', 'Data pengajuan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            if($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal Update: ' . $e->getMessage());
        }
    }

    // ========================================================================
    // HELPER FUNCTION: AGAR KODINGAN TIDAK DUPLIKAT DI STORE & UPDATE
    // ========================================================================
    private function saveDetailItems($arsip, $data)
    {
        // Helper kecil untuk cari produk (Optimasi query bisa dilakukan disini jika perlu)
        $getProductData = function($idOrCode, $name) {
            // Jika punya ID, cari by ID. Jika tidak, coba cari by Code/Name atau biarkan null
            // Disini kita sederhanakan: Return null, nanti ambil string text input user
            return null; 
            // Catatan: Jika Anda ingin link ke tabel master product, logicnya di sini.
            // Saat ini saya biarkan menyimpan text input user agar aman.
        };

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

    // ========================================================================
    // PRINT DRAFT
    // ========================================================================
    public function printDraft($id)
    {
        $arsip = Arsip::with(['department', 'unit', 'admin'])->findOrFail($id);
        return view('print.arsip_draft', compact('arsip'));
    }
}