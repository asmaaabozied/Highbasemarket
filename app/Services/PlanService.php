<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Plan;

class PlanService
{
    public function __construct(private readonly Branch $branch) {}

    public static function assignPlan(Branch $branch, Plan $plan): void
    {
        (new self($branch))->detachOldPlan($plan);

        $currentPlan = $branch->activePlan($plan->plan_type);

        if (! $currentPlan instanceof \App\Models\Plan) {
            $branch->plans()->attach($plan);
        }
    }

    public function detachOldPlan(Plan $plan): void
    {
        $currentPlan = $this->branch->activePlan($plan->plan_type);

        if ($currentPlan && $currentPlan->id !== $plan->id) {
            $this->branch->plans()->detach($currentPlan);
        }
    }
}
