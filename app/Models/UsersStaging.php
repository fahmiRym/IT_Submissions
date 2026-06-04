<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersStaging extends Model
{
    protected $table = 'users_staging';

    protected $fillable = [
        'employee_id',
        'name',
        'department_name',
        'work_unit_name',
        'matched_user_id',
        'match_method',
        'match_score',
        'status',
        'notes',
        'batch_id',
    ];

    public function matchedUser()
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }
}
