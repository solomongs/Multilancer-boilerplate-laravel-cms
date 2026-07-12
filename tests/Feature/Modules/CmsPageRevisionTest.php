<?php

use App\Modules\Cms\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('restores page fields and structured sections from a revision', function () {
    $page = Page::query()->create([
        'title' => 'Original title',
        'slug' => 'revision-page',
        'template' => 'default',
        'status' => 'draft',
        'meta_title' => 'Original SEO title',
    ]);

    $section = $page->sections()->create([
        'section_type' => 'rich_text',
        'section_name' => 'Introduction',
        'content' => ['heading' => 'Original heading', 'body' => 'Original body'],
        'settings' => ['width' => 'contained'],
        'sort_order' => 10,
        'is_enabled' => true,
    ]);

    $revision = $page->createRevision();

    $page->update([
        'title' => 'Changed title',
        'meta_title' => 'Changed SEO title',
    ]);
    $section->update([
        'content' => ['heading' => 'Changed heading', 'body' => 'Changed body'],
    ]);

    $restored = $revision->restore();

    expect($restored->title)->toBe('Original title')
        ->and($restored->meta_title)->toBe('Original SEO title')
        ->and($restored->sections)->toHaveCount(1)
        ->and($restored->sections->first()->content['heading'])->toBe('Original heading')
        ->and($revision->fresh()->restored_at)->not->toBeNull();
});
