<?php

namespace Database\Seeders;

use App\Models\CustomerVendor;
use App\Models\Inviter;
use Illuminate\Database\Seeder;

class AcceptorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer_list = CustomerVendor::query()
            ->select('id', 'acceptor_employee_id')
            ->whereNotNull('acceptor_employee_id')
            ->get();

        $data = [];

        foreach ($customer_list as $customer) {
            $data[] = [
                'employee_id'        => $customer->acceptor_employee_id,
                'customer_vendor_id' => $customer->id,
                'type'               => 'acceptor',
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        Inviter::query()->insert($data);
    }
}
