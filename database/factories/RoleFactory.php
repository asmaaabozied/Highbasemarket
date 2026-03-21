<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'roleable_id'   => $this->faker->randomNumber(),
            'roleable_type' => $this->faker->word(),
            'slug'          => $this->faker->slug(),
            'name'          => $this->faker->name(),
            'type'          => $this->faker->word(),
            'status'        => $this->faker->word(),
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ];
    }
}
