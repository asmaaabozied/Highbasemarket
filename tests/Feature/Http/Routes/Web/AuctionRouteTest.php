<?php

describe('auctions link', function () {

    it('can go to auctions link', function () {
        $response = $this->get('auctions');

        $response->assertStatus(200);
    });
})->assignee('xmohamedamin');
