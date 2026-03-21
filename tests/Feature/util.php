<?php

namespace Tests\Feature;

use App\Enum\RecurrenceTypeEnum;
use App\Enum\SourceTypeEnum;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeVisit;
use App\Models\Permission;
use App\Models\ScheduleVisit;
use App\Models\User;

function createAccount($job_title = 'administrator'): array
{
    $account = Account::factory()->create();
    $branch  = $account->branches()->first();

    $employee = Employee::factory()->create([
        'account_id' => $account->id,
        'job_title'  => $job_title,
    ]);

    $user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    return [$account, $branch, $user];
}

function addPermissions(User $user, $permissions = [], $type = 'account', $module = ''): void
{
    $role = $user->getAccount()->roles()->create([
        'name' => fake()->name,
        'type' => $type,
    ]);

    Permission::insert(
        collect($permissions)->map(function ($permission) use ($module) {
            return [
                'name'   => $permission,
                'module' => $module,
                'for'    => ' ',
            ];
        })->toArray()
    );

    $role->permissions()->sync(Permission::all());

    $user->roles()->attach($role->id);
}

function addAddressToBranch(Branch $branch): void
{
    $branch->update([
        'address' => [
            'country'     => 5,
            'city'        => 'Nairobi',
            'postal_code' => '00100',
            'street'      => 'Test Street',
            'building'    => 'Test Building',
        ],
    ]);
}

function createSchedule(array $overrides = [])
{
    $data = [
        'recurrence_type'  => RecurrenceTypeEnum::WEEKLY,
        'recurrence_value' => 1,
        'start_date'       => '2025-01-01',
        'employee_id'      => 1,
        'customer_id'      => 1,
    ];
    $raw = array_merge($data, $overrides);

    return ScheduleVisit::factory()->create($raw);
}

function createExistingVisit(int $scheduleId, string $date)
{
    return EmployeeVisit::factory()->create([
        'schedule_visit_id' => $scheduleId,
        'scheduled_at'      => $date,
        'source_type'       => SourceTypeEnum::SCHEDULE,
    ]);
}
