<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipAdjustItem extends Model
{
    // Sesuaikan dengan nama tabel di database Anda
    protected $table = 'arsip_adjust_items'; 

    protected $fillable = [
        'arsip_id',
        'product_code', // Ingat: CODE bukan ID
        'product_name',
        'qty_in',
        'qty_out',
        'lot',
        // 'panjang', // tambahkan jika ada
    ];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }
}