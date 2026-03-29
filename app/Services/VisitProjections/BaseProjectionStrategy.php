<?php

namespace App\Services\VisitProjections;

use App\Interfaces\ProjectionStrategy;
use App\Models\ScheduleVisit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BaseProjectionStrategy implements ProjectionStrategy
{
    public function project(ScheduleVisit $schedule, Carbon $start, Carbon $end): Collection
    {
        return collect([]);
    }

    public function next(ScheduleVisit $schedule, Carbon $from): ?Carbon
    {
        return now();
    }

    public function hasVisits(ScheduleVisit $schedule, Carbon $from): bool
    {
        return (bool) $schedule->visits->first();
    }

    protected function getEffectiveStart(Carbon $start, ScheduleVisit $schedule): Carbon
    {
        if (! empty($schedule->start_date)) {
            return $start->copy()->max(Carbon::parse($schedule->start_date));
        }

        return $start->copy();
    }
}
