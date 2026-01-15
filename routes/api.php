<?php

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
