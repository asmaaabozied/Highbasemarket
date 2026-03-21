<?php

namespace Feature\Http\Auth;

use App\Models\Account;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated(): void
    {
        $account  = Account::factory()->create();
        $employee = Employee::factory()->create(['account_id' => $account->id]);
        $user     = User::factory()->create([
            'userable_id'   => $employee->id,
            'userable_type' => Employee::class,
        ]);

        $this->actingAs($user);

        $response = $this->putJson('/user/profile-information', [
            'first_name'         => 'Test Name',
            'last_name'          => 'Test Name',
            'phone'              => ['code' => '+973', 'number' => '33333333'],
            'email'              => 'test@highbase.co',
            'whatsapp_confirmed' => true,
            'whatsapp_number'    => ['code' => '+973', 'number' => '33333333'],
            'job_position'       => 'Developer',
            'linkedin_profile'   => 'https://linkedin.com/in/test',
        ]);

        $response->assertSuccessful();

        $this->assertEquals('Test Name', $user->refresh()->first_name);
        $this->assertEquals('test@highbase.co', $user->email);
        $this->assertEquals('Developer', $user->userable->job_position);
    }
}
