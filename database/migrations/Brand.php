<?php

use App\Models\Interest;
use App\Traits\Sluggable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Searchable, Sluggable, SoftDeletes, Translatable;

    protected $guarded = ['id'];

    public array $translatables = ['name', 'description'];

    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    public function Interests(): HasMany
    {
        return $this->hasMany(Interest::class);
    }

    protected $casts = [
        'config' => 'array',
    ];
}
