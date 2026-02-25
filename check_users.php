<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
echo "Total users: " . $users->count() . "\n";
foreach ($users as $user) {
    echo "Username: {$user->username}, Role: {$user->role}, Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
}
