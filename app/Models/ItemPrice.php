<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPrice extends Model
{
    protected $table = 'item_prices';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'harga',
        'currency',
        'satuan',
        'keterangan',
        'updated_by',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Cari harga aktif untuk kode_barang tertentu (null kalau belum diset). */
    public static function findByKode(string $kode): ?self
    {
        return static::where('kode_barang', $kode)->first();
    }
}
