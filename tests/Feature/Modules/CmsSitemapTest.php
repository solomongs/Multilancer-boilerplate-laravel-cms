<?php

use App\Modules\Cms\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only publicly available CMS pages in the XML sitemap', function () {
    Page::query()->create([
        'title' => 'Homepage',
        'slug' => 'home',
        'template' => 'home',
        'status' => 'published',
        'is_homepage' => true,
        'published_at' => now()->subDay(),
    ]);

    Page::query()->create([
        'title' => 'Services',
        'slug' => 'services',
        'template' => 'default',
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);

    Page::query()->create([
        'title' => 'Draft',
        'slug' => 'draft-page',
        'template' => 'default',
        'status' => 'draft',
    ]);

    Page::query()->create([
        'title' => 'Scheduled',
        'slug' => 'scheduled-page',
        'template' => 'default',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
        ->assertSee('<loc>'.url('/').'</loc>', false)
        ->assertSee('<loc>'.url('/services').'</loc>', false)
        ->assertDontSee('draft-page')
        ->assertDontSee('scheduled-page');
});
