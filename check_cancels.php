<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$types = \App\Models\Arsip::select('jenis_pengajuan')->distinct()->get();
foreach ($types as $type) {
    echo "Type: [" . $type->jenis_pengajuan . "]\n";
}

$cancels = \App\Models\Arsip::where('jenis_pengajuan', 'like', 'Cancel%')
    ->select('id', 'jenis_pengajuan', 'no_doc', 'no_registrasi')
    ->orderBy('id', 'desc')
    ->take(10)
    ->get();

echo "\nRecent Cancel records:\n";
foreach ($cancels as $c) {
    echo "ID: {$c->id} | Type: {$c->jenis_pengajuan} | NoDoc: {$c->no_doc} | NoReg: {$c->no_registrasi}\n";
}
