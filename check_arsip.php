<?php

use App\Models\Arsip;

$items = Arsip::whereIn('id', [3, 4, 5, 6])->get(['id', 'no_registrasi', 'created_at']);

foreach ($items as $item) {
    echo "ID: {$item->id} | No Reg: {$item->no_registrasi} | Created: {$item->created_at}\n";
}
