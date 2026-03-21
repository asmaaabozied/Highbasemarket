<?php

describe('brand link', function () {

    it('can go to brand link', function () {
        $response = $this->get('storefront/brands');

        $response->assertStatus(200);
    });
})->assignee('xmohamedamin');
