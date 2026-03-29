<?php

namespace App\Services\VisitProjections;

use App\Models\ScheduleVisit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OneTimeProjection extends BaseProjectionStrategy
{
    public function project(ScheduleVisit $schedule, Carbon $start, Carbon $end): Collection
    {
        $date = $schedule->one_time_date ? Carbon::parse($schedule->one_time_date)->startOfDay() : null;

        if ($date && $date->between($start, $end)) {
            return collect([$date]);
        }

        return collect();
    }

    public function next(ScheduleVisit $schedule, Carbon $from): ?Carbon
    {
        $date = $schedule->one_time_date ? Carbon::parse($schedule->one_time_date)->startOfDay() : null;

        return ($date && $date->gte($from->startOfDay())) ? $date : null;
    }
}
