<?php

use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UrlShortenerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Homepage
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Sitemap route
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// URL Shortener redirect route
Route::get('/s/{shortCode}', [UrlShortenerController::class, 'redirect'])->name('url.redirect');

// Auth routes are loaded from the admin package
require __DIR__.'/auth.php';
