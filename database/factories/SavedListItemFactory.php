<?php

namespace Database\Factories;

use App\Models\SavedList;
use App\Models\SavedListItem;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SavedListItemFactory extends Factory
{
    protected $model = SavedListItem::class;

    public function definition(): array
    {
        return [
            'quantity'   => $this->faker->randomFloat(),
            'packaging'  => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'saved_list_id' => SavedList::factory(),
            'stock_id'      => Stock::factory(),
        ];
    }
}
