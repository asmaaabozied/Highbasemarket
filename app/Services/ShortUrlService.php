<?php

namespace App\Services;

use AshAllenDesign\ShortURL\Classes\Builder;
use AshAllenDesign\ShortURL\Models\ShortURL;

class ShortUrlService
{
    public static function get($url, $key)
    {
        $shorted_url = ShortURL::findByKey($key);

        if ($shorted_url instanceof \AshAllenDesign\ShortURL\Models\ShortURL) {
            return $shorted_url->default_short_url;
        }

        return (new self)->create($url, $key)->default_short_url;
    }

    public function create(string $url, string $key): \AshAllenDesign\ShortURL\Models\ShortURL
    {
        return app(Builder::class)
            ->destinationUrl($url)
            ->urlKey($key)
            ->make();
    }
}
