<?php

namespace App\Services\VisitProjections;

use App\Models\ScheduleVisit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BiWeeklyProjection extends BaseProjectionStrategy
{
    public function project(ScheduleVisit $schedule, Carbon $start, Carbon $end): Collection
    {
        $dayOfWeekIso = (int) $schedule->recurrence_value;

        $effectiveStart = $this->getEffectiveStart($start, $schedule);

        $current = $effectiveStart->dayOfWeek === $dayOfWeekIso
            ? $effectiveStart->copy()
            : $effectiveStart->copy()->next($dayOfWeekIso);

        $dates = collect();
        while ($current->lte($end)) {
            $dates->push($current->copy());
            $current->addWeeks(2);
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

        // Snap to biweekly: ensure candidate is aligned with schedule start
        $scheduleStart = Carbon::parse($schedule->start_date)->startOfDay();
        while ($scheduleStart->diffInWeeks($candidate) % 2 !== 0) {
            $candidate->addWeeks(2);
        }

        return $candidate->startOfDay();

    }
}
