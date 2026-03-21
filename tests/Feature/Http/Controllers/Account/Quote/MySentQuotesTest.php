<?php

require_once 'Helpers.php';

use function Tests\Feature\Http\Controllers\Account\Quote\Helpers\preparationData;

it('can show my sent quote', function () {

    [$user] = preparationData();

    $this->actingAs($user);

    $response = $this->get(route('account.quotes.index'));

    $response->assertSee('quotes');

    $response->assertStatus(200);
});
