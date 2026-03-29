<?php

namespace App\Services;

use App\Enum\EmployeeVisitStatusEnum;
use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeVisit;
use App\Models\Order;
use App\Models\ScheduleVisit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection as SupportCollection;

class OrderVisitService
{
    private Branch $seller;

    private Branch|AnonymousCustomerBranch $customer;

    private Employee $employee;

    private ?Paginator $employees = null;

    public function setSeller(Branch $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function setBuyer(Branch|AnonymousCustomerBranch $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function setEmployee(Employee $employee): self
    {
        $this->employee = $employee;

        return $this;
    }

    public function fetchEmployees(int $per_page = 10): void
    {
        if ($this->employee->isManager() || $this->employee->isAdministrator()) {
            $this->employees = $this->employeesForManager($per_page);
        }

        if ($this->employee->isSupervisor()) {
            $this->employees = $this->employeesForSupervisor($per_page);
        }
    }

    public function getEmployees(): ?Paginator
    {
        $this->fetchEmployees(20);
        $this->parseEmployeesVisits();

        $items = $this->employees?->getCollection();

        if ($items) {
            $sortedItems = $items->sortBy(function (Employee $employee) {
                if (! empty($employee['visits']) && count($employee['visits']) > 0) {
                    $visit = $employee['visits'][0];

                    if (isset($visit['scheduled_at']) && $visit['scheduled_at']) {
                        return $visit['scheduled_at'];
                    }
                }

                return '9999-12-31';
            })->values();

            // Set the sorted collection back to the paginator
            $this->employees->setCollection($sortedItems);
        }

        return $this->employees;
    }

    public function parseEmployeesVisits(): void
    {
        if ($this->employees instanceof Paginator) {
            $this->employees->getCollection()->transform(function (Employee $employee): Employee {
                $employee->visits = $this->getCurrentAndNextVisits($employee);

                return $employee;
            });
        }
    }

    private function employeesForManager(int $per_page = 10): Paginator
    {
        return Employee::where('account_id', $this->seller->account_id)
            ->whereHas('user', function ($query): void {
                $query->whereHas('roles', function ($q): void {
                    $q->whereHas('permissions', function ($perm): void {
                        $perm->where('name', 'view employee visit');
                    });
                });
            })
            ->where(function ($query): void {
                $query->whereHas('branches', function ($q): void {
                    $q->where('branches.id', $this->seller->id);
                })->orWhereDoesntHave('branches');
            })
            ->with('user:id,first_name,last_name,userable_id,userable_type,email')
            ->paginate($per_page);
    }

    private function employeesForSupervisor(int $per_page = 10): Paginator
    {
        return Employee::where('account_id', $this->seller->account_id)
            ->whereNotIn('job_title', ['administrator', 'manager'])
            ->whereHas('user', function ($query): void {
                $query->whereHas('roles', function ($q): void {
                    $q->whereHas('permissions', function ($perm): void {
                        $perm->where('name', 'view employee visit');
                    });
                });
            })
            ->where(function ($query): void {
                $query->whereHas('branches', function ($q): void {
                    $q->where('branches.id', $this->seller->id);
                })->orWhereDoesntHave('branches');
            })
            ->with('user:id,first_name,last_name,userable_id,userable_type,email')
            ->paginate($per_page);
    }

    public function getCurrentAndNextVisits(Employee $employee): array
    {
        $visit = EmployeeVisit::where('employee_id', $employee->id)
            ->where('visitable_id', $this->customer->id)
            ->where('visitable_type', $this->customer::class)
            ->whereDate('scheduled_at', '>=', now()->toDateString())
            ->orderBy('scheduled_at', 'desc')
            ->whereIn('status', ['scheduled', 'pending'])
            ->first()?->toArray();

        if ($visit) {
            $visit['scheduled_at'] = Carbon::parse($visit['scheduled_at'])->toDateString();
        }

        $scheduledVisits = ScheduleVisit::where('employee_id', $employee->id)
            ->where('visitable_id', $this->customer->id)
            ->where('visitable_type', $this->customer::class)
            ->get();

        $scheduledVisits->each(function ($schedule): void {
            $schedule->scheduled_at = $schedule->nextVisit()?->toDateString();
        });

        $visits = [];

        if ($visit) {
            $visits[] = $visit;
        }

        if (count($scheduledVisits) > 0) {
            array_merge($visits, $scheduledVisits->toArray());
        }

        return $visits;
    }

    public static function make(): self
    {
        return new self;
    }

    public static function getVisited(Branch $seller, Branch $buyer): Branch|AnonymousCustomerBranch
    {
        if ($buyer->isCustomerOf($seller)) {
            return $buyer;
        }

        return AnonymousCustomerService::getAnonymousCustomerOfBranch($seller, $buyer);
    }

    public function getOrdersReadyForDelivery(): \Illuminate\Database\Eloquent\Collection
    {
        $orders = Order::query()->withWhereHas('lines', function ($query): void {
            $query->whereIn('status', ['approved', 'shipped'])
                ->whereDoesntHave('visits', function ($q): void {
                    $q->whereHas('employeeVisit', function ($query): void {
                        $query->where('scheduled_at', '>=', now()->toDateString())
                            ->where(function ($q): void {
                                $q->whereNotNull('confirmed_at')
                                    ->orWhereIn('status', [
                                        EmployeeVisitStatusEnum::SCHEDULED,
                                        EmployeeVisitStatusEnum::ON_THE_WAY,
                                        EmployeeVisitStatusEnum::PENDING,
                                        EmployeeVisitStatusEnum::VISITED,
                                    ]);
                            });
                    });
                })
                ->withSum([
                    'visits' => function ($q): void {
                        $q->where('status', 'delivered')
                            ->orWhereHas('employeeVisit', function ($q2): void {
                                $q2->where('scheduled_at', '>=', now()->toDateString())
                                    ->where(function ($q): void {
                                        $q->whereNotNull('confirmed_at')
                                            ->orWhereIn('status', [
                                                EmployeeVisitStatusEnum::SCHEDULED,
                                                EmployeeVisitStatusEnum::ON_THE_WAY,
                                                EmployeeVisitStatusEnum::PENDING,
                                                EmployeeVisitStatusEnum::VISITED,
                                            ]);
                                    });
                            });
                    },
                ], 'quantity')
                ->withWhereHas('product', function ($query): void {
                    $query->where('branch_id', $this->seller->id);
                })->with('variant:id,name,product_id,image');
        })->with(['branch', 'anonymousCustomerBranch'])->orderBy('created_at', 'desc')->get();

        $orders->map(function ($order): void {
            $order->lines = $order->lines->map(function ($line): void {
                $line->name  = $line->variant?->name;
                $line->image = $line->variant?->image;

                if ($line->visits_sum_quantity >= $line->quantity) {
                    $line->quantity_to_deliver = 0;
                } else {
                    $line->quantity_to_deliver = $line->quantity - $line->visits_sum_quantity;
                }
            })->sortBy([
                ['status', 'desc'],
            ]);

            $order->customer_name    = $order->branch_id ? $order->branch?->name : $order->anonymousCustomerBranch?->name;
            $order->customer_address = $order->formatCustomerAddress();
            $order->location         = $this->getPinLocation($order);

            unset($order->branch);
            unset($order->anonymousCustomerBranch);
        });

        return $orders;
    }

    public static function formatOrderVisits(Collection|SupportCollection $visits): Collection|SupportCollection
    {
        $visits->each(function ($visit): void {
            $visit->lines = $visit->lines->map(function ($line): void {
                $line->name      = $line->orderLine->variant->name;
                $line->image     = $line->orderLine->variant->image;
                $line->packaging = $line->orderLine->packaging;
            });
        });

        return $visits;
    }

    private function getPinLocation(Order $order)
    {
        try {
            if ($order->branch) {
                return data_get($order->branch?->address, 'pin_location');
            }

            return data_get($order->anonymousCustomerBranch?->address, 'pin_location');
        } catch (\Exception) {
            return null;
        }
    }
}
