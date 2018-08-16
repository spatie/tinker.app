<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    protected $guarded = [];

    public static function findByName(string $containerName): ?Container
    {
        return static::query()
            ->where('name', $containerName)
            ->first();
    }
}
