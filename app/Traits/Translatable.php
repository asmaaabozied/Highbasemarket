<?php

namespace App\Traits;

use App\Database\TranslatableBuilder;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

trait Translatable
{
    protected bool $translationsApplied = false;

    protected bool $skipTranslations = false;

    protected static bool $autoTranslateResults = true;

    public static function bootTranslatable(): void
    {
        static::addGlobalScope('autoTranslations', function ($builder): void {
            $locale = app()->getLocale();

            if ($locale !== 'en') {
                $builder->with(['translations' => function ($q) use ($locale): void {
                    $q->where('lang', $locale);
                }]);
            }
        });

        static::saved(function ($model): void {
            if (count($model->translatables) > 0) {
                foreach ($model->translatables as $translatable) {
                    if ($translations = request(Str::plural($translatable))) {
                        foreach ($translations as $translation) {
                            $model->translate($model, $translatable, $translation);
                        }
                    }
                }
            }
        });
    }

    public function newEloquentBuilder($query): \App\Database\TranslatableBuilder
    {
        return new TranslatableBuilder($query);
    }

    private function translate($model, ?string $field, array $translation): void
    {
        if ($field && $translation) {
            $model->translations()->updateOrCreate(
                ['lang' => $translation['lang'], 'field' => $field],
                ['field' => $field, ...$translation]
            );
        }
    }

    public function applyTranslations(?string $locale = null): self
    {
        if ($this->translationsApplied) {
            return $this;
        }

        $locale ??= app()->getLocale();

        if ($locale !== 'en' && isset($this->translatables)) {
            foreach ($this->translatables as $field) {
                $translation = $this->translations
                    ?->where('lang', $locale)
                    ->where('field', $field)
                    ->first();

                if ($translation?->translation) {
                    $this->attributes[$field] = $translation->translation;
                }
            }
        }

        $this->translationsApplied = true;

        return $this;
    }

    public function withoutTranslations(): self
    {
        $this->skipTranslations = true;

        return $this;
    }

    public static function withoutAutoTranslations(): static
    {
        static::$autoTranslateResults = false;

        return new static;
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
}
