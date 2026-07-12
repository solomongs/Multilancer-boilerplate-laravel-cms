<?php

use App\Modules\Cms\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders a published homepage with enabled sections in display order', function () {
    $this->withoutVite();

    $page = Page::query()->create([
        'title' => 'M2026 Home',
        'slug' => 'home',
        'template' => 'home',
        'status' => 'published',
        'is_homepage' => true,
        'published_at' => now(),
    ]);

    $page->sections()->createMany([
        [
            'section_type' => 'rich_text',
            'section_name' => 'Second',
            'content' => ['heading' => 'Second section', 'body' => 'Rendered second.'],
            'sort_order' => 20,
            'is_enabled' => true,
        ],
        [
            'section_type' => 'hero',
            'section_name' => 'First',
            'content' => ['title' => 'First section', 'text' => 'Rendered first.'],
            'sort_order' => 10,
            'is_enabled' => true,
        ],
        [
            'section_type' => 'rich_text',
            'section_name' => 'Disabled',
            'content' => ['heading' => 'Hidden section'],
            'sort_order' => 5,
            'is_enabled' => false,
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['First section', 'Second section'])
        ->assertDontSee('Hidden section');
});

it('only exposes pages that are published and not scheduled for the future', function () {
    $this->withoutVite();

    Page::query()->create([
        'title' => 'Visible Page',
        'slug' => 'visible-page',
        'template' => 'default',
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    Page::query()->create([
        'title' => 'Draft Page',
        'slug' => 'draft-page',
        'template' => 'default',
        'status' => 'draft',
    ]);

    Page::query()->create([
        'title' => 'Future Page',
        'slug' => 'future-page',
        'template' => 'default',
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $this->get('/visible-page')->assertOk()->assertSee('Visible Page');
    $this->get('/draft-page')->assertNotFound();
    $this->get('/future-page')->assertNotFound();
});

it('keeps only one page marked as the homepage', function () {
    $first = Page::query()->create([
        'title' => 'First Home',
        'slug' => 'first-home',
        'status' => 'published',
        'is_homepage' => true,
    ]);

    $second = Page::query()->create([
        'title' => 'Second Home',
        'slug' => 'second-home',
        'status' => 'published',
        'is_homepage' => true,
    ]);

    expect($first->fresh()->is_homepage)->toBeFalse()
        ->and($second->fresh()->is_homepage)->toBeTrue();
});
