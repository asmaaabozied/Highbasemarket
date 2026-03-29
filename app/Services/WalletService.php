<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Admin;
use App\Models\QuoteDetail;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function getAccountWallet()
    {
        $auth = auth()->user();

        $wallet = $auth->isAdmin()
            ? Wallet::query()->where('walletable_type', Admin::class)->first()
            : $auth->getAccount()?->wallet;

        return $wallet?->load('transactions');
    }

    public function getAccountWalletNumber()
    {
        $wallet = auth()->user()->getAccount()->wallet;

        return $wallet->id;
    }

    public function getMainWallet()
    {
        return Wallet::query()->where('walletable_type', Admin::class)->first();
    }

    public function chargeWallet($wallet, $payment): void
    {
        DB::transaction(function () use ($wallet, $payment): void {
            $wallet->balance += $payment->amount;
            $wallet->save();

            $wallet->transactions()->create([
                'type'                 => Transaction::CREDIT,
                'currency'             => $payment->currency,
                'amount'               => $payment->amount,
                'transactionable_type' => Account::class,
                'transactionable_id'   => auth()->user()?->getAccount()->id,
            ]);
        });
    }

    public function transferUpdatePlanRenewal($plan): bool
    {
        if ($plan->is_duration_price === 'No' && $plan->duration_price > 0 && $plan->duration_paid === 'No') {
            $wallet = Wallet::query()->findOrFail(currentBranch()->id);

            if ($wallet->balance < $plan->duration_price) {
                return false;
            }

            $this->transfer(
                Wallet::query()->findOrFail(currentBranch()->id),

                collect([
                    'amount'       => $plan->duration_price,
                    'to_wallet_id' => Wallet::query()
                        ->where('walletable_type', Admin::class)
                        ->first()->id,
                ])
            );

            return $plan->update(['duration_paid' => 'Yes']);
        }

        return true;
    }

    public function transfer(Wallet $wallet_transfer, $request)
    {
        return DB::transaction(function ($result) use ($wallet_transfer, $request): void {
            $wallet_transfer->balance -= $request->get('amount');

            if ($wallet_transfer->save()) {
                if ($request->get('messageId')) {
                    app()->make(MessageSentService::class)->acceptChat($request->messageId);
                }

                if ($request->get('quoteId')) {
                    $quoteDetail = QuoteDetail::query()->findOrFail($request->quoteId);
                    (new QuoteService)->quoteUpdatePayment($quoteDetail);
                }
            }

            $wallet_transfer->transactions()->create([
                'type'                 => Transaction::DEBIT,
                'currency'             => $wallet_transfer->currency,
                'amount'               => $request->get('amount'),
                'transactionable_type' => Account::class,
                'transactionable_id'   => auth()->user()?->getAccount()->id,
            ]);

            $to_wallet = Wallet::query()->find($request->get('to_wallet_id'));

            $to_wallet->update([
                'balance' => ($to_wallet->balance + $request->get('amount')),
            ]);

            $to_wallet->transactions()->create([
                'type'                 => Transaction::CREDIT,
                'currency'             => $to_wallet->currency,
                'amount'               => $request->get('amount'),
                'transactionable_type' => $to_wallet->walletable_type,
                'transactionable_id'   => $to_wallet->walletable_id,
            ]);
        });
    }
}
