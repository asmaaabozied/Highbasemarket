<?php

use App\Models\Account;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Progress;
use App\Models\Quote;
use App\Models\Step;
use App\Models\Terms;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Account\Quote\Helpers\confirmQuote;

beforeEach(function () {

    test()->withoutMiddleware(ValidateCsrfToken::class);

    $this->account = Account::factory()
        ->hasEmployees()
        ->hasBranches(2)
        ->create();

    test()->branch = $this->account->branches()->first();
    test()->vendor = $this->account->branches()
        ->where('id', '<>', test()->branch->id)
        ->first();

    $employee = $this->account->employees()->first();

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

describe('Confirm function', function () {
    it('can confirm', function () {

        $quote = Quote::factory()
            ->withFullDetails(
                1, 1,
                \App\Models\Progress::factory()
                    ->has(
                        \App\Models\Step::factory(2)
                    )
                    ->create([
                        'branch_id' => $this->branch->id,
                    ])
            )->create([
                'creator' => test()->branch->id,
                'vendor'  => test()->vendor->id,
            ]);

        $seller_response = confirmQuote($quote, $this->branch);

        $confirm = \App\Models\Confirm::query()
            ->where('creator', $this->branch->id)
            ->where('confirmable_id', $quote->id)
            ->where('type', 'quote')
            ->first();

        $status = $quote->status;

        $seller_response->assertSessionHasNoErrors();
        expect($seller_response->status())->toBe(302)
            ->and($confirm->creator)->toBe($this->branch->id)
            ->and($confirm->customer)->toBeNull();

        // other side confirm
        $buyer_response = confirmQuote($quote, $this->vendor);

        $confirm->refresh();
        $quote->refresh();

        $buyer_response->assertSessionHasNoErrors();
        expect($buyer_response->status())->toBe(302)
            ->and($confirm->creator)->toBe($this->branch->id)
            ->and($confirm->consumer)->toBe($this->vendor->id)
            ->and($quote->status)->toBe('inactive')
            ->and($quote->quoteDetails()->first()->progress->steps()->first()->status)->toBe('pending');

    });

    it("can't confirm wih with same branch", function () {
        $quote = Quote::factory()
            ->withFullDetails(
                1, 1,
                \App\Models\Progress::factory()
                    ->has(
                        \App\Models\Step::factory(2)
                    )
                    ->create([
                        'branch_id' => $this->branch->id,
                    ])
            )->create([
                'creator' => test()->branch->id,
                'vendor'  => test()->vendor->id,
            ]);

        $seller_response = confirmQuote($quote, $this->branch);

        $confirm = \App\Models\Confirm::query()
            ->where('creator', $this->branch->id)
            ->where('confirmable_id', $quote->id)
            ->where('type', 'quote')
            ->first();

        $status = $quote->status;

        $seller_response->assertSessionHasNoErrors();
        expect($seller_response->status())->toBe(302)
            ->and($confirm->creator)->toBe($this->branch->id)
            ->and($confirm->customer)->toBeNull();

        // other side confirm
        $buyer_response = confirmQuote($quote, $this->branch);

        $confirm->refresh();
        $quote->refresh();

        $buyer_response->assertSessionHasNoErrors();
        expect($buyer_response->status())->toBe(302)
            ->and($confirm->creator)->toBe($this->branch->id)
            ->and($confirm->consumer)->toBeNull()
            ->and($quote->status)->toBe('active')
            ->and($quote->quoteDetails()->first()->progress->steps()->first()->status)->toBe('inactive');

    });

    it('can confirm with difference branch', function () {

        $quote = Quote::factory()
            ->withFullDetails(
                1, 1,
                \App\Models\Progress::factory()
                    ->has(
                        \App\Models\Step::factory(2)
                    )
                    ->create([
                        'branch_id' => $this->branch->id,
                    ])
            )->create([
                'creator' => test()->branch->id,
                'vendor'  => test()->vendor->id,
            ]);

        $foreign_branch = $this->account->branches()->whereNotIn('id', [test()->branch->id, test()->vendor->id])->first();

        $seller_response = confirmQuote($quote, $this->branch);

        $confirm = \App\Models\Confirm::query()
            ->where('creator', $this->branch->id)
            ->where('confirmable_id', $quote->id)
            ->where('type', 'quote')
            ->first();

        $seller_response->assertSessionHasNoErrors();
        expect($seller_response->status())->toBe(302)
            ->and($confirm->creator)->toBe($this->branch->id)
            ->and($confirm->customer)->toBeNull();

        // other side confirm
        $buyer_response = confirmQuote($quote, $foreign_branch);

        $confirm->refresh();
        $quote->refresh();

        expect($buyer_response->status())->toBe(302)
            ->and($confirm->creator)->toBe($this->branch->id)
            ->and($confirm->consumer)->toBeNull()
            ->and($quote->status)->toBe('active')
            ->and(session('error'))->toBe('You are not author to confirm this branch');

    });

});
