<?php
// Script Reset Password Sementara
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

try {
    $user = User::where('username', 'fahmi')->first();
    if ($user) {
        $user->password = Hash::make('bismillah'); // Password diset jadi bismillah
        $user->role = 'superadmin'; // Pastikan role fahmi adalah superadmin
        $user->is_active = 1; // Pastikan akun aktif
        $user->save();
        echo "\n\n>>> SUCCESS: User 'fahmi' reset to password: 'bismillah' with role: 'superadmin' <<<\n\n";
    } else {
        echo "\n\n>>> ERROR: User 'fahmi' not found in database! <<<\n\n";
    }
} catch (\Exception $e) {
    echo "\n\n>>> CRITICAL ERROR: " . $e->getMessage() . " <<<\n\n";
}
