<?php

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

describe('accessing dashboard as admin', function () {
    it('should return the admin dashboard', function () {
        $admin = Admin::factory()->create(['position' => 'administrator', 'status' => 'active']);

        $user = $admin->user()->create([
            'first_name'        => 'Admin',
            'last_name'         => 'administrator',
            'email'             => 'admin@admin.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard/Dashboard')
            ->has('invitationsCount')
            ->has('invitationsStats')
            ->has('invitations')
            ->has('admins')
        );
    });
});
