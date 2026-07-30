<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipPersonalNote extends Model
{
    protected $table = 'arsip_personal_notes';

    protected $fillable = [
        'arsip_id',
        'user_id',
        'note',
    ];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
