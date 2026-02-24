<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// These migrations are pending but the changes already exist in the DB.
// We just need to mark them as "ran" so artisan doesn't try to run them again.

$pending = [
    '2026_02_21_124117_create_settings_table',
    '2026_02_23_164239_rename_ket_eror_to_keterangan_in_arsips_table',
];

$maxBatch = \DB::table('migrations')->max('batch') ?? 0;
$newBatch = $maxBatch + 1;

foreach ($pending as $migration) {
    $exists = \DB::table('migrations')->where('migration', $migration)->exists();
    if (!$exists) {
        \DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $newBatch,
        ]);
        echo "MARKED: $migration (batch $newBatch)\n";
    } else {
        echo "SKIP (already recorded): $migration\n";
    }
}

echo "\nDone! All migrations are now in sync.\n";
