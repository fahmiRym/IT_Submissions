<?php

namespace App\Http\Controllers;

use App\Models\Arsip;

class VerificationController extends Controller
{
    /**
     * Halaman verifikasi publik untuk QR tanda tangan digital.
     */
    public function show($token)
    {
        $arsip = Arsip::with(['department', 'unit', 'admin', 'signatures.user', 'approvals.approver'])
            ->where('verify_token', $token)
            ->first();

        if (!$arsip) {
            return response()->view('verify.invalid', [], 404);
        }

        // Verifikasi ulang integritas tiap tanda tangan
        $signatures = $arsip->signatures->map(function ($sig) use ($arsip) {
            $expected = hash('sha256', implode('|', [
                $arsip->id,
                $sig->user_id,
                $sig->role_label,
                optional($sig->signed_at)->toIso8601String(),
                $arsip->no_registrasi,
            ]));
            $sig->is_valid = hash_equals((string) $sig->hash, $expected);
            return $sig;
        });

        return view('verify.show', [
            'arsip' => $arsip,
            'signatures' => $signatures,
        ]);
    }
}
