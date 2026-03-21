<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class AdminWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (Wallet::query()->where('walletable_type', Admin::class)->exists()) {
            return;
        }
        $admin  = User::query()->where('userable_type', Admin::class)->pluck('id');
        $wallet = Wallet::query()->create([
            'balance'         => 0,
            'currency'        => 'USD',
            'walletable_type' => Admin::class,
        ]);
        $wallet->admins()->sync($admin);
    }
}
