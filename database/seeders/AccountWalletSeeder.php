<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class AccountWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = Account::query()
            ->withCount('employees')
            ->where('status', 'active')->get();

        foreach ($accounts as $account) {
            if ($account->employees_count > 0 && ! $account->wallet->exists()) {
                Wallet::query()->create([
                    'walletable_id'   => $account->id,
                    'walletable_type' => Account::class,
                    'balance'         => 0,
                    'currency'        => 'USD',
                ]);
            }
        }
    }
}
