<?php

namespace App\Services;

use App\Enum\SubscriptionCost;
use App\Enum\SubscriptionPayment;
use App\Models\Branch;
use App\Models\Plan;
use Carbon\Carbon;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

class PlanModuleService
{
    public function attributes(string $module, $plan, $attribute = []): \Illuminate\Support\Collection
    {
        if (! $plan) {
            return collect();
        }

        $subscription = collect($plan->attributes)->firstWhere('name', $module);

        if (! $subscription) {
            return collect($plan->attributes['attribute'])->pluck('value', 'name');
        }

        return collect($subscription['attribute'])->pluck('value', 'name');
    }

    public function getPlanAttributes(string $module, $plan): \Illuminate\Support\Collection
    {
        if (! $plan) {
            throw new NotFoundException(__('Your subscription has expired. To continue using .... , please renew your plan'));
        }

        return $this->attributes($module, $plan);
    }

    public function getVendorPercentage($attribute, $quote): float
    {
        return currentBranch()->id === $quote->quote->creator
                    ? ($attribute->get('is_percentage')
                    ? $quote->price * ((float) $attribute?->get('amountPerRequest') / 100)
                    : 0.0) : 0.0;

    }

    public function branchActivePlan($planType = null, $moduleName = null, $branchId = null)
    {
        $query = currentBranch();

        if ($branchId) {
            $query = Branch::query()->findOrFail($branchId);
        }

        return $query->plans()
            ->where('subscriptions.status', 'active')
            ->when($planType, function ($plan) use ($planType): void {
                $plan->where('plan_type', $planType);
            })
            ->when($moduleName, function ($plan) use ($moduleName): void {
                $plan->whereJsonContains('attributes', ['name' => $moduleName]);
            })->latest()->first();
    }

    public function branchPlan($planType = null, $moduleName = null, $branchId = null)
    {
        $query = currentBranch();

        if ($branchId) {
            $query = Branch::query()->findOrFail($branchId);
        }

        return $query->plans()
            ->when($planType, function ($plan) use ($planType): void {
                $plan->where('plan_type', $planType);
            })
            ->when($moduleName, function ($plan) use ($moduleName): void {
                $plan->whereJsonContains('attributes', ['name' => $moduleName]);
            })->get();
    }

    public function templatePlan($planType = null, $moduleName = null, $isTemplate = true)
    {
        return Plan::query()
            ->where('status', 'active')
            ->where('is_template', $isTemplate)
            ->when($planType, function ($plan) use ($planType): void {
                $plan->where('plan_type', $planType);
            })
            ->when($moduleName, function ($plan) use ($moduleName): void {
                $plan->whereJsonContains('attributes', ['name' => $moduleName]);
            })->latest()->first();
    }

    public function checkPlanSubscription($plan, $duration): bool
    {
        return $plan->pivot->created_at->diffInDays(Carbon::now(), false) >= $duration;
    }

    public function assignPlanToBranchByAccountType($account, \App\Models\Branch $branch): void
    {
        $attribute = $this->getAttributes('globalMarket', 'Add Customer', true);

        $plans = Plan::query()
            ->where('is_template', true)
            ->when($attribute->get('is_percentage') && $account->invitation, function ($query) use ($attribute): void {
                $query->where('id', '<>', $attribute->get('planId'));

            })->whereJsonContains('auto_assignees', $account->domain)->get();

        (new BranchPlanService)->assignCreate($branch, $plans->pluck('id')->toArray());
    }

    public function updatePlanAttributeValue($plan, $moduleName, $attributeName, $newValue): void
    {
        $planAttribute = $plan?->attributes;
        foreach ($planAttribute as &$item) {
            if ($item['name'] === $moduleName) {
                foreach ($item['attribute'] as &$attr) {
                    if ($attr['name'] === $attributeName) {
                        $attr['value'] = $newValue;
                        break;
                    }
                }
            }
        }
        $plan->attributes = $planAttribute;
        $plan->save();
    }

    public function renewalPlanCurrentBranch($plan): void
    {
        $plan->branches()->updateExistingPivot(currentBranch()->id,
            [
                'status'     => true,
                'created_at' => Carbon::now(),
            ]
        );
    }

    public function getAddCustomerPlan()
    {
        return $this->branchActivePlan(planType: 'globalMarket', moduleName: 'Add Customer');
    }

    public function expirePlan()
    {
        return $this->branchPlan(planType: 'globalMarket', moduleName: 'Add Customer');
    }

    public function getAttributes($planType, $module, $template = false, $branchId = null): \Illuminate\Support\Collection
    {
        $plan = $this->branchActivePlan(planType: $planType, moduleName: $module, branchId: $branchId);

        if ($template) {
            $plan = $this->templatePlan(planType: $planType, moduleName: $module);
        }

        return collect([
            'planId' => $plan?->id,
            ...$this->attributes(module: $module, plan: $plan),
        ]);
    }

    public function checkPlanCost($cost, $payment, $duration_paid = null): bool
    {
        return in_array($cost, [
            SubscriptionCost::FREE,
            SubscriptionCost::FREE_FOR_CURRENT_REQUEST,
            SubscriptionCost::FREE_FOR_CURRENT_PERIOD,
        ]) || $payment === SubscriptionPayment::PERCENTAGE;
    }

    public function checkPercentage($percentage): bool
    {
        return in_array($percentage, [
            SubscriptionPayment::PERCENTAGE,
            SubscriptionPayment::BOTH,
        ]);
    }

    public function getPlanBySubscriptionType($type, $branchId = null)
    {

        if (! $branchId) {
            $branchId = currentBranch()->id;
        }

        return Plan::query()
            ->with('exceptions')
            ->where('subscription_type', $type)
            ->where('status', 'active')
            ->whereHas('branches', function ($join) use ($branchId): void {
                $join->where('branches.id', $branchId);
            })
            ->first();
    }

    public function renewalNumberOfRequest($plan, $attribute): void
    {
        $currentRequestCount = currentBranch()->customers->count();

        if ($attribute->get('numberOfRequest') <= $currentRequestCount) {
            $this->updatePlanDurationStatus($plan);
            $this->updatePlanAttributeValue(
                plan: $plan,
                moduleName: 'Add Customer',
                attributeName: 'numberOfRequests',
                newValue: $attribute->get('numberOfRequests') + 10
            );
        }
    }

    public function renewalPeriod($plan): void
    {
        $subscriptionFinish = $this->checkPlanSubscription($plan, $plan->duration);

        if ($subscriptionFinish) {
            $this->renewalPlanCurrentBranch($plan);
        }
    }

    public function updatePlanDurationStatus($plan, $paid = 'No'): void
    {
        $plan->update([
            'duration_paid' => $paid,
        ]);
    }

    public function changePlanStatus($plan, $status): void
    {
        $plan->update([
            'status' => $status,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function renewalDuration($plan): void
    {
        try {
            DB::beginTransaction();
            $this->changePlanStatus($plan, 'active');
            $this->updatePlanDurationStatus($plan, 'Yes');
            $this->renewalPlanCurrentBranch($plan);
            DB::commit();
        } catch (Throwable) {
            DB::rollBack();
        }

    }
}
