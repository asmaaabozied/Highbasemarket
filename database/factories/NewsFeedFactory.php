<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\NewsFeed;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class NewsFeedFactory extends Factory
{
    protected $model = NewsFeed::class;

    public function definition(): array
    {
        return [
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
            'post'        => $this->faker->word(),
            'attachments' => $this->faker->words(),
            'branch_id'   => Branch::factory(),
        ];
    }
}
