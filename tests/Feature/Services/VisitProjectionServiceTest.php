<?php

use App\Enum\RecurrenceTypeEnum;
use App\Services\VisitProjectionService;
use Carbon\Carbon;
use Carbon\CarbonInterface;

use function Tests\Feature\createAccount;
use function Tests\Feature\createExistingVisit;
use function Tests\Feature\createSchedule;

beforeEach(function () {
    [$this->account, $this->branch, $this->user] = createAccount();
    Carbon::setTestNow('2025-01-01 00:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

it('generates monthly visits with catch-up to next month when day does not exist', function () {
    $schedule = createSchedule([
        'recurrence_type'  => RecurrenceTypeEnum::MONTHLY,
        'recurrence_value' => 29,
        'employee_id'      => $this->user->userable_id,
        'customer_id'      => $this->branch->id,
    ]);
    $service = new VisitProjectionService;

    $result = $service->getProjectedVisitsForSchedule(
        $schedule->id,
        '2025-01-01', // non-leap year
        '2025-04-30'
    );

    $dates = $result->pluck('date')->map->toDateString()->toArray();
    expect($dates)->toBe([
        '2025-01-29',
        '2025-03-01', // Feb 29 → catch-up to Mar 1
        '2025-03-29', // March 29 → two in March
        '2025-04-29',
    ]);
});
it('marks visits as existing when found in database', function () {
    $schedule = createSchedule([
        'recurrence_type'  => RecurrenceTypeEnum::WEEKLY,
        'recurrence_value' => CarbonInterface::MONDAY, // ISO 1
    ]);

    // Create an existing visit on 2025-01-06 (Monday)
    createExistingVisit($schedule->id, '2025-01-06 09:00:00');

    $service = new VisitProjectionService;

    $result = $service->getProjectedVisitsForSchedule(
        $schedule->id,
        '2025-01-01',
        '2025-01-15'
    );
    $visit = $result->first(function ($visit) {
        return $visit['date']->toDateString() === '2025-01-06';
    });

    expect($visit)
        ->toHaveKey('is_generated', true)
        ->and($visit)->toHaveKey('visit_schedule_id')
        ->and($visit['visit_schedule_id'])->toBeGreaterThan(0);
});

it('generates correct weekly visits', function () {
    $schedule = createSchedule([
        'recurrence_type'  => RecurrenceTypeEnum::WEEKLY,
        'recurrence_value' => CarbonInterface::TUESDAY, // ISO 2
    ]);

    $service = new VisitProjectionService;

    $result = $service->getProjectedVisitsForSchedule(
        $schedule->id,
        '2025-01-01',
        '2025-01-31'
    );

    $dates = $result->pluck('date')->map->toDateString()->toArray();

    expect($dates)->toBe([
        '2025-01-07',
        '2025-01-14',
        '2025-01-21',
        '2025-01-28',
    ]);
});
it('generates correct biweekly visits', function () {
    $schedule = createSchedule([
        'recurrence_type'  => RecurrenceTypeEnum::BIWEEKLY,
        'recurrence_value' => CarbonInterface::FRIDAY, // ISO 5
    ]);

    $service = new VisitProjectionService;

    $result = $service->getProjectedVisitsForSchedule(
        $schedule->id,
        '2025-01-01',
        '2025-02-28'
    );

    $dates = $result->pluck('date')->map->toDateString()->toArray();

    expect($dates)->toBe([
        '2025-01-03',
        '2025-01-17',
        '2025-01-31',
        '2025-02-14',
        '2025-02-28',
    ]);
});
it('generates duration-based visits aligned to schedule start date', function () {
    $schedule = createSchedule([
        'recurrence_type'  => RecurrenceTypeEnum::DURATION,
        'recurrence_value' => 5, // every 5 days
        'start_date'       => '2025-01-02',
    ]);

    $service = new VisitProjectionService;

    $result = $service->getProjectedVisitsForSchedule(
        $schedule->id,
        '2025-01-01',
        '2025-01-20'
    );

    $dates = $result->pluck('date')->map->toDateString()->toArray();

    expect($dates)->toBe([
        '2025-01-07',
        '2025-01-12',
        '2025-01-17',
    ]);
});
it('throws exception for invalid day of month', function () {
    $schedule = createSchedule([
        'recurrence_type'  => RecurrenceTypeEnum::MONTHLY,
        'recurrence_value' => 32, // invalid
    ]);

    $service = new VisitProjectionService;

    $this->expectException(InvalidArgumentException::class);

    $service->getProjectedVisitsForSchedule(
        $schedule->id,
        '2025-01-01',
        '2025-01-31'
    );
});
it('throws exception if start date is after end date', function () {
    $schedule = createSchedule();

    $service = new VisitProjectionService;

    $this->expectException(InvalidArgumentException::class);

    $service->getProjectedVisitsForSchedule(
        $schedule->id,
        '2025-02-01',
        '2025-01-01'
    );
});
