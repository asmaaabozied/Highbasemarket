<?php

use App\Models\Account;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;

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

describe('Assign to profile function', function () {
    it('can update terms', function () {
        $term = \App\Models\Terms::factory()
            ->create([
                'branch_id' => $this->branch->id,
            ]);

        session()->put('current_branch', $this->branch);

        $response = $this->put(route('account.default-terms.update', $term->id));

        expect(\App\Models\Terms::count())->toBe(1)
            ->and($term->fresh()->term_default)->toBe(1);
    });
})->assignee('xmohamedamin');
