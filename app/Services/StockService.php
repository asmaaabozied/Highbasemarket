<?php

namespace App\Services;

use Illuminate\Support\Collection;

class StockService
{
    public static function validateTiers(array $tiers): void
    {
        foreach ($tiers as $tier) {
            if (! isset($tier['min'])) {
                continue;
            }

            if (! isset($tier['max'])) {
                continue;
            }

            if ($tier['min'] < $tier['max']) {
                continue;
            }

            throw new \InvalidArgumentException('Minimum quantity must be less than maximum quantity.');
        }
    }

    public static function format(Collection $products)
    {
        $new_data = collect([]);

        $products->each(function ($item) use (&$new_data): void {
            $new_data->push([
                'id'           => $item->id,
                'name'         => $item->variant?->name ?? $item->product?->name,
                'image'        => $item->image ?? $item->variant->image ?? $item->product?->image,
                'uuid'         => $item->uuid,
                'product_id'   => $item->product_id,
                'variant_id'   => $item->variant?->id,
                'branch_id'    => $item->branch_id,
                'category'     => $item->product?->category,
                'brand'        => $item->product?->brand,
                'quantity'     => $item->quantity,
                'price'        => $item->price,
                'barcode'      => $item->variant?->barcode,
                'packaging'    => $item->packaging,
                'show_price'   => $item->show_price,
                'status'       => $item->status,
                'allow_orders' => $item->allow_orders,
                'config'       => $item->config,
                'created_at'   => $item->created_at,
                'sku'          => $item->sku,
            ]);
        });

        return $new_data;
    }
}
