<?php

namespace App\Services;

use App\Dto\EmployeeVisitReportDto;
use App\EmployeeJobEnum;
use App\Enum\EmployeeVisitStatusEnum;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeVisit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class VisitReportService
{
    public function buildRoleSummary(Employee $employee, string $period, Carbon $date): array
    {
        $dates = $this->resolvePeriodDates($period, $date);
        $start = $dates['start'];
        $end   = $dates['end'];
        $role  = strtolower($employee->job_title);

        if ($role === EmployeeJobEnum::EMPLOYEE->value) {
            return [
                $this->getEmployeeSummary($employee, $period, $start, $end),
            ];
        }

        // Supervisors, Managers, Admins: return summary PER BRANCH
        $branchIds = $this->resolveBranchIds($employee);
        $summaries = [];

        foreach ($branchIds as $branchId) {
            $visits = EmployeeVisit::where('branch_id', $branchId)
                ->whereBetween('scheduled_at', [$start, $end])
                ->with(['employee', 'branch', 'visitable'])
                ->get();

            if ($visits->isEmpty()) {
                continue;
            }

            $branch = Branch::find($branchId);

            $summaries[] = match ($role) {
                EmployeeJobEnum::SUPERVISOR->value => $this->buildSupervisorBranchSummary(
                    $employee,
                    $visits,
                    $branch,
                    $period,
                    $start,
                    $end
                ),
                EmployeeJobEnum::MANAGER->value => $this->buildManagerBranchSummary(
                    $employee,
                    $visits,
                    $branch,
                    $period,
                    $start,
                    $end
                ),
                default => $this->buildAdminBranchSummary(
                    $employee,
                    $visits,
                    $branch,
                    $period,
                    $start,
                    $end
                ),
            };
        }

        return $summaries;
    }

    private function resolvePeriodDates(string $period, Carbon $date): array
    {
        return match ($period) {
            'daily'   => ['start' => $date->copy()->startOfDay(), 'end' => $date->copy()->endOfDay()],
            'weekly'  => ['start' => $date->copy()->startOfWeek(), 'end' => $date->copy()->endOfWeek()],
            'monthly' => ['start' => $date->copy()->startOfMonth(), 'end' => $date->copy()->endOfMonth()],
            default   => throw new InvalidArgumentException('Invalid period type'),
        };
    }

    private function getEmployeeSummary(Employee $employee, string $period, Carbon $start, Carbon $end): array
    {
        $visits = EmployeeVisit::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('scheduled_at', [$start, $end])
            ->with(['branch', 'visitable'])
            ->get();

        return $this->buildEmployeeSummary($employee, $visits, $period, $start, $end);
    }

    /**
     * Build employee summary structure
     */
    private function buildEmployeeSummary(
        Employee $employee,
        Collection $visits,
        string $periodType,
        Carbon $startDate,
        Carbon $endDate
    ): array {

        return [
            'recipient' => [
                'type'  => 'employee',
                'id'    => $employee->id,
                'name'  => $employee->user?->name,
                'email' => $employee->user?->email,
            ],
            'period' => [
                'type'  => $periodType,
                'start' => $startDate->toDateString(),
                'end'   => $endDate->toDateString(),
                'label' => $this->getPeriodLabel($periodType, $startDate),
            ],
            'summary' => [
                'total_visits'    => $visits->count(),
                'completed'       => $visits->where('status', EmployeeVisitStatusEnum::VISITED)->count(),
                'pending'         => $visits->where('status', EmployeeVisitStatusEnum::PENDING)->count(),
                'date_changed'    => $visits->where('status', EmployeeVisitStatusEnum::DATE_CHANGED)->count(),
                'missed'          => $visits->where('status', EmployeeVisitStatusEnum::MISSED)->count(),
                'postponed'       => $visits->where('status', EmployeeVisitStatusEnum::POSTPONED)->count(),
                'completion_rate' => $this->calculateCompletionRate($visits),
            ],
            'by_status'      => $this->groupByStatus($visits),
            'by_branch'      => $this->groupByBranch($visits),
            'by_source'      => $this->groupBySource($visits),
            'visits_details' => $this->formatVisitsForEmployee($visits),
        ];
    }

    private function getPeriodLabel(string $periodType, Carbon $date): string
    {
        return match ($periodType) {
            'daily'   => $date->format('l, F j, Y'),
            'weekly'  => 'Week of '.$date->startOfWeek()->format('F j').' - '.$date->endOfWeek()->format('F j, Y'),
            'monthly' => $date->format('F Y'),
        };
    }

    private function calculateCompletionRate(Collection $visits): float
    {
        $total = $visits->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $visits->where('status', EmployeeVisitStatusEnum::VISITED)->count();

        return round(($completed / $total) * 100, 2);
    }

    private function groupByStatus(Collection $visits): array
    {
        return $visits->groupBy('status')->map(fn ($group): array => [
            'count'      => $group->count(),
            'percentage' => round(($group->count() / max($visits->count(), 1)) * 100, 2),
        ])->toArray();
    }

    private function groupByBranch(Collection $visits): array
    {
        return $visits->groupBy('branch_id')->map(function (\Illuminate\Database\Eloquent\Collection $group): array {
            $branch = $group->first()->branch;

            return [
                'branch_id'       => $branch?->id,
                'branch_name'     => $branch?->name,
                'count'           => $group->count(),
                'completed'       => $group->where('status', EmployeeVisitStatusEnum::VISITED)->count(),
                'completion_rate' => $this->calculateCompletionRate($group),
            ];
        })->values()->toArray();
    }

    private function groupBySource(Collection $visits): array
    {
        return $visits->groupBy('source_type')->map(fn ($group): array => [
            'count'      => $group->count(),
            'percentage' => round(($group->count() / max($visits->count(), 1)) * 100, 2),
        ])->toArray();
    }

    private function formatVisitsForEmployee(Collection $visits): array
    {
        return $visits->map(fn ($visit): array => [
            'id'           => $visit->id,
            'scheduled_at' => $visit->scheduled_at->toDateTimeString(),
            'status'       => $visit->status,
            'branch'       => $visit->branch?->name,
            'visitable'    => $visit->visitable?->name,
            'purpose'      => $visit->purpose,
        ])->toArray();
    }

    private function resolveBranchIds(Employee $employee): array
    {
        $assigned = $employee->branches()
            ->pluck('branches.id')
            ->toArray();

        if (! empty($assigned)) {
            return $assigned;
        }

        return $employee->account
            ->branches()
            ->pluck('id')
            ->toArray();
    }

    private function buildSupervisorBranchSummary(
        Employee $supervisor,
        Collection $visits,
        Branch $branch,
        string $periodType,
        Carbon $startDate,
        Carbon $endDate
    ): array {

        return [
            'recipient' => [
                'type'  => EmployeeJobEnum::SUPERVISOR->value,
                'id'    => $supervisor->id,
                'name'  => $supervisor->user->name,
                'email' => $supervisor->user->email,
            ],
            'scope' => [
                'branch' => [
                    'id'   => $branch->id,
                    'name' => $branch->name,
                ],
            ],
            'period' => [
                'type'  => $periodType,
                'start' => $startDate->toDateString(),
                'end'   => $endDate->toDateString(),
                'label' => $this->getPeriodLabel($periodType, $startDate),
            ],
            'summary' => [
                'total_visits'    => $this->getTotalVisitsCount($visits),
                'completed'       => $visits->where('status', EmployeeVisitStatusEnum::VISITED)->count(),
                'pending'         => $visits->where('status', EmployeeVisitStatusEnum::PENDING)->count(),
                'scheduled'       => $visits->where('status', EmployeeVisitStatusEnum::SCHEDULED)->count(),
                'cancelled'       => $visits->where('status', EmployeeVisitStatusEnum::CANCELLED)->count(),
                'postponed'       => $visits->where('status', EmployeeVisitStatusEnum::POSTPONED)->count(),
                'missed'          => $visits->where('status', EmployeeVisitStatusEnum::MISSED)->count(),
                'completion_rate' => $this->calculateCompletionRate($visits),
            ],
            'by_status'      => $this->groupByStatus($visits),
            'by_employee'    => $this->groupByEmployee($visits),
            'visits_details' => $this->formatVisitsForSupervisor($visits),
            'generated_at'   => now()->toDateTimeString(),
        ];
    }

    public function getTotalVisitsCount(\Illuminate\Support\Collection $visits): int
    {
        return $visits->filter(fn ($visit): bool => in_array($visit->status->value,
            ['visited', 'date_changed', 'missed', 'postponed']))->count();

    }

    private function groupByEmployee(Collection $visits): array
    {
        return $visits->groupBy('employee_id')->map(function (\Illuminate\Database\Eloquent\Collection $group): array {
            $employee = $group->first()->employee;

            return [
                'employee_id'     => $employee->id,
                'employee_name'   => $employee->name,
                'count'           => $group->count(),
                'completed'       => $group->where('status', EmployeeVisitStatusEnum::VISITED)->count(),
                'completion_rate' => $this->calculateCompletionRate($group),
            ];
        })->sortByDesc('completed')->values()->toArray();
    }

    private function formatVisitsForSupervisor(Collection $visits): array
    {
        return $visits->map(fn ($visit): array => [
            'id'           => $visit->id,
            'employee'     => $visit->employee->user->name,
            'scheduled_at' => $visit->scheduled_at->toDateTimeString(),
            'status'       => $visit->status,
            'branch'       => $visit->branch?->name,
            'visitable'    => $visit->visitable?->name,
        ])->toArray();
    }

    private function buildManagerBranchSummary(
        Employee $manager,
        Collection $visits,
        Branch $branch,
        string $periodType,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        return [
            'recipient' => [
                'type'  => EmployeeJobEnum::MANAGER->value,
                'id'    => $manager->id,
                'name'  => $manager->user->name,
                'email' => $manager->user->email,
            ],
            'scope' => [
                'branch' => [
                    'id'   => $branch->id,
                    'name' => $branch->name,
                ],
            ],
            'period' => [
                'type'  => $periodType,
                'start' => $startDate->toDateString(),
                'end'   => $endDate->toDateString(),
                'label' => $this->getPeriodLabel($periodType, $startDate),
            ],
            'summary' => [
                'total_visits'    => $this->getTotalVisitsCount($visits),
                'completed'       => $visits->where('status', EmployeeVisitStatusEnum::VISITED)->count(),
                'pending'         => $visits->where('status', EmployeeVisitStatusEnum::PENDING)->count(),
                'scheduled'       => $visits->where('status', EmployeeVisitStatusEnum::SCHEDULED)->count(),
                'missed'          => $visits->where('status', EmployeeVisitStatusEnum::MISSED)->count(),
                'postponed'       => $visits->where('status', EmployeeVisitStatusEnum::POSTPONED)->count(),
                'completion_rate' => $this->calculateCompletionRate($visits),
            ],
            'by_status'      => $this->groupByStatus($visits),
            'by_employee'    => $this->groupByEmployee($visits),
            'visits_details' => $this->formatVisitsForManager($visits),
            'generated_at'   => now()->toDateTimeString(),
        ];
    }

    private function formatVisitsForManager(Collection $visits): array
    {
        return $this->formatVisitsForSupervisor($visits);
    }

    private function buildAdminBranchSummary(
        Employee $admin,
        Collection $visits,
        Branch $branch,
        string $periodType,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        return [
            'recipient' => [
                'type'  => EmployeeJobEnum::ADMINISTRATOR->value,
                'id'    => $admin->id,
                'name'  => $admin->user->name,
                'email' => $admin->user->email,
            ],
            'scope' => [
                'branch' => [
                    'id'   => $branch->id,
                    'name' => $branch->name,
                ],
            ],
            'period' => [
                'type'  => $periodType,
                'start' => $startDate->toDateString(),
                'end'   => $endDate->toDateString(),
                'label' => $this->getPeriodLabel($periodType, $startDate),
            ],
            'summary' => [
                'total_visits'    => $this->getTotalVisitsCount($visits),
                'completed'       => $visits->where('status', EmployeeVisitStatusEnum::VISITED)->count(),
                'pending'         => $visits->where('status', EmployeeVisitStatusEnum::PENDING)->count(),
                'scheduled'       => $visits->where('status', EmployeeVisitStatusEnum::SCHEDULED)->count(),
                'missed'          => $visits->where('status', EmployeeVisitStatusEnum::MISSED)->count(),
                'postponed'       => $visits->where('status', EmployeeVisitStatusEnum::POSTPONED)->count(),
                'completion_rate' => $this->calculateCompletionRate($visits),
            ],
            'by_status'      => $this->groupByStatus($visits),
            'by_employee'    => $this->groupByEmployee($visits),
            'visits_details' => $this->formatVisitsForAdmin($visits),
            'generated_at'   => now()->toDateTimeString(),
        ];
    }

    private function formatVisitsForAdmin(Collection $visits): array
    {
        return $visits->map(fn ($visit): array => [
            'id'           => $visit->id,
            'employee'     => $visit->employee->name,
            'scheduled_at' => $visit->scheduled_at->toDateTimeString(),
            'status'       => $visit->status,
            'branch'       => $visit->branch?->name,
            'visitable'    => $visit->visitable?->name,
            'source'       => $visit->source_type,
        ])->toArray();
    }

    public function generateReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $employeeId = null,
        ?int $customerId = null
    ): EmployeeVisitReportDto {
        $query = EmployeeVisit::whereBetween('scheduled_at', [$startDate, $endDate]);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($customerId) {
            $query->where('visitable_id', $customerId);
        }

        $visits = $query->get();

        // Count by status - adjust these based on YOUR actual status values
        $visited   = $visits->where('status', 'completed')->count();
        $missed    = $visits->where('status', 'missed')->count();
        $postponed = $visits->where('status', 'postponed')->count();

        // Date changed = visits that have parent_visit_id (rescheduled)
        $dateChanged = $visits->whereNotNull('parent_visit_id')->count();

        return new EmployeeVisitReportDto(
            visited: $visited,
            missed: $missed,
            dateChanged: $dateChanged,
            postponed: $postponed,
            dateRange: [
                'start' => $startDate->format('Y-m-d'),
                'end'   => $endDate->format('Y-m-d'),
            ],
            employeeId: $employeeId,
            visitable_id: $customerId
        );
    }

    public function generateMonthlyTrend(?int $employeeId = null, int $months = 6): array
    {
        $start = now()->copy()->subMonths($months)->startOfMonth();
        $end   = now()->copy()->subMonth()->endOfMonth();

        $visits = EmployeeVisit::query()
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->whereBetween('scheduled_at', [$start, $end])
            ->selectRaw('COUNT(*) as total, DATE_FORMAT(scheduled_at, "%Y-%m") as month')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $visits->toArray();
    }

    private function getTopPerformers(Collection $visits, int $limit): array
    {
        return $visits->groupBy('employee_id')
            ->map(function (\Illuminate\Database\Eloquent\Collection $group): array {
                $employee = $group->first()->employee;

                return [
                    'employee_id'     => $employee->id,
                    'employee_name'   => $employee->name,
                    'total_visits'    => $group->count(),
                    'completed'       => $group->where('status', EmployeeVisitStatusEnum::VISITED)->count(),
                    'completion_rate' => $this->calculateCompletionRate($group),
                ];
            })
            ->sortByDesc('completion_rate')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getNeedsAttention(Collection $visits): array
    {
        return [
            'missed' => $visits->where('status', EmployeeVisitStatusEnum::MISSED)
                ->count(),
            'postponed_multiple_times' => $visits->whereNotNull('postpone_reason')
                ->filter(function ($visit): true {
                    // Count how many times this visit was postponed (if you track history)
                    return true; // Implement your logic
                })
                ->count(),
            'long_pending' => $visits->where('status', EmployeeVisitStatusEnum::PENDING)
                ->filter(fn ($v): bool => $v->scheduled_at < now()->subDays(3))
                ->count(),
        ];
    }

    private function calculateTrends(): array
    {
        return [
            'visits_change'          => 0,
            'completion_rate_change' => 0,
        ];
    }
}
