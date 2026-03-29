<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitLine;
use App\Models\OrderLine;
use DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class OrderItemsDeliveryService
{
    private Branch $seller;

    private Employee $employee;

    private Collection $items;

    private Collection|\Illuminate\Database\Eloquent\Collection $visitLines;

    public function __construct(?Branch $seller = null, ?Employee $employee = null, ?Collection $items = null)
    {
        if ($seller instanceof \App\Models\Branch) {
            $this->setSeller($seller);
        }

        if ($employee instanceof \App\Models\Employee) {
            $this->setEmployee($employee);
        }

        if ($items instanceof \Illuminate\Support\Collection) {
            $this->setItems($items);
        }
    }

    private function setSeller(Branch $seller): void
    {
        $this->seller = $seller;
    }

    public function setEmployee(Employee $employee): self
    {
        $this->employee = $employee;

        return $this;
    }

    public function setItems(Collection $items): self
    {
        $this->items = $items;

        $this->getVisitLines();

        return $this;
    }

    private function getVisitLines(): void
    {
        $this->visitLines = EmployeeVisitLine::whereIn('id', $this->items->pluck('id'))
            ->with('employeeVisit', function ($query): void {
                $query->select('id', 'branch_id', 'employee_id', 'scheduled_at')
                    ->with('branch:id,account_id,name,slug');
            })
            ->with('orderLine', function ($query): void {
                $query
                    ->select('id', 'order_id', 'product_id', 'variant_id', 'quantity', 'status', 'delivered_at')
                    ->with('variant:id,name');
            })
            ->get();
    }

    public function confirmDelivery(): void
    {
        DB::transaction(function (): void {
            $this->visitLines->each(function (EmployeeVisitLine $line): void {
                $this->validateEmployee($line->employeeVisit);
                $this->validateSeller($line->employeeVisit);
                $this->validateOrderLine($line->orderLine);

                if ($line->status === 'delivered') {
                    return;
                }

                $line->update(['status' => 'delivered']);

                $line->orderLine->update([
                    'status'       => 'delivered',
                    'delivered_at' => now(),
                ]);
            });
        });
    }

    private function validateSeller(EmployeeVisit $visit): void
    {
        if ($this->seller->id === $visit->branch_id) {
            return;
        }

        throw new AuthorizationException(__("Trying to access a resource doesn't belongs to your current active branch"));
    }

    private function validateEmployee(EmployeeVisit $visit): void
    {
        if (
            $visit->employee_id === $this->employee->id ||
            $this->employee->myEmployees($visit->branch)->where('id', $this->employee->id)->exists()
        ) {
            return;
        }

        throw new AuthorizationException(__('You are not authorized to confirm delivery for this item.'));
    }

    private function validateOrderLine(OrderLine $orderLine): void
    {
        if (! in_array($orderLine->status, ['pending', 'approved', 'shipped'])) {
            throw new AuthorizationException(
                __('item :name cannot be delivered due because the current status is :status, but you can only deliver items only when the status in: :statuses',
                    [
                        'name'    => $orderLine->variant->name,
                        'status'  => $orderLine->variant->status,
                        'statues' => implode(', ', ['pending', 'approved', 'shipped']),
                    ])
            );
        }
    }

    public static function make(?Branch $seller = null, ?Employee $employee = null, ?Collection $items = null): self
    {
        return new self($seller, $employee, $items);
    }
}
