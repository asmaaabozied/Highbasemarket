<?php

namespace App\Services;

use Carbon\Carbon;

class StepService
{
    public function time(\DateTimeInterface|\Carbon\WeekDay|\Carbon\Month|string|int|float|null $date, array $days): float|int
    {
        if (isset($days['timer'])) {
            $currentDate = Carbon::now();
            $targetDate  = Carbon::parse($date)->addDays(intval($days['timer']));
            $numberDays  = $currentDate->diffInDays($targetDate);
        }

        return $numberDays ?? 0;
    }
}
