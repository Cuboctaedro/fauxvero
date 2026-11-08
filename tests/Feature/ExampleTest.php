<?php

test('the application returns a successful response', function () {
    $response = $this->get('/el');

    $response->assertStatus(200);
});
