<?php

namespace App\StepActions;

use App\Models\Step;

class StepActive implements ActionStrategyInterface
{
    public function update($step, $data): void
    {
        $step->update([
            'status' => Step::PENDING,
        ]);
    }
}
