<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::factory()->create(['position' => 'administrator', 'status' => 'active']);

        $admin->user()->create([
            'first_name'        => 'Admin',
            'last_name'         => 'administrator',
            'email'             => 'admin@admin.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}
