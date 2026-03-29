<?php

namespace App\StepActions;

use App\Models\Action;
use App\Models\Step;

class StepReject implements ActionStrategyInterface
{
    public function update($step, $data): void
    {
        Action::query()->findOrFail($data['actionId'])
            ->update([
                'status' => Action::REJECTED,
            ]);
        $step->update([
            'status' => Step::REJECTED,
        ]);
    }
}
