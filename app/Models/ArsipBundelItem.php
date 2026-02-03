<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipBundelItem extends Model
{
    // Pastikan nama tabel ini sesuai dengan yang ada di database Anda
    protected $table = 'arsip_bundel_items'; 

    protected $fillable = [
        'arsip_id',
        'no_doc',      // Nomor Dokumen / Item
        'qty',
        'keterangan',
    ];

    // Relasi balik ke Arsip Utama (Opsional, tapi bagus untuk ada)
    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }
}