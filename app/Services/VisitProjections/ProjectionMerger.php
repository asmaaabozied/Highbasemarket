<?php

namespace App\Services\VisitProjections;

use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitOverride;
use App\Models\ScheduleVisit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectionMerger
{
    public function merge(ScheduleVisit $schedule, Collection $projectedDates, Carbon $start, Carbon $end): Collection
    {
        $existingVisits = $this->getExistingVisits($schedule, $start, $end);
        $overrides      = $this->getOverrides($schedule, $start, $end);
        $merged         = collect();

        foreach ($projectedDates as $date) {
            $dateStr = $date->toDateString();

            // Include existing visits for this projected date if they exist
            if ($existingVisits->has($dateStr)) {
                $merged = $merged->concat($existingVisits[$dateStr]);
            }

            // Include overrides for this projected date if they exist
            if ($overrides->has($dateStr)) {
                $merged = $merged->concat(
                    $overrides[$dateStr]->map(fn (\App\Models\EmployeeVisitOverride $override): \App\Models\EmployeeVisit => EmployeeVisit::fromOverride($schedule, $override))
                );
            }

            // If no existing visits or overrides, create a "projected visit"
            if (! $existingVisits->has($dateStr) && ! $overrides->has($dateStr)) {
                $merged->push(EmployeeVisit::fromVirtualSchedule($schedule, $date));
            }
        }

        // Include any extra visits or overrides that are outside projected dates
        $projectedDateStrings = $projectedDates->map(fn ($d) => $d->toDateString())->all();

        $extraVisits = $existingVisits
            ->reject(fn ($_, $dateStr): bool => in_array($dateStr, $projectedDateStrings, true))
            ->collapse();

        $extraOverrides = $overrides->filter(fn ($group, $dateStr): bool => ! in_array($dateStr, $projectedDateStrings, true));

        $transformedOverrides = $extraOverrides->map(fn ($group) => $group->map(fn (\App\Models\EmployeeVisitOverride $override): \App\Models\EmployeeVisit => EmployeeVisit::fromOverride($schedule, $override)))->collapse();

        return $merged->concat($extraVisits)
            ->concat($transformedOverrides);
    }

    private function getExistingVisits(ScheduleVisit $schedule, Carbon $start, Carbon $end): Collection
    {
        return EmployeeVisit::query()
            ->with(['employee.user', 'visitable', 'schedule', 'comments.employee.user'])
            ->where(function ($q) use ($schedule): void {
                $q->where('schedule_visit_id', $schedule->id)
                    ->orWhereNull('schedule_visit_id');
            })
            ->whereBetween('scheduled_at', [$start, $end])
            ->get()
            ->groupBy(fn (EmployeeVisit $visit) => $visit->scheduled_at->toDateString());
    }

    private function getOverrides(ScheduleVisit $schedule, Carbon $start, Carbon $end): Collection
    {
        return EmployeeVisitOverride::query()
            ->where('schedule_visit_id', $schedule->id)
            ->whereBetween('visit_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (EmployeeVisitOverride $visit) => $visit->visit_date->toDateString());
    }
}
