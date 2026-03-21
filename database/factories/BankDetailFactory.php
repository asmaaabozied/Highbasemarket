<?php

namespace Database\Factories;

use App\Models\BankDetail;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BankDetailFactory extends Factory
{
    protected $model = BankDetail::class;

    public function definition(): array
    {
        return [
            'bank_name'      => $this->faker->name(),
            'account_name'   => $this->faker->name(),
            'swift_code'     => $this->faker->word(),
            'account_number' => $this->faker->word(),
            'iban'           => $this->faker->word(),
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),

            'branch_id' => Branch::factory(),
        ];
    }
}
