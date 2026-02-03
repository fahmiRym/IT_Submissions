<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Arsip;
use App\Models\User;
use App\Models\Department;
use App\Models\Unit;
use App\Models\Manager;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    echo "Creating dependencies...\n";
    // Reuse or create user
    $admin = User::first();
    if (!$admin) {
        $admin = User::factory()->create();
    }
    
    $dept = Department::create(['name' => 'TST']);
    $unit = Unit::create(['name' => 'U1']);
    $manager = Manager::create(['name' => 'Mgr']); // Make sure Manager creates OK

    echo "Creating Arsip...\n";
    $arsip = Arsip::create([
        'tgl_pengajuan' => now(),
        'admin_id' => $admin->id,
        'department_id' => $dept->id,
        'unit_id' => $unit->id,
        'manager_id' => $manager->id,
        'status' => 'Process',
        'ket_process' => 'Process',
    ]);

    echo "No Reg Generated: " . $arsip->no_registrasi . "\n";
    
    if (empty($arsip->no_registrasi)) {
        throw new Exception("No Registrasi was NOT generated!");
    }

    echo "Testing Update Immutability...\n";
    $original = $arsip->no_registrasi;
    $originalTgl = $arsip->tgl_pengajuan;
    
    // Try to change
    $arsip->no_registrasi = "CHANGED";
    $arsip->tgl_pengajuan = now()->addDays(5);
    $arsip->save();
    
    // Check local instance (should be reset by hook?) 
    // Wait, the hook sets properties on the model instance. So they should be reset immediately?
    // Let's check from DB.
    $fresh = Arsip::find($arsip->id);
    
    echo "Original No Reg: $original\n";
    echo "Current DB No Reg: " . $fresh->no_registrasi . "\n";
    
    if ($fresh->no_registrasi === $original) {
        echo "SUCCESS: No Reg verified immutable.\n";
    } else {
        echo "FAIL: No Reg changed!\n";
    }

    DB::rollBack();
    echo "Done (Rolled back DB).\n";

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
