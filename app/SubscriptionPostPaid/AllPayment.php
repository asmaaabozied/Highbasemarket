<?php

namespace App\SubscriptionPostPaid;

use App\Enum\SubscriptionInviter;
use App\Events\InfluencerPaymentReminder;
use App\Events\PaymentReminder;
use App\Models\Admin;
use App\Models\Invitation;
use App\Models\QuotePayment;
use App\Models\User;
use Carbon\Carbon;

class AllPayment implements PostPaidStrategyInterface
{
    public function execute($quote, $attribute): void
    {
        $creator           = $quote->quote->creator_branch->account;
        $influencerId      = Invitation::query()->where('invitable_id', $creator->id)->first()?->admin_id;
        $influencerData    = [];
        $influencer_amount = 0;

        $influencer = User::query()
            ->where('userable_id', $influencerId)
            ->where('userable_type', Admin::class)
            ->first();

        $company_amount = $quote->price * ((float) $attribute->get('amountPerRequest') / 100);

        if ($influencer) {
            $influencer_amount = $company_amount * ($influencer->userable->influencer_percentage / 100);

            $data = [
                [
                    'name'                => SubscriptionInviter::BETWEEN_HIGH_BASE_AND_INFLUENCER,
                    'quoteId'             => $quote->id,
                    'amount'              => $influencer_amount,
                    'influencer_id'       => $influencerId,
                    'created_at'          => Carbon::now(),
                    'vendor_account_id'   => $creator->id,
                    'customer_account_id' => $quote->quote->vendor_branch->account->id,
                    'influencer_shared'   => $influencer->userable->influencer_percentage,
                ],
            ];

            $influencerData = [
                'influencer' => $influencer,
                'creator'    => $creator->name,
                'amount'     => $influencer_amount,
            ];
        }

        $data[] = [
            'name'                => SubscriptionInviter::BETWEEN_HIGH_BASE_AND_INFLUENCER,
            'quoteId'             => $quote->id,
            'amount'              => $company_amount - $influencer_amount,
            'influencer_id'       => null,
            'created_at'          => Carbon::now(),
            'vendor_account_id'   => $creator->id,
            'customer_account_id' => $quote->quote->vendor_branch->account->id,
            'influencer_shared'   => $attribute->get('amountPerRequest'),
        ];

        QuotePayment::query()->insert($data);

        $companyData = [
            'amount'            => $company_amount,
            'branch'            => $quote->quote->creator_branch,
            'creator'           => $creator,
            'influencer_amount' => $influencer_amount,
        ];

        PaymentReminder::dispatch($companyData);

        if (count($influencerData)) {
            InfluencerPaymentReminder::dispatch($influencerData);
        }
    }
}
