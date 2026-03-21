<?php

namespace Database\Factories;

use App\Models\Progress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class StepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'         => $this->faker->word,
            'description'  => $this->faker->text,
            'type'         => 'payment',
            'reaction'     => 'vendor',
            'confirmation' => 'client',
            'progress_id'  => Progress::factory(),
            'status'       => 'inactive',
        ];
    }
}
