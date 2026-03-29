<?php

namespace App\Services\VisitProjections;

use App\Models\ScheduleVisit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DurationProjection extends BaseProjectionStrategy
{
    public function project(ScheduleVisit $schedule, Carbon $start, Carbon $end): Collection
    {
        $intervalDays = (int) $schedule->recurrence_value;

        $scheduleStart = Carbon::parse($schedule->start_date)->startOfDay();

        $effectiveStart = $this->getEffectiveStart($start, $schedule);

        $daysOffset = $scheduleStart->diffInDays($start) % $intervalDays;
        $first      = $daysOffset === 0
            ? $effectiveStart->copy()
            : $effectiveStart->copy()->addDays($intervalDays - $daysOffset);

        $dates   = collect();
        $current = $first;

        while ($current->lte($end)) {
            $dates->push($current->copy());
            $current->addDays($intervalDays);
        }

        return $dates;
    }

    public function next(ScheduleVisit $schedule, Carbon $from): ?Carbon
    {
        // Number of days between each visit (e.g., every 7 days)
        $intervalDays = (int) $schedule->recurrence_value;

        // If the interval is not valid (e.g., 0 or negative), no future visit can be calculated
        if ($intervalDays <= 0) {
            return null;
        }

        // The date when this schedule originally started
        $scheduleStart = Carbon::parse($schedule->start_date)->startOfDay();

        // Calculate how many days have passed since the start of the schedule up to `$from`
        $daysSinceStart = $scheduleStart->diffInDays($from);

        // Find the remainder — how far `$from` is into the current interval cycle
        // Example: if interval = 7 days and 10 days passed → remainder = 3 (3 days into current cycle)
        $remainder = $daysSinceStart % $intervalDays;

        // Determine the next visit date
        // If `$from` is exactly at the start of a new cycle (remainder = 0),
        // then the next visit is today (`$from`).
        // Otherwise, move forward to the next interval boundary.
        return $remainder === 0
            ? $from->copy()->startOfDay()
            : $from->copy()->addDays($intervalDays - $remainder)->startOfDay();
    }
}
