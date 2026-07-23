<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiLicenseController;
use App\Http\Controllers\Api\ApiPaymentController;
use App\Http\Controllers\Api\KomerceWebhookController;
use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\HandleSpotColorCors;
use Illuminate\Support\Facades\Route;

Route::middleware([HandleSpotColorCors::class])->group(function () {
    Route::post('/webhooks/komerce/payment-notification', [KomerceWebhookController::class, 'paymentNotification'])
        ->name('webhooks.komerce.payment');

    Route::post('/auth/register', [ApiAuthController::class, 'register']);
    Route::post('/auth/login', [ApiAuthController::class, 'login']);

    Route::middleware(AuthenticateApiToken::class)->group(function () {
        Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
        Route::get('/auth/me', [ApiAuthController::class, 'me']);
        Route::get('/license/status', [ApiLicenseController::class, 'status']);
        Route::get('/license/devices', [ApiLicenseController::class, 'devices']);
        Route::post('/license/activate', [ApiLicenseController::class, 'activate']);
        Route::post('/license/deactivate', [ApiLicenseController::class, 'deactivate']);
        Route::post('/license/upgrade', [ApiLicenseController::class, 'upgrade']);
        Route::get('/payment/status/{orderId}', [ApiPaymentController::class, 'show'])->name('payment.status');
        Route::post('/payment/simulate/{orderId}', [ApiPaymentController::class, 'simulate'])->name('payment.simulate');
    });
});
