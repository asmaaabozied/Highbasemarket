<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Follower;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FollowerFactory extends Factory
{
    protected $model = Follower::class;

    public function definition(): array
    {
        return [
            'follower_id' => Branch::factory(),
            'branch_id'   => Branch::factory(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];
    }
}
