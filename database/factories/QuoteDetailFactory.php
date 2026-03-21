<?php

namespace Database\Factories;

use App\Models\Progress;
use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\Terms;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteDetailFactory extends Factory
{
    protected $model = QuoteDetail::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->sentence,
            'quote_type'  => $this->faker->randomElement([0, 1, 3]),
            'price'       => $this->faker->randomFloat(2, 10, 1000),
            'progress_id' => Progress::factory(),
            'term_id'     => Terms::factory(),
            'quote_id'    => Quote::factory(),
        ];
    }
}
