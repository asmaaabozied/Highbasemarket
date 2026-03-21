<?php

namespace Database\Seeders;

use App\Models\AnonymousCustomer;
use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class AnonymousCustomerSuggestSeeder extends Seeder
{
    public function run(): void
    {

        $branch   = Branch::first();
        $employee = Employee::first();

        if (! $branch || ! $employee) {
            $this->command->warn('AnonymousCustomerSuggestSeeder requires at least one Branch and one Employee. Run core seeders first.');

            return;
        }

        $branchId  = $branch->id;
        $createdBy = $employee->id;

        $address = [
            'city'         => null,
            'state'        => null,
            'address'      => null,
            'block_number' => null,
            'pin_location' => null,
        ];

        $phone = ['code' => '966', 'number' => '501234567', 'country' => 'SA'];

        // ----- 1. Same email, multiple rows (dedup test: expect 1 result, latest id) -----
        $acDedup = AnonymousCustomer::create([
            'cr_number'  => 'CR-DEDUP',
            'vat_number' => 'VAT-DEDUP',
        ]);

        foreach (
            [
                ['name' => 'dedup@test.com', 'email' => 'dedup@test.com'],
                ['name' => 'dedup@test.com', 'email' => 'dedup@test.com'],
                ['name' => 'dedup@test.com', 'email' => 'dedup@test.com'],
            ] as $i => $row
        ) {
            AnonymousCustomerBranch::create([
                'anonymous_customer_id' => $acDedup->id,
                'name'                  => $row['name'],
                'email'                 => $row['email'],
                'phone'                 => $phone,
                'address'               => $address,
                'branch_id'             => $branchId,
                'created_by'            => $createdBy,
            ]);
        }

        // ----- 2. Different emails, same name prefix (search "acme" -> 2 unique) -----
        $acAcme1 = AnonymousCustomer::create(['cr_number' => 'CR-ACME1', 'vat_number' => 'VAT-ACME1']);
        AnonymousCustomerBranch::create([
            'anonymous_customer_id' => $acAcme1->id,
            'name'                  => 'Acme Corp',
            'email'                 => 'acme@a.com',
            'phone'                 => $phone,
            'address'               => $address,
            'branch_id'             => $branchId,
            'created_by'            => $createdBy,
        ]);

        $acAcme2 = AnonymousCustomer::create(['cr_number' => 'CR-ACME2', 'vat_number' => 'VAT-ACME2']);
        AnonymousCustomerBranch::create([
            'anonymous_customer_id' => $acAcme2->id,
            'name'                  => 'Acme Ltd',
            'email'                 => 'acme@b.com',
            'phone'                 => $phone,
            'address'               => $address,
            'branch_id'             => $branchId,
            'created_by'            => $createdBy,
        ]);

        // ----- 2b. Null email (excluded by whereNotNull) - search "acme" still returns 2 -----
        $acAcmeNull = AnonymousCustomer::create(['cr_number' => 'CR-ACME-NULL', 'vat_number' => 'VAT-ACME-NULL']);
        AnonymousCustomerBranch::create([
            'anonymous_customer_id' => $acAcmeNull->id,
            'name'                  => 'Acme No Email',
            'email'                 => null,
            'phone'                 => $phone,
            'address'               => $address,
            'branch_id'             => $branchId,
            'created_by'            => $createdBy,
        ]);

        // ----- 3. Pagination: 12 rows with unique emails, same prefix "page" -----
        for ($i = 1; $i <= 12; $i++) {
            $ac = AnonymousCustomer::create([
                'cr_number'  => "CR-PAGE-{$i}",
                'vat_number' => "VAT-PAGE-{$i}",
            ]);
            AnonymousCustomerBranch::create([
                'anonymous_customer_id' => $ac->id,
                'name'                  => "page-test-{$i}@example.com",
                'email'                 => "page-test-{$i}@example.com",
                'phone'                 => $phone,
                'address'               => $address,
                'branch_id'             => $branchId,
                'created_by'            => $createdBy,
            ]);
        }

        // ----- 4. Single match (search "solo") -----
        $acSolo = AnonymousCustomer::create(['cr_number' => 'CR-SOLO', 'vat_number' => 'VAT-SOLO']);
        AnonymousCustomerBranch::create([
            'anonymous_customer_id' => $acSolo->id,
            'name'                  => 'Solo Customer Inc',
            'email'                 => 'solo@test.com',
            'phone'                 => $phone,
            'address'               => $address,
            'branch_id'             => $branchId,
            'created_by'            => $createdBy,
        ]);

    }
}
