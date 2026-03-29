<?php

namespace App\Services;

use App\Enum\SubscriptionType;
use App\Models\Account;
use App\Models\Admin;
use App\Models\Invitation;
use App\Models\QuotePayment;

class InfluencerService
{
    public function getInfluencerOrders(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return QuotePayment::query()
            ->select(

                'accounts.id as account_id',
                'accounts.name as account_name',
                'quote_payments.*',
                'users.first_name as user_name',
                'branches.name as by_branch_name',
            )
            ->when(auth()->user()->isInfluencer(), function ($where): void {
                $where->where('quote_payments.influencer_id', auth()->user()->userable_id);
            })
            ->leftJoin('invitations', function ($join): void {
                $join->on('invitations.invitable_id', 'quote_payments.vendor_account_id')
                    ->when(auth()->user()->isInfluencer(), function ($query): void {
                        $query->where('invitations.admin_id', auth()->user()->userable_id);
                    });
            })
            ->leftJoin('accounts', 'accounts.id', 'invitations.invitable_id')
            ->join('quote_details', 'quote_details.id', 'quote_payments.quoteId')
            ->join('quotes', 'quote_details.quote_id', 'quotes.id')
            ->join('branches', 'branches.id', 'quotes.creator')
            ->leftJoin('users', function ($join): void {
                $join->on('users.userable_id', 'invitations.admin_id')
                    ->where('users.userable_type', Admin::class);
            })

            ->paginate();
    }

    public function getInfluencerAccounts(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Account::query()
            ->select(
                'accounts.id',
                'accounts.name as account_name',
                'accounts.type as account_type',
                'accounts.status as account_status',
                'accounts.created_at as account_created_at'
            )
            ->join('invitations', function ($join): void {
                $join->on('invitations.invitable_id', 'accounts.id')
                    ->where('invitations.admin_id', auth()->user()->userable_id);
            })
            ->withCount('branches')
            ->paginate();
    }

    public function getInfluencerAccountBranch(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Invitation::query()
            ->select(
                'branches.name as branch_name',
                'accounts.name as account_name',
                'branches.address->country as branch_country',
                'branches.status as branch_status',
                'plans.attributes as attributes',
            )
            ->selectRaw('(SELECT COUNT(*) FROM quotes WHERE quotes.creator = branches.id) as out_count')
            ->selectRaw('(SELECT COUNT(*) FROM quotes WHERE quotes.vendor = branches.id) as in_count')
            ->join('branches', 'branches.account_id', 'invitations.invitable_id')
            ->join('accounts', 'accounts.id', 'branches.account_id')
            ->join('branch_plans', 'branch_plans.branch_id', 'branches.id')
            ->join('plans', function ($join): void {
                $join->on('branch_plans.plan_id', 'plans.id')
                    ->where('plan_type', SubscriptionType::GLOBAL)
                    ->where('plans.status', 'active');
            })
            ->where('invitations.admin_id', auth()->user()->userable_id)
            ->paginate();
    }
}
