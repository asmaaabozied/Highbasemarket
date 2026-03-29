<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\Stock;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function bulkStocksToCart($data): void
    {
        $uuids = collect($data)->pluck('uuid')->toArray();

        $stocks = Stock::query()->whereIn('uuid', $uuids)->get();
        $branch = currentBranch();

        $carts = $stocks->map(function ($product) use ($data, $branch) {
            $request = collect($data)->where('uuid', $product->uuid)->first();

            if (
                ! isset($branch?->address['country']) ||
                ! isset($product->branch?->address['country']) ||
                $branch->address['country'] != $product->branch?->address['country']
            ) {
                return back()->withErrors(['address' => __('this Item is not available on your country.')]);
            }

            if ($product->moq > $request['quantity']) {
                $request['quantity'] = $product->moq;
            }

            if (! $product->allow_orders) {
                throw ValidationException::withMessages([
                    'product_id' => __('Product :product is not available for purchase.', ['product' => $product->variant?->name]),
                ]);
            }

            return [
                'employee_id' => auth()->user()->userable_id,
                'branch_id'   => currentBranch()->id,
                'product_id'  => $product->id,
                'variant_id'  => $product->variant_id,
                'quantity'    => $request['quantity'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        })->toArray();

        Cart::insert($carts);
    }

    public function updateSelectedCarts(Collection $items, Branch $branch): void
    {
        $items->each(function (array $item): void {
            $cart = Cart::where('id', $item['cart_id'])
                ->where('branch_id', currentBranch()->id)
                ->first();

            if (! $cart) {
                return;
            }

            $cart->update([
                'quantity'  => $item['quantity'],
                'packaging' => $item['packaging'],
            ]);
        });

    }
}
