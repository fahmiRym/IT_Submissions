<?php

use App\Http\Controllers\Superadmin\UserController;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void
    {
        // Hanya reset hr_import users yang BELUM ganti password sendiri
        // (must_change_password=true berarti default lama masih dipakai).
        $users = User::where('source', 'hr_import')
            ->where('must_change_password', true)
            ->get();

        foreach ($users as $u) {
            $default = UserController::defaultPasswordForRole($u->role ?: 'admin');
            $u->password = Hash::make($default);
            $u->saveQuietly();
        }
    }

    public function down(): void
    {
        // No-op: tidak menyimpan password lama (NIK karyawan), tidak bisa restore.
    }
};
