<?php

namespace Feature\Http\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_registration_screen_cannot_be_rendered_if_support_is_disabled(): void
    {
        if (\Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::registration())) {
            $this->markTestSkipped('Registration support is enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test Company',
            'first_name'            => 'Test',
            'last_name'             => 'User',
            'email'                 => 'test@highbase.co',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'phone'                 => ['code' => '+973', 'phone' => '333333333'],
            'type'                  => 'factory',
            'terms'                 => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }
}
