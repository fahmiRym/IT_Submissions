<?php

// File: app/Models/ArsipMutasiItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipMutasiItem extends Model
{
    protected $table = 'arsip_mutasi_items';

    protected $fillable = [
        'arsip_id',
        'type', 
        'product_code', // Kita pakai ini, bukan product_id
        'product_name',
        'qty',
        'lot',
        'panjang',
    ];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }
}
