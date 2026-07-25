<?php

use App\Http\Controllers\AdminAffiliateController;
use App\Http\Controllers\AdminCommissionController;
use App\Http\Controllers\AdminMailSettingsController;
use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminClientController;
use App\Http\Controllers\AdminLicenseController;
use App\Http\Controllers\AdminLandingContentController;
use App\Http\Controllers\AdminPaymentOrderController;
use App\Http\Controllers\AdminTransactionController;
use App\Http\Controllers\ClientEmailVerificationController;
use App\Http\Controllers\ClientLoginController;
use App\Http\Controllers\ClientRegistrationController;
use App\Http\Controllers\ClientSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
Route::get('/client', [DashboardController::class, 'client'])->name('dashboard.client');
Route::get('/affiliate', [DashboardController::class, 'affiliate'])->name('dashboard.affiliate');
Route::post('/register/client', [ClientRegistrationController::class, 'store'])->name('register.client');
Route::post('/email/verification-resend', [ClientEmailVerificationController::class, 'resend'])
    ->middleware('throttle:6,1')
    ->name('verification.resend');
Route::get('/email/verify/{id}/{hash}', [ClientEmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/login/client', [ClientLoginController::class, 'store'])->name('login.client');
Route::post('/logout', [ClientLoginController::class, 'destroy'])->name('logout');
Route::get('/api/client/me', [ClientSessionController::class, 'me'])->name('client.me');
Route::get('/api/client/license', [ClientSessionController::class, 'license'])->name('client.license');
Route::get('/api/client/transactions', [ClientSessionController::class, 'transactions'])->name('client.transactions');
Route::post('/api/client/license/deactivate', [ClientSessionController::class, 'deactivateLicense'])->name('client.license.deactivate');

Route::get('/api/admin/clients', [AdminClientController::class, 'index'])->name('admin.clients.index');
Route::post('/api/admin/clients', [AdminClientController::class, 'store'])->name('admin.clients.store');
Route::put('/api/admin/clients/{client}', [AdminClientController::class, 'update'])->name('admin.clients.update');
Route::delete('/api/admin/clients/{client}', [AdminClientController::class, 'destroy'])->name('admin.clients.destroy');

Route::get('/api/admin/affiliates', [AdminAffiliateController::class, 'index'])->name('admin.affiliates.index');
Route::post('/api/admin/affiliates', [AdminAffiliateController::class, 'store'])->name('admin.affiliates.store');
Route::get('/api/admin/affiliates/{affiliate}/transactions', [AdminAffiliateController::class, 'transactions'])->name('admin.affiliates.transactions');
Route::put('/api/admin/affiliates/{affiliate}', [AdminAffiliateController::class, 'update'])->name('admin.affiliates.update');
Route::delete('/api/admin/affiliates/{affiliate}', [AdminAffiliateController::class, 'destroy'])->name('admin.affiliates.destroy');

Route::get('/api/admin/commissions', [AdminCommissionController::class, 'index'])->name('admin.commissions.index');
Route::post('/api/admin/commissions/{paymentOrder}/pay', [AdminCommissionController::class, 'pay'])->name('admin.commissions.pay');

Route::get('/api/admin/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
Route::post('/api/admin/applications', [AdminApplicationController::class, 'store'])->name('admin.applications.store');
Route::put('/api/admin/applications/{application}', [AdminApplicationController::class, 'update'])->name('admin.applications.update');
Route::delete('/api/admin/applications/{application}', [AdminApplicationController::class, 'destroy'])->name('admin.applications.destroy');

Route::get('/api/admin/licenses', [AdminLicenseController::class, 'index'])->name('admin.licenses.index');
Route::post('/api/admin/licenses/{clientApplication}/renew', [AdminLicenseController::class, 'renew'])->name('admin.licenses.renew');
Route::post('/api/admin/licenses/{clientApplication}/revoke', [AdminLicenseController::class, 'revoke'])->name('admin.licenses.revoke');

Route::get('/api/admin/transactions', [AdminTransactionController::class, 'index'])->name('admin.transactions.index');
Route::get('/api/admin/payment-orders', [AdminPaymentOrderController::class, 'index'])->name('admin.payment-orders.index');

Route::get('/api/admin/mail-settings', [AdminMailSettingsController::class, 'show'])->name('admin.mail-settings.show');
Route::put('/api/admin/mail-settings', [AdminMailSettingsController::class, 'update'])->name('admin.mail-settings.update');
Route::post('/api/admin/mail-settings/test', [AdminMailSettingsController::class, 'sendTest'])->name('admin.mail-settings.test');

Route::get('/api/admin/landing-content', [AdminLandingContentController::class, 'index'])->name('admin.landing-content.index');
Route::put('/api/admin/landing-content/{key}', [AdminLandingContentController::class, 'update'])->name('admin.landing-content.update');
