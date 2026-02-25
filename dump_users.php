<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::select('id', 'username', 'role', 'is_active')->get();
foreach ($users as $user) {
    echo "ID: {$user->id} | User: {$user->username} | Role: {$user->role} | Active: " . ($user->is_active ? '1' : '0') . "\n";
}
