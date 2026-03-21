<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\QuoteProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Quote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the quote should have related QuoteDetails.
     */
    public function withDetails(int $count = 1): static
    {
        return $this->has(
            QuoteDetail::factory()->count($count)
        );
    }

    /**
     * Indicate that the quote should have related QuoteDetails with QuoteProducts.
     */
    public function withFullDetails(int $detailsCount = 1, int $productsPerDetail = 1, $belongs = null): static
    {
        return $belongs ? $this->has(
            QuoteDetail::factory()
                ->count($detailsCount)
                ->has(
                    QuoteProduct::factory()->count($productsPerDetail)
                )
                ->for($belongs)
        ) : $this->has(
            QuoteDetail::factory()
                ->count($detailsCount)
                ->has(
                    QuoteProduct::factory()->count($productsPerDetail)
                )
        );
    }
}
