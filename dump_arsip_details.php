<?php
define('LARAVEL_START', microtime(true));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$arsip = App\Models\Arsip::where('no_registrasi', 'EXP-260522-U3C-001')->with('adjustItems')->first();
if ($arsip) {
    echo "Arsip ID: " . $arsip->id . "\n";
    echo "Detail Barang JSON: " . json_encode($arsip->detail_barang) . "\n";
    echo "Adjust Items:\n";
    foreach ($arsip->adjustItems as $item) {
        echo " - ID: " . $item->id . "\n";
        echo "   product_code: " . $item->product_code . "\n";
        echo "   product_name: " . $item->product_name . "\n";
        echo "   qty_in: " . $item->qty_in . "\n";
        echo "   qty_out: " . $item->qty_out . "\n";
        echo "   lot: " . $item->lot . "\n";
        echo "   odoo: " . $item->odoo . "\n";
        echo "   fisik: " . $item->fisik . "\n";
        echo "   keterangan_in: " . $item->keterangan_in . "\n";
        echo "   keterangan_out: " . $item->keterangan_out . "\n";
    }
} else {
    echo "Arsip not found.\n";
}
