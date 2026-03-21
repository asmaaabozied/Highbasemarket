<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'       => 'Plan-'.$this->faker->word,
            'description' => $this->faker->text,
            'amount'      => $this->faker->numberBetween(1, 100),
            'attributes'  => [
                'name'      => 'support',
                'attribute' => [
                    ['name' => 'type', 'type' => 'select', 'value' => 'default', 'options' => [
                        ['option' => 'default'],
                        ['option' => 'other'],
                    ]],
                ],
            ],
            'status'            => 'active',
            'duration'          => 45,
            'plan_type'         => 'globalMarket',
            'plan_mode'         => 'paid',
            'is_auto_renewable' => true,
        ];
    }
}
