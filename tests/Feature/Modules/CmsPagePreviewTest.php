<?php

use App\Models\User;
use App\Modules\Cms\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires authentication to preview an unpublished page', function () {
    $page = Page::query()->create([
        'title' => 'Private Draft',
        'slug' => 'private-draft',
        'template' => 'default',
        'status' => 'draft',
    ]);

    $this->get(route('cms.pages.preview', ['page' => $page->id]))
        ->assertRedirect('/login');
});

it('renders an authenticated draft preview with noindex metadata', function () {
    $this->withoutVite();

    $user = User::factory()->create();
    $page = Page::query()->create([
        'title' => 'Preview Draft',
        'slug' => 'preview-draft',
        'template' => 'default',
        'status' => 'draft',
        'meta_description' => 'Draft page description.',
    ]);
    $page->sections()->create([
        'section_type' => 'hero',
        'section_name' => 'Draft hero',
        'content' => ['title' => 'Unpublished preview content'],
        'sort_order' => 10,
        'is_enabled' => true,
    ]);

    $this->actingAs($user)
        ->get(route('cms.pages.preview', ['page' => $page->id]))
        ->assertOk()
        ->assertSee('Preview mode: this page may not be publicly published.')
        ->assertSee('Unpublished preview content')
        ->assertSee('noindex,nofollow', false);

    $this->get('/preview-draft')->assertNotFound();
});
