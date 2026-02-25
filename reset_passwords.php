<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fahmi = \App\Models\User::where('username', 'fahmi')->first();
if ($fahmi) {
    $fahmi->password = \Illuminate\Support\Facades\Hash::make('bismillah');
    $fahmi->save();
    echo "Fahmi password reset to: bismillah\n";
}

$admin = \App\Models\User::where('username', 'admin')->first();
if ($admin) {
    $admin->password = \Illuminate\Support\Facades\Hash::make('admin123');
    $admin->save();
    echo "Admin password reset to: admin123\n";
}
