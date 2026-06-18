<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationPreferenceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\RejectionReasonController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Public\LegalController;
use Illuminate\Support\Facades\Route;

// Public legal content endpoints (no auth required)
Route::get('legal/terms',   [LegalController::class, 'terms'])->name('api.legal.terms');
Route::get('legal/privacy', [LegalController::class, 'privacy'])->name('api.legal.privacy');

Route::post('auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.auth.login');

Route::post('auth/forgot-password/request-code', [ForgotPasswordController::class, 'requestCode'])
    ->middleware('throttle:forgot-password-request')
    ->name('api.auth.forgot-password.request-code');

Route::post('auth/forgot-password/verify-code', [ForgotPasswordController::class, 'verifyCode'])
    ->middleware('throttle:10,1')
    ->name('api.auth.forgot-password.verify-code');

Route::post('auth/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])
    ->middleware('throttle:10,1')
    ->name('api.auth.forgot-password.reset');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('home', [HomeController::class, 'index'])
        ->name('api.home');

    Route::post('auth/logout', [AuthController::class, 'logout'])
        ->name('api.auth.logout');

    Route::post('auth/change-password', [AuthController::class, 'changePassword'])
        ->middleware('throttle:10,1')
        ->name('api.auth.change-password');

    Route::get('profile', [ProfileController::class, 'show'])
        ->name('api.profile.show');

    Route::patch('profile/notifications', [NotificationPreferenceController::class, 'update'])
        ->name('api.profile.notifications.update');

    Route::get('notifications', [NotificationController::class, 'index'])
        ->name('api.notifications.index');

    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('api.notifications.read-all');

    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('api.notifications.read');

    Route::get('attendance', [AttendanceController::class, 'index'])
        ->name('api.attendance.index');

    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->name('api.attendance.check-in');

    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->name('api.attendance.check-out');

    Route::get('ratings', [RatingController::class, 'index'])
        ->name('api.ratings.index');

    Route::get('cities', [CityController::class, 'index'])
        ->name('api.cities.index');

    Route::get('rejection-reasons', [RejectionReasonController::class, 'index'])
        ->name('api.rejection-reasons.index');

    Route::get('finances', [FinanceController::class, 'index'])
        ->name('api.finances.index');

    Route::get('orders', [OrderController::class, 'index'])
        ->name('api.orders.index');

    Route::get('orders/{order}', [OrderController::class, 'show'])
        ->name('api.orders.show');

    Route::post('orders/{order}/deliver', [OrderController::class, 'deliver'])
        ->name('api.orders.deliver');

    Route::post('orders/{order}/reject', [OrderController::class, 'reject'])
        ->name('api.orders.reject');

    Route::get('support', [SupportController::class, 'index'])
        ->name('api.support.index');

    Route::post('support', [SupportController::class, 'store'])
        ->name('api.support.store');

    Route::get('support/{id}', [SupportController::class, 'show'])
        ->name('api.support.show');

    Route::post('support/{id}/messages', [SupportController::class, 'sendMessage'])
        ->name('api.support.messages.store');
});
