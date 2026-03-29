<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public static function getSubCategories(): Collection
    {
        return Category::query()
            ->select('id', 'name')
            ->whereHas('parent', function ($query): void {
                $query->whereNotNull('parent_id');
            })
            ->get();
    }
}
