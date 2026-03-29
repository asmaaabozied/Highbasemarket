<?php

namespace App\Services;

use App\Enum\ModuleEnum;
use App\Models\QuotePayment;
use App\SubscriptionPostPaid\PostPaidStrategyContext;

class PercentagePaymentService
{
    /**
     * @throws \Exception
     */
    public static function payment($quote, $step): void
    {
        $payment = QuotePayment::query()
            ->where('vendor_account_id', $quote->quote->creator_branch->account->id)
            ->where('customer_account_id', $quote->quote->vendor_branch->account->id);

        $module = ActiveSubscriptionService::make()
            ->globalPlan()
            ->getModule(ModuleEnum::ADD_CUSTOMER);

        if ($module->get('is_percentage') && ! $step) {
            $payment->update(['quote_status' => 'done']);
        }

        if ($module->get('is_percentage') && ! $payment->first()) {
            $strategy = new PostPaidStrategyContext('Between highbase and influencer');

            $strategy->execute($quote, $module);
        }
    }
}
