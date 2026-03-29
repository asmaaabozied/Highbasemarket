<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Plan;

class PricingService
{
    public function getSubscriptionByType($plan_type, ?Branch $branch = null): \Illuminate\Database\Eloquent\Collection
    {
        $countryId = currentBranch()->address['country'] ?? null;

        $plans = Plan::query()
            ->where(function ($q) use ($countryId): void {
                $q->whereJsonLength('countries', 0)
                    ->orWhereJsonContains('countries', (int) $countryId);
            })
            ->where('plan_type', $plan_type)->get();

        if ($branch instanceof \App\Models\Branch) {
            $activePlan = $branch->activePlan($plan_type);

            $plans->transform(function ($plan) use ($activePlan): \App\Models\Plan {
                $plan->subscribed_to = $activePlan && $activePlan->id === $plan->id;

                return $plan;
            });
        }

        return $plans;
    }

    public function getMySubscriptionByType($plan_type)
    {
        $plans = currentBranch()
            ->plans()
            ->withPivot(['attributes'])
            ->where('plan_type', $plan_type)
            ->get();

        $plans->transform(function ($plan): \Illuminate\Database\Eloquent\Model {
            $plan->subscribed_to = true;

            return $plan;
        });

        return $plans;
    }
}
