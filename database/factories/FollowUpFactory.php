<?php

namespace Database\Factories;

use App\Models\FollowUp;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FollowUpFactory extends Factory
{
    protected $model = FollowUp::class;

    public function definition(): array
    {
        return [
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
            'registered_at' => Carbon::now(),
            'visited_pages' => $this->faker->words(),
            'opened_at'     => Carbon::now(),

            'invitation_id' => Invitation::factory(),
        ];
    }
}
