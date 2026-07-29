<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active', 'branch_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function arsips()
    {
        return $this->hasMany(Arsip::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
