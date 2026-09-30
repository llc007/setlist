<?php

use App\Models\User;

test('landing page returns successful status', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('quecantamos');
});

test('landing page shows login and register links for guests', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee(route('login'));
    $response->assertSee(route('register'));
});

test('landing page shows dashboard link when user is authenticated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertSuccessful();
    $response->assertSee(url('/dashboard'));
});

test('landing page contains music vintage hero elements', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('vinyl');
    $response->assertSee('33');
});
