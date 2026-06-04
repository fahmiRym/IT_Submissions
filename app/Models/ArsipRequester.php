<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipRequester extends Model
{
    protected $table = 'arsip_requesters';
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;

    protected $fillable = [
        'arsip_id',
        'user_id',
        'employee_id',
        'name_snapshot',
        'is_primary',
        'created_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
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
