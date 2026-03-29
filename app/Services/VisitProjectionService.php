<?php

namespace App\Services;

use App\Dto\ChangeVisitDateDto;
use App\Dto\PostponeVisitDto;
use App\Enum\EmployeeVisitStatusEnum;
use App\Events\VisitDateChanged;
use App\Models\AnonymousCustomer;
use App\Models\Branch;
use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitOverride;
use App\Models\ScheduleVisit;
use App\Models\Visit;
use App\Services\VisitProjections\ProjectionMerger;
use App\Services\VisitProjections\ProjectionStrategyFactory;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

readonly class VisitProjectionService
{
    public function __construct(
        private ProjectionStrategyFactory $factory,
        private ProjectionMerger $merger
    ) {}

    public function getNextVisitDate(
        ScheduleVisit $schedule,
        ?Carbon $fromDate = null,
        ?EmployeeVisit $visit = null
    ): ?Carbon {

        $baseDate = $this->resolveBaseDate($schedule, $fromDate);

        if ($visit && $visit->exists() && $visit->nextSchedule) {
            return $visit->nextSchedule->scheduled_at->startOfDay();
        }

        $override = $this->getFutureOverride($schedule);

        if ($override instanceof \App\Models\EmployeeVisitOverride) {
            return Carbon::parse($override->visit_date)->startOfDay();
        }

        $strategy = $this->factory->make($schedule);

        return $strategy->next($schedule, $baseDate);
    }

    private function resolveBaseDate(ScheduleVisit $schedule, ?Carbon $fromDate = null): Carbon
    {
        $candidates = array_filter([
            $fromDate?->copy()->startOfDay(),
            $this->getLatestVisitDate($schedule),
            $schedule->start_date ? Carbon::parse($schedule->start_date)->startOfDay() : null,
        ]);

        return collect($candidates)->max() ?? Carbon::today();
    }

    private function getLatestVisitDate(ScheduleVisit $schedule): ?Carbon
    {
        $date = $schedule->visits()
            ->latest('scheduled_at')
            ->value('scheduled_at');

        return $date ? Carbon::parse($date)->startOfDay() : null;
    }

    private function getFutureOverride(ScheduleVisit $schedule): ?EmployeeVisitOverride
    {
        return $schedule->overrides->first();
    }

    /**
     * Apply a postponement for a future (projected) visit.
     * This marks the existing override as POSTPONED and
     * creates a new override entry for the postponed date with SCHEDULED status.
     *
     * Example:
     * - Old date: 2025-10-05 (POSTPONED)
     * - New date: 2025-10-10 (SCHEDULED)
     */
    public function applyPostponeOverride(EmployeeVisit $visit, PostponeVisitDto $dto): void
    {
        try {
            DB::transaction(function () use ($visit, $dto): void {
                // Mark the old override as POSTPONED
                $parent = EmployeeVisitOverride::updateOrCreate(
                    [
                        'schedule_visit_id' => $visit->schedule_visit_id,
                        'visit_date'        => $visit->scheduled_at->toDateString(),
                    ],
                    [
                        'status'          => EmployeeVisitStatusEnum::POSTPONED,
                        'postpone_reason' => $dto->reason,
                        'postpone_notes'  => $dto->notes,
                        'modified_by'     => $dto->user->id,
                    ]
                );

                // Create a new scheduled override for the postponed date
                EmployeeVisitOverride::create([
                    'schedule_visit_id' => $visit->schedule_visit_id,
                    'parent_visit_id'   => $parent?->id,
                    'visit_date'        => $visit->scheduled_at->copy()->addDay()->toDateString(),
                    'status'            => EmployeeVisitStatusEnum::SCHEDULED,
                    'modified_by'       => $dto->user->id,
                ]);
            });
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

    }

    public function getProjectedVisitsForSchedule(int $scheduleId, string $startDate, string $endDate): Collection
    {
        $schedule = ScheduleVisit::query()
            ->with([
                'employee',
                'visitable' => function ($morph): void {
                    $morph->morphWith([
                        Branch::class            => ['addresses'],
                        AnonymousCustomer::class => [],
                    ]);
                },
            ])
            ->findOrFail($scheduleId);

        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        if ($start->gt($end)) {
            throw new InvalidArgumentException('Start date must be before or equal to end date.');
        }

        // Use factory to get the correct strategy
        $strategy = $this->factory->make($schedule);

        // Project using strategy
        $projected = $strategy->project($schedule, $start, $end);

        return $this->merger->merge($schedule, $projected, $start, $end);

    }

    /**
     * Apply a date change for a future (projected) visit.
     * This updates the existing override for the old date (sets it as DATE_CHANGED)
     * and creates a new scheduled override for the new date.
     *
     * Example:
     * - Old date: 2025-10-05 (DATE_CHANGED)
     * - New date: 2025-10-07 (SCHEDULED)
     */
    public function rescheduleFutureVisit(EmployeeVisit $visit, ChangeVisitDateDto $dto): EmployeeVisit
    {
        $currentDate = Carbon::parse($dto->currentDate)->toDateString();
        $newDate     = Carbon::parse($dto->newDate)->toDateString();

        // 1. Find existing override for the current date
        $existingOverride = EmployeeVisitOverride::query()
            ->where('schedule_visit_id', $visit->schedule_visit_id)
            ->whereDate('visit_date', $currentDate)
            ->first();

        // 2. Update or create the current override as DATE_CHANGED
        if ($existingOverride) {
            $existingOverride->update([
                'status'          => EmployeeVisitStatusEnum::DATE_CHANGED,
                'postpone_reason' => $dto->reason,
                'notes'           => $dto->notes,
                'modified_by'     => $dto->user->id,
            ]);
        } else {
            $existingOverride = EmployeeVisitOverride::create([
                'schedule_visit_id' => $visit->schedule_visit_id,
                'visit_date'        => $currentDate,
                'status'            => EmployeeVisitStatusEnum::DATE_CHANGED,
                'modified_by'       => $dto->user->id,
                'postpone_reason'   => $dto->reason,
                'notes'             => $dto->notes,
            ]);
        }

        // 3. Create new scheduled override for new date
        EmployeeVisitOverride::create([
            'schedule_visit_id' => $visit->schedule_visit_id,
            'visit_date'        => $newDate,
            'status'            => EmployeeVisitStatusEnum::SCHEDULED,
            'parent_visit_id'   => $existingOverride->id,
            'modified_by'       => $dto->user->id,
        ]);
        event(new VisitDateChanged($visit, $currentDate, $newDate));

        return $visit;
    }
}
