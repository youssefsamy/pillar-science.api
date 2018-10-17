<?php

namespace App\Models\Behaviors;

use App\Exceptions\ImmutableModelException;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait Immutable
 *
 * This trait disables deleting and updating on a model.
 *
 * @package App\Models\Behaviors
 */
trait Immutable
{
    public static function bootImmutable()
    {
        static::deleting(function (Model $model) {
            throw new ImmutableModelException(sprintf("Model of type %s is immutable. It cannot be deleted", get_class($model)));
        });

        static::updating(function (Model $model) {
            throw new ImmutableModelException(sprintf("Model of type %s is immutable. It cannot be updated", get_class($model)));
        });
    }
}