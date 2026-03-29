<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Notifications\OrderPaymentNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class OrderPaymentService
{
    private bool $is_buyer = false;

    private int $available_amount;

    private int $product_branch_id;

    private array $payment_items = [];

    public function __construct(private readonly Order $order, private readonly ?Branch $branch = null, private array $paymentData = [])
    {
        $this->available_amount = ($this->paymentData['amount'] ?? 0.0) * 1000;
        $this->isBuyer();
    }

    /**
     * @throws \Exception
     */
    public function process(): self
    {
        $lines = $this->getLines();

        $this->validateItems($lines);

        $lines->each(function (OrderLine $line): void {
            if (! $this->canAccessLine($line)) {
                return;
            }

            if ($line->paid_at) {
                throw new \Exception(__('Order line :line is already paid', ['line' => $line->id]));
            }

            $this->makePayment($line);
        });

        $this->validateAvailableAmount();

        return $this;
    }

    private function getLines(): Collection|\Illuminate\Database\Eloquent\Collection
    {
        if ($this->paymentData['items'] ?? false) {
            return $this->order->lines()
                ->whereIn('id', $this->paymentData['items'])
                ->with('product:id,branch_id')
                ->get();
        }

        if ($this->is_buyer) {
            return $this->order->lines()
                ->with('product:id,branch_id')
                ->get();
        }

        return $this->order->lines()
            ->whereHas('product', function ($query): void {
                $query->where('branch_id', $this->branch->id);
            })
            ->with('product:id,branch_id')
            ->get();

    }

    public function validateItems(Collection|\Illuminate\Database\Eloquent\Collection $lines): void
    {
        $branch_id = $lines->pluck('product.branch_id')->unique()->values();

        if ($branch_id->count() > 1) {
            throw ValidationException::withMessages(['items' => __('Only one vendor can be paid at a time')]);
        }
    }

    private function isBuyer(): void
    {
        $this->is_buyer = $this->branch->id === $this->order->branch_id;
    }

    /**
     * @throws \Exception
     */
    private function canAccessLine(OrderLine $line): bool
    {
        if (! isset($this->product_branch_id)) {
            $this->product_branch_id = $line->product->branch_id;
        }

        if ($this->product_branch_id !== $line->product->branch_id) {
            throw new \Exception(__('Only one vendor can be paid at a time'));
        }

        if ($this->is_buyer) {
            return true;
        }

        if ($line->product->branch_id === $this->branch?->id) {
            return true;
        }

        throw new \Exception('Access denied to order line '.$line->id);
    }

    private function makePayment(OrderLine $line): void
    {
        $amount = $this->getPaymentAmount($line);

        $this->available_amount -= ($amount * 1000);

        if ($this->available_amount < 0) {
            throw new \Exception('Insufficient payment amount available');
        }

        $this->payment_items[] = $line->payments()->make([
            'amount'        => $amount,
            'order_line_id' => $line->id,
        ]);
    }

    private function getPaymentAmount(OrderLine $line): float
    {
        $payments_sum = $line->payments()
            ->whereHas('payment', function ($query): void {
                $query->where('status', 'confirmed');
            })
            ->sum('amount');

        $remaining_amount = $line->total - $payments_sum;

        if ($remaining_amount <= 0) {
            return 0.0;
        }

        return $remaining_amount;
    }

    /**
     * @throws \Exception
     */
    public function getPaymentItems(): array
    {
        if ($this->payment_items === []) {
            $this->process();
        }

        return $this->payment_items;
    }

    public function validateAvailableAmount(): void
    {
        if ($this->available_amount !== 0) {
            $this->available_amount /= 1000;
        }

        if ($this->available_amount > 0) {
            throw new \Exception(__('The payment amount exceeds the selected Items Total Price.'));
        }
    }

    /**
     * @throws \Exception
     */
    public function saveItems(Payment $payment): void
    {
        $payment->items()->saveMany($this->getPaymentItems());
    }

    public static function make(Order $order, ?Branch $branch = null, array $paymentData = []): self
    {
        return new self($order, $branch, $paymentData);
    }

    /**
     * @throws \Exception
     */
    public function create(): void
    {
        $status = Payment::statusForOrder($this->order, $this->branch);

        $payment = Payment::create([
            'order_id'          => $this->order->id,
            'branch_id'         => $this->product_branch_id,
            'employee_id'       => auth()->user()->userable_id,
            'amount'            => $this->paymentData['amount'],
            'pending'           => $this->order->remainingAmount() - $this->paymentData['amount'],
            'type'              => 'payment',
            'attachment'        => $this->paymentData['attachment'] ?? null,
            'status'            => $status,
            'confirmation_date' => $status === 'confirmed' ? now() : null,
            'confirmed_by'      => $status === 'confirmed' ? auth()->user()->userable_id : null,
        ]);

        $this->saveItems($payment);

        defer(function () use ($payment): void {
            $payment->payItems();

            $this->sendNotifications($payment);
        });
    }

    public function sendNotifications(Payment $payment): void
    {
        if ($this->branch->id !== $this->product_branch_id) {
            Bus::dispatch(function () use ($payment): void {
                $branch    = Branch::find($this->product_branch_id);
                $receivers = NotificationReceivers::make($branch->account, $branch, ['view order'])->get();

                Notification::send($receivers, new OrderPaymentNotification($this->order, $payment, $this->branch));
            });
        }
    }
}
