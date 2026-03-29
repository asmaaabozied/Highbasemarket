<?php

namespace App\StepActions;

class StepShipping implements ActionStrategyInterface
{
    public function update($step, $data): void
    {
        $step->update([
            'form' => $data['shipping'],
        ]);
    }
}
