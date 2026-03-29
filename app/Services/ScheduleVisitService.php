<?php

namespace App\Services;

use App\Dto\ScheduleVisitDto;
use App\EmployeeJobEnum;
use App\Enum\CustomerType;
use App\Enum\EmployeeVisitStatusEnum;
use App\Enum\RecurrenceTypeEnum;
use App\Enum\ScheduleTypeEnum;
use App\Events\ScheduleCreated;
use App\Http\Filters\ScheduleVisitFilter;
use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ScheduleVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

class ScheduleVisitService
{
    private Collection|\Illuminate\Database\Eloquent\Collection|null $employees = null;

    /**
     * Create a new class instance.
     */
    public function __construct(private readonly AnonymousCustomerService $anonymousCustomerService)
    {
        //
    }

    public function getPaginatedResults(ScheduleVisitFilter $filter, User $user, Branch $branch): LengthAwarePaginator
    {
        $paginator = $filter->execute(function (QueryBuilder $builder): \Spatie\QueryBuilder\QueryBuilder {
            $builder->with([
                'employee.user',
                'visitable' => function ($morphTo): void {
                    $morphTo->constrain([
                        AnonymousCustomerBranch::class => function ($query): void {
                            $query->with('customer');
                        },
                    ]);
                },
            ])->with([
                'visits' => function ($query): void {
                    $query
                        ->select(['id', 'schedule_visit_id', 'scheduled_at'])
                        ->latest()
                        ->limit(1);
                },
                'overrides' => function ($query): void {
                    $query
                        ->where('status', EmployeeVisitStatusEnum::SCHEDULED->value)
                        ->whereDate('visit_date', '>', today())
                        ->orderBy('visit_date')
                        ->limit(1);
                },
            ]);

            return $builder;
        })
            ->applyRoleBasedVisibility($user, $branch)
            ->paginate();

        $paginator->getCollection()->transform(function (ScheduleVisit $item): \App\Models\ScheduleVisit {
            $scheduleDate = null; // app(VisitProjectionService::class)->getNextVisitDate($item);
            $item->setAttribute('scheduled_date', $scheduleDate?->format('Y-m-d'));

            $stateName = $cityName = $blockNumber = null;
            $customer  = $item->visitable;

            if (! empty($customer->address)) {
                $stateName   = $cityName = null;
                $blockNumber = $customer->address['block_number'] ?? null;
            }

            $formattedAddress = collect([$stateName, $cityName, $blockNumber])
                ->filter()
                ->whenEmpty(fn (): null => null)
                ->join(', ');

            $item->setAttribute('formatted_address', $formattedAddress);

            return $item;
        });

        return $paginator;
    }

    public function getStats(): array
    {
        $branch = currentBranch();

        $employeeIds           = $this->getEmployees()->pluck('id');
        $relatedEmployeesCount = ScheduleVisit::query()
            ->where('branch_id', $branch->id)
            ->whereIn('employee_id', $employeeIds)
            ->distinct('employee_id')
            ->count('employee_id');

        $notRelatedEmployeesCount = $employeeIds->count();

        // Customer counts
        $customerIds = $branch->customers()->distinct()->pluck('customer_id');

        $relatedCustomersCount = ScheduleVisit::query()
            ->where('branch_id', $branch->id)
            ->whereIn('visitable_id', $customerIds)
            ->distinct('visitable_id')
            ->count('visitable_id');

        $notRelatedCustomersCount = $customerIds->count();
        $total                    = ScheduleVisit::query()
            ->where('branch_id', $branch->id)
            ->count();

        return [
            'stats' => [
                'scheduled_customers' => "$relatedCustomersCount/$notRelatedCustomersCount",
                'scheduled_employees' => "$relatedEmployeesCount/$notRelatedEmployeesCount",
                'total_schedules'     => $total,
            ],
        ];
    }

    public function getEmployees(): Collection
    {
        if (! $this->employees instanceof \Illuminate\Support\Collection) {
            $this->employees = auth()->user()->userable->myEmployees(currentBranch(), ['view employee visit'])
                ->get()
                ->map(function (Employee $employee): array {
                    $user = $employee->user;

                    return [
                        'id'       => $employee->id,
                        'option'   => $user?->name,
                        'value'    => $user?->userable_id,
                        'avatar'   => $user?->profile_photo_url,
                        'position' => $employee?->job_title,
                    ];
                });
        }

        return $this->employees;
    }

    public function getMetaData(): array
    {
        $employees = $this->getEmployees()->values();

        $options   = RecurrenceTypeEnum::options();
        $options[] = [
            'option' => 'One Time',
            'value'  => 'one_time',
        ];
        $visitTypes = $options;

        $anonymousCustomers = AnonymousCustomerBranch::query()
            ->where('branch_id', currentBranch()->id)
            ->get()
            ->unique('name')
            ->map(fn ($customer): array => [
                'option'  => $customer->name,
                'value'   => $customer->id,
                'avatar'  => $customer->image ?? null,
                'city'    => null,
                'country' => null,
                'type'    => CustomerType::ANONYMOUS,
            ]);

        $branchCustomers = currentBranch()->customers()->get()->unique('id')->map(function (Branch $customer): array {
            $countryName = null;
            $longitude   = null;
            $latitude    = null;

            if (! empty($customer->address) && isset($customer->address['country'])) {
                $country     = app(CountryService::class)->getCountryById($customer->address['country']);
                $countryName = $country?->name;
                $longitude   = data_get($customer->address, 'pin_location.lat') ?? null;
                $latitude    = data_get($customer->address, 'pin_location.lng') ?? null;
            }

            return [
                'option'  => $customer->name,
                'value'   => $customer->id,
                'avatar'  => $customer->image,
                'city'    => $customer->address['city'] ?? null,
                'country' => $countryName,
                'lat'     => $latitude,
                'lng'     => $longitude,
                'type'    => CustomerType::EXISTING,
            ];
        });

        if (is_array($anonymousCustomers)) {
            $anonymousCustomers = collect($anonymousCustomers);
        }

        $customer  = collect([]);
        $customer  = $customer->merge($branchCustomers);
        $customers = $customer->merge($anonymousCustomers);

        return [
            'employees'  => $employees,
            'visitTypes' => $visitTypes,
            'customers'  => $customers,
        ];
    }

    public function updateSchedule(ScheduleVisit $schedule_visit, ScheduleVisitDto $data): ScheduleVisit
    {
        $employee = Employee::findOrFail($data->employeeId);

        if ($data->clientType === CustomerType::ANONYMOUS->value) {
            $response      = $this->anonymousCustomerService->create($data->anonymousCustomer);
            $visitableId   = $response->branch->id;
            $visitableType = AnonymousCustomerBranch::class;
        } else {
            $this->ensureCustomerBelongsToBranches($employee, $data->customerId);
            $visitableId   = $data->customerId;
            $visitableType = Branch::class;
        }

        if ($this->checkDuplicate($data, $schedule_visit->id)) {
            throw ValidationException::withMessages([
                'schedule' => __('A schedule already exists for this employee, customer, and visit type.'),
            ]);
        }

        $schedule_visit->update(array_merge($data->toArray(), [
            'visitable_id'   => $visitableId,
            'visitable_type' => $visitableType,
        ]));

        return $schedule_visit;
    }

    public function ensureCustomerBelongsToBranches(Employee $employee, int $customerId): void
    {
        $currentBranchId = currentBranch()->id;

        // Check employee belongs to current branch or has no branch
        $employeeBranchIds = $employee->branches->pluck('id');

        if ($employeeBranchIds->isNotEmpty() && ! $employeeBranchIds->contains($currentBranchId)) {
            throw ValidationException::withMessages([
                'employee' => __('This employee does not belong to the current branch.'),
            ]);
        }

        // Check customer belongs to current branch
        $branchCustomerIds = currentBranch()->customers()->get()->pluck('id');

        if (! $branchCustomerIds->contains($customerId)) {
            throw ValidationException::withMessages([
                'customer' => __('This customer does not belong to the current branch.'),
            ]);
        }
    }

    private function checkDuplicate(ScheduleVisitDto $data, ?int $ignoreId = null): bool
    {
        $query = ScheduleVisit::where('employee_id', $data->employeeId)
            ->where('visitable_id', $data->customerId)
            ->where('schedule_type', $data->scheduleType?->value);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($data->scheduleType === ScheduleTypeEnum::RECURRING) {
            $query->where('recurrence_type', $data->recurrenceType);
        } else {
            $query->where('one_time_date', $data->oneTimeDate);
        }

        return $query->exists();
    }

    public function createSchedule(ScheduleVisitDto $data): ScheduleVisit
    {
        $employee = Employee::findOrFail($data->employeeId);
        $employee->branches->pluck('id');
        // Prevent supervisor from assigning managers
        $authJobTitle = strtolower(auth()->user()->userable?->job_title ?? '');

        if ($authJobTitle === EmployeeJobEnum::SUPERVISOR->value && strtolower((string) $employee->job_title) === EmployeeJobEnum::MANAGER->value) {
            throw ValidationException::withMessages(['employee' => __('You are logged in as a Supervisor and cannot assign schedules to Managers.')]);
        }

        $user     = auth()->user()->id;
        $branchId = currentBranch()->id;

        if ($data->clientType === CustomerType::ANONYMOUS->value) {
            $response      = $this->anonymousCustomerService->create($data->anonymousCustomer);
            $visitableId   = $response->branch->id;
            $visitableType = AnonymousCustomerBranch::class;

        } else {
            $this->ensureCustomerBelongsToBranches($employee, $data->customerId);
            $visitableId   = $data->customerId;
            $visitableType = Branch::class;

        }

        if ($this->checkDuplicate($data)) {
            throw ValidationException::withMessages([
                'schedule' => __('A schedule already exists for this employee, customer, and visit type.'),
            ]);
        }

        $merged = $data->toArray();

        $schedule = ScheduleVisit::create(array_merge($merged, [
            'created_by'    => $user,
            'start_date'    => now()->toDateString(),
            'one_time_date' => Carbon::parse($data->oneTimeDate)->toDateString(),
            'branch_id'     => $branchId,
            //            'client_type'    => $data->clientType,
            'visitable_id'   => $visitableId,
            'visitable_type' => $visitableType,
        ]));
        event(new ScheduleCreated($schedule));

        return $schedule;
    }
}
