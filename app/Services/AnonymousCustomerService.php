<?php

namespace App\Services;

use App\Actions\Custom\CreateAnonymousCustomerFromBranch;
use App\Dto\AnonymousCustomerBranchDto;
use App\Dto\AnonymousCustomerDto;
use App\Jobs\ConvertAnonymousCustomerBranchesJob;
use App\Models\AnonymousCustomer;
use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;

class AnonymousCustomerService
{
    public function convertAnonymousBranchesByCr(string $crNumber, array $address, int $sellerBranch): void
    {
        $branch = currentBranch();

        $branch->update(['cr' => $crNumber, 'address' => $address]);

        $customer = AnonymousCustomer::query()
            ->with('customerBranches')
            ->where('cr_number', $crNumber)
            ->first();

        if (! $customer) {
            return;
        }

        $anonymousBranchIds = $customer->customerBranches()
            ->whereNull('converted_at')
            ->pluck('id');

        if ($anonymousBranchIds->isEmpty()) {
            return;
        }

        ConvertAnonymousCustomerBranchesJob::dispatch(
            $anonymousBranchIds->all(),
            $branch->id,
            $customer->id,
            auth()->user()->userable_id);
    }

    public function create(AnonymousCustomerDto $dto): AnonymousCustomerBranchDto
    {
        $branchId = currentBranch()->id;
        $userId   = auth()->user()->userable_id;

        $customer = AnonymousCustomer::firstOrCreate(
            ['cr_number' => $dto->cr_number],
            [
                'vat_number' => $dto->vat_number,
            ]
        );

        $branch = $customer->customerBranches()->where('branch_id', $branchId)->first();

        if (! $branch || ($branch->email !== $dto->email) || ($branch->phone !== $dto->phone)) {
            $address = [
                'city'         => $dto->city,
                'state'        => $dto->state,
                'address'      => $dto->address,
                'block_number' => $dto->block_number,
                'road_street'  => $dto->road_street,
                'building_no'  => $dto->building_no,
                'pin_location' => $dto->pin_location,
            ];

            $branch = $customer->customerBranches()->create([
                'branch_id'  => $branchId,
                'name'       => $dto->name,
                'email'      => $dto->email,
                'phone'      => $dto->phone,
                'address'    => $address,
                'created_by' => $userId,
            ]);

        }

        return new AnonymousCustomerBranchDto($customer, $branch);
    }

    public static function getAnonymousCustomerOfBranch(Branch $creator, Branch $branch): AnonymousCustomerBranch
    {
        $anonymous = AnonymousCustomerBranch::where('branch_id', $creator->id)
            ->whereHas('customer', function ($query) use ($branch): void {
                $query->where('cr_number', $branch->cr);
            })->first();

        if (! $anonymous) {
            return CreateAnonymousCustomerFromBranch::execute($creator, $branch);
        }

        return $anonymous;
    }
}
