<?php

use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminClientController;
use App\Http\Controllers\AdminLicenseController;
use App\Http\Controllers\AdminPaymentOrderController;
use App\Http\Controllers\AdminTransactionController;
use App\Http\Controllers\ClientLoginController;
use App\Http\Controllers\ClientRegistrationController;
use App\Http\Controllers\ClientSessionController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/register/client', [ClientRegistrationController::class, 'store'])->name('register.client');
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

Route::get('/api/admin/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
Route::post('/api/admin/applications', [AdminApplicationController::class, 'store'])->name('admin.applications.store');
Route::put('/api/admin/applications/{application}', [AdminApplicationController::class, 'update'])->name('admin.applications.update');
Route::delete('/api/admin/applications/{application}', [AdminApplicationController::class, 'destroy'])->name('admin.applications.destroy');

Route::get('/api/admin/licenses', [AdminLicenseController::class, 'index'])->name('admin.licenses.index');
Route::post('/api/admin/licenses/{clientApplication}/renew', [AdminLicenseController::class, 'renew'])->name('admin.licenses.renew');
Route::post('/api/admin/licenses/{clientApplication}/revoke', [AdminLicenseController::class, 'revoke'])->name('admin.licenses.revoke');

Route::get('/api/admin/transactions', [AdminTransactionController::class, 'index'])->name('admin.transactions.index');
Route::get('/api/admin/payment-orders', [AdminPaymentOrderController::class, 'index'])->name('admin.payment-orders.index');
