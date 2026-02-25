<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$total = \App\Models\User::count();
$active = \App\Models\User::where('is_active', true)->count();
$inactive = \App\Models\User::where('is_active', false)->count();

echo "Total users: $total\n";
echo "Active users: $active\n";
echo "Inactive users: $inactive\n";

$admins = \App\Models\User::where('role', 'admin')->get();
echo "Admins count: " . $admins->count() . "\n";
foreach ($admins->take(10) as $admin) {
    echo "Admin: {$admin->username}, Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
}

$super = \App\Models\User::where('username', 'fahmi')->first();
if ($super) {
    echo "Superadmin fahmi: Active=" . ($super->is_active ? 'Yes' : 'No') . "\n";
}
