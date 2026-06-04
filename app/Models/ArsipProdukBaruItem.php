<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipProdukBaruItem extends Model
{
    protected $table = 'arsip_produk_baru_items';

    protected $fillable = [
        'arsip_id',
        'product_code',
        'product_name',
        'barcode',
        'tipe_produk',
        'kategori',
        'satuan',
        'status_approval',
        'keterangan',
        'updated_by',
    ];

    protected static function booted()
    {
        // Auto-generate barcode unik setelah record dibuat (butuh id).
        static::created(function ($item) {
            if (empty($item->barcode)) {
                $item->barcode = 'PB' . str_pad($item->id, 8, '0', STR_PAD_LEFT);
                $item->saveQuietly();
            }
        });
    }

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getTipeOptions(): array
    {
        return ['Stockable', 'Service', 'Consumable'];
    }

    public static function getStatusApprovalOptions(): array
    {
        return ['Waiting List', 'Done'];
    }

    public static function getKategoriOptions(): array
    {
        return [
            'All / BB Tembaga EX',
            'All / BB Ingot EX',
            'All / BP Extrusion',
            'All / BP Melting',
            'All / BP Powder C',
            'All / BP Umum',
            'All / BP Packing',
            'All / Fabrikasi',
            'All / Saleable',
            'All / Expanda',
            'All / Ingot',
            'All / Ingot / Ingot Export',
            'All / Ingot / Ingot Local',
            'All / O Ops. Kantor',
            'All / O Form',
            'All / O Melting',
            'All / O HNS',
            'All / O Packing Extrusion',
            'All / O Packing Melting',
            'All / O ListrikGas',
            'All / O Teknik',
            'All / O Umum',
            'All / O Bahan Bakar',
            'All / O SP',
            'All / O SP Ext',
            'All / O SP Melting',
            'All / O Asset',
            'All / O ATK',
            'All / Bahan Kimia',
            'All / Bahan Pembantu',
            'All / Barang Teknik',
            'All / O Extrusion',
            'All / O Jasa',
            'All / O Kimia',
            'All / O SP Kendaraan',
            'All / Barang Umum',
            'All / BB Extrusion',
            'All / BB Melting',
            'All / IM Raw Material',
            'All / IM Raw Material / Export',
            'All / Spare Part',
            'All / Xmen',
            'All / O Jasa Ekspedisi',
            'All / O PL Alubless',
            'All / BP Wood Grain',
            'All / SP Motor Ext',
            'All / SP Motor ANO',
            'All / SP Motor PC',
            'All / SP Motor MLT',
            'All / SP Motor Umum',
        ];
    }

    public static function getSatuanOptions(): array
    {
        return [
            'Box', 'Btg', 'Btl', 'Bundel', 'cm', 'Day(s)', 'Dozen(s)', 'Drum',
            'fl oz', 'foot(ft)', 'g', 'gal(s)', 'Hour(s)', 'inch(es)', 'kg', 'KG',
            'Klg', 'km', 'Lbr', 'lb(s)', 'Liter(s)', 'lpsm', 'm', 'm2', 'm3',
            'mile(s)', 'ml', 'oz(s)', 'Pack', 'Pail', 'Pcs', 'qt', 'Rim', 'Rit',
            'Roll', 'Sak', 'Set', 't', 'Truck', 'Unit(s)', 'yard',
        ];
    }
}
