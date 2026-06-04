<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;


    protected $fillable = [
        'employee_id',
        'name',
        'username',
        'email',
        'password',
        'photo',
        'signature_path',
        'role',
        'jabatan',
        'department_id',
        'work_unit_id',
        'odoo_user_id',
        'is_active',
        'source',
        'must_change_password',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = ['password'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function workUnit()
    {
        return $this->belongsTo(Unit::class, 'work_unit_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public static function getJabatanOptions(): array
    {
        return ['Staff', 'SPV', 'Kabag', 'Manager', 'Accounting', 'IT'];
    }

    public function approvalsAssigned()
    {
        return $this->hasMany(ArsipApproval::class, 'approver_id');
    }

    public function hasSignature(): bool
    {
        return !empty($this->signature_path)
            && file_exists(public_path('signatures/' . $this->signature_path));
    }

    public function signatureUrl(): ?string
    {
        return $this->signature_path ? asset('signatures/' . $this->signature_path) : null;
    }
}
