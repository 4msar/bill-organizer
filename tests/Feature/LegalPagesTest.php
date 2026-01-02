<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests can access terms page', function () {
    $response = $this->get('/terms');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('legal/Terms'));
});

test('guests can access privacy page', function () {
    $response = $this->get('/privacy');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('legal/Privacy'));
});

test('guests can access contact page', function () {
    $response = $this->get('/contact');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('legal/Contact'));
});

test('legal routes have correct names', function () {
    expect(route('legal.terms'))->toBe(url('/terms'));
    expect(route('legal.privacy'))->toBe(url('/privacy'));
    expect(route('legal.contact'))->toBe(url('/contact'));
});
