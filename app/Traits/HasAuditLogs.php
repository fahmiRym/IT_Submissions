<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditLogs
{
    public static function bootHasAuditLogs()
    {
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newValues = $model->getChanges();

            // Ignore timestamps and updated_by if they are the only changes
            unset($oldValues['updated_at'], $newValues['updated_at'], $oldValues['updated_by'], $newValues['updated_by']);

            if (!empty($newValues)) {
                $model->logAudit('updated', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getAttributes(), null);
        });
    }

    protected function logAudit($action, $oldValues, $newValues)
    {
        AuditLog::create([
            'arsip_id' => $this->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
