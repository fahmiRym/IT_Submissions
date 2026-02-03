<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\Arsip;
use App\Models\Department;
use App\Models\Manager;
use App\Models\Unit;

class PengajuanController extends Controller
{
    /**
     * ===============================
     * LIST & FORM PENGAJUAN ADMIN
     * ===============================
     */
    public function index(Request $request)
    {
        $arsips = Arsip::with(['department','manager','unit'])
            ->where('admin_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('admin.pengajuan.index', [
            'arsips'      => $arsips,
            'departments' => Department::orderBy('name')->get(),
            'managers'    => Manager::orderBy('name')->get(),
            'units'       => Unit::orderBy('name')->get(),
        ]);
    }

    /**
     * ===============================
     * UPDATE (ADMIN)
     * ===============================
     */
    public function update(Request $request, Arsip $arsip)
    {
        $request->validate([
            'department_id'  => 'required',
            'manager_id'     => 'required',
            'unit_id'        => 'required',
            'kategori'       => 'nullable',
            'keterangan'     => 'nullable',
            'no_transaksi'   => 'nullable|string',
            'target_qty'     => 'nullable|integer',
            'catatan_tambahan' => 'nullable|string',
            'detail_barang'  => 'nullable|array',
        ]);

        $totalIn = 0; $totalOut = 0;
        if (($request->jenis_pengajuan ?? $arsip->jenis_pengajuan) == 'Adjust' && is_array($request->detail_barang)) {
            $adjustData = $request->detail_barang['adjust'] ?? [];
            $totalIn = collect($adjustData)->sum('qty_in');
            $totalOut = collect($adjustData)->sum('qty_out');
        }

        $arsip->update([
            'department_id'  => $request->department_id,
            'manager_id'     => $request->manager_id,
            'unit_id'        => $request->unit_id,
            'kategori'       => $request->kategori,
            'keterangan'     => $request->keterangan,
            'no_transaksi'   => $request->no_transaksi,
            'target_qty'     => $request->target_qty,
            'catatan_tambahan' => $request->catatan_tambahan,
            'detail_barang'  => $request->detail_barang,
            'total_qty_in'   => $totalIn ?: ($arsip->total_qty_in ?? 0),
            'total_qty_out'  => $totalOut ?: ($arsip->total_qty_out ?? 0),
        ]);

        return back()->with('success','Data berhasil diperbarui');
    }

    /**
     * ===============================
     * SIMPAN PENGAJUAN BARU (ADMIN)
     * ===============================
     */
    
    public function store(Request $request)
    {
    $detail = json_decode($request->detail_barang_json, true);

    $totalIn = 0;
    $totalOut = 0;

    if ($request->jenis_pengajuan === 'Adjust' && isset($detail['adjust'])) {
        foreach ($detail['adjust'] as $it) {
            $totalIn  += (int) ($it['qty_in'] ?? 0);
            $totalOut += (int) ($it['qty_out'] ?? 0);
        }
    }

    if ($request->jenis_pengajuan === 'Mutasi' && isset($detail['mutasi'])) {
        foreach ($detail['mutasi']['asal'] ?? [] as $it) {
            $totalOut += (int) ($it['qty'] ?? 0);
        }
        foreach ($detail['mutasi']['tujuan'] ?? [] as $it) {
            $totalIn += (int) ($it['qty'] ?? 0);
        }
    }

    Arsip::create([
        'tgl_pengajuan'   => now(),
        'admin_id'        => auth()->id(),

        'jenis_pengajuan' => $request->jenis_pengajuan,
        'department_id'   => $request->department_id,
        'manager_id'      => $request->manager_id,
        'unit_id'         => $request->unit_id,

        'keterangan'      => $request->keterangan,
        'sub_jenis'       => $request->kategori ?? null,

        'detail_barang'   => $detail,
        'total_qty_in'    => $totalIn,
        'total_qty_out'   => $totalOut,
        'jumlah_qty'      => $totalIn - $totalOut,

        'no_transaksi'    => $request->no_transaksi,
        'bukti_scan'      => $request->file('bukti_scan')
                                    ->store('bukti_scan', 'public'),

        // STATUS DEFAULT
        'status'          => 'Check',
        'ba'              => 'Done',
        'arsip'           => 'Pending',
        'ket_process'     => 'Review',
    ]);

    return back()->with('success','Pengajuan berhasil dikirim');
    }

}
