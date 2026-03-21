<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class LocalProductsSeeder extends Seeder
{
    public function run(): void
    {

        $products = Product::withWhereHas('variants', function ($query): void {
            $query->whereNotNull('packages')
                ->where('packages', '!=', '[]')
                ->whereNotNull('attributes');
        })
            ->take(200)
            ->get();

        $branches = Branch::take(20)->get()->pluck('id')->toArray();

        $products->each(function ($product) use ($branches): void {
            $product->variants->map(function ($variant) use ($product, $branches): void {
                Stock::create([
                    'branch_id'  => $branches[array_rand($branches)],
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'status'     => 'active',
                    'images'     => $variant->images ?? $product->images,
                    'price'      => number_format(mt_rand(10 * 100, 100 * 100) / 100, 2),
                    'quantity'   => random_int(1, 100),
                    'packaging'  => isset($variant->packages[0]) ? $variant->packages[0]['name'] : 'box',
                ]);
            });
        });
    }
}
