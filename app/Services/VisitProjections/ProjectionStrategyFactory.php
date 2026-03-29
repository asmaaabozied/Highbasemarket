<?php

namespace App\Services\VisitProjections;

use App\Enum\RecurrenceTypeEnum;
use App\Enum\ScheduleTypeEnum;
use App\Interfaces\ProjectionStrategy;
use App\Models\ScheduleVisit;
use InvalidArgumentException;

readonly class ProjectionStrategyFactory
{
    public function __construct(
        private OneTimeProjection $oneTime,
        private WeeklyProjection $weekly,
        private BiWeeklyProjection $biWeekly,
        private MonthlyProjection $monthly,
        private DurationProjection $duration
    ) {}

    public function make(ScheduleVisit $schedule): ProjectionStrategy
    {

        if ($schedule->schedule_type === ScheduleTypeEnum::ONE_TIME) {
            return $this->oneTime;
        }

        return match ($schedule->recurrence_type) {
            RecurrenceTypeEnum::WEEKLY   => $this->weekly,
            RecurrenceTypeEnum::BIWEEKLY => $this->biWeekly,
            RecurrenceTypeEnum::MONTHLY  => $this->monthly,
            RecurrenceTypeEnum::DURATION => $this->duration,
            default                      => throw new InvalidArgumentException("Unsupported recurrence type: {$schedule->recurrence_type}")
        };
    }
}
