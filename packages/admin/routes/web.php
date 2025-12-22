<?php

use Illuminate\Support\Facades\Route;
use Admin\Http\Controllers\Api\AdSenseOAuthController;

Route::view('/', 'app');

// AdSense OAuth callback
Route::get('auth/google/adsense/callback', [AdSenseOAuthController::class, 'callback'])->name('adsense.callback');

Route::view('{any?}', 'app')
    ->where('any', '.*');
