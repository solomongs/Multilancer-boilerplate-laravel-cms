<?php

use App\Models\User;
use App\Modules\Cms\Http\Controllers\PageController;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

// Authenticated home — super admins land in the "admin" panel, everyone else in
// the user-facing "app" panel.
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user instanceof User && $user->isSuperAdmin()) {
        $panel = Filament::getPanel('admin');
        $tenant = $user->getDefaultTenant($panel);

        return redirect($tenant !== null ? $panel->getUrl($tenant) : '/'.$panel->getPath());
    }

    return redirect()->route('filament.app.pages.dashboard');
})->middleware(['auth:sanctum', config('jetstream.auth_session')])->name('dashboard');

require __DIR__.'/socialstream.php';
