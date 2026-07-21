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

        // ── ANTI DOUBLE-SIGN: tolak kalau sudah ada TTD untuk role-label ini ──
        $existing = ArsipSignature::where('arsip_id', $arsip->id)
            ->where('role_label', $label)
            ->first();
        if ($existing) {
            $msg = "Dokumen sudah ditandatangani sebagai {$label} oleh {$existing->signer_name} pada "
                 . optional($existing->signed_at)->format('d/m/Y H:i') . " WIB. Tidak boleh dua kali.";
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'status'  => 'already_signed',
                    'message' => $msg,
                ], 409);
            }
            return back()->with('warning', $msg);
        }

        // Tidak perlu specimen — TTD = QR auto-generated (verify via hash + token).
        $this->applySignature($arsip, $user, $label, $request->input('note'), $request->ip());

        $msg = "Dokumen berhasil ditandatangani sebagai {$label}.";
        if ($request->wantsJson()) {
            $arsip->refresh();
            return response()->json([
                'success' => true,
                'status'  => 'success',
                'message' => $msg,
                'data'    => $arsip->toApiDetailArray($user),
            ]);
        }
        return back()->with('success', $msg);
    }

    /**
     * Terapkan/snapshot specimen TTD user ke dokumen untuk satu peran tertentu.
     * Dipakai oleh signArsip() maupun alur approval bertingkat.
     *
     * $delegatedFromId — bila $user TTD sebagai delegasi dari user lain (mis. SPV Fulan
     * TTD slot Kabag karena Kabag Budi cuti), simpan Budi.id di sini supaya draft/verify
     * bisa render "Kabag (Diwakilkan oleh SPV Fulan)".
     */
    protected function applySignature(Arsip $arsip, $user, string $roleLabel, ?string $note = null, ?string $ip = null, ?int $delegatedFromId = null): ?ArsipSignature
    {
        $signedAt = now();
        $hash = hash('sha256', implode('|', [
            $arsip->id,
            $user->id,
            $roleLabel,
            $signedAt->toIso8601String(),
            $arsip->no_registrasi,
            (string) ($delegatedFromId ?? ''),  // include di hash supaya delegasi juga anti-forgery
            config('app.key'),    // pepper supaya hash anti-forgery
        ]));

        $arsip->ensureVerifyToken();

        // signature_path TIDAK dipakai lagi (sebelumnya snapshot PNG specimen).
        // TTD digital sekarang murni QR: nama + role + timestamp + hash + token → verifikasi
        // di /verify/{token}. Untuk kompatibilitas, kolom signature_path tetap di-NULL-kan.
        return ArsipSignature::updateOrCreate(
            ['arsip_id' => $arsip->id, 'role_label' => $roleLabel],
            [
                'user_id'           => $user->id,
                'delegated_from_id' => $delegatedFromId,
                'signer_name'       => $user->name,
                'signature_path'    => null,
                'hash'              => $hash,
                'ip_address'        => $ip,
                'signed_at'         => $signedAt,
                'note'              => $note,
            ]
        );
    }
}
