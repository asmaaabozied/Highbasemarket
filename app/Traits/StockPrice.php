<?php

namespace App\Traits;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait StockPrice
{
    public function scopeWithSpecialPrices(Builder $query, ?Branch $customer = null): void
    {
        if (! $customer instanceof \App\Models\Branch) {
            return;
        }

        $query
            ->select([
                'stocks.*',
                DB::raw('ANY_VALUE(products.name) as product_name'),
                DB::raw("
                CASE
                    WHEN COUNT(special_prices.id) > 0 THEN
                        JSON_ARRAYAGG(
                            CASE
                                WHEN special_prices.id IS NOT NULL THEN
                                    JSON_OBJECT(
                                        'special_price_id', special_prices.id,
                                        'targetable_type', special_prices.targetable_type,
                                        'targetable_id', special_prices.targetable_id,
                                        'is_increment', special_prices.is_increment,
                                        'type', special_prices.type,
                                        'amount', special_prices.amount,
                                        'template_name', special_price_templates.name,
                                        'template_id', special_prices.special_price_template_id
                                    )
                                ELSE NULL
                            END
                        )
                    ELSE NULL
                END as special_prices
                "),
            ])
            ->leftJoin('products', function ($join): void {
                $join->on('products.id', '=', 'stocks.product_id')
                    ->where('products.status', 'active')
                    ->whereNull('products.deleted_at');
            })
            ->leftJoin('special_price_templates', 'stocks.branch_id', '=', 'special_price_templates.branch_id')
            ->leftJoin('customer_special_prices', function ($join) use ($customer): void {
                $join->on('customer_special_prices.special_price_template_id', '=', 'special_price_templates.id')
                    ->where('customer_special_prices.customer_id', $customer->id)->whereNull('customer_special_prices.deleted_at');
            })
            ->leftJoin('special_prices', function ($join): void {
                $join->on('special_prices.special_price_template_id', '=', 'customer_special_prices.special_price_template_id')
                    ->where(function ($query): void {
                        $query->where(function ($q): void {
                            $q->whereColumn('special_prices.targetable_id', '=', 'stocks.id')
                                ->where('special_prices.targetable_type', \App\Models\Stock::class);
                        })
                            ->orWhere(function ($q): void {
                                $q->whereColumn('special_prices.targetable_id', '=', 'products.brand_id')
                                    ->where('special_prices.targetable_type', \App\Models\Brand::class);
                            })
                            ->orWhere(function ($q): void {
                                $q->whereColumn('special_prices.targetable_id', '=', 'products.category_id')
                                    ->where('special_prices.targetable_type', \App\Models\Category::class);
                            });
                    });
            })
            ->groupByRaw('
                `stocks`.`id`,
                `stocks`.`product_id`,
                `stocks`.`branch_id`,
                `stocks`.`variant_id`,
                `stocks`.`quantity`,
                `stocks`.`price`,
                `stocks`.`status`,
                `stocks`.`show_price`,
                `stocks`.`expiration_date`,
                `stocks`.`config`,
                `stocks`.`created_at`,
                `stocks`.`updated_at`,
                `stocks`.`deleted_at`
            ');
    }

    public function applyPercentage($percentage): float
    {
        return $this->price * ($percentage / 100);
    }

    public function applySpecialPrice(float $percentage, bool $is_increment): float
    {
        $value = $this->applyPercentage($percentage);

        if ($is_increment) {
            return $this->price + $value;
        }

        return $this->price - $value;
    }

    private function getHighbaseDiscount(): ?float
    {
        $items = [
            ['id' => 4923, 'price' => 2.250],
            ['id' => 4922, 'price' => 5.650],
            ['id' => 4912, 'price' => 1.050],
            ['id' => 4920, 'price' => 4.150],
        ];

        $item = collect($items)->firstWhere('id', $this->id);

        if ($item) {
            return $item['price'];
        }

        return $this->getPriceByName($this->variant->name ?? '');
    }

    private function getPriceByName(string $name): ?float
    {
        if (Str::endsWith($name, '24x360ml') || Str::endsWith($name, '30x250ml')) {
            return 4.150;
        }

        if (Str::endsWith($name, '6x360ml')) {
            return 1.050;
        }

        return null;
    }

    public function getPrice(): float
    {
        $prices = $this->special_prices;

        if (! $prices) {
            return $this->price;
        }

        $prices = $prices->filter();

        $stockPrice = $prices->firstWhere('targetable_type', Stock::class);

        if ($stockPrice) {
            return $this->getStockSpecialPrice($stockPrice);
        }

        $brandPrice = $prices->firstWhere('targetable_type', Brand::class);

        if ($brandPrice) {
            return $this->applySpecialPrice($brandPrice['amount'], $brandPrice['is_increment']);
        }

        $categoryPrice = $prices->firstWhere('targetable_type', Category::class);

        if ($categoryPrice) {
            return $this->applySpecialPrice($categoryPrice['amount'], $categoryPrice['is_increment']);
        }

        return $this->price;
    }

    public function getStockSpecialPrice(array $specialPriceConfig): float
    {
        if ($specialPriceConfig['type'] === 'fixed') {
            return $specialPriceConfig['amount'];
        }

        return $this->applySpecialPrice($specialPriceConfig['amount'], $specialPriceConfig['is_increment']);
    }
}
