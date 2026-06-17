<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

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
    Route::post('auth/logout', [AuthController::class, 'logout'])
        ->name('api.auth.logout');

    Route::post('auth/change-password', [AuthController::class, 'changePassword'])
        ->middleware('throttle:10,1')
        ->name('api.auth.change-password');
});
