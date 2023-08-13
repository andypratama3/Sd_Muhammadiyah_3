<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait NameHasSlug
{
    public static function bootNameHasSlug()
    {
        static::creating(function (Model $model) {
            $model->slug = Str::slug($model->name);
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
