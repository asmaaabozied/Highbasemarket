<?php

namespace App\Traits;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait StockCoupons
{
    public function scopeWithCoupons(Builder $query, ?Branch $customer = null): void
    {
        $customerId = $customer?->id;

        $query
            ->select([
                'stocks.*',
                DB::raw('ANY_VALUE(coupon_products.name) as product_name'),
                DB::raw("
                    CASE
                        WHEN COUNT(DISTINCT coupons.id) > 0 THEN
                            CONCAT('[',
                                GROUP_CONCAT(DISTINCT
                                    CASE
                                        WHEN (
                                            (coupon_customer_restrict.id IS NULL OR coupon_customer_restrict.restrictable_id = '$customerId')
                                            AND
                                            (
                                                coupon_targetable.id IS NULL
                                                OR (coupon_targetable.restrictable_type = 'App\\\\Models\\\\Stock' AND coupon_targetable.restrictable_id = stocks.id)
                                                OR (coupon_targetable.restrictable_type = 'App\\\\Models\\\\Brand' AND coupon_targetable.restrictable_id = coupon_products.brand_id)
                                                OR (coupon_targetable.restrictable_type = 'App\\\\Models\\\\Category' AND coupon_targetable.restrictable_id = coupon_products.category_id)
                                            )
                                        )
                                        THEN
                                            JSON_OBJECT(
                                                'coupon_id', coupons.id,
                                                'code', coupons.code,
                                                'name', coupons.name,
                                                'value', coupons.value,
                                                'type', coupons.type,
                                                'min_order_amount', coupons.min_order_amount,
                                                'ending_time', coupons.ending_time
                                            )
                                        ELSE NULL
                                    END
                                ),
                            ']')
                        ELSE NULL
                    END as coupons
                    "),
            ])
            ->leftJoin('products as coupon_products', function ($join): void {
                $join->on('coupon_products.id', '=', 'stocks.product_id')
                    ->where('coupon_products.status', 'active')
                    ->whereNull('coupon_products.deleted_at');
            })
            ->leftJoin('coupons', function ($join): void {
                $join->on('coupons.branch_id', '=', 'stocks.branch_id')
                    ->where('coupons.active', true)
                    ->whereNull('coupons.deleted_at')
                    ->where(function ($q): void {
                        $q->whereNull('coupons.starting_time')
                            ->orWhere('coupons.starting_time', '<=', now());
                    })
                    ->where(function ($q): void {
                        $q->whereNull('coupons.ending_time')
                            ->orWhere('coupons.ending_time', '>=', now());
                    })
                    ->where(function ($q): void {
                        $q->whereNull('coupons.quantity')
                            ->orWhereRaw('coupons.quantity > (SELECT COUNT(*) FROM coupon_usages WHERE coupon_usages.coupon_id = coupons.id)');
                    });
            })
            ->leftJoin('coupon_restrictables as coupon_targetable', function ($join): void {
                $join->on('coupon_targetable.coupon_id', '=', 'coupons.id')
                    ->whereIn('coupon_targetable.restrictable_type', [Stock::class, Brand::class, Category::class]);
            })
            ->leftJoin('coupon_restrictables as coupon_customer_restrict', function ($join): void {
                $join->on('coupon_customer_restrict.coupon_id', '=', 'coupons.id')
                    ->where('coupon_customer_restrict.restrictable_type', Branch::class);
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

    public function getCoupons(): ?\Illuminate\Support\Collection
    {
        return $this->coupons?->filter();
    }

    public function getApplicableCoupons(): array
    {
        $coupons = $this->getCoupons();

        if (! $coupons || $coupons->isEmpty()) {
            return [];
        }

        $stockCoupons    = $coupons->where('targetable_type', Stock::class);
        $brandCoupons    = $coupons->where('targetable_type', Brand::class);
        $categoryCoupons = $coupons->where('targetable_type', Category::class);
        $generalCoupons  = $coupons->whereNull('targetable_type');

        if ($stockCoupons->isNotEmpty()) {
            return $stockCoupons->values()->all();
        }

        if ($brandCoupons->isNotEmpty()) {
            return $brandCoupons->values()->all();
        }

        if ($categoryCoupons->isNotEmpty()) {
            return $categoryCoupons->values()->all();
        }

        return $generalCoupons->values()->all();
    }
}
