<?php

use App\Enum\RecurrenceTypeEnum;
use App\Enum\VisitPurposeTypeEnum;
use App\Models\Admin;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ScheduleVisit;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Tests\Feature\Http\Controllers\Account\Stock\Helpers\createAccount;

beforeEach(function () {
    [$this->account, $this->branch, $this->user] = createAccount();
    setCurrentBranch($this->branch);
});
function setCurrentBranch(Branch $branch): void
{
    app()->instance('currentBranch', $branch);
}

it('allows global admin to view all schedule visits across branches', function () {

    $branch1 = Branch::factory()->create();
    $branch2 = Branch::factory()->create();

    $schedule1 = ScheduleVisit::factory(['branch_id' => $this->branch->id])->for($branch1, 'customer')->create();
    $schedule2 = ScheduleVisit::factory(['branch_id' => $this->branch->id])->for($branch2, 'customer')->create();

    $response = actingAs($this->user)
        ->get(route('account.schedule-visits.index'));
    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('scheduleVisits.data', 2)
        );
});

it('allows branch manager to view only schedules in their own branch', function () {
    $branch      = Branch::factory()->create();
    $otherBranch = Branch::factory()->create();

    $scheduleInBranch      = ScheduleVisit::factory(['branch_id' => $this->branch->id])->for($branch, 'customer')->create();
    $scheduleInOtherBranch = ScheduleVisit::factory(['branch_id' => $otherBranch->id])->for($otherBranch,
        'customer')->create();

    $response = actingAs($this->user)
        ->get(route('account.schedule-visits.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('scheduleVisits.data', 1)
            ->where('scheduleVisits.data.0.id', $scheduleInBranch->id)
        );
});

it('allows regular employee to view only their own schedules', function () {
    $branch = Branch::factory()->create();

    $employeeUser = User::factory()
        ->for(Employee::factory()->state(['account_id' => $branch->id]), 'userable')
        ->create();

    $otherEmployee = Employee::factory()->for($branch, 'account')->create();

    $ownSchedule = ScheduleVisit::factory(['branch_id' => $this->branch->id])
        ->for($employeeUser->userable, 'employee')
        ->for($branch, 'customer')
        ->create();

    $otherSchedule = ScheduleVisit::factory(['branch_id' => $branch->id])
        ->for($otherEmployee, 'employee')
        ->for($branch, 'customer')
        ->create();

    actingAs($this->user)
        ->get(route('account.schedule-visits.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('scheduleVisits.data', 1)
            ->where('scheduleVisits.data.0.id', $ownSchedule->id)
        );
});

it('returns stats and metadata on index page', function () {
    $admin  = User::factory()->for(Employee::factory(), 'userable')->create();
    $branch = Branch::factory()->create();

    $employee = Employee::factory()->for($branch, 'account')->create();
    $customer = Branch::factory()->state([
        'address' => ['country' => 'US', 'city' => 'New York'],
    ])->create();

    $branch->customers()->attach($customer)
        ->for($employee)
        ->for($customer, 'customer')
        ->create(['branch_id' => $this->branch->id]);

    actingAs($admin)
        ->get(route('account.schedule-visits.index'))
        ->assertInertia(function (AssertableInertia $page) use ($employee, $customer) {
            $page->has('stats')
                ->has('employees')
                ->has('customers')
                ->has('visitTypes');

            // Check stats format
            expect($page['stats']['scheduled_employees'])->toMatch('/1\/1/')
                ->and($page['stats']['scheduled_customers'])->toMatch('/1\/1/')
                ->and($page['employees'])->toHaveCount(1)
                ->and($page['employees'][0]['value'])->toBe($employee->id)
                ->and($page['customers'])->toHaveCount(1)
                ->and($page['customers'][0]['value'])->toBe($customer->id)
                ->and($page['customers'][0]['city'])->toBe('New York');

            // Check metadata includes our employee and customer

        });
});

it('creates a new schedule visit successfully', function () {
    $branch = Branch::factory()->create();
    $admin  = User::factory()->for(Admin::factory())->create();

    $employee = Employee::factory()->for($branch, 'account')->create();
    $customer = Branch::factory()->create();
    $branch->customers()->attach($customer);

    $data = [
        'employee_id'      => $employee->id,
        'customer_id'      => $customer->id,
        'recurrence_type'  => RecurrenceTypeEnum::WEEKLY->value,
        'recurrence_value' => 1,
        'purpose'          => VisitPurposeTypeEnum::ORDER_PICKUP_RETURN->value,
    ];

    actingAs($admin)
        ->post(route('account.schedule-visits.store'), $data)
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('schedule_visits', [
        'employee_id'     => $employee->id,
        'customer_id'     => $customer->id,
        'branch_id'       => $branch->id,
        'recurrence_type' => RecurrenceTypeEnum::WEEKLY->value,
        'created_by'      => $admin->id,
    ]);
});

it('prevents creation of duplicate schedule for same employee/customer/recurrence', function () {
    $branch = Branch::factory()->create();
    $admin  = User::factory()->for(Admin::factory())->create();

    $employee = Employee::factory()->for($branch, 'account')->create();
    $customer = Branch::factory()->create();
    $branch->customers()->attach($customer);

    ScheduleVisit::factory()
        ->for($employee, 'employee')
        ->for($customer, 'customer')
        ->create([
            'branch_id'        => $this->branch->id,
            'recurrence_type'  => RecurrenceTypeEnum::WEEKLY,
            'recurrence_value' => 1,
        ]);

    $data = [
        'employee_id'      => $employee->id,
        'customer_id'      => $customer->id,
        'recurrence_type'  => RecurrenceTypeEnum::WEEKLY->value,
        'recurrence_value' => 1,
        'purpose'          => VisitPurposeTypeEnum::INVENTORY_CHECK_STOCK_AUDIT->value,
    ];

    actingAs($admin)
        ->post(route('account.schedule-visits.store'), $data)
        ->assertSessionHasErrors(['schedule']);
});

it('prevents supervisor from assigning schedule to a manager', function () {
    $branch = Branch::factory()->create();

    $supervisor = User::factory()
        ->for(Employee::factory()->state([
            'job_title'  => 'supervisor',
            'account_id' => $branch->id,
        ]), 'userable')
        ->create();

    $manager = Employee::factory()->state(['job_title' => 'manager'])->create();

    $customer = Branch::factory()->create();
    $branch->customers()->attach($customer);

    $data = [
        'employee_id'      => $manager->id,
        'customer_id'      => $customer->id,
        'recurrence_type'  => RecurrenceTypeEnum::WEEKLY->value,
        'recurrence_value' => 1,
    ];

    actingAs($supervisor)
        ->post(route('account.schedule-visits.store'), $data)
        ->assertSessionHasErrors(['employee']);
});

it('updates an existing schedule visit', function () {
    $branch = Branch::factory()->create();
    $admin  = User::factory()->for(Admin::factory())->create();

    $schedule = ScheduleVisit::factory()
        ->create(['branch_id' => $this->branch->id, 'purpose' => VisitPurposeTypeEnum::ORDER_PICKUP_RETURN->value]);

    $newPurpose = VisitPurposeTypeEnum::CUSTOMER_SUPPORT_SERVICE->value;

    actingAs($admin)
        ->put(route('account.schedule-visits.update', $schedule), [
            'employee_id'      => $schedule->employee_id,
            'customer_id'      => $schedule->customer_id,
            'recurrence_type'  => $schedule->recurrence_type,
            'recurrence_value' => $schedule->recurrence_value,
            'purpose'          => $newPurpose,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('schedule_visits', [
        'id'      => $schedule->id,
        'purpose' => $newPurpose,
    ]);
});

it('deletes a schedule visit', function () {
    $branch = Branch::factory()->create();
    $admin  = User::factory()->for(Admin::factory())->create();

    $schedule = ScheduleVisit::factory()->create(['branch_id' => $this->branch->id]);

    actingAs($admin)
        ->delete(route('account.schedule-visits.destroy', $schedule))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertModelMissing($schedule);
});
