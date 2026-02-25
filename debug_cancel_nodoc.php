<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cancels = \App\Models\Arsip::where('jenis_pengajuan', 'like', 'Cancel%')
    ->orderBy('id', 'desc')
    ->take(10)
    ->get(['id', 'jenis_pengajuan', 'no_doc']);

foreach ($cancels as $c) {
    echo "ID: {$c->id} | Type: [{$c->jenis_pengajuan}] | NoDoc: [" . str_replace("\n", " ", $c->no_doc) . "]\n";
}
