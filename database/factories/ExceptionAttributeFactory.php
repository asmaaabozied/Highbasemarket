<?php

namespace Database\Factories;

use App\Models\ExceptionAttribute;
use App\Models\PlanException;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExceptionAttribute>
 */
class ExceptionAttributeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attributes' => json_encode([
                'name'      => 'Order',
                'type'      => 'localMarket',
                'attribute' => [
                    [
                        'name'  => 'commission_amount',
                        'type'  => 'text',
                        'value' => 20,
                    ],
                    [
                        'name'  => 'is_percentage',
                        'type'  => 'checkbox',
                        'value' => false,
                    ],
                ],
                'status' => 1,
            ]),
        ];
    }

    public function withExceptionables($count = 2, $exceptionable_id = null, $exceptionable_type = null, $plan_id = null)
    {
        return $this->afterCreating(function (ExceptionAttribute $exceptionAttribute) use ($count, $exceptionable_id, $exceptionable_type, $plan_id): void {
            $plan_exception = PlanException::factory()->count($count)->create([
                'plan_id'            => $plan_id,
                'exceptionable_id'   => $exceptionable_id,
                'exceptionable_type' => $exceptionable_type,
            ]);
            $exceptionAttribute->exceptionables()->attach($plan_exception);
        });
    }
}
