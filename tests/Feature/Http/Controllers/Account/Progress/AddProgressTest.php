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

describe('store function', function () {
    it('can create a progress', function () {

        $response = $this->post(route('account.progress.store'), $this->storeData);

        $progress = \App\Models\Progress::query()->first();

        expect(\App\Models\Progress::count())->toBe(1)
            ->and($progress->steps()->count())->toBe(2)
            ->and($progress->title)->toBe('Store Title')
            ->and($progress->status)->toBe('active');

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

    })->assignee('xmohamedamin');

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

        $response = $this->post(route('account.progress.store'), $payload['data']);

        expect(\App\Models\Progress::count())->toBe(0);

        $response->assertSessionHasErrors($payload['fields']);

    })->with([
        [
            'field' => 'title',
        ],
        [
            'field' => 'steps',
        ],

    ]);

})->assignee('xmohamedamin');
