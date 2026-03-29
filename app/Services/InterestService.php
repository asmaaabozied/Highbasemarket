<?php

namespace App\Services;

use App\Models\Interest;

class InterestService
{
    public function InterestBranch()
    {

        $currentBranch = (new EmployeeAccountServices)->getEmployeeCurrentBranch(auth()->user()?->userable->account->id);

        return Interest::query()->where('branch_id', $currentBranch->id)->first();
    }
}
