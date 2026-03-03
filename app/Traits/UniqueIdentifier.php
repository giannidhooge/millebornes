<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait UniqueIdentifier
{
    protected static $uid_field = 'unique_identifier';

    public static function bootUniqueIdentifier(): void
    {
        static::creating(function (Model $model): void {
            self::setUidField($model);
        });
    }

    protected static function setUidField(Model $model): void
    {
        if (empty($model->getAttribute(static::$uid_field))) {
            $model->setAttribute(static::$uid_field, Str::uuid()->toString());
        }
    }
}