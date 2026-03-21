<?php

namespace Tests\Feature\Http\Controllers\Account\Order;

use App\Enum\CurrencyEnum;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Stock;

function createOrder(Branch $buyer, array $stocks = [1], string $status = 'pending', ?Employee $employee = null)
{
    $stocks = Stock::whereIn('id', $stocks)
        ->whereNot('branch_id', $buyer->id)
        ->whereNot('quantity', 0)
        ->get();

    // Default commission percentage (e.g., from config, or 10%)
    // Example: 10%

    $lines = $stocks->map(function ($stock) {
        $defaultCommissionPercentage = 10.00;
        $quantity                    = fake()->numberBetween(1, $stock->quantity);
        $unitPrice                   = $stock->price;
        $total                       = $unitPrice * $quantity;

        // Calculate commission
        $commissionAmountUsd = ($total * $defaultCommissionPercentage) / 100;

        return new OrderLine([
            'product_id' => $stock->id, // Correct: product_id, not stock.id
            'variant_id' => $stock->variant_id,
            'quantity'   => $quantity,
            'price'      => $unitPrice,
            'total'      => $total,
            'packaging'  => $stock->packaging,

            // New commission fields
            'commission_amount_usd'            => $commissionAmountUsd,
            'commission_percentage'            => $defaultCommissionPercentage,
            'commission_amount_local_currency' => $commissionAmountUsd,
            'commission_local_currency_code'   => CurrencyEnum::USD->value,
            'exchange_rate_to_usd'             => 1.00,

            // Optional
            'applied_plan_id'            => null,
            'plan_exception_source_type' => null,
        ]);
    });

    if (! $employee) {
        $employee = $buyer->account->employees()->inRandomOrder()->first();
    }

    $order = Order::factory()->create([
        'branch_id'   => $buyer->id,
        'employee_id' => $employee->id,
        'status'      => $status,
        'total'       => $lines->sum('total'),
    ]);

    $order->lines()->createMany($lines->toArray());

    return $order;
}
