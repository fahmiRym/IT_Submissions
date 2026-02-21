<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Arsip;
use App\Models\ArsipAdjustItem;
use App\Models\ArsipMutasiItem;
use App\Models\ArsipBundelItem;
use App\Models\Department;
use App\Models\Manager;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupController extends Controller
{
    /**
     * ─────────────────────────────────────────────────────────
     * EXPORT – Download semua data arsip + items sebagai JSON
     * ─────────────────────────────────────────────────────────
     */
    public function export(Request $request)
    {
        $query = Arsip::with([
            'admin:id,name,email',
            'department:id,name',
            'manager:id,name',
            'unit:id,name',
            'adjustItems',
            'mutasiItems',
            'bundelItems',
        ]);

        // Opsional: filter range tanggal
        if ($request->filled('from')) {
            $query->whereDate('tgl_pengajuan', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('tgl_pengajuan', '<=', $request->to);
        }

        $arsips = $query->latest()->get();

        $payload = [
            'meta' => [
                'app'         => config('app.name'),
                'version'     => '1.0',
                'exported_at' => now()->toIso8601String(),
                'total'       => $arsips->count(),
                'exported_by' => auth()->user()->name,
            ],
            'data' => $arsips->map(function ($a) {
                return [
                    // Core fields
                    'id'              => $a->id,
                    'no_registrasi'   => $a->no_registrasi,
                    'jenis_pengajuan' => $a->jenis_pengajuan,
                    'keterangan'      => $a->keterangan,
                    'ket_eror'        => $a->ket_eror,
                    'kategori'        => $a->kategori,
                    'pemohon'         => $a->pemohon,
                    'tgl_pengajuan'   => optional($a->tgl_pengajuan)->toIso8601String(),
                    'tgl_arsip'       => optional($a->tgl_arsip)->toDateString(),
                    'no_doc'          => $a->no_doc,
                    'no_transaksi'    => $a->no_transaksi,
                    'ba'              => $a->ba,
                    'arsip'           => $a->arsip,
                    'ket_process'     => $a->ket_process,
                    'status'          => $a->status,
                    'total_qty_in'    => $a->total_qty_in,
                    'total_qty_out'   => $a->total_qty_out,
                    'detail_barang'   => $a->detail_barang,
                    'created_at'      => optional($a->created_at)->toIso8601String(),
                    'updated_at'      => optional($a->updated_at)->toIso8601String(),

                    // Relasi (nama, bukan ID — agar portable)
                    'admin_name'       => $a->admin->name ?? null,
                    'admin_email'      => $a->admin->email ?? null,
                    'department_name'  => $a->department->name ?? null,
                    'manager_name'     => $a->manager->name ?? null,
                    'unit_name'        => $a->unit->name ?? null,

                    // Items
                    'adjust_items' => $a->adjustItems->map(fn($i) => [
                        'product_code' => $i->product_code,
                        'product_name' => $i->product_name,
                        'qty_in'       => $i->qty_in,
                        'qty_out'      => $i->qty_out,
                        'lot'          => $i->lot,
                    ])->toArray(),

                    'mutasi_items' => $a->mutasiItems->map(fn($i) => [
                        'type'         => $i->type,
                        'product_code' => $i->product_code,
                        'product_name' => $i->product_name,
                        'qty'          => $i->qty,
                        'lot'          => $i->lot,
                        'panjang'      => $i->panjang,
                        'location'     => $i->location,
                    ])->toArray(),

                    'bundel_items' => $a->bundelItems->map(fn($i) => [
                        'no_doc'     => $i->no_doc,
                        'qty'        => $i->qty,
                        'keterangan' => $i->keterangan,
                    ])->toArray(),
                ];
            })->toArray(),
        ];

        $filename = 'backup-arsip-' . now()->format('Ymd-His') . '.json';

        return response()->json($payload, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Type'        => 'application/json',
        ]);
    }

    /**
     * ─────────────────────────────────────────────────────────
     * IMPORT – Upload file JSON dan restore data
     * ─────────────────────────────────────────────────────────
     */
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:51200', // max 50MB
        ]);

        $json = file_get_contents($request->file('backup_file')->getRealPath());
        $payload = json_decode($json, true);

        if (!$payload || !isset($payload['data']) || !is_array($payload['data'])) {
            return back()->withErrors(['backup_file' => 'File JSON tidak valid atau format salah.']);
        }

        // Validasi versi
        if (($payload['meta']['version'] ?? null) !== '1.0') {
            return back()->withErrors(['backup_file' => 'Versi backup tidak kompatibel.']);
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        DB::beginTransaction();
        try {
            foreach ($payload['data'] as $idx => $row) {
                try {
                    // Resolve relasi berdasarkan nama
                    $adminId = User::where('email', $row['admin_email'] ?? '')
                                   ->orWhere('name', $row['admin_name'] ?? '')
                                   ->value('id');

                    $deptId    = Department::where('name', $row['department_name'] ?? '')->value('id');
                    $managerId = Manager::where('name', $row['manager_name'] ?? '')->value('id');
                    $unitId    = Unit::where('name', $row['unit_name'] ?? '')->value('id');

                    if (!$adminId) {
                        $skipped++;
                        $errors[] = "Row #{$idx}: User '{$row['admin_name']}' tidak ditemukan. Dilewati.";
                        continue;
                    }

                    // Upsert berdasarkan no_registrasi (jika ada) atau id asli
                    $existing = null;
                    if (!empty($row['no_registrasi'])) {
                        $existing = Arsip::where('no_registrasi', $row['no_registrasi'])->first();
                    }

                    $arsipData = [
                        'no_registrasi'   => $row['no_registrasi'] ?? null,
                        'jenis_pengajuan' => $row['jenis_pengajuan'] ?? 'Cancel',
                        'keterangan'      => $row['keterangan'] ?? null,
                        'ket_eror'        => $row['ket_eror'] ?? null,
                        'kategori'        => $row['kategori'] ?? 'None',
                        'pemohon'         => $row['pemohon'] ?? null,
                        'tgl_pengajuan'   => $row['tgl_pengajuan'] ? Carbon::parse($row['tgl_pengajuan']) : now(),
                        'tgl_arsip'       => $row['tgl_arsip'] ?? null,
                        'admin_id'        => $adminId,
                        'department_id'   => $deptId,
                        'manager_id'      => $managerId,
                        'unit_id'         => $unitId,
                        'no_doc'          => $row['no_doc'] ?? null,
                        'no_transaksi'    => $row['no_transaksi'] ?? null,
                        'ba'              => $row['ba'] ?? 'Process',
                        'arsip'           => $row['arsip'] ?? 'Process',
                        'ket_process'     => $row['ket_process'] ?? 'Pending',
                        'status'          => $row['status'] ?? 'Process',
                        'total_qty_in'    => $row['total_qty_in'] ?? 0,
                        'total_qty_out'   => $row['total_qty_out'] ?? 0,
                        'detail_barang'   => $row['detail_barang'] ?? null,
                    ];

                    if ($existing) {
                        $existing->update($arsipData);
                        $arsip = $existing;
                        // Hapus items lama dan buat ulang
                        $arsip->adjustItems()->delete();
                        $arsip->mutasiItems()->delete();
                        $arsip->bundelItems()->delete();
                    } else {
                        $arsip = Arsip::create($arsipData);
                    }

                    // Restore items
                    foreach ($row['adjust_items'] ?? [] as $item) {
                        ArsipAdjustItem::create([
                            'arsip_id'     => $arsip->id,
                            'product_code' => $item['product_code'] ?? null,
                            'product_name' => $item['product_name'] ?? '-',
                            'qty_in'       => $item['qty_in'] ?? 0,
                            'qty_out'      => $item['qty_out'] ?? 0,
                            'lot'          => $item['lot'] ?? null,
                        ]);
                    }

                    foreach ($row['mutasi_items'] ?? [] as $item) {
                        ArsipMutasiItem::create([
                            'arsip_id'     => $arsip->id,
                            'type'         => $item['type'] ?? 'asal',
                            'product_code' => $item['product_code'] ?? null,
                            'product_name' => $item['product_name'] ?? '-',
                            'qty'          => $item['qty'] ?? 0,
                            'lot'          => $item['lot'] ?? null,
                            'panjang'      => $item['panjang'] ?? null,
                            'location'     => $item['location'] ?? null,
                        ]);
                    }

                    foreach ($row['bundel_items'] ?? [] as $item) {
                        ArsipBundelItem::create([
                            'arsip_id'   => $arsip->id,
                            'no_doc'     => $item['no_doc'] ?? '-',
                            'qty'        => $item['qty'] ?? 1,
                            'keterangan' => $item['keterangan'] ?? null,
                        ]);
                    }

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Row #{$idx}: " . $e->getMessage();
                    $skipped++;
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['backup_file' => 'Gagal import: ' . $e->getMessage()]);
        }

        $msg = "Import selesai: {$imported} data berhasil diimpor, {$skipped} dilewati.";
        if ($errors) {
            session()->flash('import_errors', $errors);
        }

        return back()->with('success', $msg);
    }

    /**
     * ─────────────────────────────────────────────────────────
     * UPDATE NO REGISTRASI – Update hanya field no_registrasi
     * ─────────────────────────────────────────────────────────
     */
    public function updateNoRegistrasi(Request $request, $id)
    {
        $request->validate([
            'no_registrasi' => [
                'required',
                'string',
                'max:100',
                // Harus unik kecuali untuk arsip yg sama
                \Illuminate\Validation\Rule::unique('arsips', 'no_registrasi')->ignore($id),
            ],
        ]);

        $arsip = Arsip::findOrFail($id);
        $old = $arsip->no_registrasi;
        $arsip->update(['no_registrasi' => $request->no_registrasi]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No Registrasi berhasil diperbarui.',
                'old'     => $old,
                'new'     => $arsip->no_registrasi,
            ]);
        }

        return back()->with('success', "No Registrasi berhasil diubah dari \"{$old}\" menjadi \"{$arsip->no_registrasi}\".");
    }
}
