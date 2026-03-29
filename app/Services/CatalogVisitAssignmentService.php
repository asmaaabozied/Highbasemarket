<?php

namespace App\Services;

use App\Enum\EmployeeVisitStatusEnum;
use App\Enum\SourceTypeEnum;
use App\Enum\VisitPurposeTypeEnum;
use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeVisit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CatalogVisitAssignmentService
{
    public static function make(): self
    {
        return new self;
    }

    public function assignStoresToEmployees(
        Branch $highbaseBranch,
        array $employeeIds,
        int $createdBy,
        bool $dryRun = false
    ): array {
        $emptyResult = fn (array $errors): array => [
            'assigned'     => 0,
            'skipped'      => 0,
            'total_stores' => 0,
            'errors'       => $errors,
        ];

        if ($createdBy <= 0) {
            return $emptyResult(['createdBy must be a positive user id']);
        }

        try {
            $employees = $this->getEmployees($highbaseBranch, $employeeIds);

            if ($employees->isEmpty()) {
                return $emptyResult(['No valid employees found']);
            }

            // Only assign to employees who do not already have visits for today (skip those who do)
            $employees = $this->excludeEmployeesWithVisitsToday($highbaseBranch, $employees);

            if ($employees->isEmpty()) {
                return $emptyResult(['All selected employees already have visits assigned for today']);
            }

            $storeCount = AnonymousCustomerBranch::where('branch_id', $highbaseBranch->id)->count();

            if ($storeCount === 0) {
                return $emptyResult(['No stores found for highbase branch']);
            }

            return $this->distributeStoresInChunks($highbaseBranch, $employees, $createdBy, $dryRun);
        } catch (Throwable $e) {
            Log::error('CatalogVisitAssignmentService failed', [
                'branch_id'    => $highbaseBranch->id,
                'employee_ids' => $employeeIds,
                'message'      => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
            ]);

            return $emptyResult(['Assignment failed: '.$e->getMessage()]);
        }
    }

    private function getEmployees(Branch $highbaseBranch, array $employeeIds): Collection
    {
        $highbaseAccountId = Branch::getHighbaseAccountId();

        return Employee::query()
            ->whereIn('id', $employeeIds)
            ->where('account_id', $highbaseAccountId)
            ->whereHas('branches', function ($q) use ($highbaseBranch): void {
                $q->where('branches.id', $highbaseBranch->id);
            })
            ->get();
    }

    /**
     * Exclude employees who already have at least one visit scheduled for today (this branch).
     * Only employees with no visits today will receive new assignments.
     */
    private function excludeEmployeesWithVisitsToday(Branch $highbaseBranch, Collection $employees): Collection
    {
        $today = now()->toDateString();

        $employeeIdsWithVisitsToday = EmployeeVisit::query()
            ->where('branch_id', $highbaseBranch->id)
            ->whereDate('scheduled_at', $today)
            ->distinct()
            ->pluck('employee_id');

        return $employees->reject(fn (Employee $e) => $employeeIdsWithVisitsToday->contains($e->id))->values();
    }

    private function distributeStoresInChunks(
        Branch $highbaseBranch,
        Collection $employees,
        int $createdBy,
        bool $dryRun = false
    ): array {
        $chunkSize     = 1000;
        $totalAssigned = 0;
        $totalSkipped  = 0;
        $totalStores   = 0;
        $employeeCount = $employees->count();
        $employeeArray = $employees->shuffle()->values()->all();
        $globalIndex   = 0;

        AnonymousCustomerBranch::query()
            ->where('branch_id', $highbaseBranch->id)
            ->chunkById($chunkSize, function ($storesChunk) use (
                $highbaseBranch,
                $employeeArray,
                $employeeCount,
                $createdBy,
                $dryRun,
                &$totalAssigned,
                &$totalSkipped,
                &$totalStores,
                &$globalIndex
            ): void {
                $totalStores += $storesChunk->count();

                $storesChunk = $storesChunk->shuffle()->values();

                $storeIds            = $storesChunk->pluck('id');
                $today               = now()->toDateString();
                $existingAssignments = EmployeeVisit::where('branch_id', $highbaseBranch->id)
                    ->where('visitable_type', AnonymousCustomerBranch::class)
                    ->whereIn('visitable_id', $storeIds)
                    ->whereDate('scheduled_at', $today)
                    ->pluck('visitable_id')
                    ->flip();

                $visitsToCreate = [];

                foreach ($storesChunk as $store) {
                    $employeeIndex = $globalIndex % $employeeCount;
                    $employee      = $employeeArray[$employeeIndex];
                    $globalIndex++;

                    if ($existingAssignments->has($store->id)) {
                        $totalSkipped++;
                        continue;
                    }

                    $visitsToCreate[] = [
                        'employee_id'    => $employee->id,
                        'branch_id'      => $highbaseBranch->id,
                        'visitable_type' => AnonymousCustomerBranch::class,
                        'visitable_id'   => $store->id,
                        'status'         => EmployeeVisitStatusEnum::PENDING->value,
                        'source_type'    => SourceTypeEnum::MANUAL->value,
                        'purpose'        => VisitPurposeTypeEnum::CATALOG_DELIVERY->value,
                        'scheduled_at'   => now(),
                        'created_by'     => $createdBy,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }

                if ($visitsToCreate !== [] && ! $dryRun) {
                    DB::transaction(function () use ($visitsToCreate, &$totalAssigned): void {
                        EmployeeVisit::insert($visitsToCreate);
                        $totalAssigned += count($visitsToCreate);
                    });
                } elseif ($visitsToCreate !== [] && $dryRun) {
                    $totalAssigned += count($visitsToCreate);
                }
            });

        $errors = [];

        if ($totalSkipped > 0) {
            $errors[] = 'Some stores were already assigned';
        }

        return [
            'assigned'     => $totalAssigned,
            'skipped'      => $totalSkipped,
            'total_stores' => $totalStores,
            'errors'       => $errors,
        ];
    }
}
