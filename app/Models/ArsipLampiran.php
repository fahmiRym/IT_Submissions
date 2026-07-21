<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ArsipLampiran extends Model
{
    protected $table = 'arsip_lampiran';

    protected $fillable = [
        'arsip_id',
        'file_path',
        'original_name',
        'file_size',
        'file_hash',
        'mime_type',
        'page_count',
        'keterangan',
        'uploaded_by',
        'sort_order',
    ];

    public function arsip()
    {
        return $this->belongsTo(Arsip::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function absolutePath(): string
    {
        return Storage::disk('public')->path($this->file_path);
    }

    public function publicUrl(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function sizeHuman(): string
    {
        $b = (int) $this->file_size;
        if ($b < 1024) return $b . ' B';
        if ($b < 1024 * 1024) return number_format($b / 1024, 1) . ' KB';
        return number_format($b / 1024 / 1024, 2) . ' MB';
    }
}
