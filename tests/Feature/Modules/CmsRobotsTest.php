<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('publishes crawler rules and the CMS sitemap location', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('User-agent: *')
        ->assertSee('Allow: /')
        ->assertSee('Disallow: /soloadmin')
        ->assertSee('Disallow: /cms-preview')
        ->assertSee('Sitemap: '.route('cms.sitemap'));
});
