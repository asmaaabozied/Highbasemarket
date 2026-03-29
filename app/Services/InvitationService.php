<?php

namespace App\Services;

use App\Enum\InviteType;
use App\Models\Account;
use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\CustomerVendor;
use App\Models\Inviter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class InvitationService
{
    public static function Registered($account = null): void
    {
        if (session()->has('invitation')) {
            $invitation = session()->get('invitation');

            $invitation->update([
                'registered_at'  => now(),
                'status'         => 'registered',
                'invitable_id'   => $account?->id,
                'invitable_type' => Account::class,
            ]);

            $invitation->followUps()->update([
                'registered_at' => now(),
            ]);
        }
    }

    public static function Opened($request): void
    {
        if (session()->has('invitation')) {
            $invitation = session()->get('invitation');

            $followUp = $invitation->followUps()->latest()->first();

            if (! $followUp) {
                return;
            }

            $visitedPages = $followUp->visited_pages;

            $page = $request->route()->getName();

            $url = $request->url();

            $visitedPage = collect($visitedPages)->firstWhere('page', $page);

            if ($visitedPage) {
                $visitedPage['count']++;
            } else {
                $visitedPage = [
                    'page'  => $page,
                    'url'   => $url,
                    'count' => 1,
                ];
            }

            $visitedPages[] = $visitedPage;

            $followUp->update([
                'visited_pages' => $visitedPages,
            ]);
        }
    }

    public static function anonymousCustomerRegisterd(Account $account, Branch $branch, User $user): void
    {
        if (session()->has('invitations')) {
            $anon = AnonymousCustomerBranch::query()
                ->with('customer')
                ->where('email', $user->email)
                ->where('name', $account->name)
                ->first();

            $invitation = (object) collect(Session::get('invitations'))
                ->where('type', InviteType::EXTERNAL_VISIT->value)
                ->where('branch_id', $anon?->branch_id)
                ->last();

            if ($anon) {
                $branch->update([
                    'address'    => $anon->address,
                    'cr'         => $anon->customer->cr_number,
                    'vat_number' => $anon->customer->vat_number,
                ]);

                $anon->update(['converted_at' => now()]);

                $data = [
                    'vendor_id'           => $invitation->branch_id,
                    'customer_id'         => $branch->id,
                    'inviter_employee_id' => $invitation->employee_id,
                ];

                CustomerVendor::create($data);

            }

        }
    }

    public function addReferralByInvitationLink(
        $currentBranch_id,
        $inviter_branch_id,
        $inviter_employee_id,
        $type = null,
        $market = 'global'
    ): ?bool {
        $subBranches = [];
        $customers   = [];

        if ($type === 'customer') {
            $vendor   = $inviter_branch_id;
            $customer = $currentBranch_id;
        } else {
            $vendor   = $currentBranch_id;
            $customer = $inviter_branch_id;
        }

        if ($vendor === $customer) {
            return null;
        }

        $user           = auth()->user();
        $customerVendor = (new CustomerListService)->getInviteBranchFormList($customer, $vendor);

        if ($customerVendor->exists()) {
            $branch = $customerVendor->first();

            $this->addInviter(
                $inviter_employee_id,
                $branch->id,
            );

            $this->addInviter(
                $user?->userable?->id,
                $branch->id,
                type: 'acceptor'
            );

            return null;
        }

        if ($type === 'customer') {
            $subBranches = Branch::query()
                ->where('parent_id', $customer)
                ->pluck('id');

            $customers = CustomerVendor::query()
                ->where('vendor_id', $vendor)
                ->pluck('customer_id')->toArray();
        }

        $this->insertInvitation($customer, $vendor, $inviter_employee_id, $subBranches, $customers, $market);

        return true;
    }

    public function addInviter($employee_id, $branch_id, $type = 'inviter'): void
    {
        if (! $employee_id) {
            return;
        }

        if (Inviter::query()->where('employee_id', $employee_id)
            ->where('customer_vendor_id', $branch_id)
            ->where('type', $type)->exists()) {
            return;
        }
        Inviter::query()->create([
            'employee_id'        => $employee_id,
            'customer_vendor_id' => $branch_id,
            'type'               => $type,
        ]);
    }

    public function insertInvitation($customerId, $vendorId, $employeeId, $subBranches, $customers, $market): void
    {

        $user = auth()->user();
        $data = [];

        $config = [
            'added_through' => 'invitation link',
            'market'        => $market,
        ];

        $list = CustomerVendor::query()
            ->create([
                'vendor_id'            => $vendorId,
                'customer_id'          => $customerId,
                'inviter_employee_id'  => $employeeId,
                'acceptor_employee_id' => $user?->userable?->id,
                'config'               => $config,
            ]);

        $this->addInviter(
            $employeeId,
            $list->id,
        );

        $this->addInviter(
            $user?->userable?->id,
            $list->id,
            type: 'acceptor'
        );

        foreach ($subBranches as $subBranch) {
            if (in_array($subBranch, $customers)) {
                continue;
            }

            $data[] = [
                'vendor_id'            => $vendorId,
                'customer_id'          => $subBranch,
                'inviter_employee_id'  => $employeeId,
                'acceptor_employee_id' => $user?->userable?->id,
                'config'               => json_encode($config),
                'created_at'           => Carbon::now(),
                'updated_at'           => Carbon::now(),
            ];
        }

        CustomerVendor::insert($data);
    }
}
