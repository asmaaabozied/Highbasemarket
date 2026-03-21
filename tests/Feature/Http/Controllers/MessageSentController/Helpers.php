<?php

namespace Tests\Feature\Http\Controllers\MessageSentController\Helpers;

use App\Models\Account;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Chat;
use App\Models\Employee;
use App\Models\Message;
use App\Models\MessageQuote;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use App\Services\BranchPlanService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

function prepareData($branches = 1, $employees = 1): array
{
    test()->withoutMiddleware(ValidateCsrfToken::class);
    //    test()->withoutExceptionHandling();

    $buyer = Account::factory()
        ->hasBranches($branches)
        ->has(
            Employee::factory()->count($employees)->state([
                'job_title' => 'employee',
            ])
        )
        ->create();

    $seller = Account::factory()
        ->hasBranches($branches)
        ->has(
            Employee::factory()->count($employees)->state([
                'job_title' => 'employee',
            ])
        )
        ->create();

    $seller_employee = $seller->employees()->first();

    $seller_user = User::factory()->create([
        'userable_id'   => $seller_employee->id,
        'userable_type' => Employee::class,
    ]);

    $buyer_employee = $buyer->employees()->first();

    $buyer_user = User::factory()->create([
        'userable_id'   => $buyer_employee->id,
        'userable_type' => Employee::class,
    ]);

    return [
        $buyer,
        $seller,
        $seller_user,
        $buyer_user,
    ];
}

function createPermissions(User $user, $permissions = [], $type = 'account', $module = ''): void
{
    $role = $user->getAccount()->roles()->create([
        'name' => fake()->name,
        'type' => $type,
    ]);

    Permission::insert(
        collect($permissions)->map(function ($permission) use ($module) {
            return [
                'name'   => $permission,
                'module' => $module,
                'for'    => ' ',
            ];
        })->toArray()
    );

    $role->permissions()->sync(Permission::all());

    $user->roles()->attach($role->id);
}

function populateQuotable(): array
{
    $brand    = Brand::factory()->create();
    $category = Category::factory()->create();

    $product = Product::factory()->create([
        'brand_id'    => $brand->id,
        'category_id' => $category->id,
    ]);

    return [$brand, $product];
}

function populatePlan($branch): void
{

    $plan = Plan::factory()->create([
        'attributes' => [
            'name'      => 'Add Customer',
            'type'      => 'globalMarket',
            'attribute' => [
                [
                    'name'  => 'numberOfRequests',
                    'type'  => 'text',
                    'value' => 10,
                ],
                [
                    'name'  => 'amountPerRequest',
                    'type'  => 'text',
                    'value' => 5,
                ],
                [
                    'name'  => 'is_percentage',
                    'type'  => 'checkbox',
                    'value' => true,
                ],
            ],
        ],
    ]);

    (new BranchPlanService)->assignCreate($branch, [$plan->id]);
}

function populateMessage($buyer, $seller, $quotable)
{
    $body = 'This first message';

    $chat = Chat::factory()->create([
        'receiver_branch_id' => $seller->id,
        'sender_branch_id'   => $buyer->id,
    ]);

    $message_quote = MessageQuote::create([
        'body'            => $body,
        'messagable_type' => $quotable['type'],
        'messagable_id'   => $quotable['id'],
    ]);

    $message = Message::factory()->create([
        'body'               => $body,
        'messageable_type'   => $quotable['type'],
        'messageable_id'     => $quotable['id'],
        'receiver_branch_id' => $seller->id,
        'sender_branch_id'   => $buyer->id,
        'chat_id'            => $chat->id,
        'message_quote_id'   => $message_quote->id,
    ]);

    return $message;
}
