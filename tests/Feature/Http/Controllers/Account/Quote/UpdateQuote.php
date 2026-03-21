<?php

use App\Models\Account;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Progress;
use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\Step;
use App\Models\Terms;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

require_once 'Helpers.php';

use Illuminate\Support\Arr;

use function Tests\Feature\Http\Controllers\Account\Quote\Helpers\quoteRequestFormat;
use function Tests\Feature\Http\Controllers\Account\Quote\Helpers\quoteVariant;
use function Tests\Feature\Http\Controllers\Account\Quote\Helpers\updateRequest;

beforeEach(function () {
    test()->withoutMiddleware(ValidateCsrfToken::class);

    $account = Account::factory()
        ->hasEmployees()
        ->hasBranches(2)
        ->create();

    test()->branch = $account->branches()->first();
    test()->vendor = $account->branches()
        ->where('id', '<>', test()->branch->id)
        ->latest()->first();

    $employee = $account->employees()->first();

    test()->progress = Progress::factory()
        ->has(Step::factory()->count(3))
        ->create();

    test()->terms   = Terms::factory()->create();
    test()->product = Product::factory()
        ->has(
            \App\Models\Variant::factory(1)
        )
        ->create([
            'image' => 'https://placehold.it/640x480.png/00ff00?text=Created+Product',
        ]);

    $user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    test()->actingAs($user);
});

describe('Update Function', function () {
    it('can validate rules', function ($field) {
        $storedData = [
            'creator' => test()->branch->id,
            'vendor'  => test()->vendor->id,
            'details' => [
                [
                    'name'        => 'Quote-test',
                    'quote_type'  => 0,
                    'price'       => '100',
                    'term_id'     => test()->terms->id,
                    'progress_id' => test()->progress->id,
                    'products'    => [
                        [
                            'name'                => 'Product 1',
                            'quotable'            => test()->product->id,
                            'price'               => -100,
                            'temperature'         => -30,
                            'total_price'         => 10,
                            'quantity'            => 1,
                            'size'                => 44,
                            'pack'                => 'P',
                            'unit'                => 'Unit',
                            'tech_specifications' => 'Tech Spec 1',
                            'image'               => test()->product->image,
                            'variants'            => quoteVariant(test()->product->variants),
                        ],
                    ],
                ],
            ],
        ];

        $extractStepProperty = function () use ($field, $storedData) {

            $details = [];
            $fields  = [];

            $storedData = Arr::except($storedData, $field['quote']);

            foreach ($storedData['details'] ?? [] as $key => $tDetails) {

                $array_key = $field['details'];
                $details[] = Arr::except($tDetails, $array_key);

                $fields[] = "details.$key.$array_key";

                foreach ($details['products'] ?? [] as $product_key => $product) {
                    $array_key                 = array_keys($product)[$product_key];
                    $details[$key]['products'] = [Arr::except($product, $array_key)];
                    $fields[]                  = "details.$key.products.$product_key.$array_key";
                }
            }

            return
                [
                    'data' => Arr::except(['creator' => test()->branch->id,
                        'vendor'                     => test()->vendor->id, 'details' => $details], $field),
                    'fields' => [$field['quote'], ...$fields],
                ];
        };

        $payload = $extractStepProperty();

        $quote = Quote::factory()->withFullDetails(2, 3)->create([
            'creator' => test()->branch->id,
            'vendor'  => test()->vendor->id,
        ]);

        $updateResponse = $this->put(route('account.quotes.update', $quote), updateRequest($payload['data'], $quote));

        expect(\App\Models\Quote::count())->toBe(1)
            ->and($updateResponse->status())->toBe(302)
            ->and($updateResponse->assertSessionHasErrors($payload['fields']));

    })->with('quote fields');

    it('can update quote', function ($originalRawData) {

        $originalData = quoteRequestFormat($originalRawData);

        $this->post(route('account.quotes.store'), $originalData);

        $quote = Quote::with('quoteDetails.quoteProducts')->first();

        $response = $this->put(route('account.quotes.update', $quote), updateRequest($originalData, $quote));

        expect($response->status())->toBe(302)
            ->and($response->assertSessionHasNoErrors());

        $quote->refresh();

        expect($quote->creator)->toBe($originalData['creator'])
            ->and($quote->vendor)->toBe($originalData['vendor'])
            ->and(QuoteDetail::count())->toBe(count($originalData['details']));

        foreach ($originalData['details'] as $index => $detailData) {
            $detail = $quote->quoteDetails[$index];

            expect($detail->name)->toBe($detailData['name'])
                ->and($detail->quote_type)->toBe($detailData['quote_type'])
                ->and($detail->price)->toBe(floatval($detailData['price']))
                ->and($detail->quoteProducts)->toHaveCount(count($detailData['products']));

            foreach ($detailData['products'] as $pIndex => $productData) {
                $quoteProduct = $detail->quoteProducts[$pIndex];

                expect(json_decode($quoteProduct->product)->name)->toBe($productData['name'])
                    ->and($quoteProduct->quotable_id)->toBe($productData['quotable'])
                    ->and($quoteProduct->price)->toBe(floatval($productData['price']))
                    ->and($quoteProduct->temperature)->toBe(floatval($productData['temperature']))
                    ->and($quoteProduct->total_price)->toBe(floatval($productData['total_price']))
                    ->and($quoteProduct->quantity)->toBe($productData['quantity'])
                    ->and($quoteProduct->size)->toBe($productData['size'])
                    ->and($quoteProduct->pack)->toBe($productData['pack'])
                    ->and($quoteProduct->unit)->toBe($productData['unit'])
                    ->and($quoteProduct->tech_specifications)->toBe($productData['tech_specifications'])
                    ->and(json_decode($quoteProduct->product)->image)->toBe($productData['image']);
            }
        }

    })->with([
        'update single product quote' => [
            [
                'creator' => fn () => test()->branch->id,
                'vendor'  => fn () => test()->vendor->id,
                'details' => [
                    [
                        'name'        => 'Quote-test',
                        'quote_type'  => 0,
                        'price'       => 100,
                        'term_id'     => fn () => test()->terms->id,
                        'progress_id' => fn () => test()->progress->id,
                        'products'    => [
                            [
                                'name'                => 'Product 1',
                                'quotable'            => fn () => test()->product->id,
                                'price'               => 100,
                                'temperature'         => 30,
                                'total_price'         => 10,
                                'quantity'            => 1,
                                'size'                => 44,
                                'pack'                => 'P',
                                'unit'                => 'Unit',
                                'tech_specifications' => 'Tech Spec 1',
                                'image'               => fn () => test()->product->image,
                                'variants'            => fn () => quoteVariant(test()->product->variants),
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'update multi product quote' => [
            [
                'creator' => fn () => test()->branch->id,
                'vendor'  => fn () => test()->vendor->id,
                'details' => [
                    [
                        'name'        => 'Updated Quote',
                        'quote_type'  => 1,
                        'price'       => 150,
                        'term_id'     => fn () => test()->terms->id,
                        'progress_id' => fn () => test()->progress->id,
                        'products'    => [
                            [
                                'name'                => 'Updated Product',
                                'quotable'            => fn () => test()->product->id,
                                'price'               => 120,
                                'temperature'         => 35,
                                'total_price'         => 24,
                                'quantity'            => 2,
                                'size'                => 45,
                                'pack'                => 'B',
                                'unit'                => 'Box',
                                'tech_specifications' => 'Updated Tech Specs',
                                'image'               => fn () => test()->product->image,
                                'variants'            => fn () => quoteVariant(test()->product->variants),
                            ],
                            [
                                'name'                => 'Updated Product',
                                'quotable'            => fn () => test()->product->id,
                                'price'               => 120,
                                'temperature'         => 35,
                                'total_price'         => 24,
                                'quantity'            => 2,
                                'size'                => 45,
                                'pack'                => 'B',
                                'unit'                => 'Box',
                                'tech_specifications' => 'Updated Tech Specs',
                                'image'               => fn () => test()->product->image,
                                'variants'            => fn () => quoteVariant(test()->product->variants),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);
});
