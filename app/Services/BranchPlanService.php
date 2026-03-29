<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class BranchPlanService
{
    public function assignUpdate(Branch $branch, array $plans): void
    {
        DB::transaction(function () use ($branch, $plans): void {
            $branches = [$branch, ...$branch->subBranches];

            foreach ($branches as $branch) {
                $branch->plans()->sync($plans);
            }
        });
    }

    public function assignCreate(Branch $branch, array $plans): void
    {
        $branches = [$branch, ...$branch->subBranches];

        foreach ($branches as $branch) {
            $branch->plans()->sync($plans);
        }
    }
}
