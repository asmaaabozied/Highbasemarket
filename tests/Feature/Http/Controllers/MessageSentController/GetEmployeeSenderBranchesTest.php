<?php

require_once 'Helpers.php';

use App\Models\Message;

use function Tests\Feature\Http\Controllers\MessageSentController\Helpers\prepareData;

describe('Get Sender Branches', function () {

    it('should return correct notification  list', function () {

        [
            $buyer,
            $seller,
            $seller_user,
            $buyer_user
        ] = prepareData(0);

        $this->actingAs($seller_user);

        $message = Message::factory()->create([
            'receiver_id'        => $seller_user->id,
            'sender_id'          => $seller_user->id,
            'receiver_branch_id' => $buyer->branches()->first()->id,
            'sender_branch_id'   => $seller->branches()->first()->id,
        ]);

        $response = $this->get('sender/branches');

        $response->assertStatus(200)
            ->assertSee($seller->branches()->first()->id);
    });

    it('should return wrong notification list if not authenticate', function () {
        $response = $this->get('sender/branches');

        $response->assertStatus(302)
            ->assertDontSee('wrong branch');
    });

})->assignee('xmohamedamin');
