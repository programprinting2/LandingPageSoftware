<?php

use App\Http\Controllers\AdminAffiliateController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminCommissionController;
use App\Http\Controllers\AdminMailSettingsController;
use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminClientController;
use App\Http\Controllers\AdminLicenseController;
use App\Http\Controllers\AdminHomepageSettingsController;
use App\Http\Controllers\AdminHubContentController;
use App\Http\Controllers\AdminLandingContentController;
use App\Http\Controllers\AdminPromoVoucherController;
use App\Http\Controllers\AdminPaymentOrderController;
use App\Http\Controllers\AdminTransactionController;
use App\Http\Controllers\ClientEmailVerificationController;
use App\Http\Controllers\ClientGoogleAuthController;
use App\Http\Controllers\ClientLoginController;
use App\Http\Controllers\ClientRegistrationController;
use App\Http\Controllers\ClientSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/apps/{slug}', [LandingController::class, 'application'])->name('landing.application');
Route::get('/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
Route::get('/client', [DashboardController::class, 'client'])->name('dashboard.client');
Route::get('/affiliate', fn () => redirect()->route('dashboard.client').'#/affiliate-overview')
    ->name('dashboard.affiliate');
Route::post('/register/client', [ClientRegistrationController::class, 'store'])->name('register.client');
Route::post('/email/verification-resend', [ClientEmailVerificationController::class, 'resend'])
    ->middleware('throttle:6,1')
    ->name('verification.resend');
Route::get('/email/verify/{id}/{hash}', [ClientEmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::get('/auth/google/redirect', [ClientGoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/desktop', [ClientGoogleAuthController::class, 'desktopRedirect'])->name('auth.google.desktop');
Route::get('/auth/google/callback', [ClientGoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::post('/login/client', [ClientLoginController::class, 'store'])->name('login.client');
Route::post('/login/admin', [AdminLoginController::class, 'store'])->name('login.admin');
Route::post('/logout', [ClientLoginController::class, 'destroy'])->name('logout');
Route::get('/api/admin/me', [AdminSessionController::class, 'me'])->name('admin.me');
Route::get('/api/client/me', [ClientSessionController::class, 'me'])->name('client.me');
Route::put('/api/client/profile', [ClientSessionController::class, 'updateProfile'])->name('client.profile.update');
Route::get('/api/client/license', [ClientSessionController::class, 'license'])->name('client.license');
Route::get('/api/client/commerce/plans', [ClientSessionController::class, 'commercePlans'])->name('client.commerce.plans');
Route::post('/api/client/trial/activate', [ClientSessionController::class, 'activateTrial'])->name('client.trial.activate');
Route::post('/api/client/checkout', [ClientSessionController::class, 'checkout'])->name('client.checkout');
Route::post('/api/client/checkout/device-slots', [ClientSessionController::class, 'checkoutDeviceSlots'])->name('client.checkout.device-slots');
Route::get('/api/client/checkout/{orderId}', [ClientSessionController::class, 'checkoutStatus'])->name('client.checkout.status');
Route::post('/api/client/checkout/{orderId}/simulate', [ClientSessionController::class, 'checkoutSimulate'])->name('client.checkout.simulate');
Route::get('/api/client/transactions', [ClientSessionController::class, 'transactions'])->name('client.transactions');
Route::get('/api/client/affiliate', [ClientSessionController::class, 'affiliate'])->name('client.affiliate');
Route::post('/api/client/affiliate/enroll', [ClientSessionController::class, 'enrollAffiliate'])
    ->middleware('throttle:10,1')
    ->name('client.affiliate.enroll');
Route::patch('/api/client/affiliate/code', [ClientSessionController::class, 'updateAffiliateCode'])
    ->middleware('throttle:30,1')
    ->name('client.affiliate.code.update');
Route::post('/api/client/license/deactivate', [ClientSessionController::class, 'deactivateLicense'])->name('client.license.deactivate');
Route::get('/api/client/report-archive', [ClientSessionController::class, 'reportArchiveSettings'])->name('client.report-archive');
Route::put('/api/client/report-archive', [ClientSessionController::class, 'updateReportArchivePassword'])->name('client.report-archive.update');

Route::middleware(['auth', 'admin.user'])->group(function () {
    Route::get('/api/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::patch('/api/admin/users/{user}/role', [AdminUserController::class, 'updateRole'])
        ->middleware('super.admin')
        ->name('admin.users.update-role');
    Route::patch('/api/admin/users/{user}/affiliate', [AdminUserController::class, 'updateAffiliate'])
        ->name('admin.users.update-affiliate');
    Route::delete('/api/admin/users/{user}', [AdminUserController::class, 'destroy'])
        ->name('admin.users.destroy');

    Route::get('/api/admin/clients', [AdminClientController::class, 'index'])->name('admin.clients.index');
    Route::post('/api/admin/clients', [AdminClientController::class, 'store'])->name('admin.clients.store');
    Route::put('/api/admin/clients/{client}', [AdminClientController::class, 'update'])->name('admin.clients.update');
    Route::delete('/api/admin/clients/{client}', [AdminClientController::class, 'destroy'])->name('admin.clients.destroy');

    Route::get('/api/admin/affiliates', [AdminAffiliateController::class, 'index'])->name('admin.affiliates.index');
    Route::post('/api/admin/affiliates', [AdminAffiliateController::class, 'store'])->name('admin.affiliates.store');
    Route::get('/api/admin/affiliates/{affiliate}/transactions', [AdminAffiliateController::class, 'transactions'])->name('admin.affiliates.transactions');
    Route::put('/api/admin/affiliates/{affiliate}', [AdminAffiliateController::class, 'update'])->name('admin.affiliates.update');
    Route::delete('/api/admin/affiliates/{affiliate}', [AdminAffiliateController::class, 'destroy'])->name('admin.affiliates.destroy');

    Route::get('/api/admin/promo-vouchers', [AdminPromoVoucherController::class, 'index'])->name('admin.promo-vouchers.index');
    Route::post('/api/admin/promo-vouchers', [AdminPromoVoucherController::class, 'store'])->name('admin.promo-vouchers.store');
    Route::get('/api/admin/promo-vouchers/{promoVoucher}/usages', [AdminPromoVoucherController::class, 'usages'])->name('admin.promo-vouchers.usages');
    Route::put('/api/admin/promo-vouchers/{promoVoucher}', [AdminPromoVoucherController::class, 'update'])->name('admin.promo-vouchers.update');
    Route::delete('/api/admin/promo-vouchers/{promoVoucher}', [AdminPromoVoucherController::class, 'destroy'])->name('admin.promo-vouchers.destroy');

    Route::get('/api/admin/commissions', [AdminCommissionController::class, 'index'])->name('admin.commissions.index');
    Route::post('/api/admin/commissions/{paymentOrder}/pay', [AdminCommissionController::class, 'pay'])->name('admin.commissions.pay');

    Route::get('/api/admin/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
    Route::post('/api/admin/applications/upload-logo', [AdminApplicationController::class, 'uploadLogo'])->name('admin.applications.upload-logo');
    Route::post('/api/admin/applications', [AdminApplicationController::class, 'store'])->name('admin.applications.store');
    Route::put('/api/admin/applications/{application}', [AdminApplicationController::class, 'update'])->name('admin.applications.update');
    Route::delete('/api/admin/applications/{application}', [AdminApplicationController::class, 'destroy'])->name('admin.applications.destroy');

    Route::get('/api/admin/licenses', [AdminLicenseController::class, 'index'])->name('admin.licenses.index');
    Route::put('/api/admin/licenses/{clientApplication}/package', [AdminLicenseController::class, 'changePackage'])->name('admin.licenses.change-package');
    Route::post('/api/admin/licenses/{clientApplication}/renew', [AdminLicenseController::class, 'renew'])->name('admin.licenses.renew');
    Route::post('/api/admin/licenses/{clientApplication}/revoke', [AdminLicenseController::class, 'revoke'])->name('admin.licenses.revoke');
    Route::post('/api/admin/licenses/{clientApplication}/release-devices', [AdminLicenseController::class, 'releaseDevices'])->name('admin.licenses.release-devices');

    Route::get('/api/admin/transactions', [AdminTransactionController::class, 'index'])->name('admin.transactions.index');
    Route::get('/api/admin/payment-orders', [AdminPaymentOrderController::class, 'index'])->name('admin.payment-orders.index');

    Route::get('/api/admin/mail-settings', [AdminMailSettingsController::class, 'show'])->name('admin.mail-settings.show');
    Route::put('/api/admin/mail-settings', [AdminMailSettingsController::class, 'update'])->name('admin.mail-settings.update');
    Route::post('/api/admin/mail-settings/test', [AdminMailSettingsController::class, 'sendTest'])->name('admin.mail-settings.test');

    Route::get('/api/admin/homepage-settings', [AdminHomepageSettingsController::class, 'show'])->name('admin.homepage-settings.show');
    Route::put('/api/admin/homepage-settings', [AdminHomepageSettingsController::class, 'update'])->name('admin.homepage-settings.update');

    Route::get('/api/admin/hub-content', [AdminHubContentController::class, 'index'])->name('admin.hub-content.index');
    Route::put('/api/admin/hub-content/{key}', [AdminHubContentController::class, 'update'])->name('admin.hub-content.update');

    Route::get('/api/admin/landing-content', [AdminLandingContentController::class, 'index'])->name('admin.landing-content.index');
    Route::post('/api/admin/landing-content/upload-image', [AdminLandingContentController::class, 'uploadImage'])->name('admin.landing-content.upload-image');
    Route::put('/api/admin/landing-content/{key}', [AdminLandingContentController::class, 'update'])->name('admin.landing-content.update');
});
