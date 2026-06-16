<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\RejectionReasonController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ClientEmployeeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\BulkOrderController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\PublicOrderLocationController;
use App\Http\Controllers\Admin\WhatsAppTemplateController;
use App\Http\Controllers\Admin\AttendanceController;

Route::get('/', function () {
    return view('welcome');
});

// ─── Public Order Location Sharing ─────────────────────────────────────────────
Route::get('/order/{order_number}/share-location',  [PublicOrderLocationController::class, 'show'])->name('public.share-location');
Route::post('/order/{order_number}/share-location', [PublicOrderLocationController::class, 'update'])->name('public.share-location.update');

// ─── Set Password (invitation flow) ───────────────────────────────────────────
Route::get('/set-password',         [SetPasswordController::class, 'show'])->name('set-password');
Route::post('/set-password',        [SetPasswordController::class, 'store'])->name('set-password.store');
Route::get('/set-password/success', [SetPasswordController::class, 'success'])->name('set-password.success');

// ─── Admin ────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest-only (not already logged in as admin)
    Route::middleware('admin.guest')->group(function () {
        Route::get('login',            [AuthController::class, 'showLogin'])->name('login');
        Route::post('login',           [AuthController::class, 'login']);
        Route::get('forgot-password',  [AuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    });

    // Protected — must be authenticated admin/superadmin
    Route::middleware('admin.auth')->group(function () {
        Route::get('dashboard', fn () => view('admin.dashboard'))->name('dashboard');
        Route::post('logout',   [AuthController::class, 'logout'])->name('logout');

        // Locations management
        Route::resource('cities', CityController::class)->names('cities');
        Route::post('cities/{city}/areas',            [CityController::class, 'storeArea'])->name('cities.areas.store');
        Route::delete('cities/{city}/areas/{area}',   [CityController::class, 'destroyArea'])->name('cities.areas.destroy');
        Route::patch('cities/{city}/toggle',           [CityController::class, 'toggle'])->name('cities.toggle');

        // Settings
        Route::resource('rejection-reasons', RejectionReasonController::class)->names('rejection-reasons');
        Route::patch('rejection-reasons/{rejectionReason}/toggle', [RejectionReasonController::class, 'toggle'])->name('rejection-reasons.toggle');

        Route::get('whatsapp-templates', [WhatsAppTemplateController::class, 'index'])->name('whatsapp-templates.index');
        Route::patch('whatsapp-templates/{whatsappTemplate}', [WhatsAppTemplateController::class, 'update'])->name('whatsapp-templates.update');

        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::patch('attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

        // Users management
        Route::prefix('users')->name('')->group(function () {
            Route::resource('clients', ClientController::class)->names('clients');
            Route::post('clients/{client}/resend-invitation', [ClientController::class, 'resendInvitation'])->name('clients.resend-invitation');
            Route::get('clients/{client}/employees/create',                    [ClientEmployeeController::class, 'create'])->name('clients.employees.create');
            Route::post('clients/{client}/employees',                          [ClientEmployeeController::class, 'store'])->name('clients.employees.store');
            Route::patch('clients/{client}/employees/{employee}/status',       [ClientEmployeeController::class, 'updateStatus'])->name('clients.employees.status');
            Route::delete('clients/{client}/employees/{employee}',             [ClientEmployeeController::class, 'destroy'])->name('clients.employees.destroy');
            Route::get('api/areas', [ClientController::class, 'areas'])->name('api.areas');

            Route::resource('drivers', DriverController::class)->names('drivers');
            Route::post('drivers/{driver}/resend-invitation', [DriverController::class, 'resendInvitation'])->name('drivers.resend-invitation');
            Route::get('drivers/{driver}/location-history',   [DriverController::class, 'locationHistory'])->name('drivers.location-history');

            Route::resource('admins',  AdminUserController::class)->names('admins');
        });

        // Orders Management
        Route::get('orders/import',            [BulkOrderController::class, 'showImport'])->name('orders.import');
        Route::get('orders/import/template',   [BulkOrderController::class, 'downloadTemplate'])->name('orders.import.template');
        Route::post('orders/import/upload',    [BulkOrderController::class, 'import'])->name('orders.import.upload');
        Route::post('orders/calculate-price',  [OrderController::class, 'calculatePrice'])->name('orders.calculate-price');
        Route::resource('orders', OrderController::class)->names('orders');

        // Financial Ledger & Settlements
        Route::get('financials',                         [FinancialController::class, 'index'])->name('financials.index');
        Route::get('financials/settle-driver/{driver}',  [FinancialController::class, 'driverSettlementForm'])->name('financials.settle-driver');
        Route::post('financials/settle-driver/{driver}', [FinancialController::class, 'settleDriver'])->name('financials.settle-driver.submit');
        Route::get('financials/payout-client/{client}',  [FinancialController::class, 'clientPayoutForm'])->name('financials.payout-client');
        Route::post('financials/payout-client/{client}', [FinancialController::class, 'payoutClient'])->name('financials.payout-client.submit');
    });
});
