<?php

namespace App\Services;

use App\Actions\CreateVisitAction;
use App\Dto\VisitDto;
use App\Dto\VisitResultDto;
use App\Enum\VisitType;
use App\Http\Filters\VisitsFilter;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

readonly class VisitService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private CreateVisitAction $createVisitAction)
    {
        //
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function handleVisit(VisitDto $visitData, Branch $visitedBranch, int $employeeId): VisitResultDto
    {

        $alreadyVisited = Visit::query()
            ->where('employee_id', $employeeId)
            ->where('branch_id', $visitedBranch->id)
            ->whereDate('visited_at', now()->toDateString())
            ->exists();

        if ($alreadyVisited) {
            return new VisitResultDto(VisitType::NONE,
                __('You have already logged a visit today. No further visits can be recorded.'));
        }

        if ($visitData->distanceMeters > 500) {
            return new VisitResultDto(VisitType::NONE,
                __('Scan detected outside the allowed perimeter; this does not qualify as a valid visit.'));
        }

        $this->createVisitAction->execute($visitData);

        return new VisitResultDto(VisitType::VISIT);

    }

    public function getPaginatedResult(VisitsFilter $query): LengthAwarePaginator
    {

        return $query->execute(function ($builder): void {
            $builder
                ->where('vendor_id', currentBranch()->id)
                ->with(['employee.user', 'branch']);
        })->paginate(10);
    }

    public function updateVisit(Visit $visit, VisitDto $data): Visit
    {
        $visit->update([
            'notes' => $data->notes,
        ]);

        if ($data->attachments) {
            foreach ($data->attachments as $file) {
                $visit->addMedia($file)->toMediaCollection('attachments');
            }
        }

        return $visit;
    }

    public function totalVisitsThisMonth(int $branchId): int
    {
        return Visit::query()
            ->where('branch_id', $branchId)
            ->whereBetween('visited_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();
    }

    public function activeAgentsThisWeek(int $accountId): int
    {
        return Employee::query()
            ->where('account_id', $accountId)
            ->whereHas('visits', function ($q): void {
                $q->whereBetween('visited_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
            })
            ->count();
    }

    /**
     * Calculate the total number of visits for a given employee.
     */
    public function employeeVisits(VisitsFilter $query, Employee $employee): LengthAwarePaginator
    {
        return $query
            ->execute(fn ($builder) => $builder
                ->where('employee_id', $employee->id)
                ->with(['branch']))
            ->paginate();

    }
}
