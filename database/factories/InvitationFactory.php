<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
            'vendor_name' => $this->faker->name(),
            'vendor_type' => $this->faker->word(),
            'email'       => $this->faker->unique()->safeEmail(),
            'status'      => $this->faker->word(),

            'admin_id' => Admin::factory(),
        ];
    }
}
