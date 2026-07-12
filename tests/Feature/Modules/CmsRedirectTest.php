<?php

use App\Modules\Cms\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('resolves a single-segment legacy redirect and records analytics', function () {
    $redirect = Redirect::query()->create([
        'source_path' => 'old-about',
        'destination_url' => '/about',
        'status_code' => 301,
        'is_enabled' => true,
    ]);

    $this->get('/old-about')
        ->assertStatus(301)
        ->assertHeader('Location', url('/about'));

    $redirect->refresh();

    expect($redirect->source_path)->toBe('/old-about')
        ->and($redirect->hit_count)->toBe(1)
        ->and($redirect->last_hit_at)->not->toBeNull();
});

it('resolves a nested legacy redirect', function () {
    Redirect::query()->create([
        'source_path' => '/legacy/services/web-design.html',
        'destination_url' => '/services',
        'status_code' => 308,
        'is_enabled' => true,
    ]);

    $this->get('/legacy/services/web-design.html')
        ->assertStatus(308)
        ->assertHeader('Location', url('/services'));
});

it('does not resolve a disabled redirect', function () {
    Redirect::query()->create([
        'source_path' => '/disabled-path',
        'destination_url' => '/new-path',
        'status_code' => 301,
        'is_enabled' => false,
    ]);

    $this->get('/disabled-path')->assertNotFound();
});

it('rejects unsafe and self-referencing destinations', function () {
    expect(fn () => Redirect::query()->create([
        'source_path' => '/same-path',
        'destination_url' => '/same-path/',
        'status_code' => 301,
        'is_enabled' => true,
    ]))->toThrow(ValidationException::class);

    expect(fn () => Redirect::query()->create([
        'source_path' => '/unsafe-path',
        'destination_url' => 'javascript:alert(1)',
        'status_code' => 301,
        'is_enabled' => true,
    ]))->toThrow(ValidationException::class);
});
