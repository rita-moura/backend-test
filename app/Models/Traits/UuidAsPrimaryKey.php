<?php

namespace App\Models\Traits;

use Ramsey\Uuid\Uuid;

trait UuidAsPrimaryKey
{
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * This function is used internally by models to
     * test if the model has auto increment value
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    public static function bootUuidAsPrimaryKey()
    {
        static::creating(function ($model) {
            $model->incrementing = false;

            $keyName = method_exists($model, 'getKeyName') ? $model->getKeyName() : 'id';
            $model->attributes[$keyName] = isset($model->attributes[$keyName])
                ? $model->attributes[$keyName]
                : Uuid::uuid4()->toString();
        });
    }
}
