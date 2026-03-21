<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\CustomerVendor;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerVendor>
 */
class CustomerVendorFactory extends Factory
{
    protected $model = CustomerVendor::class;

    public function definition(): array
    {

        return [
            'vendor_id'            => Branch::factory(),
            'customer_id'          => Branch::factory(),
            'inviter_employee_id'  => Employee::factory(),
            'acceptor_employee_id' => Employee::factory(),
            'config'               => null,
        ];
    }
}
