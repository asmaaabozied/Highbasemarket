<?php

namespace App\Services;

use App\Dto\CommissionCalculationResultDto;
use App\Enum\OrderTypeEnum;
use App\Models\Branch;
use App\Models\OrderLine;
use App\Models\Stock;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class OrderLineService
{
    private Stock $product;

    private OrderLine $orderLine;

    private float $price;

    private array $discountData;

    public function __construct(
        private readonly array $data,
        public readonly ?int $index,
        public readonly ?string $payment_method,
        public readonly ?string $order_type = OrderTypeEnum::NORMAL_ORDER->value,
        private readonly ?Branch $buyer = null,
        private readonly ?string $status = null,
    ) {
        $this->getProduct();

        $this->validatePackaging();

        $this->validateStock();

        $this->getPrice();

        $this->disabled();

        $this->allowedProduct();

        $this->validateCredit();

        $this->applyDiscount();
    }

    private function getProduct(): void
    {
        $this->product = Stock::where('stocks.id', $this->data['product_id'])
            ->with('product.category')
            ->when($this->buyer, function ($query): void {
                $query->WithSpecialPrices($this->buyer);
            })
            ->first();
    }

    private function validatePackaging(): void
    {
        if (is_array($this->product->packages) && count($this->product->packages) > 0) {
            $packages = collect($this->product->packages)->pluck('name')->toArray();

            if (! in_array($this->data['packaging'], $packages)) {
                throw ValidationException::withMessages(["cart.$this->index.item" => __('Invalid selected packaging')]);
            }

            return;
        }

        if (is_object($this->product->packaging) && $this->product->packaging != null) {
            $packages = collect($this->product->packaging)->pluck('name')->toArray();

            if (! in_array($this->data['packaging'], $packages)) {
                throw ValidationException::withMessages(["cart.$this->index.item" => __('Invalid selected packaging')]);
            }

            return;
        }

        if ($this->data['packaging'] != $this->product->packaging) {
            throw ValidationException::withMessages(["cart.$this->index.item" => __('Invalid selected packaging')]);
        }
    }

    private function validateStock(): void
    {
        if ($this->data['quantity'] > $this->product->quantity) {
            throw ValidationException::withMessages(["cart.$this->index.item" => __('Insufficient stock')]);
        }

        if ($this->data['quantity'] < $this->product->moq) {
            throw ValidationException::withMessages([
                "cart.$this->index.item" => __('Quantity should be :min or More', ['min' => $this->product->moq]),
            ]);
        }

        if ($this->product->expiration_date && $this->product->expiration_date < now()) {
            throw ValidationException::withMessages(["cart.$this->index.item" => __('Product is expired')]);
        }
    }

    private function getPrice(): void
    {
        if ($this->isInstantOrder()) {
            $this->price = $this->product->price;

            return;
        }

        $this->validateActivePrice();

        $this->price = $this->product->getPrice();

        if ($this->product->special_prices) {
            return;
        }

        if ($this->product->selling_price) {
            $this->price = $this->product->selling_price;
        }

        if ($this->product->tiers) {
            $tier = collect($this->product->tiers)->first(fn (array $tier): bool => $tier['min'] <= $this->data['quantity']);

            if ($tier) {
                $this->price = $tier['price'];
            }
        }
    }

    private function isInstantOrder(): bool
    {
        return $this->order_type === OrderTypeEnum::INSTANT_ORDER->value;
    }

    private function validateActivePrice(): void
    {
        if (! $this->product->show_price && ! $this->isInstantOrder()) {
            throw ValidationException::withMessages(["cart.$this->index.item" => __('Product is not available')]);
        }
    }

    private function disabled(): void
    {
        if ($this->product->status != 'active') {
            throw new AuthorizationException(__('Product is not available'));
        }

    }

    private function allowedProduct(): void
    {
        if ($this->isInstantOrder()) {
            return;
        }

        if ($this->product->branch->account_id === currentBranch()->account_id) {
            throw new AuthorizationException(__('Can not order your own product'));
        }
    }

    private function validateCredit(): void
    {
        if ($this->payment_method != 'credit') {
            return;
        }

        if (! $this->buyer->isCustomerOf($this->product->branch)) {
            throw new AuthorizationException(__('You are not allowed to order on credit'));
        }

        if (! $this->product->allow_credit) {
            throw new AuthorizationException(__('Product does not allow credit orders'));
        }

        if ($this->product->credit_limit < $this->price * $this->data['quantity']) {
            throw ValidationException::withMessages(["cart.$this->index.item" => __('Credit limit exceeded for this product')]);
        }

        if (! $this->buyer->canPurchaseByCredit($this->product->branch)) {
            throw new AuthorizationException(__('You are not allowed to purchase on credit'));
        }
    }

    private function applyDiscount(): void
    {
        if ($this->isInstantOrder() && isset($this->data['discount'])) {
            $discount            = $this->data['discount'];
            $maxProductDiscount  = $this->product->max_discount_percentage ?? 0;
            $maxEmployeeDiscount = auth()->user()->userable->max_discount_percentage ?? 0;

            if (! auth()->user()->isAdministrator() && ($discount > $maxProductDiscount || $discount > $maxEmployeeDiscount)) {
                throw ValidationException::withMessages(["cart.$this->index.item" => __('Discount exceeds allowed limit')]);
            }

            $originalPrice = $this->price;

            $this->price = $originalPrice - ($originalPrice * ($discount / 100));

            $this->discountData = [
                'original_price'   => $originalPrice,
                'discounted_price' => $this->price,
                'discount_percent' => $discount,
                'product_id'       => $this->product->id,
            ];
        } else {
            $this->discountData = [];
        }

    }

    public function getDiscountData(): array
    {
        return $this->discountData;
    }

    public function get(): OrderLine
    {
        $this->makeOrderLine();

        return $this->orderLine;
    }

    public function makeOrderLine(): void
    {
        $totalPrice = $this->price * $this->data['quantity'];

        try {
            $commissionResult = CalculateItemCommissionService::make(
                seller: $this->product->branch,
                stock: $this->product,
                total: $totalPrice,
                buyer: $this->buyer
            )->process();

        } catch (Exception) {
            // Fallback: 10% of total
            $commissionResult = new CommissionCalculationResultDto(
                percent: 10.0,
                amountInLocalCurrency: $totalPrice * 0.10,
                amountInUsd: ($totalPrice * 0.10) / 2.65,
                localCurrency: $this->product->currency ?? 'BHD',
                exchangeRateToUsd: 2.65,
                planId: null,
                exceptionType: null
            );
        }

        if (! $this->isInstantOrder()) {
            $orderLineStatus = 'pending';
        } elseif ($this->status) {
            $orderLineStatus = $this->status;
        } else {
            $orderLineStatus = 'approved';
        }

        $this->orderLine = OrderLine::make([
            'product_id' => $this->data['product_id'],
            'variant_id' => $this->product->variant_id,
            'packaging'  => $this->data['packaging'],
            'quantity'   => $this->data['quantity'],
            'price'      => $this->price,
            'total'      => $totalPrice,
            'currency'   => $this->product?->currency ?? 'BHD',
            'status'     => $orderLineStatus,
            // New commission fields
            'commission_percentage'            => $commissionResult->percent,
            'commission_amount_local_currency' => $commissionResult->amountInLocalCurrency,
            'commission_local_currency_code'   => $commissionResult->localCurrency,
            'commission_amount_usd'            => $commissionResult->amountInUsd,
            'exchange_rate_to_usd'             => $commissionResult->exchangeRateToUsd,
            'applied_plan_id'                  => $commissionResult->planId,
            'plan_exception_source_type'       => $commissionResult->exceptionType,

            'hb_commission' => $commissionResult->amountInLocalCurrency,
        ]);

    }
}
