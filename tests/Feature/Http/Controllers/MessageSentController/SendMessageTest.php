<?php

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Product;
use Illuminate\Support\Facades\Event;

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\MessageSentController\Helpers\populateQuotable;
use function Tests\Feature\Http\Controllers\MessageSentController\Helpers\prepareData;

beforeEach(function () {
    config(['reverb.testing' => true]);
    config(['broadcasting.default' => 'log']);

});

it('can send message to branch', function ($type) {

    [
        $buyer,
        $seller,
        $seller_user,
        $buyer_user
    ] = prepareData(0);

    $seller_branch = $seller->branches()->first();

    [$product, $brand] = populateQuotable();

    $data = [
        'receiver_id' => $seller_branch->id,
        'body'        => 'Give me this product',
        'sender_id'   => $seller_user->id,
    ];

    if ($type === 'product') {
        $data['messageable_id']   = $product->id;
        $data['messageable_type'] = Product::class;
    } else {
        $data['messageable_id']   = $brand->id;
        $data['messageable_type'] = \App\Models\Brand::class;
    }

    $this->actingAs($buyer_user)
        ->withSession(['current_branch', $buyer->branches()->first()]);

    Event::fake();

    $response = $this->post(route('send.message'), $data);
    $response->assertStatus(200);

    $message = Message::query()
        ->with(['chat', 'messageQuote', 'messageable'])
        ->first();

    expect($message->chat->count())->toBe(1)
        ->and($message->messageable->count())->toBe(1)
        ->and($message->messageQuote->count())->toBe(1)
        ->and($message->sender_branch_id)->toBe($buyer->branches()->first()->id)
        ->and($message->receiver_branch_id)->toBe($seller->branches()->first()->id)
        ->and([$product->id, $brand->id])->toContain($message->messageable->id)
        ->and($message->messageQuote->body)->toBe($data['body'])
        ->and([$product->id, $brand->id])->toContain($message->messageQuote->messagable_id);

    Event::assertDispatched(MessageSent::class, function ($event) use ($data) {
        return $event->message->body === $data['body'];
    });

})->with([
    ['type' => 'product'],
    ['type' => 'brand'],

])->assignee('xmohamedamin');
