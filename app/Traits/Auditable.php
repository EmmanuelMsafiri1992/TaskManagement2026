<?php

namespace App\Traits;

use App\Models\AuditTrail;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            AuditTrail::log($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = collect($model->getOriginal())
                ->only(array_keys($model->getDirty()))
                ->toArray();

            $newValues = $model->getDirty();

            if (!empty($newValues)) {
                AuditTrail::log($model, 'updated', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            AuditTrail::log($model, 'deleted', $model->getAttributes(), null);
        });
    }

    public function auditTrails()
    {
        return $this->morphMany(AuditTrail::class, 'auditable');
    }
}
