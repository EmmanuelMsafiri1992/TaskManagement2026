<?php

use App\Http\Controllers\Api\JobWebhookController;
use App\Http\Controllers\Api\PublicLeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public Lead Capture API (for emphxs.com integration)
Route::prefix('public/leads')->group(function () {
    Route::post('/', [PublicLeadController::class, 'store']);
    Route::get('/options', [PublicLeadController::class, 'options']);
});

// Job Webhook API (for nyasajob integration)
Route::prefix('webhooks/jobs')->group(function () {
    Route::post('/posted', [JobWebhookController::class, 'jobPosted']);
    Route::get('/countries', [JobWebhookController::class, 'getActiveCountries']);
    Route::get('/health', [JobWebhookController::class, 'health']);
});
