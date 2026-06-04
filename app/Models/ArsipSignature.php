<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipSignature extends Model
{
    protected $table = 'arsip_signatures';

    protected $fillable = [
        'arsip_id',
        'user_id',
        'role_label',
        'signer_name',
        'signature_path',
        'hash',
        'note',
        'ip_address',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function signatureUrl(): ?string
    {
        return $this->signature_path ? asset('signatures/' . $this->signature_path) : null;
    }
}
