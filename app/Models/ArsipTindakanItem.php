<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ArsipTindakanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'arsip_id',
        'tindakan_in',
        'ket_tindakan_in',
        'tindakan_out',
        'ket_tindakan_out',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class, 'arsip_id');
    }
}

