<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Arsip;
use App\Models\Department;
use App\Models\Unit;
use App\Models\Manager;
use App\Models\ArsipAdjustItem;
use App\Models\ArsipMutasiItem;
use App\Models\ArsipBundelItem;
use App\Models\ArsipProdukBaruItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ArsipApiController extends Controller
{
    use \App\Traits\SignsArsip;
    use \App\Traits\HandlesApproval;

    /**
     * Relasi standar yang dipakai untuk detail pengajuan.
     */
    private array $detailRelations = [
        'department',
        'unit',
        'manager',
        'admin',
        'superadmin',
        'adjustItems',
        'mutasiItems',
        'bundelItems',
        'produkBaruItems',
        'signatures.delegatedFrom',
        'approvals.approver',
        'approvals.delegatedFrom',
        'lampirans',
        'requesters.user:id,employee_id,name',
    ];

    /**
     * Bangun URL publik untuk sebuah file scan.
     */
    private function fileUrl(?string $filename): ?string
    {
        if (!$filename) {
            return null;
        }
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            return route('pdf.viewer', ['filename' => $filename]);
        }
        return asset('storage/bukti_scan/' . $filename);
    }

    /**
     * Tempelkan URL semua file (bukti scan, BA accounting, scan final) ke model.
     */
    private function appendFileUrls(Arsip $arsip): Arsip
    {
        $arsip->bukti_scan_url = $this->fileUrl($arsip->bukti_scan);
        $arsip->scan_ba_accounting_url = $this->fileUrl($arsip->scan_ba_accounting);
        $arsip->scan_final_url = $this->fileUrl($arsip->scan_final);
        return $arsip;
    }

    /**
     * Normalisasi jenis pengajuan ke format DB (underscore) agar cocok dgn ENUM.
     * Contoh: "Mutasi Billet" -> "Mutasi_Billet", "Produk Baru" -> "Produk_Baru".
     */
    private function normalizeJenis(?string $jenis): string
    {
        return str_replace(' ', '_', trim((string) $jenis));
    }

    /**
     * API untuk Dashboard Utama Android
     */
    public function getDashboard(Request $request)
    {
        $totalPengajuan = Arsip::count();
        $systemDone = Arsip::where('ket_process', 'Done')->count();
        $physicalDone = Arsip::where('arsip', 'Done')->count();

        $ketPending = Arsip::where('ket_process', 'Pending')->count();
        $ketReview = Arsip::where('ket_process', 'Review')->count();
        $ketProcess = Arsip::where('ket_process', 'Process')->count();
        $ketDone = $systemDone;
        $ketVoid = Arsip::where('ket_process', 'Void')->count();
        $baOutstanding = Arsip::whereIn('ba', ['Pending', 'Process'])->count();

        // Statistik Produk Baru
        $produkBaruTotal = Arsip::where('jenis_pengajuan', 'Produk_Baru')->count();
        $produkBaruDone = Arsip::where('jenis_pengajuan', 'Produk_Baru')->where('ket_process', 'Done')->count();
        $produkBaruWaiting = Arsip::where('jenis_pengajuan', 'Produk_Baru')
            ->whereIn('ket_process', ['Review', 'Process', 'Pending'])->count();

        $recentArsips = Arsip::with(['department', 'unit', 'produkBaruItems'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($arsip) => $this->appendFileUrls($arsip));

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
                    'totalPengajuan' => $totalPengajuan,
                    'totalArsip' => $physicalDone,
                    'arsipDone' => $systemDone,
                    'arsipProcess' => Arsip::where('arsip', 'Pending')->count(),
                    'ketPending' => $ketPending,
                    'ketReview' => $ketReview,
                    'ketProcess' => $ketProcess,
                    'ketDone' => $ketDone,
                    'ketVoid' => $ketVoid,
                    'baOutstanding' => $baOutstanding,
                    'produkBaruTotal' => $produkBaruTotal,
                    'produkBaruDone' => $produkBaruDone,
                    'produkBaruWaiting' => $produkBaruWaiting,
                ],
                'recent_pengajuan' => $recentArsips
            ]
        ], 200);
    }

    /**
     * API untuk mendapatkan data master (Department, Unit, Manager, opsi Produk Baru, Lokasi).
     */
    public function getMasterData()
    {
        // `departments` table TIDAK punya kolom `code` (cuma `units` yg punya).
        // Kalau `code` dimasukkan → SQL error → 502 di mobile setelah login.
        $departments = Department::where('is_active', true)->get(['id', 'name']);
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);
        $managers = Manager::where('is_active', true)->get(['id', 'name']);

        // Backward-compat: daftar label
        $jenisPengajuan = [
            'Adjust',
            'Mutasi Billet',
            'Mutasi Produk',
            'Internal Memo',
            'Bundel',
            'Cancel',
            'Produk Baru',
        ];

        // Disarankan dipakai Android: value (DB) + label (UI)
        $jenisPengajuanOptions = [
            ['value' => 'Adjust', 'label' => 'Adjust'],
            ['value' => 'Mutasi_Billet', 'label' => 'Mutasi Billet'],
            ['value' => 'Mutasi_Produk', 'label' => 'Mutasi Produk'],
            ['value' => 'Internal_Memo', 'label' => 'Internal Memo'],
            ['value' => 'Bundel', 'label' => 'Bundel'],
            ['value' => 'Cancel', 'label' => 'Cancel'],
            ['value' => 'Produk_Baru', 'label' => 'Produk Baru'],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data Master Berhasil Diambil',
            'data' => [
                'departments' => $departments,
                'units' => $units,
                'managers' => $managers,
                'jenis_pengajuan' => $jenisPengajuan,
                'jenis_pengajuan_options' => $jenisPengajuanOptions,
                'locations' => ArsipMutasiItem::getLocations(),
                'produk_baru' => [
                    'tipe' => ArsipProdukBaruItem::getTipeOptions(),
                    'kategori' => ArsipProdukBaruItem::getKategoriOptions(),
                    'satuan' => ArsipProdukBaruItem::getSatuanOptions(),
                    'status_approval' => ArsipProdukBaruItem::getStatusApprovalOptions(),
                ],
                // Approval bertingkat: kandidat approver + peran per jenis
                'approver_users' => \App\Models\User::where('is_active', true)
                    ->orderBy('name')->get(['id', 'name', 'jabatan', 'role']),
                'approval_roles' => [
                    'Adjust'      => ['SPV', 'Kabag', 'Manager', 'Accounting'],
                    'Produk_Baru' => [],
                    'default'     => ['SPV', 'Kabag', 'Manager'],
                ],
            ]
        ], 200);
    }

    /**
     * API untuk menyimpan Pengajuan Arsip dari Android
     */
    public function storePengajuan(Request $request)
    {
        $request->validate([
            'jenis_pengajuan' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'unit_id' => 'required|exists:units,id',
            'manager_id' => 'nullable|exists:managers,id',
            'keterangan' => 'nullable|string',
            'no_transaksi' => 'nullable|string',
            'pemohon' => 'nullable|string',
            'detail_barang_json' => 'nullable|string',
            'bukti_scan' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        try {
            $jenisDb = $this->normalizeJenis($request->jenis_pengajuan);

            // GUARD: Produk_Baru dimatikan sementara via Settings
            if ($jenisDb === 'Produk_Baru'
                && \App\Models\Setting::get('produk_baru_enabled', '1') !== '1') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fitur Pengajuan Produk Baru sedang dinonaktifkan sementara. Hubungi Superadmin / Departemen IT.',
                ], 403);
            }

            // No Registrasi (pakai request asli agar unit_id ikut terbaca)
            $noRegistrasi = Arsip::generateNoRegistrasi($request);

            // Upload bukti scan (opsional). Produk Baru tidak butuh dokumen.
            $filename = null;
            if ($request->hasFile('bukti_scan')) {
                $file = $request->file('bukti_scan');
                $extension = $file->getClientOriginalExtension();
                $filename = str_replace([' ', '/', '\\'], '_', $noRegistrasi) . '-' . time() . '.' . $extension;
                $file->storeAs('public/bukti_scan', $filename);
            }

            $detail = json_decode($request->input('detail_barang_json', ''), true);
            if (!is_array($detail)) {
                $detail = null;
            }

            // Hitung total qty in/out
            $totalIn = 0;
            $totalOut = 0;
            if ($detail) {
                if (isset($detail['adjust']) && is_array($detail['adjust'])) {
                    foreach ($detail['adjust'] as $it) {
                        $totalIn += (float) ($it['qty_in'] ?? 0);
                        $totalOut += (float) ($it['qty_out'] ?? 0);
                    }
                }
                if (isset($detail['mutasi']) && is_array($detail['mutasi'])) {
                    foreach (($detail['mutasi']['tujuan'] ?? []) as $it) {
                        $totalIn += (float) ($it['qty'] ?? 0);
                    }
                    foreach (($detail['mutasi']['asal'] ?? []) as $it) {
                        $totalOut += (float) ($it['qty'] ?? 0);
                    }
                }
                if (isset($detail['bundel']) && is_array($detail['bundel'])) {
                    foreach ($detail['bundel'] as $it) {
                        $totalOut += (float) ($it['qty'] ?? 0);
                    }
                }
            }

            $arsip = Arsip::create([
                'no_registrasi' => $noRegistrasi,
                'jenis_pengajuan' => $jenisDb,
                'keterangan' => $request->keterangan,
                'no_transaksi' => $request->no_transaksi,
                'pemohon' => $request->pemohon,
                'detail_barang' => $detail,
                'total_qty_in' => $totalIn,
                'total_qty_out' => $totalOut,
                'department_id' => $request->department_id,
                'unit_id' => $request->unit_id,
                'manager_id' => $request->manager_id,
                'tgl_pengajuan' => now(),
                'status' => 'Check',
                'ba' => 'Pending',
                'arsip' => 'Pending',
                'ket_process' => 'Review',
                'admin_id' => $request->user()->id,
                'bukti_scan' => $filename,
            ]);

            $this->storeDetailItems($arsip, $jenisDb, $detail);

            // Rantai approval bertingkat. Android kirim `approvers` (map role->user_id)
            // atau di dalam detail_barang_json['approvers'].
            $approvers = $request->input('approvers');
            if (!is_array($approvers) && isset($detail['approvers']) && is_array($detail['approvers'])) {
                $approvers = $detail['approvers'];
            }
            $this->initApprovalChain($arsip, (array) ($approvers ?? []), $request->user());

            $superadmin = \App\Models\User::where('role', 'superadmin')->first();
            \App\Models\Notification::create([
                'user_id' => $superadmin ? $superadmin->id : 1,
                'role_target' => 'superadmin',
                'arsip_id' => $arsip->id,
                'title' => 'Pengajuan Baru',
                'message' => 'Pengajuan No: ' . $noRegistrasi . ' (' . $jenisDb . ') baru dibuat oleh ' . $request->user()->name,
                'is_read' => false,
            ]);

            $arsip->load($this->detailRelations);
            $this->appendFileUrls($arsip);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan Arsip Berhasil Disimpan',
                'data' => $arsip
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengajuan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simpan item per-baris pengajuan ke tabel relasi sesuai jenisnya.
     * $jenis diterima dalam format DB (underscore).
     */
    private function storeDetailItems($arsip, string $jenis, $detail): void
    {
        if (!is_array($detail)) {
            return;
        }

        if ($jenis === 'Adjust' && !empty($detail['adjust'])) {
            foreach ($detail['adjust'] as $it) {
                ArsipAdjustItem::create([
                    'arsip_id' => $arsip->id,
                    'product_code' => $it['product_code'] ?? null,
                    'product_name' => $it['product_name'] ?? ($it['nama_produk'] ?? null),
                    'qty_in' => (float) ($it['qty_in'] ?? 0),
                    'qty_out' => (float) ($it['qty_out'] ?? 0),
                    'lot' => $it['lot'] ?? null,
                    'location' => $it['location'] ?? null,
                    'odoo' => $it['odoo'] ?? null,
                    'fisik' => $it['fisik'] ?? null,
                    'keterangan_in' => $it['keterangan_in'] ?? null,
                    'keterangan_out' => $it['keterangan_out'] ?? null,
                ]);
            }
        }

        if (in_array($jenis, ['Mutasi_Billet', 'Mutasi_Produk'], true) && !empty($detail['mutasi'])) {
            foreach (['asal', 'tujuan'] as $type) {
                foreach (($detail['mutasi'][$type] ?? []) as $it) {
                    ArsipMutasiItem::create([
                        'arsip_id' => $arsip->id,
                        'type' => $type,
                        'product_code' => $it['product_code'] ?? null,
                        'product_name' => $it['product_name'] ?? ($it['nama_produk'] ?? null),
                        'qty' => (float) ($it['qty'] ?? 0),
                        'lot' => $it['lot'] ?? null,
                        'panjang' => $it['panjang'] ?? null,
                        'location' => $it['location'] ?? null,
                    ]);
                }
            }
        }

        if ($jenis === 'Bundel' && !empty($detail['bundel'])) {
            foreach ($detail['bundel'] as $it) {
                ArsipBundelItem::create([
                    'arsip_id' => $arsip->id,
                    'no_doc' => $it['no_doc'] ?? null,
                    'qty' => (float) ($it['qty'] ?? 0),
                    'keterangan' => $it['keterangan'] ?? null,
                ]);
            }
        }

        if ($jenis === 'Produk_Baru' && !empty($detail['produk_baru'])) {
            foreach ($detail['produk_baru'] as $it) {
                $name = $it['product_name'] ?? ($it['nama_produk'] ?? null);
                $code = $it['product_code'] ?? null;
                if (!$name && !$code) {
                    continue;
                }
                // barcode otomatis dibuat oleh model (event created).
                ArsipProdukBaruItem::create([
                    'arsip_id' => $arsip->id,
                    'product_code' => $code,
                    'product_name' => $name ?? '-',
                    'tipe_produk' => $it['tipe_produk'] ?? null,
                    'kategori' => $it['kategori'] ?? null,
                    'satuan' => $it['satuan'] ?? null,
                    'status_approval' => $it['status_approval'] ?? 'Waiting List',
                    'keterangan' => $it['keterangan'] ?? null,
                    'updated_by' => $arsip->admin_id,
                ]);
            }
        }
    }

    /**
     * API detail satu pengajuan (lengkap dengan item, URL file, approvals, TTD, delegation).
     * Payload dibangun via helper Arsip::toApiDetailArray() — dipakai juga oleh
     * approveArsip / rejectArsip / signArsip supaya response konsisten.
     */
    public function show($id)
    {
        $arsip = Arsip::with($this->detailRelations)->find($id);

        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak ditemukan'
            ], 404);
        }

        $this->appendFileUrls($arsip);

        return response()->json([
            'success' => true,
            'message' => 'Detail Pengajuan Berhasil Diambil',
            'data'    => $arsip->toApiDetailArray(auth()->user()),
        ], 200);
    }

    /**
     * API untuk mendapatkan daftar BA yang masih Outstanding (Pending/Process)
     */
    public function getOutstandingBA(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $arsips = Arsip::with(['department', 'unit', 'admin'])
            ->whereIn('ba', ['Pending', 'Process'])
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $arsips->getCollection()->transform(fn($a) => $this->appendFileUrls($a));

        return response()->json([
            'success' => true,
            'message' => 'Daftar BA Outstanding Berhasil Diambil',
            'data' => $arsips
        ], 200);
    }

    /**
     * API untuk mendapatkan daftar semua pengajuan dengan filter
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $query = Arsip::with(['department', 'unit', 'admin', 'produkBaruItems']);

        if ($request->filled('ket_process')) {
            $query->where('ket_process', $request->ket_process);
        }
        if ($request->filled('arsip')) {
            $query->where('arsip', $request->arsip);
        }
        if ($request->filled('ba')) {
            $query->where('ba', $request->ba);
        }
        if ($request->filled('jenis_pengajuan')) {
            $jenis = $request->jenis_pengajuan;
            $jenisAlt = $this->normalizeJenis($jenis);
            $query->whereIn('jenis_pengajuan', array_unique([$jenis, $jenisAlt]));
        }

        // Filter milik user yang login (opsional, untuk "pengajuan saya")
        if ($request->boolean('mine')) {
            $query->where('admin_id', $request->user()->id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($x) use ($q) {
                $x->where('no_registrasi', 'like', "%$q%")
                    ->orWhere('no_transaksi', 'like', "%$q%")
                    ->orWhere('no_doc', 'like', "%$q%")
                    ->orWhere('keterangan', 'like', "%$q%");
            });
        }

        $arsips = $query->orderBy('id', 'desc')->paginate($perPage);
        $arsips->getCollection()->transform(fn($a) => $this->appendFileUrls($a));

        return response()->json([
            'success' => true,
            'message' => 'Daftar Pengajuan Berhasil Diambil',
            'data' => $arsips
        ], 200);
    }
}
