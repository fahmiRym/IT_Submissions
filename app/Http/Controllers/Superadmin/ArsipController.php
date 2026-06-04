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
use App\Models\ArsipProdukBaruItem; // Tambahan
use App\Models\ArsipTindakanItem; // Tambahan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Tambahan untuk Transaction
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class ArsipController extends Controller
{
    use \App\Traits\SignsArsip;
    use \App\Traits\HandlesApproval;

    /**
     * ===============================
     * INDEX – LIST + FILTER + SEARCH
     * ===============================
     */
    public function index(Request $request)
    {
        $query = Arsip::with(['admin', 'department', 'manager', 'unit', 'adjustItems', 'mutasiItems', 'bundelItems', 'produkBaruItems']);

        /* ================= FILTER LOGIC ================= */
        // Use a helper array to store filters that should apply to stats too
        $filters = [
            'q' => $request->q,
            'department_id' => $request->department_id,
            'manager_id' => $request->manager_id,
            'unit_id' => $request->unit_id,
            'kategori' => $request->kategori,
            'jenis_pengajuan' => $request->jenis_pengajuan ?? $request->jenis,
            'admin_id' => $request->admin_id ?? $request->user_id,
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
            if ($request->ba === 'Outstanding') {
                $query->whereIn('ba', ['Pending', 'Process']);
            } else {
                $query->where('ba', $request->ba);
            }
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
            if (empty($value))
                continue;

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
            'total' => (clone $statsQuery)->count(),
            'Review' => (clone $statsQuery)->where('ket_process', 'Review')->count(),
            'Process' => (clone $statsQuery)->where('ket_process', 'Process')->count(),
            'Done' => (clone $statsQuery)->where('ket_process', 'Done')->count(),
            'Pending' => (clone $statsQuery)->where('ket_process', 'Pending')->count(),
            'Void' => (clone $statsQuery)->where('ket_process', 'Void')->count(),
            'ba_pending' => (clone $statsQuery)->where('ba', 'Pending')->count(),
            'ba_process' => (clone $statsQuery)->where('ba', 'Process')->count(),
            'ba_outstanding' => (clone $statsQuery)->whereIn('ba', ['Pending', 'Process'])->count(),
            'ba_done' => (clone $statsQuery)->where('ba', 'Done')->count(),
            'arsip_pending' => (clone $statsQuery)->where('arsip', 'Pending')->count(),
            'arsip_process' => (clone $statsQuery)->where('arsip', 'Process')->count(),
            'arsip_done' => (clone $statsQuery)->where('arsip', 'Done')->count(),
        ];

        return view('superadmin.arsip.index', [
            'arsips' => $arsips,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'managers' => Manager::where('is_active', true)->orderBy('name')->get(),
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
            'users' => User::where('role', 'admin')->where('is_active', true)->orderBy('name')->get(),
            'superadmins' => User::where('role', 'superadmin')->where('is_active', true)->orderBy('name')->get(),
            'approverUsers' => User::where('is_active', true)->orderBy('jabatan')->orderBy('name')->get(['id', 'name', 'jabatan', 'role']),
            'sort' => $sort,
            'dir' => $dir,
            'stats' => $stats
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
            'tgl_pengajuan' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'manager_id' => 'required|exists:managers,id',
            'unit_id' => 'required|exists:units,id',
            'jenis_pengajuan' => 'required|string|max:30',
            'status' => 'required|in:Check,Process,Done,Reject,Void',
            'bukti_scan' => 'nullable|file|mimes:pdf|max:5120',
            'tindakan' => 'nullable|string',
            'catatan_it' => 'nullable|string',
            'tindakan_it_rows' => 'nullable|array',
            'tindakan_it_rows.*.tindakan_in' => 'nullable|string',
            'tindakan_it_rows.*.ket_tindakan_in' => 'nullable|string',
            'tindakan_it_rows.*.tindakan_out' => 'nullable|string',
            'tindakan_it_rows.*.ket_tindakan_out' => 'nullable|string',
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
            $calculateQty = function ($items, $type) {
                if (!is_array($items))
                    return 0;
                return collect($items)->sum(function ($item) use ($type) {
                    if ($type === 'in')
                        return $item['qty_in'] ?? ($item['qty'] ?? 0);
                    if ($type === 'out')
                        return $item['qty_out'] ?? ($item['qty'] ?? 0);
                    return 0;
                });
            };

            $rawItems = $request->input('detail_barang', []);

            if ($request->jenis_pengajuan == 'Adjust') {
                $totalIn = collect($rawItems['adjust'] ?? [])->sum('qty_in');
                $totalOut = collect($rawItems['adjust'] ?? [])->sum('qty_out');
            } elseif (str_contains($request->jenis_pengajuan, 'Mutasi')) {
                // Mutasi Asal = Out, Mutasi Tujuan = In
                $totalOut = collect($rawItems['mutasi_asal'] ?? [])->sum('qty');
                $totalIn = collect($rawItems['mutasi_tujuan'] ?? [])->sum('qty');
            } elseif ($request->jenis_pengajuan == 'Bundel') {
                $totalIn = collect($rawItems['bundel'] ?? [])->sum('qty');
            }

            $arsip = Arsip::create([
                'no_registrasi' => Arsip::generateNoRegistrasi($request),
                'tgl_pengajuan' => $request->tgl_pengajuan ? \Carbon\Carbon::parse($request->tgl_pengajuan)->setTimeFrom(now()) : now(),
                'tgl_arsip' => $request->tgl_arsip,
                'admin_id' => $request->user_id,
                'superadmin_id' => auth()->id(),
                'department_id' => $request->department_id,
                'manager_id' => $request->manager_id,
                'unit_id' => $request->unit_id,
                'kategori' => $request->kategori ?: 'None', // Handle empty string
                'pemohon' => $request->pemohon,
                'keterangan' => $request->keterangan,
                'tindakan' => $request->tindakan,
                'catatan_it' => $request->catatan_it,
                'tindakan_in' => null,
                'ket_tindakan_in' => null,
                'tindakan_out' => null,
                'ket_tindakan_out' => null,
                'no_doc' => $request->no_doc,
                'no_transaksi' => $request->no_transaksi,
                'jenis_pengajuan' => $request->jenis_pengajuan,
                'target_qty' => $request->target_qty,
                'catatan_tambahan' => $request->catatan_tambahan,
                'sub_jenis' => $request->sub_jenis,
                'detail_barang' => $rawItems, // Backup JSON
                'total_qty_in' => $totalIn,
                'total_qty_out' => $totalOut,
                'status' => $request->status,
                'bukti_scan' => $filename,
                'ba' => $request->ba ?? ($request->status == 'Done' ? 'Done' : 'Process'),
                'arsip' => $request->arsip ?? ($request->status == 'Done' ? 'Done' : 'Pending'),
                'ket_process' => $request->ket_process ?? 'Review',
            ]);

            // SIMPAN DETIL KE TABLE RELASI
            $this->saveDetailItems($arsip, $rawItems);
            $this->syncProdukBaruItems($arsip, $rawItems['produk_baru'] ?? []);
            $this->syncTindakanItems($arsip, $request);

            // BANGUN RANTAI APPROVAL BERTINGKAT (approver dipilih per pengajuan)
            $pengaju = \App\Models\User::find($arsip->admin_id);
            if ($pengaju) {
                $this->initApprovalChain($arsip, (array) $request->input('approvers', []), $pengaju);
            }

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
        try {
            $arsip = Arsip::with([
                'admin',
                'department',
                'manager',
                'unit',
                'adjustItems',
                'mutasiItems',
                'bundelItems',
                'produkBaruItems',
                'tindakanItems',
                'approvals.approver',
                'editor'
            ])->findOrFail($id);

            $tindakanRows = $arsip->tindakanItems
                ? $arsip->tindakanItems->map(fn($row) => [
                    'tindakan_in' => $row->tindakan_in,
                    'ket_tindakan_in' => $row->ket_tindakan_in,
                    'tindakan_out' => $row->tindakan_out,
                    'ket_tindakan_out' => $row->ket_tindakan_out,
                ])->values()->all()
                : [];

            return response()->json([
                'status' => 'success',
                'data' => array_merge(
                    $arsip->toArray(),
                    [
                        'tindakan_it_rows' => $tindakanRows,
                        'approval_started' => $arsip->approvalStarted(),
                        'approval_map' => $arsip->approvals->whereNotNull('approver_id')
                            ->pluck('approver_id', 'role_label'),
                    ]
                )
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data arsip: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * ===============================
     * DETAIL PRODUK BARU (barcode, tgl dibuat, last modify, change log)
     * ===============================
     */
    public function produkDetail($id)
    {
        try {
            $arsip = Arsip::with(['admin', 'department', 'unit', 'editor', 'produkBaruItems.editor'])
                ->findOrFail($id);

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
                    'editor'        => $arsip->editor->name ?? null,
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
                    'editor'          => $it->editor->name ?? null,
                ]),
                'logs'   => $logs,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ===============================
     * ARSIP SISTEM (LOGIC FIX)
     * ===============================
     */
    public function arsipSistem(Request $request, $id)
    {
        try {
            // Jalankan logika arsip sistem terpusat di Model
            $arsip = Arsip::processArchiving($id, $request->sequence_number);

            if ($request->wantsJson() || $request->ajax()) {
                $finalNoDoc = $arsip->no_doc;
                $noRegistrasiFix = $arsip->no_registrasi;
                $isCancel = in_array(trim($arsip->jenis_pengajuan), ['Cancel', 'Cancelled']);

                $noTransaksiSub = '';
                $copyAllText = '';

                if ($isCancel) {
                    // Filter Sub Transaksi (buang baris yang diawali MO atau PO)
                    $indukPrefixes = ['MO', 'PO', 'SOF', 'LL'];
                    $allLines = preg_split('/\r\n|\n|\r/', $arsip->no_transaksi ?? '');
                    $subLines = array_filter($allLines, function ($line) use ($indukPrefixes) {
                        $trimmed = trim($line);
                        if ($trimmed === '') return false;
                        foreach ($indukPrefixes as $prefix) {
                            if (str_starts_with($trimmed, $prefix)) return false;
                        }
                        return true;
                    });
                    $noTransaksiSub = implode("\n", array_values($subLines));

                    $copyAllText = $finalNoDoc;
                    if ($noTransaksiSub !== '') {
                        $copyAllText .= "\n" . $noTransaksiSub;
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'no_doc' => $finalNoDoc,
                    'no_registrasi' => $noRegistrasiFix,
                    'jenis_pengajuan' => $arsip->jenis_pengajuan,
                    'no_transaksi_sub' => $noTransaksiSub,
                    'copy_all_text' => $copyAllText,
                    'message' => "Berhasil diarsipkan!",
                ]);
            }

            return redirect()
                ->route('superadmin.arsip.index')
                ->with('success', "Berhasil Arsip! No Doc: {$arsip->no_doc}");

        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
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
            'end_date' => 'required|date|after_or_equal:start_date',
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
            'status' => 'required|in:Check,Process,Pending,Done,Reject,Void',
            'ket_process' => 'nullable|in:Review,Process,Done,Pending,Void,Partial Done',
            'ba' => 'nullable',
            'arsip' => 'nullable',
            'bukti_scan' => 'nullable|file|mimes:pdf|max:5120',
            'scan_final' => 'nullable|file|mimes:pdf|max:10240',
            'tindakan' => 'nullable|string',
            'catatan_it' => 'nullable|string',
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

            // ✅ SCAN FINAL (Tim IT / Superadmin)
            $scanFinalName = $arsip->scan_final;
            if ($request->hasFile('scan_final')) {
                if ($scanFinalName) {
                    Storage::disk('public')->delete('bukti_scan/' . $scanFinalName);
                }
                $fFinal = $request->file('scan_final');
                $cleanFinal = preg_replace('/[^A-Za-z0-9\.\_\-]/', '_', $fFinal->getClientOriginalName());
                $scanFinalName = 'FINAL_' . ($arsip->no_registrasi ?: 'NR') . '_' . time() . '_' . $cleanFinal;
                $scanFinalName = preg_replace('/[^A-Za-z0-9\.\_\-]/', '_', $scanFinalName);
                $fFinal->storeAs('bukti_scan', $scanFinalName, ['disk' => 'public', 'visibility' => 'public']);
            }

            // MAP STATUS UTAMA
            $map = [
                'Check' => ['ba' => 'Done', 'arsip' => 'Pending', 'ket_process' => 'Review'],
                'Process' => ['ba' => 'Done', 'arsip' => 'Pending', 'ket_process' => 'Process'],
                'Pending' => ['ket_process' => 'Pending'],
                'Done' => ['ba' => 'Done', 'arsip' => 'Done', 'ket_process' => 'Done'],
                'Reject' => ['ba' => 'Void', 'arsip' => 'None', 'ket_process' => 'Void'],
                'Void' => ['ba' => 'Void', 'arsip' => 'None', 'ket_process' => 'Void'],
            ];

            // 1. Ambil Data Detail dari Request
            $rawItems = $request->detail_barang ?: [];

            // 2. Hitung Ulang Total Qty (Mutasi, Adjust, Bundel)
            $totalIn = 0;
            $totalOut = 0;

            if (!empty($rawItems['mutasi_asal']))
                $totalOut += collect($rawItems['mutasi_asal'])->sum('qty');
            if (!empty($rawItems['mutasi_tujuan']))
                $totalIn += collect($rawItems['mutasi_tujuan'])->sum('qty');
            if (!empty($rawItems['bundel']))
                $totalOut += collect($rawItems['bundel'])->sum('qty');
            if (!empty($rawItems['adjust'])) {
                $totalIn += collect($rawItems['adjust'])->sum('qty_in');
                $totalOut += collect($rawItems['adjust'])->sum('qty_out');
            }

            // 3. Siapkan Data Update & Automation
            $updateData = [
                'admin_id' => $request->user_id,
                'no_registrasi' => $request->no_registrasi ?? $arsip->no_registrasi,
                'tgl_pengajuan' => $request->tgl_pengajuan ? \Carbon\Carbon::parse($request->tgl_pengajuan)->setTimeFrom(now()) : $arsip->tgl_pengajuan,
                'tgl_arsip' => $request->tgl_arsip,
                'department_id' => $request->department_id,
                'manager_id' => $request->manager_id,
                'unit_id' => $request->unit_id,
                'no_doc' => $request->no_doc,
                'no_transaksi' => $request->no_transaksi,
                'kategori' => $request->kategori ?? 'None',
                'jenis_pengajuan' => $request->jenis_pengajuan ?? $arsip->jenis_pengajuan,
                'pemohon' => $request->pemohon,
                'target_qty' => $request->target_qty ?? $arsip->target_qty,
                'keterangan' => $request->keterangan ?? $arsip->keterangan,
                'tindakan' => $request->tindakan,
                'catatan_it' => $request->catatan_it,
                'tindakan_in' => $request->tindakan_in,
                'ket_tindakan_in' => $request->ket_tindakan_in,
                'tindakan_out' => $request->tindakan_out,
                'ket_tindakan_out' => $request->ket_tindakan_out,
                'sub_jenis' => $request->sub_jenis ?? $arsip->sub_jenis,
                'status' => $request->status,
                'bukti_scan' => $filename,
                'scan_final' => $scanFinalName,
                'detail_barang' => $rawItems,
                'total_qty_in' => $totalIn,
                'total_qty_out' => $totalOut,
                'updated_by' => auth()->id(),
            ];

            // Masukkan Automasi Status (Default)
            if (isset($map[$request->status])) {
                $updateData = array_merge($updateData, $map[$request->status]);
            }

            // IJINKAN OVERRIDE MANUAL (Jika form diisi secara spesifik & tidak kosong)
            if ($request->filled('ba'))
                $updateData['ba'] = $request->ba;
            if ($request->filled('arsip'))
                $updateData['arsip'] = $request->arsip;
            if ($request->filled('ket_process'))
                $updateData['ket_process'] = $request->ket_process;

            // JIKA STATUS DI EDIT MENJADI DONE, TAPI NO DOC KOSONG -> BERI PERINGATAN ATAU TETAPKAN
            // Catatan: Sebaiknya No Doc hanya digenerate lewat tombol Arsip Sistem agar terkontrol.

            $arsip->update($updateData);

            // 4. Sinkronisasi Tabel Relasi (Hapus Lama, Buat Baru)
            // Produk Baru TIDAK di-delete: di-upsert agar barcode & tanggal dibuat tetap stabil.
            ArsipMutasiItem::where('arsip_id', $arsip->id)->delete();
            ArsipAdjustItem::where('arsip_id', $arsip->id)->delete();
            ArsipBundelItem::where('arsip_id', $arsip->id)->delete();
            ArsipTindakanItem::where('arsip_id', $arsip->id)->delete();

            $this->saveDetailItems($arsip, $rawItems);
            $this->syncProdukBaruItems($arsip, $rawItems['produk_baru'] ?? []);
            $this->syncTindakanItems($arsip, $request);

            // Bangun ulang rantai approval HANYA jika belum berjalan
            $arsip->load('approvals');
            if (!$arsip->approvalStarted() && $request->has('approvers')) {
                $pengaju = \App\Models\User::find($arsip->admin_id) ?? auth()->user();
                $this->initApprovalChain($arsip, (array) $request->input('approvers', []), $pengaju);
            }

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
            'user_id' => $arsip->admin_id,
            'arsip_id' => $arsip->id,
            'title' => $title,
            'message' => $message,
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
    private function syncTindakanItems(Arsip $arsip, Request $request)
    {
        $rows = $request->input('tindakan_it_rows', []);

        if (!is_array($rows)) {
            $rows = [];
        }

        if (empty($rows)) {
            return;
        }

        // Catatan: jika tabel belum ada (belum migrate), request yang membuka modal edit
        // akan tetap gagal di bagian `edit()` karena eager-load `tindakanItems`.
        // Namun penyimpanan tetap dijaga agar tidak bikin error saat empty rows.

        // Gunakan counter berurutan untuk sort_order. Key array ($i) berasal dari
        // Date.now() di sisi JS (mis. 1779964800462) yang melebihi jangkauan INT.
        $order = 0;
        foreach ($rows as $row) {
            if (!is_array($row)) continue;

            $tIn = trim((string)($row['tindakan_in'] ?? ''));
            $kIn = trim((string)($row['ket_tindakan_in'] ?? ''));
            $tOut = trim((string)($row['tindakan_out'] ?? ''));
            $kOut = trim((string)($row['ket_tindakan_out'] ?? ''));

            if ($tIn === '' && $kIn === '' && $tOut === '' && $kOut === '') {
                continue;
            }

            ArsipTindakanItem::create([
                'arsip_id' => $arsip->id,
                'tindakan_in' => $tIn !== '' ? $tIn : null,
                'ket_tindakan_in' => $kIn !== '' ? $kIn : null,
                'tindakan_out' => $tOut !== '' ? $tOut : null,
                'ket_tindakan_out' => $kOut !== '' ? $kOut : null,
                'sort_order' => $order++,
            ]);
        }
    }


    private function saveDetailItems($arsip, $data)
    {

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
                    // Draft Adjust extra fields (sudah ditambahkan via migration)
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
     * update row yang berubah, buat row baru, hapus row yang dihapus dari form.
     */
    private function syncProdukBaruItems(Arsip $arsip, $items)
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

    /**
     * ===============================
     * PRINT DRAFT
     * ===============================
     */
    public function printDraft($id)
    {
        $arsip = Arsip::with(['department', 'unit', 'admin', 'manager', 'bundelItems', 'adjustItems', 'produkBaruItems', 'signatures.user'])->findOrFail($id);
        $arsip->ensureVerifyToken();

        // Produk Baru tidak punya draft dokumen — hanya form yang diproses superadmin.
        if ($arsip->jenis_pengajuan === 'Produk_Baru') {
            return redirect()->route('superadmin.arsip.index', ['jenis' => 'Produk_Baru'])
                ->with('error', 'Pengajuan Produk Baru tidak memiliki draft dokumen.');
        }

        if ($arsip->jenis_pengajuan === 'Bundel') {
            return view('print.arsip_draft_bundel', compact('arsip'));
        }

        return view('print.arsip_draft', compact('arsip'));
    }
}