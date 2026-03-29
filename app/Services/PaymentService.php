<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Plan;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class PaymentService
{
    /**
     * @var AllocateBulkCommissionPaymentService|Application|mixed|object
     */
    private readonly AllocateBulkCommissionPaymentService $commissionService;

    public function __construct()
    {
        $this->commissionService = app(AllocateBulkCommissionPaymentService::class);
    }

    public function fulfillPayment()
    {
        $payment_session = session('payment_session');

        if (! $payment_session) {
            return false;
        }

        $type = $payment_session['type'] ?? null;

        if (! $type) {
            return false;
        }

        return $this->$type($payment_session['resource']);
    }

    public function plan(Plan $plan)
    {
        PlanService::assignPlan(currentBranch(), $plan);

        return to_route('account.pricing');
    }

    public function customer(Branch $branch)
    {
        currentBranch()->addCustomer($branch, 'payment');

        return to_route('account.send.message', $branch->slug);
    }

    public function wallet()
    {
        $wallet = (new WalletService)->getAccountWallet();

        if (! $wallet) {
            return false;
        }

        $wallet->deposit(session('payment_session')['amount']);

        return to_route('account.wallets.index')->with(['success' => 'Payment Successful.']);
    }

    public function quote() {}

    public function commission_payment($resource): RedirectResponse
    {
        $session = session('payment_session');

        $amount = $session['amount'] ?? 0;

        $payerBranch = currentBranch(); // seller paying commission

        if ($amount <= 0) {
            session()->forget('payment_session');

            return redirect()->route('account.commissions.index')->with('error', 'Invalid payment amount.');
        }

        // Allocate bulk payment to overdue commissions
        $this->commissionService->allocate(seller: $payerBranch, paymentAmount: $amount);

        // Clear session
        session()->forget('payment_session');

        return redirect()->route('account.commissions.index')
            ->with('success', 'Commission payment applied successfully.');
    }
}
