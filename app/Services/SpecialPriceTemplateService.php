<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Stock;
use Illuminate\Support\Collection;

class SpecialPriceTemplateService
{
    public static function validateItems(array|Collection $items): void
    {
        if (is_array($items)) {
            $items = collect($items);
        }

        $duplicates = $items->duplicates(fn (array $item): string => $item['targetable_type'].'-'.$item['targetable_id']);

        if ($duplicates->isNotEmpty()) {
            throw new \InvalidArgumentException('Duplicate items found: '.$duplicates->implode(', '));
        }
    }

    public static function formatStocks($stocks, \Illuminate\Database\Eloquent\Collection|Collection $items): void
    {
        $stocks->transform(function (array $stock) use ($items): array {
            $item = $items->where('targetable_id', $stock['id'])->where('targetable_type', Stock::class)->first();

            if ($item) {
                $stock['amount']       = $item->amount;
                $stock['type']         = $item->type;
                $stock['is_increment'] = $item->is_increment;
            }

            return $stock;
        });
    }

    public static function formatBrands(Collection $brands, \Illuminate\Database\Eloquent\Collection|Collection $items): void
    {
        $brands->each(function (Brand $brand) use ($items): void {
            $item = $items->where('targetable_id', $brand->id)->where('targetable_type', $brand::class)->first();

            if ($item) {
                $brand->amount       = $item->amount;
                $brand->type         = $item->type;
                $brand->is_increment = $item->is_increment;
            }
        })->sortBy('amount');
    }

    public static function formatCategories(Collection $categories, \Illuminate\Database\Eloquent\Collection|Collection $items): void
    {
        $categories->each(function ($category) use ($items): void {
            $item = $items->where('targetable_id', $category->id)->where('targetable_type', $category::class)->first();

            if ($item) {
                $category->amount       = $item->amount;
                $category->type         = $item->type;
                $category->is_increment = $item->is_increment;
            }
        })->sortBy('amount');

    }
}
