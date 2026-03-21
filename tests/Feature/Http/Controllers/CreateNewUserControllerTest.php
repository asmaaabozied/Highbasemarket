<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

it('can create new user', function (array $data) {
    test()->withoutMiddleware(ValidateCsrfToken::class);

    $response = $this->post(route('register'), $data);
    $response->assertStatus(302);
    $response->assertSessionDoesntHaveErrors();
})
    ->with([
        'factory' => [
            [
                'name'                  => 'X-test no.1',
                'email'                 => 'test@app.com',
                'type'                  => 'factory',
                'first_name'            => 'test-user-case-1',
                'last_name'             => 'test-user-case-1',
                'phone'                 => ['code' => '123', 'number' => '123'],
                'password'              => 'password',
                'password_confirmation' => 'password',
                'terms'                 => true,
            ],
        ],
        'distributer' => [
            [
                'name'                  => 'X-test no.3',
                'email'                 => 'test@app.com',
                'type'                  => 'distributer',
                'first_name'            => 'test-user-case-2',
                'last_name'             => 'test-user-case-2',
                'phone'                 => ['code' => '123', 'number' => '123'],
                'password'              => 'password',
                'password_confirmation' => 'password',
                'terms'                 => true,
            ],
        ],
        'local' => [
            [
                'name'                  => 'X-test no.3',
                'email'                 => 'test@app.com',
                'type'                  => 'local',
                'first_name'            => 'test-user-case-3',
                'last_name'             => 'test-user-case-3',
                'phone'                 => ['code' => '123', 'number' => '123'],
                'password'              => 'password',
                'password_confirmation' => 'password',
                'terms'                 => true,
            ],
        ],
    ]);
