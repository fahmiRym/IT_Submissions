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
    /**
     * ─────────────────────────────────────────────────────────
     * EXPORT – Download data arsip (JSON) + File Bukti Scan (ZIP)
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

        if ($request->filled('from')) {
            $query->whereDate('tgl_pengajuan', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('tgl_pengajuan', '<=', $request->to);
        }

        $arsips = $query->latest()->get();

        $data = $arsips->map(function ($a) {
            return [
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
                'bukti_scan'      => $a->bukti_scan,
                'created_at'      => optional($a->created_at)->toIso8601String(),
                'updated_at'      => optional($a->updated_at)->toIso8601String(),

                'admin_name'       => $a->admin->name ?? null,
                'admin_email'      => $a->admin->email ?? null,
                'department_name'  => $a->department->name ?? null,
                'manager_name'     => $a->manager->name ?? null,
                'unit_name'        => $a->unit->name ?? null,

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
        })->toArray();

        $payload = [
            'meta' => [
                'app'         => config('app.name'),
                'version'     => '1.0',
                'exported_at' => now()->toIso8601String(),
                'total'       => count($data),
                'exported_by' => auth()->user()->name,
            ],
            'data' => $data
        ];

        // Buat ZIP
        $zipName = 'backup-arsip-' . now()->format('Ymd-His') . '.zip';
        $tempDir = storage_path('app/temp-backup-' . uniqid());
        if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

        $jsonPath = $tempDir . '/data.json';
        file_put_contents($jsonPath, json_encode($payload, JSON_PRETTY_PRINT));

        $zipPath = storage_path('app/' . $zipName);
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            // Tambahkan JSON
            $zip->addFile($jsonPath, 'data.json');

            // Tambahkan Bukti Scan
            foreach ($arsips as $a) {
                if ($a->bukti_scan) {
                    $filePath = storage_path('app/public/bukti_scan/' . $a->bukti_scan);
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, 'files/' . $a->bukti_scan);
                    }
                }
            }
            $zip->close();
        }

        // Cleanup temp
        @unlink($jsonPath);
        @rmdir($tempDir);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * ─────────────────────────────────────────────────────────
     * IMPORT – Upload file ZIP/JSON dan restore data + file
     * ─────────────────────────────────────────────────────────
     */
    public function import(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:102400', // max 100MB
        ]);

        $file    = $request->file('backup_file');
        $ext     = $file->getClientOriginalExtension();
        $tempDir = storage_path('app/temp-import-' . uniqid());
        $payload = null;

        if ($ext === 'zip') {
            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath()) === TRUE) {
                if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
                $zip->extractTo($tempDir);
                $zip->close();

                $jsonPath = $tempDir . '/data.json';
                if (file_exists($jsonPath)) {
                    $payload = json_decode(file_get_contents($jsonPath), true);
                }
            }
        } else {
            // Legacy JSON Import
            $payload = json_decode(file_get_contents($file->getRealPath()), true);
        }

        if (!$payload || !isset($payload['data'])) {
            // Cleanup jika gagal
            if (is_dir($tempDir)) \Illuminate\Support\Facades\File::deleteDirectory($tempDir);
            return back()->withErrors(['backup_file' => 'Format backup tidak valid. Gunakan file .zip hasil export terbaru.']);
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        DB::beginTransaction();
        try {
            foreach ($payload['data'] as $idx => $row) {
                try {
                    // 1. Resolve Admin
                    $adminId = User::where('email', $row['admin_email'] ?? '')
                                   ->orWhere('name', $row['admin_name'] ?? '')
                                   ->orWhere('username', strtolower(str_replace(' ', '', $row['admin_name'] ?? '')))
                                   ->value('id');

                    if (!$adminId && !empty($row['admin_name'])) {
                        $adminId = User::create([
                            'name'          => $row['admin_name'],
                            'username'      => strtolower(str_replace(' ', '', $row['admin_name'])),
                            'email'         => $row['admin_email'] ?? (strtolower(str_replace(' ', '', $row['admin_name'])) . '@system.com'),
                            'password'      => bcrypt('password123'),
                            'role'          => 'admin',
                            'department_id' => 1,
                            'is_active'     => true
                        ])->id;
                    }

                    // 2. Resolve Master Data
                    $deptId = Department::where('name', $row['department_name'] ?? '')->value('id');
                    if (!$deptId && !empty($row['department_name'])) {
                        $deptId = Department::create(['name' => $row['department_name'], 'is_active' => true])->id;
                    }

                    $managerId = Manager::where('name', $row['manager_name'] ?? '')->value('id');
                    if (!$managerId && !empty($row['manager_name'])) {
                        $managerId = Manager::create(['name' => $row['manager_name'], 'is_active' => true])->id;
                    }

                    $unitId = Unit::where('name', $row['unit_name'] ?? '')->value('id');
                    if (!$unitId && !empty($row['unit_name'])) {
                        $unitId = Unit::create(['name' => $row['unit_name'], 'is_active' => true])->id;
                    }

                    if (!$adminId) {
                        $skipped++;
                        $errors[] = "Row #{$idx}: User data incomplete.";
                        continue;
                    }

                    // 3. Upsert Arsip
                    $existing = Arsip::where('no_registrasi', $row['no_registrasi'])->first();
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
                        'bukti_scan'      => $row['bukti_scan'] ?? null,
                    ];

                    if ($existing) {
                        $existing->update($arsipData);
                        $arsip = $existing;
                        $arsip->adjustItems()->delete();
                        $arsip->mutasiItems()->delete();
                        $arsip->bundelItems()->delete();
                    } else {
                        $arsip = Arsip::create($arsipData);
                    }

                    // 4. Restore Items (Adjust, Mutasi, Bundel)
                    foreach ($row['adjust_items'] ?? [] as $i) {
                        ArsipAdjustItem::create(array_merge($i, ['arsip_id' => $arsip->id]));
                    }
                    foreach ($row['mutasi_items'] ?? [] as $i) {
                        ArsipMutasiItem::create(array_merge($i, ['arsip_id' => $arsip->id]));
                    }
                    foreach ($row['bundel_items'] ?? [] as $i) {
                        ArsipBundelItem::create(array_merge($i, ['arsip_id' => $arsip->id]));
                    }

                    // 5. Restore Physical File (Jika ini ZIP)
                    if ($ext === 'zip' && $arsip->bukti_scan) {
                        $srcFile = $tempDir . '/files/' . $arsip->bukti_scan;
                        $dstDir  = storage_path('app/public/bukti_scan');
                        if (!file_exists($dstDir)) mkdir($dstDir, 0777, true);

                        if (file_exists($srcFile)) {
                            copy($srcFile, $dstDir . '/' . $arsip->bukti_scan);
                        }
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
            if (is_dir($tempDir)) \Illuminate\Support\Facades\File::deleteDirectory($tempDir);
            return back()->withErrors(['backup_file' => 'Critical Import Failure: ' . $e->getMessage()]);
        }

        // Cleanup
        if (is_dir($tempDir)) \Illuminate\Support\Facades\File::deleteDirectory($tempDir);

        $msg = "Import Berhasil! {$imported} data + bukti scan dipulihkan. {$skipped} gagal/dilewati.";
        if ($errors) session()->flash('import_errors', $errors);

        return back()->with('success', $msg);
    }

    public function updateNoRegistrasi(Request $request, $id)
    {
        $request->validate([
            'no_registrasi' => [
                'required', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('arsips', 'no_registrasi')->ignore($id),
            ],
        ]);

        $arsip = Arsip::findOrFail($id);
        $old = $arsip->no_registrasi;
        $arsip->update(['no_registrasi' => $request->no_registrasi]);

        return back()->with('success', "No Registrasi diubah: {$old} -> {$arsip->no_registrasi}");
    }
}
