<?php

namespace App\Services;

use App\Dto\CommissionCalculationResultDto;
use App\Enum\CurrencyEnum;
use App\Enum\GccCountryEnum;
use App\Enum\ModuleEnum;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Stock;
use App\PlanExceptionStrategy\Product;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CalculateItemCommissionService
{
    private Collection|array $module;

    private ?Plan $plan = null;

    private readonly FxRateService $fxRateService;

    public function __construct(
        private readonly Branch $seller,
        private readonly Stock $stock,
        private readonly float $total,
        private readonly ?Branch $buyer = null,
    ) {
        $this->fxRateService = app(FxRateService::class);
    }

    /**
     * Calculates commission for a stock item based on the seller's plan and order status.
     *
     * @throws Exception
     */
    public function process(): CommissionCalculationResultDto
    {
        // Load the seller's active plan
        $this->getPlan();

        // Load the applicable order module based on exceptions or default plan
        $this->getOrderModule();

        // Determine if this is the first order from the buyer to the seller
        $isFirstOrder = ! $this->buyer?->isCustomerOf($this->seller);
        $configKey    = $isFirstOrder ? 'first' : 'others';

        // Get the commission value and whether it's percentage based
        $commissionValue = $this->module->get("{$configKey}_commission_amount");
        $isPercentage    = $this->module->get("{$configKey}_is_percentage", true);

        // Calculate amount in local currency
        $amountInLocalCurrency = $isPercentage
            ? $this->total * ($commissionValue / 100)
            : $commissionValue;

        // Get seller's country and corresponding currency
        //        $sellerCountry = $this->seller->address['country'] ?? GccCountryEnum::BH;
        //        $localCurrency = $this->countryService->getCountryCurrency($sellerCountry);

        $localCurrency = $this->stock->currency ?? CurrencyEnum::BHD->value;

        // Get the exchange rate from local currency to USD
        $fxRate = $this->fxRateService->getRate(from: $localCurrency);

        // Convert the amount to USD
        $amountInUsd = $amountInLocalCurrency * $fxRate;

        // Build and return the result DTO with commission details
        return new CommissionCalculationResultDto(
            percent: $isPercentage ? $commissionValue : null,
            amountInLocalCurrency: round($amountInLocalCurrency, 2),
            amountInUsd: round($amountInUsd, 2),
            localCurrency: $this->stock->currency,
            exchangeRateToUsd: $fxRate,
            planId: $this->plan->id,
            exceptionType: $this->getModuleByException()?->exceptionable_type,
        );
    }

    private function getPlan(): void
    {
        $this->plan = $this->seller->activePlan('local');

        if (! $this->plan instanceof \App\Models\Plan) {
            throw new Exception(__('No active plan found for branch :branch .', ['branch' => $this->seller->name]));
        }
    }

    /**
     * @throws Exception
     */
    public function getOrderModule(): void
    {
        if ($this->plan->exceptions()->count()) {
            $exception    = $this->getModuleByException();
            $plan_module  = collect($exception->attributes)->firstWhere('name', ModuleEnum::Order->value);
            $this->module = collect($plan_module['attribute'] ?? [])->pluck('value', 'name');
        } else {
            $this->module = ActiveSubscriptionService::make($this->seller)
                ->localPlan()
                ->getModule(ModuleEnum::Order);
        }

        if (! $this->module) {
            throw new Exception(__('No active module found for branch :branch .', ['branch' => $this->seller->name]));
        }
    }

    private function getModuleByException(): ?Model
    {
        if (($productException = $this->getProductException()) instanceof \Illuminate\Database\Eloquent\Model) {
            return $productException;
        }

        if (($brandException = $this->getBrandException()) instanceof \Illuminate\Database\Eloquent\Model) {
            return $brandException;
        }

        return $this->getCategoryException();
    }

    private function getProductException(): ?Model
    {
        $exception = $this->plan->exceptions()
            ->where('exceptionable_type', Product::class)
            ->where('exceptionable_id', $this->stock->product->id)
            ->first();

        return $exception?->attributes()?->first();
    }

    private function getBrandException(): ?Model
    {
        $exception = $this->plan->exceptions()
            ->where('exceptionable_type', Brand::class)
            ->where('exceptionable_id', $this->stock->product->brand_id)
            ->first();

        return $exception?->attributes()?->first();
    }

    private function getCategoryException(): ?Model
    {
        $exception = $this->plan->exceptions()
            ->where('exceptionable_type', Category::class)
            ->whereIn('exceptionable_id', $this->getCategoryChane())
            ->first();

        return $exception?->attributes()?->first();
    }

    private function getCategoryChane(): array
    {
        $categories = [];

        $category = $this->stock->product->category;

        while ($category) {
            $categories[] = $category->id;
            $category     = $category->parent;
        }

        return $categories;
    }

    public static function make(Branch $seller, Stock $stock, float $total, ?Branch $buyer = null): self
    {
        return new self(seller: $seller, stock: $stock, total: $total, buyer: $buyer);
    }
}
