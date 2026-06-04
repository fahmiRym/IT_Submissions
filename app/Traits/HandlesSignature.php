<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait HandlesSignature
{
    /**
     * Simpan specimen tanda tangan user (dari canvas base64 ATAU file upload).
     * Field yang didukung:
     *  - signature_data : data URL base64 PNG (hasil signature_pad)
     *  - signature_file : file gambar (png/jpg)
     *  - remove_signature = 1 : hapus specimen
     */
    public function updateSignature(Request $request)
    {
        $request->validate([
            'signature_file' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'signature_data' => 'nullable|string',
        ]);

        $user = Auth::user();
        $dir = public_path('signatures');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $deleteOld = function () use ($user, $dir) {
            if ($user->signature_path && file_exists($dir . '/' . $user->signature_path)) {
                @unlink($dir . '/' . $user->signature_path);
            }
        };

        // Hapus specimen
        if ($request->input('remove_signature') == '1') {
            $deleteOld();
            $user->signature_path = null;
            $user->save();
            return back()->with('success', 'Tanda tangan digital dihapus.');
        }

        $filename = 'sign_' . $user->id . '_' . time() . '.png';

        // 1) Dari canvas (base64 data URL)
        $dataUrl = $request->input('signature_data');
        if ($dataUrl && str_starts_with($dataUrl, 'data:image')) {
            $encoded = substr($dataUrl, strpos($dataUrl, ',') + 1);
            $encoded = str_replace(' ', '+', $encoded);
            $binary = base64_decode($encoded, true);
            if ($binary === false) {
                return back()->with('error', 'Format tanda tangan tidak valid.');
            }
            $deleteOld();
            file_put_contents($dir . '/' . $filename, $binary);
            $user->signature_path = $filename;
            $user->save();
            return back()->with('success', 'Tanda tangan digital berhasil disimpan.');
        }

        // 2) Dari file upload
        if ($request->hasFile('signature_file')) {
            $deleteOld();
            $request->file('signature_file')->move($dir, $filename);
            $user->signature_path = $filename;
            $user->save();
            return back()->with('success', 'Tanda tangan digital berhasil disimpan.');
        }

        return back()->with('error', 'Tidak ada tanda tangan yang dikirim.');
    }
}
