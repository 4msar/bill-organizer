<?php

use function Pest\Laravel\get;

describe('Sitemap', function () {
    it('returns sitemap xml with correct content-type', function () {
        $response = get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    });

    it('contains homepage URL in sitemap', function () {
        $response = get('/sitemap.xml');

        $response->assertSee('<loc>' . config('app.url') . '</loc>', false);
    });

    it('contains login and register URLs in sitemap', function () {
        $response = get('/sitemap.xml');

        $response->assertSee('<loc>' . config('app.url') . '/login</loc>', false);
        $response->assertSee('<loc>' . config('app.url') . '/register</loc>', false);
    });

    it('follows valid XML sitemap format', function () {
        $response = get('/sitemap.xml');

        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
        $response->assertSee('</urlset>', false);
    });
});