<?php

namespace App\Services;

use App\Models\Cart;

class CleanCartService
{
    public function __construct(private readonly array $data) {}

    public function process(): void
    {
        $data = collect($this->data)->pluck('cart_id');

        if ($data->count()) {
            Cart::where('branch_id', currentBranch()->id)
                ->whereIn('id', $data)
                ->delete();

            return;
        }

        Cart::where('branch_id', currentBranch()->id)
            ->whereIn('branch_product_id', $data->pluck('branch_product_id'))
            ->whereIn('variant_id', $data->pluck('variant_id'))
            ->delete();
    }
}
