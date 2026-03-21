<?php

namespace Database\Factories;

use App\Enum\CommissionLedgerStatusEnum;
use App\Models\CommissionLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommissionLedger>
 */
class CommissionLedgerFactory extends Factory
{
    protected $model = CommissionLedger::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount_usd'      => fake()->randomFloat(2, 100, 1000),
            'paid_amount_usd' => fake()->randomFloat(2, 100, 1000),
            'status'          => CommissionLedgerStatusEnum::PARTIAL_PAID,
            'payable_at'      => now(),
            'line_item_id'    => null,
        ];
    }
}
