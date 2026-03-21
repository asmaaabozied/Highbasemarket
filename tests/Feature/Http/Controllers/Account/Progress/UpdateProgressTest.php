<?php

use App\Models\Account;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Arr;

beforeEach(function () {
    $this->withoutMiddleware();

    $account = Account::factory()
        ->hasEmployees()
        ->hasBranches()
        ->create();

    $employee     = $account->employees()->first();
    $this->branch = $account->branches()->first();

    $user = User::factory()->create([
        'userable_id'   => $employee->id,
        'userable_type' => Employee::class,
    ]);

    $this->ActingAs($user);

    $this->storeData = [
        'title'     => 'Store Title',
        'status'    => 'active',
        'branch_id' => $this->branch->id,
        'steps'     => [
            [
                'name'         => 'New Step Name',
                'type'         => 'approval',
                'form'         => json_encode(['field1' => 'text']),
                'confirmation' => 'both',
                'reaction'     => 'client',
                'description'  => 'Updated description',
                'status'       => 'inactive',
            ],
            [
                'name'         => 'New Step Added',
                'type'         => 'task',
                'form'         => null,
                'confirmation' => 'vendor',
                'reaction'     => 'client',
                'description'  => 'This is a new step',
                'status'       => 'inactive',
            ],
        ],
    ];
});

it('can validate rules', function ($field) {

    $extractStepProperty = function () use ($field) {

        $steps     = [];
        $stepField = [];

        $storedData = Arr::except($this->storeData, $field);

        foreach ($storedData['steps'] ?? [] as $key => $step) {

            $array_key = array_keys($step)[$key];
            $steps[]   = Arr::except($step, $array_key);

            $stepField[] = "steps.$key.$array_key";
        }

        return
            [
                'data'   => Arr::except([...$storedData, 'steps' => $steps], $field),
                'fields' => [$field, ...$stepField],
            ];
    };

    $payload = $extractStepProperty();

    $progress = \App\Models\Progress::factory()
        ->has(
            \App\Models\Step::factory(2)
        )
        ->create([
            'branch_id' => $this->branch->id,
        ]);

    $response = $this->put(route('account.progress.update', $progress->id), $payload['data']);

    expect(\App\Models\Progress::count())->toBe(1);

    $response->assertSessionHasErrors($payload['fields']);

})->with([
    [
        'field' => 'title',
    ],
    [
        'field' => 'steps',
    ],

])->assignee('xmohamedamin');

describe('update function', function () {
    it('can update progress', function () {
        $progress = \App\Models\Progress::factory()
            ->has(
                \App\Models\Step::factory(2)
            )
            ->create();

        $response = $this->put('account/progress/'.$progress->fresh()->id, $this->storeData);

        expect(\App\Models\Progress::count())->toBe(1)
            ->and($progress->fresh()->steps()->count())->toBe(2)
            ->and($progress->fresh()->title)->toBe('Store Title')
            ->and($progress->fresh()->status)->toBe('active')
            ->and($progress->fresh()->steps()->pluck('name')->toArray())->toEqual(['New Step Name', 'New Step Added']);

        foreach ($this->storeData['steps'] as $key => $current_step) {
            $step = $progress->steps[$key];

            expect($step->name)->toBe($current_step['name'])
                ->and($step->type)->toBe($current_step['type'])
                ->and($step->form)->toEqual($current_step['form'])
                ->and($step->confirmation)->toBe($current_step['confirmation'])
                ->and($step->reaction)->toBe($current_step['reaction'])
                ->and($step->description)->toBe($current_step['description'])
                ->and($step->status)->toBe($current_step['status']);
        }

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();
    });
})->assignee('xmohamedamin');
