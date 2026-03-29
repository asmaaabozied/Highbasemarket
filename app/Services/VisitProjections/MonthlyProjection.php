<?php

namespace App\Services\VisitProjections;

use App\Models\ScheduleVisit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class MonthlyProjection extends BaseProjectionStrategy
{
    public function project(ScheduleVisit $schedule, Carbon $start, Carbon $end): Collection
    {
        $dayOfMonth = (int) $schedule->recurrence_value;

        if ($dayOfMonth < 1 || $dayOfMonth > 31) {
            throw new InvalidArgumentException('Day of month must be between 1 and 31.');
        }

        $dates        = collect();
        $currentMonth = $start->copy()->startOfMonth();

        while ($currentMonth->lte($end)) {
            $candidate = $currentMonth->copy()->day($dayOfMonth);

            if ($candidate->lt($start) || $candidate->month !== $currentMonth->month || $candidate->day !== $dayOfMonth) {
                $currentMonth->addMonthNoOverflow();
                continue;
            }

            if ($candidate->lte($end)) {
                $dates->push($candidate->startOfDay());
            }

            $currentMonth->addMonthNoOverflow();
        }

        return $dates;
    }

    public function next(ScheduleVisit $schedule, Carbon $from): ?Carbon
    {
        $dayOfMonth = (int) $schedule->recurrence_value;

        if ($dayOfMonth < 1 || $dayOfMonth > 31) {
            return null;
        }

        $candidate = $from->copy()->day($dayOfMonth);

        if (! $this->hasVisits($schedule, $from) && $candidate->lt($from) || $candidate->day !== $dayOfMonth) {
            $candidate = $from->copy()->addMonthNoOverflow()->day($dayOfMonth);
        }

        return $candidate->startOfDay();

    }
}
