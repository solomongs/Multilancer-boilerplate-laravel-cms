<?php

use App\Modules\Leads\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::post('/contact/submit', [LeadController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('leads.store');
