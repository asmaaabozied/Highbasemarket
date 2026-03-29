<?php

namespace App\Services;

use App\Dto\CatalogImportResult;
use App\Models\AnonymousCustomer;
use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\City;
use App\Models\State;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CatalogStoreImportService
{
    public static function make(): self
    {
        return new self;
    }

    public function importStores(
        Collection $rows,
        Branch $highbaseBranch,
        int $rowOffset = 0,
        bool $dryRun = false
    ): CatalogImportResult {
        $imported  = 0;
        $errors    = [];
        $createdBy = auth()->id() ?? 1;

        foreach ($rows as $index => $row) {
            $fileRow = $rowOffset + $index + 2;

            try {
                $row = $this->normalizeRow(is_array($row) ? $row : (array) $row);
                $this->validateRow($row);

                if (! $dryRun && $this->persistStore($row, $highbaseBranch, $createdBy)) {
                    $imported++;
                }
            } catch (ValidationException $e) {
                $errors[] = 'Row '.$fileRow.': '.$e->validator->errors()->first();
            } catch (Throwable $e) {
                $errors[] = 'Row '.$fileRow.': '.$e->getMessage();
            }
        }

        return new CatalogImportResult(
            imported: $imported,
            processed: $rows->count(),
            errors: $errors
        );
    }

    private function normalizeRow(array $row): array
    {
        return collect($row)->mapWithKeys(function ($value, $key): array {
            $normalizedKey = Str::lower(Str::trim((string) $key));
            $normalizedKey = Str::replace([' ', '|', '(', ')'], ['_', '_', '', ''], $normalizedKey);

            return [$normalizedKey => $value];
        })->toArray();
    }

    private function validateRow(array $row): void
    {
        $cr        = trim((string) (data_get($row, 'cr_no') ?? data_get($row, 'cr_number') ?? ''));
        $validator = Validator::make(
            ['cr' => $cr],
            ['cr'          => ['required', 'string', 'max:50']],
            ['cr.required' => 'Missing required field (CR NO)']
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function resolveStoreName(array $row): string
    {
        $commercialName = data_get($row, 'commercial_name_en');
        $crNumber       = data_get($row, 'cr_no') ?? data_get($row, 'cr_number');

        return $commercialName ?: 'Store '.$crNumber;
    }

    private function buildAddress(array $row): array
    {
        $block            = data_get($row, 'block_no') ?? data_get($row, 'block');
        $town             = data_get($row, 'town');
        $governorate      = data_get($row, 'governorate');
        $roadStreet       = data_get($row, 'road_street_number');
        $buildingNo       = data_get($row, 'building_no') ?? data_get($row, 'building_number');
        $flatShopNo       = data_get($row, 'flat_shop_no');
        $poBox            = data_get($row, 'po_box');
        $formattedAddress = data_get($row, 'formatted_address');

        $stateId = $this->findStateIdByName($governorate);
        $cityId  = $this->findCityIdByName($town, $stateId);

        return array_filter([
            'address'      => $formattedAddress,
            'block_number' => $block,
            'city'         => $cityId,
            'state'        => $stateId,
            'po_box'       => $poBox,
            'building_no'  => $buildingNo,
            'flat_shop_no' => $flatShopNo,
            'road_street'  => $roadStreet,
        ]);
    }

    private function findStateIdByName(?string $stateName): ?int
    {
        if ($stateName === null || trim($stateName) === '') {
            return null;
        }
        $stateName = trim($stateName);
        $state     = State::query()
            ->where('country_id', 18)
            ->where('name', $stateName)
            ->orWhere('name', 'like', '%'.$stateName.'%')
            ->first();

        return $state?->id;
    }

    private function findCityIdByName(?string $cityName, ?int $stateId): ?int
    {
        if ($cityName === null || trim($cityName) === '') {
            return null;
        }
        $cityName = trim($cityName);
        $query    = City::query()
            ->where('name', $cityName)
            ->orWhere('name', 'like', '%'.$cityName.'%');

        if ($stateId !== null) {
            $query->where('state_id', $stateId);
        }
        $city = $query->first();

        return $city?->id;
    }

    private function persistStore(array $row, Branch $highbaseBranch, int $createdBy): bool
    {
        $crNumber  = data_get($row, 'cr_no') ?? data_get($row, 'cr_number');
        $storeName = $this->resolveStoreName($row);
        $address   = $this->buildAddress($row);

        $customer = AnonymousCustomer::firstOrCreate(
            ['cr_number' => $crNumber],
            ['created_by' => $createdBy]
        );

        $anonymousBranch = AnonymousCustomerBranch::query()
            ->where('branch_id', $highbaseBranch->id)
            ->where('anonymous_customer_id', $customer->id)
            ->first();

        if (! $anonymousBranch) {
            AnonymousCustomerBranch::create([
                'branch_id'             => $highbaseBranch->id,
                'anonymous_customer_id' => $customer->id,
                'name'                  => $storeName,
                'address'               => $address,
                'created_by'            => $createdBy,
            ]);

            return true;
        }

        $existingAddress = $anonymousBranch->address ?? [];
        $anonymousBranch->update([
            'name'    => $storeName,
            'address' => array_merge($existingAddress, $address),
        ]);

        return false;
    }
}
