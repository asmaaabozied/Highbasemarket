<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Coupon::factory()->count(50)->create();
        $this->command->info('Created 50 dummy coupons successfully.');
    }
}
