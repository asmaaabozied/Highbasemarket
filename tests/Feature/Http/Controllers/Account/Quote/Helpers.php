<?php

namespace Tests\Feature\Http\Controllers\Account\Quote\Helpers;

use App\Enum\AccountType;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Quote;
use App\Models\User;
use Closure;
use Illuminate\Support\Arr;

function quoteRequestFormat($data): array
{

    $evaluateClosures = function ($array) {
        return collect($array)->map(function ($value) {
            if ($value instanceof Closure) {
                return $value();
            }

            if (is_array($value)) {
                return collect($value)->map(function ($v) {
                    return $v instanceof Closure ? $v() : $v;
                })->all();
            }

            return $value;
        })->all();
    };

    $data = [
        'creator' => $evaluateClosures($data['creator'])[0],
        'vendor'  => $evaluateClosures($data['vendor'])[0],
        'details' => $evaluateClosures($data['details']),
    ];

    if (isset($data['details'][0]['products'])) {
        $data['details'][0]['products'] = collect($data['details'][0]['products'])->map(function ($product) use ($evaluateClosures) {
            return $evaluateClosures($product);
        })->all();
    }

    return $data;
}

function quoteVariant($variants): array
{
    return collect($variants)->map(function ($variant) {
        return [
            'id'         => $variant->id,
            'product_id' => $variant->product_id,
            'attributes' => $variant->attributes,
            'packages'   => $variant->packages,
            'quantity'   => 1,
            'price'      => 200.0,
        ];
    })->toArray();
}

function updateRequest($data, $quote): array
{
    return [
        'creator' => $data['creator'] ?? null,
        'vendor'  => $data['vendor'] ?? null,
        'details' => isset($data['details']) ? collect($data['details'])->map(function ($details, $index) use ($data, $quote) {
            $detail = $quote->quoteDetails[$index];

            return [
                'id' => $detail->id,
                ...Arr::except($data['details'][$index], 'products'),

                'products' => isset($details['products']) ? collect($details['products'])->map(function ($product, $index) use ($details, $detail) {
                    $product = $detail->quoteProducts[$index];

                    return array_merge([
                        'id' => $product->id],
                        ...$details['products'
                    ]);
                })->toArray() : null,
            ];
        })->toArray() : null,
    ];
}

function confirmQuote($quote, $branch)
{
    session()->put('current_branch', $branch);

    return test()->put(route('account.quote.status', $quote->id), [
        'status'           => 'inactive',
        'confirmable_type' => Quote::class,
        'confirmable_id'   => $quote->id,
        'clientType'       => (currentBranch()->id === $quote->creator ? AccountType::VENDOR : AccountType::CLIENT),
        'type'             => 'quote',
    ]);
}

function preparationData(): array
{
    test()->withoutMiddleware();

    $account = Account::factory()
        ->hasEmployees()
        ->hasBranches(0)
        ->create();

    $employee = $account->employees()->first();

    $user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    return [$user];
}
