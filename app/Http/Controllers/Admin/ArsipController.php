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
use App\Models\ArsipProdukBaruItem;
use App\Models\Unit;
use App\Models\Department;
use App\Models\Manager;
use App\Models\Product;
use App\Models\Notification;

class ArsipController extends Controller
{
    use \App\Traits\SignsArsip;
    use \App\Traits\HandlesApproval;

    public function index(Request $request)
    {
        $query = Arsip::with(['department', 'manager', 'unit', 'adjustItems', 'mutasiItems', 'bundelItems', 'produkBaruItems', 'approvals']);

        // Jika Accounting, izinkan data milik sendiri ATAU semua data Adjustment.
        // Jika bukan Accounting dan bukan Superadmin, batasi hanya data milik sendiri.
        if (auth()->user()->role === 'accounting') {
            $query->where(function ($q) {
                $q->where('admin_id', auth()->id())
                  ->orWhere('jenis_pengajuan', 'Adjust');
            });
        } elseif (auth()->user()->role !== 'superadmin') {
            $query->where('admin_id', auth()->id());
        }

        /* ================= FILTER LOGIC ================= */
        $filters = [
            'q' => $request->q,
            'department_id' => $request->department_id,
            'kategori' => $request->kategori,
            'jenis_pengajuan' => $request->jenis_pengajuan ?? $request->jenis,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ];

        foreach ($filters as $key => $value) {
            if (empty($value))
                continue;

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
        $statsQuery = Arsip::query();
        if (auth()->user()->role === 'accounting') {
            $statsQuery->where(function ($q) {
                $q->where('admin_id', auth()->id())
                  ->orWhere('jenis_pengajuan', 'Adjust');
            });
        } elseif (auth()->user()->role !== 'superadmin') {
            $statsQuery->where('admin_id', auth()->id());
        }
        foreach ($filters as $key => $value) {
            if (empty($value))
                continue;

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
            'total' => (clone $statsQuery)->count(),
            'Review' => (clone $statsQuery)->where('ket_process', 'Review')->count(),
            'Process' => (clone $statsQuery)->where('ket_process', 'Process')->count(),
            'Done' => (clone $statsQuery)->where('ket_process', 'Done')->count(),
            'Pending' => (clone $statsQuery)->where('ket_process', 'Pending')->count(),
            'Void' => (clone $statsQuery)->where('ket_process', 'Void')->count(),
            'ba_pending' => (clone $statsQuery)->where('ba', 'Pending')->count(),
            'ba_process' => (clone $statsQuery)->where('ba', 'Process')->count(),
            'ba_done' => (clone $statsQuery)->where('ba', 'Done')->count(),
            'arsip_pending' => (clone $statsQuery)->where('arsip', 'Pending')->count(),
            'arsip_process' => (clone $statsQuery)->where('arsip', 'Process')->count(),
            'arsip_done' => (clone $statsQuery)->where('arsip', 'Done')->count(),
        ];

        return view('admin.arsip.index', [
            'arsips' => $arsips,
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'managers' => Manager::where('is_active', true)->orderBy('name')->get(),
            'approverUsers' => \App\Models\User::where('is_active', true)
                ->where('id', '!=', auth()->id())
                ->orderBy('jabatan')->orderBy('name')->get(['id', 'name', 'jabatan', 'role']),
            'sort' => $sort,
            'dir' => $dir,
            'stats' => $stats
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
            'produkBaruItems',
            'approvals.approver',
            'department',
            'unit',
            'manager'
        ])->findOrFail($id);

        // Security check for non-superadmins
        if (auth()->user()->role !== 'superadmin') {
            if (auth()->user()->role === 'accounting') {
                if ($arsip->admin_id !== auth()->id() && $arsip->jenis_pengajuan !== 'Adjust') {
                    abort(403, 'Unauthorized action.');
                }
            } else {
                if ($arsip->admin_id !== auth()->id()) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        // 2. Kembalikan JSON agar bisa dibaca JavaScript
        return response()->json([
            'status' => 'success',
            'data' => array_merge($arsip->toArray(), [
                'approval_started' => $arsip->approvalStarted(),
                'approval_map' => $arsip->approvals->whereNotNull('approver_id')
                    ->pluck('approver_id', 'role_label'),
            ])
        ]);
    }

    public function store(Request $request)
    {
        // ... (LOGIKA STORE ANDA SUDAH BAGUS, SAYA PERTAHANKAN) ...
        // ... (Saya ringkas disini agar fokus ke perbaikan Edit/Update) ...

        $validator = Validator::make($request->all(), [
            'jenis_pengajuan' => 'required',
            'department_id' => 'required',
            'unit_id' => 'required',
            'manager_id' => 'required',
            'tgl_pengajuan' => 'nullable|date',
            'bukti_scan' => 'nullable|file|mimes:pdf|max:5120',
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
            $totalIn = 0;
            $totalOut = 0;

            if ($request->has('mutasi_asal'))
                $totalOut += collect($request->mutasi_asal)->sum('qty');
            if ($request->has('mutasi_tujuan'))
                $totalIn += collect($request->mutasi_tujuan)->sum('qty');
            if ($request->has('bundel'))
                $totalOut += collect($request->bundel)->sum('qty');
            if ($request->has('adjust')) {
                $totalIn += collect($request->adjust)->sum('qty_in');
                $totalOut += collect($request->adjust)->sum('qty_out');
            }

            // D. SIMPAN HEADER
            $arsip = Arsip::create([
                'tgl_pengajuan' => $request->tgl_pengajuan ? \Illuminate\Support\Carbon::parse($request->tgl_pengajuan)->setTimeFrom(now()) : now(),
                'admin_id' => auth()->id(),
                'department_id' => $request->department_id,
                'unit_id' => $request->unit_id,
                'manager_id' => $request->manager_id,
                'no_registrasi' => $noRegistrasiFix,
                'no_transaksi' => $request->no_transaksi,
                'jenis_pengajuan' => $request->jenis_pengajuan,
                'kategori' => $request->kategori ?? 'None',
                'pemohon' => $request->pemohon,
                'keterangan' => $request->keterangan,
                'detail_barang' => $request->only(['mutasi_asal', 'mutasi_tujuan', 'bundel', 'adjust', 'produk_baru']),
                'total_qty_in' => $totalIn,
                'total_qty_out' => $totalOut,
                'bukti_scan' => $filename,
                'status' => 'Check',
                'ket_process' => 'Review',
                'ba' => 'Pending',
                'arsip' => 'Pending',
            ]);

            // E. SIMPAN DETAIL (Gunakan Helper Lokal)
            $this->saveDetailItems($arsip, $request->all());
            $this->syncProdukBaruItems($arsip, $request->input('produk_baru', []));

            // E2. BANGUN RANTAI APPROVAL BERTINGKAT (approver dipilih per pengajuan)
            $this->initApprovalChain($arsip, (array) $request->input('approvers', []), auth()->user());

            // F. NOTIFIKASI KE SUPERADMIN
            // Cari salah satu user superadmin untuk mengisi user_id (karena constraint NOT NULL)
            $superadmin = \App\Models\User::where('role', 'superadmin')->first();
            $targetId = $superadmin ? $superadmin->id : 1; // Fallback ID 1 jika tidak ada

            Notification::create([
                'user_id' => $targetId,
                'role_target' => 'superadmin',
                'arsip_id' => $arsip->id,
                'title' => 'Pengajuan Baru',
                'message' => 'Pengajuan No: ' . $noRegistrasiFix . ' (' . $request->jenis_pengajuan . ') baru dibuat oleh ' . auth()->user()->name,
                'is_read' => false,
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

        // Security check for non-superadmins
        if (auth()->user()->role !== 'superadmin') {
            if (auth()->user()->role === 'accounting') {
                if ($arsip->admin_id !== auth()->id() && $arsip->jenis_pengajuan !== 'Adjust') {
                    abort(403, 'Unauthorized action.');
                }
            } else {
                if ($arsip->admin_id !== auth()->id()) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        if (in_array($arsip->status, ['Done', 'Reject', 'Void']) || in_array($arsip->ket_process, ['Done', 'Void'])) {
            if ($request->ajax())
                return response()->json(['message' => 'Data sudah selesai (Done) dan tidak bisa diubah'], 403);
            return back()->with('error', 'Data sudah selesai diproses.');
        }

        $request->validate([
            'bukti_scan' => 'nullable|file|mimes:pdf|max:5120',
        ]);

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
            $totalIn = 0;
            $totalOut = 0;

            if (!empty($dataToProcess['mutasi_asal']))
                $totalOut += collect($dataToProcess['mutasi_asal'])->sum('qty');
            if (!empty($dataToProcess['mutasi_tujuan']))
                $totalIn += collect($dataToProcess['mutasi_tujuan'])->sum('qty');
            if (!empty($dataToProcess['bundel']))
                $totalOut += collect($dataToProcess['bundel'])->sum('qty');
            if (!empty($dataToProcess['adjust'])) {
                $totalIn += collect($dataToProcess['adjust'])->sum('qty_in');
                $totalOut += collect($dataToProcess['adjust'])->sum('qty_out');
            }

            // 4. Update Header Arsip
            $arsip->update([
                'department_id' => $request->department_id,
                'manager_id' => $request->manager_id,
                'unit_id' => $request->unit_id,
                'jenis_pengajuan' => $request->jenis_pengajuan, // Update jenis juga jika berubah
                'no_transaksi' => $request->no_transaksi,
                'kategori' => $request->kategori,
                'pemohon' => $request->pemohon,
                'keterangan' => $request->keterangan,
                'total_qty_in' => $totalIn,
                'total_qty_out' => $totalOut,
                // Update JSON backup juga
                'detail_barang' => $dataToProcess,
                'updated_by' => auth()->id(),
            ]);

            // 5. REFRESH DETAIL ITEM (Hapus Lama, Buat Baru)
            // Ini cara paling aman untuk edit data "One-to-Many" agar sinkron
            ArsipMutasiItem::where('arsip_id', $arsip->id)->delete();
            ArsipAdjustItem::where('arsip_id', $arsip->id)->delete();
            ArsipBundelItem::where('arsip_id', $arsip->id)->delete();

            // Simpan Item Baru
            $this->saveDetailItems($arsip, $dataToProcess);
            // Produk Baru: upsert (jaga barcode & tanggal dibuat)
            $this->syncProdukBaruItems($arsip, $dataToProcess['produk_baru'] ?? []);

            // Bangun ulang rantai approval HANYA jika belum berjalan
            $arsip->load('approvals');
            if (!$arsip->approvalStarted() && $request->has('approvers')) {
                $pengaju = \App\Models\User::find($arsip->admin_id) ?? auth()->user();
                $this->initApprovalChain($arsip, (array) $request->input('approvers', []), $pengaju);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['status' => 'success', 'message' => 'Data berhasil diupdate']);
            }
            return back()->with('success', 'Data pengajuan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
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
        $getProductData = function ($idOrCode, $name) {
            // Jika punya ID, cari by ID. Jika tidak, coba cari by Code/Name atau biarkan null
            // Disini kita sederhanakan: Return null, nanti ambil string text input user
            return null;
            // Catatan: Jika Anda ingin link ke tabel master product, logicnya di sini.
            // Saat ini saya biarkan menyimpan text input user agar aman.
        };

        // 1. MUTASI
        $saveMutasi = function ($items, $type) use ($arsip) {
            if (!is_array($items))
                return;
            foreach ($items as $item) {
                if (empty($item['no_doc']) && empty($item['nama_produk']) && empty($item['product_code']))
                    continue;

                ArsipMutasiItem::create([
                    'arsip_id' => $arsip->id,
                    'type' => $type,
                    'product_code' => $item['product_code'] ?? $item['no_doc'] ?? null,
                    'product_name' => $item['nama_produk'] ?? $item['no_doc'] ?? '-',
                    'qty' => $item['qty'] ?? 0,
                    'lot' => $item['lot'] ?? $item['keterangan'] ?? null,
                    'panjang' => $item['panjang'] ?? null,
                    'location' => $item['location'] ?? null,
                ]);
            }
        };

        if (!empty($data['mutasi_asal']))
            $saveMutasi($data['mutasi_asal'], 'asal');
        if (!empty($data['mutasi_tujuan']))
            $saveMutasi($data['mutasi_tujuan'], 'tujuan');

        // 2. BUNDEL
        if (!empty($data['bundel']) && is_array($data['bundel'])) {
            foreach ($data['bundel'] as $item) {
                if (empty($item['no_doc']))
                    continue;
                ArsipBundelItem::create([
                    'arsip_id' => $arsip->id,
                    'no_doc' => $item['no_doc'],
                    'qty' => $item['qty'] ?? 1,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }
        }

        // 3. ADJUST
        if (!empty($data['adjust']) && is_array($data['adjust'])) {
            foreach ($data['adjust'] as $item) {
                // Cek minimal ada nama barang/kode
                $identifier = $item['nama_produk'] ?? $item['no_doc'] ?? $item['product_code'] ?? null;
                if (!$identifier)
                    continue;

                ArsipAdjustItem::create([
                    'arsip_id' => $arsip->id,
                    'product_code' => $item['product_code'] ?? null,
                    'product_name' => $item['nama_produk'] ?? $item['no_doc'] ?? '-',
                    'qty_in' => $item['qty_in'] ?? 0,
                    'qty_out' => $item['qty_out'] ?? 0,
                    'lot' => $item['lot'] ?? $item['keterangan'] ?? null,
                    'location' => $item['location'] ?? null,
                    'odoo' => $item['odoo'] ?? null,
                    'fisik' => $item['fisik'] ?? null,
                    'keterangan_in' => $item['keterangan_in'] ?? null,
                    'keterangan_out' => $item['keterangan_out'] ?? null,
                ]);
            }
        }

        // Produk Baru ditangani terpisah via syncProdukBaruItems() (upsert).
    }

    /**
     * Upsert item Produk Baru: pertahankan barcode & created_at untuk row lama,
     * update yang berubah, buat row baru, hapus row yang dihapus dari form.
     */
    private function syncProdukBaruItems($arsip, $items)
    {
        if (!is_array($items)) {
            $items = [];
        }

        $keepIds = [];

        foreach ($items as $row) {
            if (!is_array($row)) continue;

            $name = trim((string) ($row['nama_produk'] ?? ''));
            $code = trim((string) ($row['product_code'] ?? ''));
            if ($name === '' && $code === '') continue;

            $payload = [
                'product_code'    => $row['product_code'] ?? null,
                'product_name'    => $row['nama_produk'] ?? '-',
                'tipe_produk'     => $row['tipe_produk'] ?? null,
                'kategori'        => $row['kategori'] ?? null,
                'satuan'          => $row['satuan'] ?? null,
                'status_approval' => $row['status_approval'] ?? 'Waiting List',
                'keterangan'      => $row['keterangan'] ?? null,
                'updated_by'      => auth()->id(),
            ];

            $existingId = !empty($row['id']) ? (int) $row['id'] : null;
            $existing = $existingId
                ? ArsipProdukBaruItem::where('arsip_id', $arsip->id)->find($existingId)
                : null;

            if ($existing) {
                $existing->update($payload);
                $keepIds[] = $existing->id;
            } else {
                $new = ArsipProdukBaruItem::create(array_merge($payload, [
                    'arsip_id' => $arsip->id,
                    'barcode'  => !empty($row['barcode']) ? $row['barcode'] : null,
                ]));
                $keepIds[] = $new->id;
            }
        }

        ArsipProdukBaruItem::where('arsip_id', $arsip->id)
            ->whereNotIn('id', $keepIds ?: [0])
            ->delete();
    }

    // ========================================================================
    // DETAIL PRODUK BARU (barcode, tgl dibuat, last modify, change log)
    // ========================================================================
    public function produkDetail($id)
    {
        $arsip = Arsip::with(['admin', 'department', 'unit', 'produkBaruItems'])->findOrFail($id);

        // Security check non-superadmin
        if (auth()->user()->role !== 'superadmin') {
            if (auth()->user()->role === 'accounting') {
                if ($arsip->admin_id !== auth()->id() && $arsip->jenis_pengajuan !== 'Adjust') {
                    abort(403, 'Unauthorized action.');
                }
            } elseif ($arsip->admin_id !== auth()->id()) {
                abort(403, 'Unauthorized action.');
            }
        }

        $logs = \App\Models\AuditLog::with('user')
            ->where('arsip_id', $arsip->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($l) => [
                'action'     => $l->action,
                'user'       => $l->user->name ?? 'System',
                'changes'    => $l->new_values,
                'created_at' => optional($l->created_at)->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'status' => 'success',
            'arsip'  => [
                'no_registrasi' => $arsip->no_registrasi,
                'no_doc'        => $arsip->no_doc,
                'pengaju'       => $arsip->admin->name ?? '-',
                'department'    => $arsip->department->name ?? '-',
                'unit'          => $arsip->unit->name ?? '-',
                'status'        => $arsip->status,
                'ket_process'   => $arsip->ket_process,
                'created_at'    => optional($arsip->created_at)->format('d/m/Y H:i'),
                'updated_at'    => optional($arsip->updated_at)->format('d/m/Y H:i'),
            ],
            'items'  => $arsip->produkBaruItems->map(fn($it) => [
                'product_code'    => $it->product_code,
                'product_name'    => $it->product_name,
                'barcode'         => $it->barcode,
                'tipe_produk'     => $it->tipe_produk,
                'kategori'        => $it->kategori,
                'satuan'          => $it->satuan,
                'status_approval' => $it->status_approval,
                'keterangan'      => $it->keterangan,
                'created_at'      => optional($it->created_at)->format('d/m/Y H:i'),
                'updated_at'      => optional($it->updated_at)->format('d/m/Y H:i'),
            ]),
            'logs'   => $logs,
        ]);
    }

    // ========================================================================
    // PRINT DRAFT
    // ========================================================================
    public function printDraft($id)
    {
        $arsip = Arsip::with(['department', 'unit', 'admin', 'manager', 'bundelItems', 'adjustItems', 'produkBaruItems', 'signatures.user'])->findOrFail($id);

        // Security check for non-superadmins
        if (auth()->user()->role !== 'superadmin') {
            if (auth()->user()->role === 'accounting') {
                if ($arsip->admin_id !== auth()->id() && $arsip->jenis_pengajuan !== 'Adjust') {
                    abort(403, 'Unauthorized action.');
                }
            } else {
                if ($arsip->admin_id !== auth()->id()) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        $arsip->ensureVerifyToken();

        // Produk Baru tidak punya draft dokumen — hanya form yang diproses superadmin.
        if ($arsip->jenis_pengajuan === 'Produk_Baru') {
            return redirect()->route('admin.arsip.index', ['jenis' => 'Produk_Baru'])
                ->with('error', 'Pengajuan Produk Baru tidak memiliki draft dokumen.');
        }

        if ($arsip->jenis_pengajuan === 'Bundel') {
            return view('print.arsip_draft_bundel', compact('arsip'));
        }

        return view('print.arsip_draft', compact('arsip'));
    }

    // ========================================================================
    // RE-UPLOAD SCAN BA (Khusus Accounting setelah Approve Adjustment)
    // ========================================================================
    public function reuploadBaScan(Request $request, $id)
    {
        // Hanya untuk jenis Adjust
        $arsip = Arsip::findOrFail($id);

        // Security check for non-superadmins
        if (auth()->user()->role !== 'superadmin') {
            if (auth()->user()->role === 'accounting') {
                if ($arsip->admin_id !== auth()->id() && $arsip->jenis_pengajuan !== 'Adjust') {
                    abort(403, 'Unauthorized action.');
                }
            } else {
                if ($arsip->admin_id !== auth()->id()) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        if ($arsip->jenis_pengajuan !== 'Adjust') {
            return back()->with('error', 'Fitur re-upload BA hanya tersedia untuk pengajuan Adjustment.');
        }

        // Harus sudah di-approve (ba = Done atau Process atau ket_process = Process/Done)
        if (!in_array($arsip->ket_process, ['Process', 'Done']) && !in_array($arsip->ba, ['Process', 'Done'])) {
            return back()->with('error', 'Berkas BA hanya bisa diupload ulang setelah pengajuan disetujui.');
        }

        $request->validate([
            'scan_ba' => 'required|file|mimes:pdf|max:10240',
        ], [
            'scan_ba.required' => 'File scan BA wajib dipilih.',
            'scan_ba.mimes'    => 'File harus berformat PDF.',
            'scan_ba.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            // Hapus file lama jika ada
            if ($arsip->scan_ba_accounting) {
                Storage::disk('public')->delete('bukti_scan/' . $arsip->scan_ba_accounting);
            }

            // Upload file baru
            $file = $request->file('scan_ba');
            $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $filename = 'BA_' . $arsip->no_registrasi . '_' . time() . '_' . $cleanName;
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            $file->storeAs('bukti_scan', $filename, 'public');

            // Simpan ke kolom scan_ba_accounting
            $arsip->update([
                'scan_ba_accounting' => $filename,
                'updated_by' => auth()->id(),
            ]);

            return back()->with('success', 'Scan BA berhasil diupload. Terima kasih, ' . auth()->user()->name . '!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }
}