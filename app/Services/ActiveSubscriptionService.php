<?php

namespace App\Services;

use App\Enum\ModuleEnum;
use App\Models\Branch;
use App\Models\Plan;

class ActiveSubscriptionService
{
    private Plan $plan;

    public function __construct(private ?Branch $branch = null)
    {
        $this->branch = $branch ?? currentBranch();
    }

    /**
     * this method should be used when you want to set the branch,
     * don't use it if you already set the branch in the constructor.
     * It will override the current branch.
     */
    public function setBranch(Branch $branch): self
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function activePlan(string $type = 'global'): void
    {
        $this->plan = $this->branch->activePlan($type);

        if (! $this->plan) {
            throw new \Exception('No active plan found for the branch.');
        }
    }

    /**
     * @throws \Exception
     */
    public function localPlan(): self
    {
        $this->activePlan('local');

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function globalPlan(): self
    {
        $this->activePlan('global');

        return $this;
    }

    public function get(): Plan
    {
        return $this->plan;
    }

    public function getModule(ModuleEnum $module)
    {
        $plan_module = collect($this->plan->attributes)->firstWhere('name', $module->value);

        if (! $plan_module) {
            throw new \Exception("Module {$module->value} not found in the plan attributes.");
        }

        return collect($plan_module['attribute'] ?? [])->pluck('value', 'name');
    }

    public static function make(?Branch $branch = null): self
    {
        return new self($branch);
    }
}
