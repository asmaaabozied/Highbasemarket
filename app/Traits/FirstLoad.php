<?php

namespace App\Traits;

trait FirstLoad
{
    public function isFirstLoad(): bool
    {
        $isFirstLoad = ! request()->session()->has('page_loaded_'.auth()->user()->id);

        if ($isFirstLoad) {
            request()->session()->put('page_loaded_'.auth()->user()->id, true);
        }

        return $isFirstLoad;
    }
}
