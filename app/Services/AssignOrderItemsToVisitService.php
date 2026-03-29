<?php

namespace App\Services;

use App\Enum\EmployeeVisitStatusEnum;
use App\Enum\VisitPurposeTypeEnum;
use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitLine;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\ScheduleVisit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class AssignOrderItemsToVisitService
{
    private Branch $seller;

    private Branch|AnonymousCustomerBranch $customer;

    private Employee $activeEmployee;

    private Employee $employee;

    private Order $order;

    private array|Collection $requestItems = [];

    private array|Collection $orderItems = [];

    private array $metadata = [];

    public function __construct(
        ?Branch $seller = null,
        Branch|AnonymousCustomerBranch|null $customer = null,
        ?Employee $employee = null,
        ?Order $order = null,
        ?Employee $activeEmployee = null,
        Collection|array|null $requestItems = [],
    ) {
        if ($seller instanceof \App\Models\Branch) {
            $this->seller = $seller;
        }

        if ($customer) {
            $this->customer = $customer;
        }

        if ($employee instanceof \App\Models\Employee) {
            $this->employee = $employee;
        }

        if ($activeEmployee instanceof \App\Models\Employee) {
            $this->activeEmployee = $activeEmployee;
        }

        if ($order instanceof \App\Models\Order) {
            $this->order = $order;
        }

        if ($requestItems) {
            $this->requestItems = is_array($requestItems) ? collect($requestItems) : $requestItems;
        }
    }

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

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function setActiveEmployee(Employee $activeEmployee): self
    {
        $this->activeEmployee = $activeEmployee;

        return $this;
    }

    public function setRequestItems(Collection|array $requestItems): self
    {
        $this->requestItems = $requestItems;

        return $this;
    }

    public function setMetadata(Collection|array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function process(): void
    {
        $this->getOrderItems();
        $this->validateEmployee();
        $this->validateOrderItems();

        $visit = $this->fetchEmployeeVisitInDate();

        if ($visit instanceof \App\Models\EmployeeVisit) {
            $this->assignItems($visit);

            return;
        }

        $schedule = $this->fetchEmployeeSchedule();

        $visit = $schedule instanceof \App\Models\ScheduleVisit ? $this->getVisitFromSchedule() : $this->createNewVisit();

        $this->assignItems($visit);
    }

    private function fetchEmployeeVisitInDate(): ?EmployeeVisit
    {
        return $this->employee->employeeVisits()
            ->where('scheduled_at', $this->metadata['date'] ?? now()->toDateString())
            ->where('visitable_id', $this->customer->id)
            ->where('visitable_type', $this->customer::class)
            ->whereNull('checkout_at')
            ->whereIn('status',
                [
                    EmployeeVisitStatusEnum::PENDING,
                    EmployeeVisitStatusEnum::ON_THE_WAY,
                    EmployeeVisitStatusEnum::SCHEDULED,
                    EmployeeVisitStatusEnum::VISITED,
                ]
            )
            ->first();
    }

    private function fetchEmployeeSchedule(): ?ScheduleVisit
    {
        $schedules = ScheduleVisit::where('employee_id', $this->employee->id)
            ->where('branch_id', $this->seller->id)
            ->where('visitable_id', $this->customer->id)
            ->where('visitable_type', $this->customer::class)
            ->get();

        if ($schedules->count()) {
            return $schedules->firstWhere(fn ($schedule): bool => $schedule->nextVisit()?->toDateString() === ($this->metadata['date'] ?? now()->toDateString()));
        }

        return null;
    }

    private function validateEmployee(): void
    {
        $this->validateEmployeeBelongsToSellerAccount();
        $this->validateEmployeeCanMakeVisits();
        $this->validateEmployeeCanAssignEmployee();
    }

    private function validateEmployeeBelongsToSellerAccount(): void
    {
        if ($this->employee->account_id !== $this->seller->account_id) {
            throw new AuthorizationException(__('Employee does not belong to the seller account.'));
        }
    }

    private function validateEmployeeCanMakeVisits(): void
    {
        if (! $this->employee->user->hasPermission('view employee visit')) {
            throw new AuthorizationException(__('Employee does not have permission to create visits.'));
        }
    }

    private function validateEmployeeCanAssignEmployee(): void
    {
        // in case the current active user of Admin type
        if (! $this->activeEmployee || $this->employee->id === $this->activeEmployee->id) {
            return;
        }

        $exists = $this->activeEmployee->myEmployees($this->seller, ['view employee visit'])->firstWhere('id', $this->employee->id);

        if (! $exists) {
            throw new AuthorizationException(__('the employee is not under your supervision, or Employee does not have the necessary permission.'));
        }
    }

    private function getOrderItems(): void
    {
        $this->orderItems = OrderLine::whereIn('id', collect($this->requestItems)->pluck('id')->toArray())
            ->with('product')
            ->withSum(['visits' => function ($q): void {
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
            }], 'quantity')
            ->get()->collect();
    }

    /**
     * @throws \Exception
     */
    private function validateOrderItems(): void
    {
        if (is_array($this->orderItems)) {
            $this->orderItems = collect($this->orderItems);
        }

        foreach ($this->orderItems as $item) {
            $this->validateSelectedQuantity($item);
            $this->itemBelongsToSeller($item);
            $this->validateItemBelongsToOrder($item);
        }
    }

    private function itemBelongsToSeller(OrderLine $line): void
    {
        if ($line->product->branch_id !== $this->seller->id) {
            throw new \Exception("Order line ID {$line->id} does not belong to the seller branch.");
        }
    }

    private function validateSelectedQuantity(OrderLine $line): void
    {
        $selected_quantity = $this->requestItems->firstWhere('id', $line->id)['quantity'] ?? 0;

        if ($selected_quantity < 0 || $selected_quantity > $line->quantity) {
            throw new AuthorizationException("Invalid selected quantity for order line ID {$line->id}.");
        }

        if ($selected_quantity + $line->visits_sum_quantity > $line->quantity) {
            throw new AuthorizationException("Selected quantity for order line ID {$line->id} exceeds available quantity.");
        }
    }

    private function validateItemBelongsToOrder(OrderLine $line): void
    {
        if ($line->order_id !== $this->order->id) {
            throw new AuthorizationException("Order line ID {$line->id} does not belong to the specified order.");
        }
    }

    private function getVisitFromSchedule(?ScheduleVisit $schedule = null)
    {
        if (! isset($this->metadata['schedule_id']) && ! $schedule) {
            throw new AuthorizationException('Schedule Not found for the given customer and Employee');
        }

        $scheduleId = $schedule instanceof \App\Models\ScheduleVisit ? $schedule->id : $this->metadata['schedule_id'];

        $visit = EmployeeVisit::where('employee_id', $this->employee->id)
            ->where('visitable_id', $this->customer->id)
            ->where('visitable_type', $this->customer::class)
            ->where('status', EmployeeVisitStatusEnum::SCHEDULED)
            ->where('schedule_visit_id', $scheduleId)
            ->where('scheduled_at', $this->metadata['date'] ?? now()->toDateString())
            ->first();

        if (! $visit) {
            return $this->makeVisitFromSchedule($schedule);
        }

        return $visit;
    }

    private function makeVisitFromSchedule(?ScheduleVisit $schedule = null): EmployeeVisit
    {
        if (! $schedule instanceof \App\Models\ScheduleVisit) {
            $schedule = ScheduleVisit::where('id', $this->metadata['schedule_id'])
                ->where('employee_id', $this->employee->id)
                ->where('branch_id', $this->seller->id)
                ->firstOrFail();
        }

        if (! $schedule) {
            throw new AuthorizationException('Schedule visit not found for the given ID and employee.');
        }

        return EmployeeVisit::create([
            'employee_id'       => $this->employee->id,
            'visitable_id'      => $this->customer->id,
            'visitable_type'    => $this->customer::class,
            'status'            => EmployeeVisitStatusEnum::SCHEDULED,
            'scheduled_at'      => $schedule->nextVisit(),
            'purpose'           => VisitPurposeTypeEnum::ORDER_DELIVERY->value,
            'source_type'       => 'scheduled',
            'schedule_visit_id' => $schedule->id,
            'created_by'        => $this->activeEmployee?->user?->id ?? null,
        ]);
    }

    private function createNewVisit(): EmployeeVisit
    {
        return EmployeeVisit::create([
            'employee_id'    => $this->employee->id,
            'visitable_id'   => $this->customer->id,
            'visitable_type' => $this->customer::class,
            'branch_id'      => $this->seller->id,
            'status'         => EmployeeVisitStatusEnum::PENDING,
            'scheduled_at'   => $this->metadata['date'] ?? now()->toDateString(),
            'purpose'        => VisitPurposeTypeEnum::ORDER_DELIVERY->value,
            'source_type'    => 'manual',
            'created_by'     => $this->activeEmployee?->user?->id ?? null,
        ]);
    }

    private function existedAssign(EmployeeVisit $visit, OrderLine|array $line): ?EmployeeVisitLine
    {
        $lineId = is_array($line) ? $line['id'] : $line->id;

        return $visit->lines()->where('order_line_id', $lineId)->first();
    }

    private function updateExitedCarriedLine(EmployeeVisitLine $existedAssign, int $newQuantity): void
    {
        $newQuantity = $existedAssign->quantity + $newQuantity;

        $orderLine = $this->orderItems->firstWhere('id', $existedAssign->order_line_id);

        if ($newQuantity > $orderLine->quantity) {
            $newQuantity = $orderLine->quantity;
        }

        $existedAssign->update([
            'quantity' => $newQuantity,
        ]);
    }

    public function assignItems(EmployeeVisit $visit): void
    {
        $items = [];

        foreach ($this->requestItems as $requestedItem) {
            $existedAssign = $this->existedAssign($visit, $requestedItem);

            if ($existedAssign && $existedAssign->quantity >= $this->orderItems->firstWhere('id', $requestedItem['id'])->quantity) {
                continue;
            }

            if ($existedAssign && $existedAssign->quantity < $this->orderItems->firstWhere('id', $requestedItem['id'])->quantity) {
                $this->updateExitedCarriedLine(
                    $existedAssign,
                    $requestedItem['quantity']
                );
                continue;
            }

            $items[] = EmployeeVisitLine::make([
                'order_line_id' => $requestedItem['id'],
                'quantity'      => $requestedItem['quantity'],
            ]);
        }

        $visit->lines()->saveMany($items);
    }

    public static function make(
        ?Branch $seller = null,
        Branch|AnonymousCustomerBranch|null $customer = null,
        ?Employee $employee = null,
        ?Order $order = null,
        ?Employee $activeEmployee = null,
        Collection|array|null $requestItems = [],
    ): AssignOrderItemsToVisitService {
        return new self(
            seller: $seller,
            customer: $customer,
            employee: $employee,
            order: $order,
            activeEmployee: $activeEmployee,
            requestItems: $requestItems,
        );
    }
}
