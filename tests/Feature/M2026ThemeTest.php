<?php

use App\Services\ThemeManager;

it('discovers the M2026 theme and its application layout', function () {
    $themes = app(ThemeManager::class);

    expect($themes->themeExists('m2026'))->toBeTrue()
        ->and($themes->hasCustomLayout('app', 'm2026'))->toBeTrue();
});
