<?php

namespace App\Services;

use App\Enum\AccountType;
use App\Models\Confirm;

class ConfirmService
{
    public function create(array $data)
    {

        $branch  = (new EmployeeAccountServices)->getEmployeeCurrentBranch();
        $confirm = Confirm::query()->where('confirmable_id', $data['confirmable_id'])
            ->where('type', $data['type'])
            ->first();

        if (! $confirm) {
            $confirm = Confirm::query()->create([
                'creator'          => $data['clientType'] === AccountType::VENDOR ? $branch->id : null,
                'consumer'         => $data['clientType'] === AccountType::CLIENT ? $branch->id : null,
                'confirmable_type' => $data['confirmable_type'],
                'confirmable_id'   => $data['confirmable_id'],
                'type'             => $data['type'],
            ]);
        } else {

            if ($data['clientType'] === AccountType::CLIENT) {
                $confirm->consumer = $branch->id;
            }

            if ($data['clientType'] === AccountType::VENDOR) {
                $confirm->creator = $branch->id;
            }
            $confirm->save();
        }

        return $confirm;
    }

    public function check($confirmId, $clientType): bool
    {
        return Confirm::query()->where('id', $confirmId)
            ->when($clientType === AccountType::BOTH, function ($confirm): void {
                $confirm->whereNotNull('consumer')
                    ->whereNotNull('creator');

            })
            ->when($clientType === AccountType::VENDOR, function ($confirm): void {
                $confirm->whereNotNull('creator');

            })
            ->when($clientType === AccountType::CLIENT, function ($confirm): void {
                $confirm->whereNotNull('consumer');

            })->exists();
    }
}
