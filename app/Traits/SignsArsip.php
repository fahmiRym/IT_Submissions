<?php

namespace App\Traits;

use App\Models\Arsip;
use App\Models\ArsipSignature;
use Illuminate\Http\Request;

trait SignsArsip
{
    /**
     * Terapkan tanda tangan digital user (sesuai perannya) ke sebuah pengajuan.
     * Peran → label: superadmin = "Departemen IT", accounting = "Accounting", lainnya = "Pemohon".
     */
    public function signArsip(Request $request, $id)
    {
        $arsip = Arsip::findOrFail($id);
        $user = auth()->user();

        $label = match ($user->role) {
            'superadmin' => 'Departemen IT',
            'accounting' => 'Accounting',
            default      => 'Pemohon',
        };

        // Otorisasi: non-superadmin hanya boleh TTD pengajuan miliknya
        // (accounting boleh TTD pengajuan Adjust siapa pun).
        if ($user->role !== 'superadmin') {
            $own = (int) $arsip->admin_id === (int) $user->id;
            $accAdjust = $user->role === 'accounting' && $arsip->jenis_pengajuan === 'Adjust';
            if (!$own && !$accAdjust) {
                abort(403, 'Tidak berwenang menandatangani dokumen ini.');
            }
        }

        if (!$user->hasSignature()) {
            $msg = 'Anda belum mengatur specimen tanda tangan. Silakan atur di menu Profil.';
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $this->applySignature($arsip, $user, $label, $request->input('note'), $request->ip());

        $msg = "Dokumen berhasil ditandatangani sebagai {$label}.";
        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => $msg]);
        }
        return back()->with('success', $msg);
    }

    /**
     * Terapkan/snapshot specimen TTD user ke dokumen untuk satu peran tertentu.
     * Dipakai oleh signArsip() maupun alur approval bertingkat.
     */
    protected function applySignature(Arsip $arsip, $user, string $roleLabel, ?string $note = null, ?string $ip = null): ?ArsipSignature
    {
        if (!$user->hasSignature()) {
            return null;
        }

        $dir = public_path('signatures');
        $snapshot = 'snap_' . $arsip->id . '_' . $user->id . '_' . time() . '.png';
        @copy($dir . '/' . $user->signature_path, $dir . '/' . $snapshot);

        $signedAt = now();
        $hash = hash('sha256', implode('|', [
            $arsip->id,
            $user->id,
            $roleLabel,
            $signedAt->toIso8601String(),
            $arsip->no_registrasi,
        ]));

        $arsip->ensureVerifyToken();

        return ArsipSignature::updateOrCreate(
            ['arsip_id' => $arsip->id, 'role_label' => $roleLabel],
            [
                'user_id'        => $user->id,
                'signer_name'    => $user->name,
                'signature_path' => $snapshot,
                'hash'           => $hash,
                'ip_address'     => $ip,
                'signed_at'      => $signedAt,
                'note'           => $note,
            ]
        );
    }
}
