<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'status'     => 'active',
            'job_title'  => 'administrator',
        ];
    }

    public function configure(): Factory|EmployeeFactory
    {
        return $this->afterCreating(function (Employee $employee): void {
            $user = User::factory()->create();

            $employee->user()->save($user);
        });
    }
}
