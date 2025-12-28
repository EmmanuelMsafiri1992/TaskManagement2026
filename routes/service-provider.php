<?php

use App\Http\Controllers\ServiceProvider\AuthController;
use App\Http\Controllers\ServiceProvider\DashboardController;
use App\Http\Controllers\ServiceProvider\RecordingController;
use App\Http\Controllers\ServiceProvider\LessonPlanController;
use Illuminate\Support\Facades\Route;

// Guest routes - Only registration (login is now unified)
Route::middleware('guest:service_provider')->group(function () {
    Route::get('register', [AuthController::class, 'showRegister'])->name('service-provider.register');
    Route::post('register', [AuthController::class, 'register']);
});

// Redirect old login route to main login
Route::get('login', function () {
    return redirect()->route('login');
})->name('service-provider.login');

// Authenticated routes
Route::middleware('auth:service_provider')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('service-provider.logout');

    // Agreement signing (required before accessing dashboard)
    Route::get('agreement', [AuthController::class, 'showAgreement'])->name('service-provider.agreement');
    Route::post('agreement', [AuthController::class, 'signAgreement']);

    // Dashboard and main features (require signed agreement)
    Route::middleware('agreement.signed')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('service-provider.dashboard');
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Recording Sessions
        Route::prefix('recordings')->group(function () {
            Route::get('/', [RecordingController::class, 'index'])->name('service-provider.recordings');
            Route::get('create', [RecordingController::class, 'create'])->name('service-provider.recordings.create');
            Route::post('/', [RecordingController::class, 'store']);
            Route::get('{session}', [RecordingController::class, 'show'])->name('service-provider.recordings.show');
            Route::post('{session}/clock-in', [RecordingController::class, 'clockIn']);
            Route::post('{session}/clock-out', [RecordingController::class, 'clockOut']);
            Route::post('{session}/start-recording', [RecordingController::class, 'startRecording']);
            Route::post('{session}/stop-recording', [RecordingController::class, 'stopRecording']);
            Route::post('{session}/submit', [RecordingController::class, 'submit']);
            Route::post('{session}/upload-video', [RecordingController::class, 'uploadVideo']);
        });

        // Lesson Plans
        Route::prefix('lesson-plans')->group(function () {
            Route::get('/', [LessonPlanController::class, 'index'])->name('service-provider.lesson-plans');
            Route::get('create', [LessonPlanController::class, 'create'])->name('service-provider.lesson-plans.create');
            Route::post('/', [LessonPlanController::class, 'store']);
            Route::get('{plan}', [LessonPlanController::class, 'show'])->name('service-provider.lesson-plans.show');
            Route::get('{plan}/edit', [LessonPlanController::class, 'edit'])->name('service-provider.lesson-plans.edit');
            Route::put('{plan}', [LessonPlanController::class, 'update']);
            Route::post('{plan}/submit', [LessonPlanController::class, 'submit']);
        });

        // Profile
        Route::get('profile', [DashboardController::class, 'profile'])->name('service-provider.profile');
        Route::put('profile', [DashboardController::class, 'updateProfile']);

        // Payment Settings
        Route::put('payment-settings', [DashboardController::class, 'updatePaymentSettings'])->name('service-provider.payment-settings');
        Route::put('payment-method', [DashboardController::class, 'updatePaymentMethod'])->name('service-provider.payment-method');

        // Payments History
        Route::get('payments', [DashboardController::class, 'payments'])->name('service-provider.payments');

        // Agreement Download
        Route::get('download-agreement', [DashboardController::class, 'downloadAgreement'])->name('service-provider.download-agreement');

        // API endpoints for Vue components
        Route::prefix('api')->group(function () {
            Route::get('subjects', [DashboardController::class, 'subjects']);
            Route::get('subjects/{subject}/topics', [DashboardController::class, 'topics']);
            Route::get('statistics', [DashboardController::class, 'statistics']);
        });
    });
});
