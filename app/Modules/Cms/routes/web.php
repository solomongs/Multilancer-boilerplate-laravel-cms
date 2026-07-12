<?php

use App\Modules\Cms\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/cms-preview/{page:id}', [PageController::class, 'preview'])
    ->middleware(['auth:sanctum', config('jetstream.auth_session')])
    ->name('cms.pages.preview');

$reservedSlugs = array_map(
    static fn (string $slug): string => preg_quote($slug, '/'),
    array_filter(config('cms.reserved_slugs', []), 'is_string'),
);
$reservedPattern = $reservedSlugs === []
    ? ''
    : '(?!(?:'.implode('|', $reservedSlugs).')$)';

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '^'.$reservedPattern.'[a-z0-9][a-z0-9-]*$')
    ->name('cms.pages.show');
