<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Mengurus:
 *  - Link akun lama (legacy) ke NIK (employee_id) lewat data hr_import
 *  - Force change password
 */
class AccountSetupController extends Controller
{
    /* ===== LINK NIK ===== */

    public function showLinkNik()
    {
        $u = auth()->user();
        if ($u->role === 'superadmin' || !empty($u->employee_id)) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.link_nik');
    }

    public function linkNik(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'string', 'max:20'],
        ], [
            'employee_id.required' => 'NIK karyawan wajib diisi.',
        ]);

        $u = auth()->user();
        if ($u->role === 'superadmin' || !empty($u->employee_id)) {
            return redirect()->route('admin.dashboard');
        }

        $nik = trim($request->employee_id);

        // Cari user hr_import dengan NIK tsb
        $hrUser = User::where('employee_id', $nik)
            ->where('source', 'hr_import')
            ->first();

        if (!$hrUser) {
            return back()->withErrors([
                'employee_id' => "NIK \"{$nik}\" tidak ditemukan di database karyawan. Hubungi Superadmin.",
            ])->withInput();
        }

        // Cek NIK sudah dipakai user lain (selain hr_import yang akan di-adopt)
        $taken = User::where('employee_id', $nik)
            ->where('id', '!=', $hrUser->id)
            ->where('id', '!=', $u->id)
            ->exists();
        if ($taken) {
            return back()->withErrors([
                'employee_id' => "NIK \"{$nik}\" sudah ter-link ke akun lain.",
            ])->withInput();
        }

        DB::transaction(function () use ($u, $hrUser) {
            // (A) ADOPT — strategy. URUTAN PENTING: lepaskan NIK dari hr_user DULU
            //     supaya tidak kena unique constraint pas assign ke akun lama.

            $nik          = $hrUser->employee_id;
            $workUnitId   = $hrUser->work_unit_id;
            $departmentId = $hrUser->department_id;
            $odooUserId   = $hrUser->odoo_user_id;

            // STEP 1: lepas NIK dari user hr_import + deactivate + rename username
            $hrUser->employee_id = null;
            $hrUser->is_active = false;
            $hrUser->username = $hrUser->username . '_merged_' . $u->id;
            $hrUser->saveQuietly();

            // STEP 2: assign NIK + data ke akun user lama
            $u->employee_id          = $nik;
            $u->work_unit_id         = $u->work_unit_id ?: $workUnitId;
            $u->department_id        = $u->department_id ?: $departmentId;
            $u->odoo_user_id         = $u->odoo_user_id ?: $odooUserId;
            $u->last_synced_at       = now();
            $u->must_change_password = true; // force ke step ganti password
            $u->saveQuietly();
        });

        return redirect()->route('auth.change-password')
            ->with('success', 'NIK berhasil di-link. Sekarang silakan ganti password Anda.');
    }

    /* ===== CHANGE PASSWORD ===== */

    public function showChangePassword()
    {
        $u = auth()->user();
        if ($u->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }
        return view('auth.change_password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ], [
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.different' => 'Password baru tidak boleh sama dengan password lama.',
        ]);

        $u = auth()->user();

        if (!Hash::check($request->current_password, $u->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah.']);
        }

        $u->password = Hash::make($request->new_password);
        $u->must_change_password = false;
        $u->saveQuietly();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Password berhasil diganti. Selamat datang kembali!');
    }
}
