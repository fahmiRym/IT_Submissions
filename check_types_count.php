<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$counts = \App\Models\Arsip::select('jenis_pengajuan', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
    ->groupBy('jenis_pengajuan')
    ->get();

foreach ($counts as $c) {
    echo "Type: [" . $c->jenis_pengajuan . "] Total: " . $c->total . "\n";
}

$recent = \App\Models\Arsip::whereIn('jenis_pengajuan', ['Cancel', 'Cancelled'])
    ->orderBy('id', 'desc')
    ->take(5)
    ->get();

foreach ($recent as $r) {
    echo "ID: {$r->id} | Type: {$r->jenis_pengajuan} | NoDoc: {$r->no_doc}\n";
}
