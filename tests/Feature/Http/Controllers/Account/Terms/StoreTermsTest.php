<?php

use App\Models\Account;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware();

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

describe('store function', function () {
    it('can create a Terms', function () {

        $response = $this->post(route('account.terms.store'), $this->storeData);

        $progress = \App\Models\Terms::query()->first();

        expect(\App\Models\Terms::count())->toBe(1)
            ->and($progress->title)->toBe('terms-test')
            ->and($progress->assigned)->toBe(0);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        Storage::disk('public')->assertExists(
            $progress->getMedia('terms')->first()->getPathRelativeToRoot()
        );

    })->assignee('xmohamedamin');

    it('can validate rules', function ($field) {

        $payload = Arr::except($this->storeData, $field);

        $response = $this->post(route('account.terms.store'), $payload);

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

})->assignee('xmohamedamin');
