<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    public static function bootSluggable(): void
    {
        static::saving(function ($model): void {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name.'-'.now()->timestamp);
            }
        });
    }
}
