<?php

namespace App\StepActions;

class ActionStrategyContext
{
    private readonly ActionStrategyInterface $actionStrategy;

    public function __construct(string $action)
    {
        $this->actionStrategy = match ($action) {
            'active'        => new StepActive,
            'reject'        => new StepReject,
            'actionTimer'   => new StepTimer,
            'shipping'      => new StepShipping,
            'actionConfirm' => new StepConfirm,
            default         => throw new \InvalidArgumentException('You must pass an action'),
        };
    }

    public function update($step, $data): void
    {
        $this->actionStrategy->update($step, $data);
    }
}
