<?php

namespace App\StepActions;

interface ActionStrategyInterface
{
    public function update($step, $data): void;
}
