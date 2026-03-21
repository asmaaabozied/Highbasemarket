<?php

use App\Jobs\GenerateVisitsJob;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use function Tests\Feature\createAccount;
use function Tests\Feature\createSchedule;

beforeEach(function () {
    [$this->account, $this->branch, $this->user] = createAccount();
    Carbon::setTestNow('2025-01-01 00:00:00');

    createSchedule([
        'start_date' => '2025-06-01',
        'end_date'   => '2025-06-30',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

dataset('june 2025 dates', function () {
    return collect(CarbonPeriod::create('2025-06-01', '2025-06-30'))
        ->map(fn ($date) => [$date->toDateString()])
        ->all();
});

it('runs GenerateDailyVisits for each day via scheduler', function (string $date) {
    Bus::fake();

    // Set "now" to 12:01 AM of this date
    Carbon::setTestNow(Carbon::parse($date)->startOfDay()->addMinute());

    Artisan::call('visits:generate-daily');

    Bus::assertDispatched(GenerateVisitsJob::class, function ($job) use ($date) {
        return $job->date === $date;
    });
})->with('june 2025 dates');

it('does not run before the scheduled time', function (string $date) {
    Bus::fake();

    // Simulate "now" at midnight (00:00) instead of 00:01
    Carbon::setTestNow(Carbon::parse($date)->startOfDay());

    Artisan::call('schedule:run');

    Bus::assertNotDispatched(GenerateVisitsJob::class);
})->with('june 2025 dates');

it('does not run after the scheduled time window', function (string $date) {
    Bus::fake();

    // Simulate "now" late at night (23:59)
    Carbon::setTestNow(Carbon::parse($date)->endOfDay()->subMinute());

    Artisan::call('schedule:run');

    Bus::assertNotDispatched(GenerateVisitsJob::class);
})->with('june 2025 dates');
