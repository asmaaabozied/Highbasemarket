<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\MessageSentController\Helpers\prepareData;

describe('message sent', function () {

    it('can message route and data', function () {

        [
            $buyer,
            $seller,
            $seller_user,
            $buyer_user
        ] = prepareData(0);

        $this->actingAs($seller_user);

        $response = $this->get(route('account.send.message'));

        $response->assertSee('branches');

        $response->assertStatus(200);
    });

    it('can return wrong value if not authentcate', function () {

        $response = $this->get(route('account.send.message'));

        $response->assertRedirect('/login');
    });

})->assignee('xmohamedamin');
