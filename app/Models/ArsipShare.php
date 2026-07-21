<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipShare extends Model
{
    protected $table = 'arsip_shares';

    protected $fillable = [
        'arsip_id',
        'target_type',  // 'user' | 'role'
        'user_id',
        'role',
        'shared_by',
        'note',
    ];

    public function scopeForUser($q, $userId)        { return $q->where('target_type', 'user')->where('user_id', $userId); }
    public function scopeForRole($q, string $role)   { return $q->where('target_type', 'role')->where('role', $role); }

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'shared_by');
    }
}
