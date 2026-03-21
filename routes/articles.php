<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

Route::get('about/{lang?}', function ($lang = 'en') {
    $path = resource_path("markdown/highbase/about-$lang.md");

    return inertia('Accounts/User-guide/Markdown', [
        'doc' => Str::markdown(File::get($path), [
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'symbol'     => '# ',
            ],
        ], [new HeadingPermalinkExtension]),
        'lang' => $lang,
    ]);
})
    ->whereIn('lang', ['en', 'ar'])
    ->name('about');

Route::get('global/about/{lang?}', function ($lang = 'en') {
    $path = resource_path("markdown/highbase/global-about-$lang.md");

    return inertia('Accounts/User-guide/Markdown', [
        'doc' => Str::markdown(File::get($path), [
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'symbol'     => '# ',
            ],
        ], [new HeadingPermalinkExtension]),
        'lang' => $lang,
    ]);
})
    ->whereIn('lang', ['en', 'ar'])
    ->name('global.about');

Route::get('saudi-food/{lang?}', function ($lang = null) {
    $lang = ucfirst($lang);

    return view("Saudi.$lang", [
        'lang' => $lang,
    ]);
})
    ->whereIn('lang', ['en', 'ar'])
    ->name('saudi-food');

Route::get('/ac/{file?}/{lang?}', function ($file, $lang = 'en') {
    $path = resource_path("markdown/highbase/$file-$lang.md");

    return inertia('Accounts/User-guide/Markdown', [
        'doc' => Str::markdown(File::get($path), [
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'symbol'     => '# ',
            ],
        ], [new HeadingPermalinkExtension]),
        'lang' => $lang,

    ]);
})
    ->whereIn('lang', ['en', 'ar'])
    ->name('ac');
