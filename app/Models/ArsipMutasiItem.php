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
        'location',
    ];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public static function getLocations()
    {
        return [
            'Physical Locations/INK/Billet Oven 1',
            'Physical Locations/INK/Billet Oven 2',
            'Physical Locations/INK/Billet Oven 3',
            'Physical Locations/INK/Billet Oven 4',
            'Physical Locations/INK/Billet Oven 5',
            'Physical Locations/INK/Billet Oven 6',
            'Physical Locations/INK/Billet Oven 7',
            'Physical Locations/INK/Unit 2 - Billet Oven 8',
            'Physical Locations/INK/Unit 2 - Billet Oven 9',
            'Physical Locations/INK/Unit 2 - Billet Oven 10',
            'Physical Locations/INK/Unit 3 - Billet Oven 11',
            'Physical Locations/INK/Unit 3 - Billet Oven 12',
            'Physical Locations/INK/Unit 3 - Billet Oven 13',
            'Physical Locations/INK/Unit 3 - Billet Oven 14',
            'Physical Locations/INK/Unit 3 - Billet Oven 15',
            'Physical Locations/INK/Unit 3 - Billet Oven 16',
            'Physical Locations/INK/Unit 3 - Billet Oven 17',
            'Physical Locations/INK/Unit 3 - Billet Oven 19',
            'Physical Locations/INK/Unit 3 - Billet Oven 20',
            'Physical Locations/INK/Unit 3 - Billet Oven 22',
            'Physical Locations/INK/Unit 3 - Billet Oven 23',
            'Physical Locations/INK/Unit 4B - Billet Oven 24',
            'Physical Locations/INK/Unit 4B - Billet Oven 25',
            'Physical Locations/INK/Unit 4B - Billet Oven 26',
        ];
    }
}
