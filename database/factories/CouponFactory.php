<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name'      => [
                'ar' => $this->faker->words(2, true),
                'en' => $this->faker->words(2, true),
            ],
            'code'        => strtoupper($this->faker->unique()->bothify('COUP###??')),
            'description' => [
                'ar' => $this->faker->sentence(),
                'en' => $this->faker->sentence(),
            ],
            'value'                 => $this->faker->randomFloat(2, 5, 50),
            'min_order_amount'      => $this->faker->randomFloat(2, 0, 100),
            'type'                  => $this->faker->randomElement(['amount', 'percent']),
            'quantity'              => null,
            'quantity_per_customer' => null,
            'starting_time'         => null,
            'ending_time'           => null,
            'active'                => true,
        ];
    }

    /**
     * Indicate that the coupon is a percentage discount.
     */
    public function percent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type'  => 'percent',
            'value' => $this->faker->randomFloat(2, 5, 30),
        ]);
    }

    /**
     * Indicate that the coupon is a fixed amount discount.
     */
    public function amount(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type'  => 'amount',
            'value' => $this->faker->randomFloat(2, 5, 50),
        ]);
    }

    /**
     * Indicate that the coupon is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'active' => false,
        ]);
    }

    /**
     * Indicate that the coupon has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'starting_time' => now()->subDays(30),
            'ending_time'   => now()->subDays(1),
        ]);
    }

    /**
     * Indicate that the coupon is not yet active (future start date).
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes): array => [
            'starting_time' => now()->addDays(1),
            'ending_time'   => now()->addDays(30),
        ]);
    }

    /**
     * Set the coupon with time limits that are currently valid.
     */
    public function validTimeRange(): static
    {
        return $this->state(fn (array $attributes): array => [
            'starting_time' => now()->subDays(1),
            'ending_time'   => now()->addDays(30),
        ]);
    }

    /**
     * Set a total usage limit.
     */
    public function withQuantityLimit(int $quantity): static
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => $quantity,
        ]);
    }

    /**
     * Set a per-customer usage limit.
     */
    public function withPerCustomerLimit(int $quantity): static
    {
        return $this->state(fn (array $attributes): array => [
            'quantity_per_customer' => $quantity,
        ]);
    }

    /**
     * Set the minimum order amount.
     */
    public function withMinOrderAmount(float $amount): static
    {
        return $this->state(fn (array $attributes): array => [
            'min_order_amount' => $amount,
        ]);
    }
}
