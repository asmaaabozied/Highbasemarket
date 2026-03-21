<?php

namespace Tests\Feature\Services\Commission\Helpers;

use App\Models\Order;
use App\Models\OrderLine;

/**
 * Helper to create an Order with at least one OrderLine.
 */
function makeOrderWithLine(int $branchId, int $buyerId, int $employeeId): OrderLine
{
    $order = Order::factory()->create([
        'branch_id'   => $buyerId,
        'employee_id' => $employeeId,
    ]);

    return OrderLine::factory()
        ->forProduct(['branch_id' => $branchId])
        ->forOrder($order)
        ->create();
}
