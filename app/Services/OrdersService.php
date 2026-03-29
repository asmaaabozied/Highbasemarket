<?php

namespace App\Services;

use App\Actions\Orders\ValidateCartCouponsAction;
use App\Enum\OrderTypeEnum;
use App\Events\OrderCreated;
use App\Jobs\SendOrderNotificationsJob;
use App\Models\Branch;
use App\Models\CouponUsage;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrdersService
{
    private readonly string $payment_method;

    private readonly string $order_type;

    public function __construct(private array $data, private readonly ?Branch $buyer = null)
    {
        $this->payment_method = $data['payment_method'] ?? 'credit';
        $this->order_type     = $data['source'] ?? OrderTypeEnum::NORMAL_ORDER->value;

        if (! in_array($this->payment_method, ['cash', 'credit', 'bank_transfer'])) {
            throw ValidationException::withMessages(['error' => __('Invalid payment method provided.')]);
        }

        if (empty($data['products']) || ! is_array($data['products'])) {
            throw ValidationException::withMessages(['error' => __('Products data is required and must be an array.')]);
        }
    }

    public static function make(array $data, ?Branch $buyer = null): self
    {
        return new self($data, $buyer);
    }

    public static function OrderStatus(Order $order)
    {
        $line_statuses = [
            'pending',
            'rejected',
            'approved',
            'paid',
            'delivered',
        ];

        $status = $order->status;

        $unified_status = $order->lines->pluck('status')->unique();

        if ($unified_status->count() === 1) {
            return $unified_status->first();
        }

        $unified_status->each(function ($line) use (&$status, $line_statuses): void {
            $status = $line_statuses[max(array_search($line, $line_statuses), array_search($status, $line_statuses))];
        });

        return "partially $status";
    }

    public static function OrderCommission(Order $order): float|int
    {
        $commission = 0;

        $order->lines->each(function ($line) use (&$commission): void {
            if (in_array($line->status, ['cancelled', 'rejected'])) {
                return;
            }
            $commission += $line->hb_commission;
        });

        return $commission;
    }

    /**
     * @throws Throwable
     */
    public function process(): ?Order
    {
        $lines     = [];
        $discounts = collect();

        foreach ($this->data['products'] as $index => $product) {
            $service = new OrderLineService(
                data: $product,
                index: $index,
                payment_method: $this->payment_method,
                order_type: $this->order_type,
                buyer: $this->buyer,
                status: $this->data['status'] ?? null,
            );

            $lines[] = $service->get();

            $discounts->push($service->getDiscountData());
        }

        if ($this->isNormalOrder()) {
            $this->validateMinOrderValue($lines);
        }

        return DB::transaction(function () use ($lines, $discounts) {
            $branchId                  = null;
            $anonymousCustomerId       = null;
            $anonymousCustomerBranchId = null;

            if ($this->isNormalOrder()) {
                $branchId = $this->buyer?->id;
            }

            if ($this->isInstantOrder()) {
                if (! empty($this->data['type']) && $this->data['type'] === 'existing') {
                    $branchId = $this->buyer?->id;
                }

                $anonymousCustomerId       = $this->data['anonymous_customer_id'] ?? null;
                $anonymousCustomerBranchId = $this->data['anonymous_customer_branch_id'] ?? null;
            }

            $couponData    = app(ValidateCartCouponsAction::class)->execute($this->data['coupons'] ?? [], collect($lines), $this->buyer);
            $subtotal      = collect($lines)->sum('total');
            $totalDiscount = $couponData->sum('discount_amount');

            $order = Order::create([
                'branch_id'                    => $branchId,
                'employee_id'                  => $this->isNormalOrder() ? Auth::user()?->userable_id : null,
                'seller_employee_id'           => $this->isInstantOrder() ? Auth::user()?->userable_id : null,
                'subtotal'                     => $subtotal,
                'total'                        => $subtotal - $totalDiscount,
                'payment_method'               => $this->payment_method,
                'order_type'                   => $this->order_type,
                'anonymous_customer_id'        => $anonymousCustomerId,
                'anonymous_customer_branch_id' => $anonymousCustomerBranchId,
                'discount_amount'              => $totalDiscount,
            ]);

            if ($this->payment_method === 'credit') {
                $this->validateCredit($lines);
            }

            $order->lines()->saveMany($lines);

            if ($this->isInstantOrder() && ($this->data['status'] ?? null) === 'approved') {
                collect($lines)->each(function ($line): void {
                    $line->product->decreaseStock($line->quantity);
                });
            }

            $discounts = $discounts->keyBy('product_id');

            if ($this->isInstantOrder()) {
                $this->createTradeDiscounts($lines, $discounts);
            }

            DB::afterCommit(function () use ($order, $couponData): void {
                (new CleanCartService($this->data['products']))->process();

                if ($couponData->isNotEmpty()) {
                    foreach ($couponData as $validatedCoupon) {
                        CouponUsage::create([
                            'coupon_id'       => $validatedCoupon['coupon_id'],
                            'user_id'         => Auth::id(),
                            'customer_id'     => $order->branch_id,
                            'order_id'        => $order->id,
                            'discount_amount' => $validatedCoupon['discount_amount'],
                        ]);
                    }
                }
                SendOrderNotificationsJob::dispatch($order);
            });

            event(new OrderCreated($order));

            return $order;
        });
    }

    private function isNormalOrder(): bool
    {
        return $this->order_type === OrderTypeEnum::NORMAL_ORDER->value;
    }

    private function isInstantOrder(): bool
    {
        return $this->order_type === OrderTypeEnum::INSTANT_ORDER->value;
    }

    public function validateCredit(array $lines): void
    {
        collect($lines)->groupBy(fn ($line) => $line->product->branch_id)->each(function (Collection $group, $branch_id): void {
            $branch = $group->first()->product->branch;

            if (! $branch || ! $this->buyer->isCustomerOf($branch)) {
                throw new AuthorizationException(__('Branch not found or not a customer'));
            }

            if (! $this->buyer->canPurchaseByCredit($branch)) {
                throw new AuthorizationException(__('You can not purchase on credit from this branch'));
            }

            $config = $this->buyer->myVendors()
                ->where('vendor_id', $branch->id)
                ->first()?->config;

            $config = $config['credit_settings'] ?? [];

            $this->validateBills($this->buyer, $branch, $config);
            $this->validateTotalCredit($this->buyer, $branch, $config, $group);
            $this->validateLimitPerOrder($group, $config);
        });
    }

    public function validateBills(Branch $buyer, Branch $vendor, array $config): void
    {
        $bills = $config['number_of_bills'] ?? null;

        if (! $bills) {
            return;
        }

        $orders_count = Order::where('branch_id', $buyer->id)
            ->where('payment_method', 'credit')
            ->whereHas('lines', function ($query) use ($vendor): void {
                $query->whereHas('product', function ($query) use ($vendor): void {
                    $query->where('branch_id', $vendor->id);
                })
                    ->whereNotIn('status', ['rejected', 'cancelled'])
                    ->whereNull('paid_at');
            })
            ->count();

        if ($orders_count >= $bills) {
            throw new AuthorizationException(__('You have reached the maximum number of bills allowed for this vendor'));
        }
    }

    public function validateTotalCredit(Branch $buyer, Branch $vendor, array $config, $lines): void
    {
        $limit = $config['maximum_credit_limit'] ?? null;

        if (! $limit) {
            return;
        }

        $orders = Order::where('branch_id', $buyer->id)
            ->where('payment_method', 'credit')
            ->withSum([
                'lines' => function ($query) use ($vendor): void {
                    $query
                        ->whereNotIn('status', ['rejected', 'cancelled'])
                        ->whereHas('product', function ($query) use ($vendor): void {
                            $query->where('branch_id', $vendor->id);
                        })->whereNull('paid_at');
                },
            ], 'total')
            ->get();

        $total = $orders->sum('lines_sum_total');

        if ($total + $lines->sum('total') >= $limit) {
            throw new AuthorizationException(__('You have reached the maximum credit limit allowed for this vendor'));
        }

        if (is_array($lines)) {
            $total += collect($lines)->sum('total');
        } else {
            $total += $lines->sum('total');
        }

        if ($total > $limit) {
            throw new AuthorizationException(__('The total amount exceeds the allowed credit limit for this vendor'));
        }
    }

    public function validateLimitPerOrder($lines, array $config): void
    {
        $limit = $config['credit_limit_per_order'] ?? null;

        if (! $limit) {
            return;
        }

        $total = is_array($lines) ? collect($lines)->sum('total') : $lines->sum('total');

        if ($total > $limit) {
            throw new AuthorizationException(__('The total amount exceeds the allowed limit for this order'));
        }
    }

    private function createTradeDiscounts(array $lines, Collection $discounts): void
    {
        foreach ($lines as $line) {
            $discount = $discounts->get($line->product_id);

            if (! $discount) {
                continue;
            }
            $line->tradeDiscount()->create([
                'branch_id'        => currentBranch()->id,
                'employee_id'      => auth()->user()->userable_id,
                'original_price'   => $discount['original_price'],
                'discounted_price' => $discount['discounted_price'],
                'discount_percent' => $discount['discount_percent'],
            ]);
        }
    }

    private function validateMinOrderValue(array $lines): void
    {

        collect($lines)->groupBy(fn ($line) => $line->product->branch_id)->each(function (Collection $group, $branch_id): void {
            $branch = $group->first()->product->branch;

            $total = $group->sum('total');

            if ($branch->config && isset($branch->config['min_order_amount'])) {
                $minOrderValue = $branch->config['min_order_amount'];

                if ($total < $minOrderValue) {
                    throw ValidationException::withMessages([
                        'error' => __(
                            'One of the suppliers requires a minimum order value of :value',
                            ['value' => $minOrderValue.' '.$group->first()->product->currency]
                        ),
                    ]);
                }
            }
        });
    }
}
