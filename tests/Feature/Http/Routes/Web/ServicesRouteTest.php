<?php

describe('service link', function () {

    it('can go to services link', function () {
        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertSee('services');
    });
})->assignee('xmohamedamin');
