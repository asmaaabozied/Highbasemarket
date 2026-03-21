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

beforeEach(function () {

    test()->withoutMiddleware(ValidateCsrfToken::class);

    $account = Account::factory()
        ->hasEmployees()
        ->hasBranches()
        ->create();

    test()->branch = $account->branches()->first();
    test()->vendor = $account->branches()
        ->where('id', '<>', test()->branch->id)
        ->first();

    $employee = $account->employees()->first();

    test()->progress = Progress::factory()
        ->has(Step::factory()->count(3))
        ->create();

    test()->terms   = Terms::factory()->create();
    test()->product = Product::factory()->create([
        'image' => 'https://placehold.it/640x480.png/00ff00?text=Created+Product',
    ]);

    $user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    test()->actingAs($user);
});

describe('Store Function', function () {
    it('can validate rules', function ($field) {
        $storedData = [
            'creator' => test()->branch->id,
            'vendor'  => test()->vendor->id,
            'details' => [
                [
                    'name'        => 'Quote-test',
                    'quote_type'  => 0,
                    'price'       => -100,
                    'term_id'     => test()->terms->id,
                    'progress_id' => test()->progress->id,
                    'products'    => [
                        [
                            'name'                => 'Product 1',
                            'quotable'            => test()->product->id,
                            'price'               => -100,
                            'temperature'         => -30,
                            'total_price'         => -10,
                            'quantity'            => -1,
                            'size'                => -44,
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

                if (! in_array($field['details'], ['price', 'products'])) {
                    $fields[] = "details.$key.$array_key";
                }

                foreach ($details['products'] ?? [] as $product_key => $product) {
                    $array_key                 = $field['products'];
                    $details[$key]['products'] = [Arr::except(
                        collect($product)->map(function ($arr) use ($product) {
                            return [
                                ...$product,
                                'price' => -200,
                            ];
                        })->toArray(), $array_key)];
                    $fields[] = "details.$key.products.$product_key.$array_key";
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

        $updateResponse = $this->post(route('account.quotes.store'), $payload['data']);

        expect(\App\Models\Quote::count())->toBe(0)
            ->and($updateResponse->status())->toBe(302)
            ->and($updateResponse->assertSessionHasErrors($payload['fields']));

    })->with('quote fields');

    it('can create quote', function ($rawData) {

        $data = quoteRequestFormat($rawData);
        // Send the request
        $response = $this->post(route('account.quotes.store'), $data);

        // Assert response
        $response->assertSessionHasNoErrors();
        expect($response->status())->toBe(302)
            ->and(Quote::count())->toBe(1);

        // Assert quote was created
        $quote = Quote::with('quoteDetails')->first();

        // Assert quote attributes match input
        expect($quote->creator)->toBe($data['creator'])
            ->and($quote->vendor)->toBe($data['vendor'])
            ->and(QuoteDetail::count())->toBe(count($data['details']));

        // Assert quote details were created

        // Verify each quote detail
        foreach ($data['details'] as $index => $detailData) {
            $detail = $quote->quoteDetails[$index];

            expect($detail->name)->toBe($detailData['name'])
                ->and($detail->quote_type)->toBe($detailData['quote_type'])
                ->and($detail->price)->toBe(floatval($detailData['price']))
                ->and($detail->quoteProducts)->toHaveCount(count($detailData['products']));

            // Assert products were created for this detail

            // Verify each product's data
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
        'standard quote' => [
            [
                'creator' => fn () => test()->branch->id,
                'vendor'  => fn () => test()->vendor->id,
                'details' => [[
                    'name'        => 'Quote-test',
                    'quote_type'  => 0,
                    'price'       => 100,
                    'term_id'     => fn () => test()->terms->id,
                    'progress_id' => fn () => test()->progress->id,
                    'products'    => [[
                        'name'                => 'Product 1',
                        'quotable'            => fn () => test()->product->id,
                        'price'               => -100,
                        'temperature'         => 30,
                        'total_price'         => 10,
                        'quantity'            => 1,
                        'size'                => 44,
                        'pack'                => 'P',
                        'unit'                => 'Unit',
                        'tech_specifications' => 'Tech Spec 1',
                        'image'               => fn () => test()->product->image,
                    ]],
                ]],
            ],
        ],
        'multi product quote' => [
            [
                'creator' => fn () => test()->branch->id,
                'vendor'  => fn () => test()->vendor->id,
                'details' => [[
                    'name'        => 'Multi-Product-Quote',
                    'quote_type'  => 0,
                    'price'       => 200,
                    'term_id'     => fn () => test()->terms->id,
                    'progress_id' => fn () => test()->progress->id,
                    'products'    => [[
                        'name'                => 'Product 1',
                        'quotable'            => fn () => test()->product->id,
                        'price'               => -100,
                        'temperature'         => 30,
                        'total_price'         => 10,
                        'quantity'            => 1,
                        'size'                => 23,
                        'pack'                => 'P',
                        'unit'                => 'Unit',
                        'tech_specifications' => 'Tech Spec 1',
                        'image'               => fn () => test()->product->image,
                    ],
                        [
                            'name'                => 'Product 2',
                            'quotable'            => fn () => test()->product->id,
                            'price'               => 100,
                            'temperature'         => 25,
                            'total_price'         => 20,
                            'quantity'            => 2,
                            'size'                => 34,
                            'pack'                => 'B',
                            'unit'                => 'Box',
                            'tech_specifications' => 'Tech Spec 2',
                            'image'               => fn () => test()->product->image,
                        ]],
                ]],
            ],
        ],
    ]);
});
