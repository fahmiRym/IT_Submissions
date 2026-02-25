<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('username', 'fahmi')->first();
if ($user) {
    $passwords = ['bismillah', 'password', 'admin123', 'inkalum123'];
    foreach ($passwords as $p) {
        if (\Illuminate\Support\Facades\Hash::check($p, $user->password)) {
            echo "Match found for 'fahmi': $p\n";
            exit;
        }
    }
    echo "No common password match for 'fahmi'\n";
} else {
    echo "User fahmi not found\n";
}
