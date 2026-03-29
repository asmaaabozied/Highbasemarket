<?php

namespace App\Services\VisitProjections;

use App\Models\ScheduleVisit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class WeeklyProjection extends BaseProjectionStrategy
{
    public function project(ScheduleVisit $schedule, Carbon $start, Carbon $end): Collection
    {

        $dayOfWeekIso = (int) $schedule->recurrence_value;

        if ($dayOfWeekIso < 0 || $dayOfWeekIso > 6) {
            throw new InvalidArgumentException('Day of week must be between 0 (Sunday) and 6 (Saturday).');
        }

        // Ensure we don't start before the schedule actually starts
        $effectiveStart = $this->getEffectiveStart($start, $schedule);

        $current = $effectiveStart->dayOfWeek === $dayOfWeekIso
            ? $effectiveStart->copy()
            : $effectiveStart->copy()->next($dayOfWeekIso);

        $dates = collect();
        while ($current->lte($end)) {
            $dates->push($current->copy());
            $current->addWeek();
        }

        return $dates;
    }

    public function next(ScheduleVisit $schedule, Carbon $from): ?Carbon
    {
        $dayOfWeekIso = (int) $schedule->recurrence_value;

        if ($dayOfWeekIso < 0 || $dayOfWeekIso > 6) {
            return null;
        }

        $candidate = $from->copy()->next($dayOfWeekIso);

        if (! $this->hasVisits($schedule, $from) && $from->dayOfWeek === $dayOfWeekIso) {
            $candidate = $from->copy();
        }

        return $candidate->startOfDay();

    }
}
