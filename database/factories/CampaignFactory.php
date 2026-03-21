<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'name'       => $this->faker->name(),
            'Links'      => $this->faker->words(),
            'status'     => $this->faker->word(),
            'ended_at'   => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'admin_id' => Admin::factory(),
        ];
    }
}
