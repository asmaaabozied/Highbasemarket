<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class LocalProductsServices
{
    private static bool $branchDataLoaded = false;

    public static function loadBranchData(): static
    {
        self::$branchDataLoaded = true;

        return new static;
    }

    public static function format(array &$items): void
    {
        collect($items)->map(function ($item): void {
            if ($item->relationLoaded('product')) {
                $item->name = $item->product?->name;
            }

            if ($item->relationLoaded('variant') && $item->variant) {
                $item->image        = self::getImage($item);
                $item->name         = $item->variant?->name ?? $item->name;
                $item->variants     = [[...$item->getAttributes(), 'attributes' => $item->variant?->attributes]];
                $item->attributes   = $item->variant?->attributes ?? [];
                $item->full_package = $item->variant?->getFullPackage($item->packaging);
                $item->images ??= $item->variant?->images ?? $item->product?->images;
            }

            $item->price = auth()->user() && $item->show_price ? $item->price : null;

            array_merge(collect($item->alternatives)->map(function (array $variant) use ($item): array {
                $instance_variant = $item->product?->variants?->firstWhere('id', $variant['variant_id']);

                if ($instance_variant) {
                    return array_merge($item->getAttributes(), $instance_variant->toArray());
                }

                return $variant;
            })->toArray(), $item?->variants ?? []);

            if ($item->product->relationLoaded('category')) {
                $item->category = $item->product->category;
            }

            if ($item->product->relationLoaded('brand')) {
                $item->brand = $item->product->brand;
            }

            if ($item->relationLoaded('branch') && $item->branch) {
                self::creditData($item, $item->allow_credit);
            }

            if ($item->relationLoaded('listItem') && $item->listItem) {
                $item->isListed = true;
            }

            if ($item->special_prices) {
                $item->price         = $item->getPrice();
                $item->selling_price = null;
            }

            if (self::$branchDataLoaded) {
                $loaded = $item->relationLoaded('branch');
                $branch = self::getItemBranch($item);

                $item->min_order_value = $branch?->getMinOrderValue();

                if (! $loaded) {
                    unset($item->branch);
                }
            }

            $account = auth()->user()?->getAccount();

            // Hide special prices for key accounts and vendors if the item is from a different branch
            if (
                currentBranch()
                && ($account->is_key_account || $account->type === 'vendor')
                && ! $item->special_prices
                && $item->branch_id != currentBranch()->id
            ) {
                $item->price         = null;
                $item->selling_price = null;
                $item->tiers         = null;
            }

            $item->slug = $item->uuid;

            $item->canPurchase = $item->canPurchase();

            unset($item->product);
            unset($item->variant);
        });
    }

    public static function getBranchCategories(Branch $branch): Collection
    {
        return Category::withWhereHas('children', function ($query) use ($branch): void {
            $query->withWhereHas('children', function ($query) use ($branch): void {
                $query->whereHas('products', function ($query) use ($branch): void {
                    $query->whereHas('stocks', function ($query) use ($branch): void {
                        $query->where('branch_id', $branch->id);
                    });
                });
            });
        })->get();
    }

    private static function creditData($item, $allow_credit = false): void
    {
        if (! $allow_credit) {
            return;
        }

        $customer = $item->branch->customers->first();
        $config   = json_decode((string) $customer->pivot->config, true);

        if (isset($config['credit_settings'])) {
            $item->purchase_by_credit = $config['credit_settings']['allow_credit'] ?? false;
        }
    }

    private static function getImage($item)
    {
        if ($item->getOriginal('image')) {
            return $item->image;
        }

        if ($item->getOriginal('images') && count($item->images) > 0) {
            return $item->images[0];
        }

        if ($item->variant->image) {
            return $item->variant->image;
        }

        if ($item->variant->images && count($item->variant->images) > 0) {
            return $item->variant->images[0];
        }

        return $item->product->image;
    }

    private static function getItemBranch($item)
    {
        if ($item->relationLoaded('branch') && $item->branch) {
            return $item->branch;
        }

        return Branch::find($item->branch_id);
    }

    private static function getHighbaseDiscount($id, $name): ?float
    {
        $items = [
            ['id' => 4923, 'price' => 2.250],
            ['id' => 4922, 'price' => 5.650],
            ['id' => 4912, 'price' => 1.050],
            ['id' => 4920, 'price' => 4.150],
        ];

        $item = collect($items)->firstWhere('id', $id);

        if ($item) {
            return $item['price'];
        }

        return self::getPriceByName($name);
    }

    private static function getPriceByName(string $name): ?float
    {
        if (Str::endsWith($name, '24x360ml') || Str::endsWith($name, '30x250ml')) {
            return 4.150;
        }

        if (Str::endsWith($name, '6x360ml')) {
            return 1.050;
        }

        return null;
    }
}
