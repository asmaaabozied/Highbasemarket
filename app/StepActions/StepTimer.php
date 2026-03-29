<?php

namespace App\StepActions;

use App\Models\Step;
use App\Services\StepService;

class StepTimer implements ActionStrategyInterface
{
    public function update($step, $data): void
    {
        $days = (new StepService)->time($step->updated_at, $step->form);

        if (round($days) <= 0) {
            $step->update([
                'status' => Step::COMPLETE,
            ]);

            $newStep = $step->progress->steps()->where('status', Step::INACTIVE)->first();
            $newStep?->update([
                'status' => Step::PENDING,
            ]);
        }
    }
}
