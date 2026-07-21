<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppVersion extends Model
{
    protected $table = 'app_versions';

    protected $fillable = [
        'app_slug',
        'app_name',
        'latest_version',
        'version_code',
        'apk_path',
        'apk_url_override',
        'force_update',
        'changelog',
        'file_size',
        'file_hash',
        'uploaded_by',
    ];

    protected $casts = [
        'force_update' => 'boolean',
        'version_code' => 'integer',
        'file_size'    => 'integer',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * URL absolut untuk download APK — prioritas: manual override > storage path.
     */
    public function getApkUrlAttribute(): ?string
    {
        if (!empty($this->apk_url_override)) return $this->apk_url_override;
        if (!empty($this->apk_path))         return url(Storage::url($this->apk_path));
        return null;
    }
}
