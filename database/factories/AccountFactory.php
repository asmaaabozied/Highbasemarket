<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'   => fake()->name(),
            'type'   => 'vendor',
            'status' => 'active',
            'domain' => 'factory',
        ];
    }

    public function configure(): Factory|AccountFactory
    {
        return $this->afterCreating(function (Account $account): void {
            $account->branches()->saveMany(
                Branch::factory()->count(1)->for($account)->make()
            );
            $account->employees()->saveMany(
                Employee::factory()->count(1)->for($account)->make()
            );
        });
    }
}
