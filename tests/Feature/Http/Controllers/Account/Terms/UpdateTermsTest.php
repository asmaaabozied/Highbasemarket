<?php

use App\Models\Account;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

beforeEach(function () {
    $this->withoutMiddleware();
    //    $this->withoutExceptionHandling();

    $account = Account::factory()
        ->hasEmployees()
        ->hasBranches()
        ->create();

    $employee     = $account->employees()->first();
    $this->branch = $account->branches()->first();

    $this->user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    $this->ActingAs($this->user);

    $this->storeData = [
        'title'    => 'terms-test',
        'content'  => '<p>Terms</p>',
        'assigned' => false,
        'file'     => UploadedFile::fake()->create('my-file.pdf'),
    ];
});

it('can validate rules', function ($field) {

    $payload = Arr::except($this->storeData, $field);

    $term = \App\Models\Terms::factory()
        ->create();

    $response = $this->put(route('account.terms.update', $term->id), $payload);

    expect(\App\Models\Progress::count())->toBe(0);

    $response->assertSessionHasErrors([$field]);

})->with([
    [
        'field' => 'title',
    ],
    [
        'field' => 'content',
    ],

]);

describe('update function', function () {
    it('can update terms', function () {
        $term = \App\Models\Terms::factory()
            ->create();

        $response = $this->put(route('account.terms.update', $term->id), $this->storeData);

        expect(\App\Models\Terms::count())->toBe(1)
            ->and($term->fresh()->title)->toBe('terms-test')
            ->and($term->fresh()->assigned)->toBe(0)
            ->and($term->fresh()->content)->toBe('<p>Terms</p>');
    });
})->assignee('xmohamedamin');
