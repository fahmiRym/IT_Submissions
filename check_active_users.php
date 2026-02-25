<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$inactiveUsers = \App\Models\User::where('is_active', false)->get();
echo "Inactive users: " . $inactiveUsers->count() . "\n";
foreach ($inactiveUsers as $user) {
    echo "ID: {$user->id} | User: {$user->username} | Role: {$user->role}\n";
}

$admins = \App\Models\User::where('role', 'admin')->get();
echo "Total Admins: " . $admins->count() . "\n";
echo "Active Admins: " . $admins->where('is_active', true)->count() . "\n";
echo "Inactive Admins: " . $admins->where('is_active', false)->count() . "\n";

$super = \App\Models\User::where('role', 'superadmin')->get();
echo "Total Superadmins: " . $super->count() . "\n";
foreach ($super as $s) {
    echo "Super: {$s->username} | Active: " . ($s->is_active ? 'Yes' : 'No') . "\n";
}
