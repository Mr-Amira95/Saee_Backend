<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationPreferenceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\RejectionReasonController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\BankDetailController;
use App\Http\Controllers\Api\ClientUserController;
use App\Http\Controllers\Api\TrackOrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Public\LegalController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ContactInformationController;
use App\Http\Controllers\Public\ContactSubmissionController;
use App\Http\Controllers\Public\CustomerStoriesController;
use App\Http\Controllers\Public\FaqController as PublicFaqController;
use App\Http\Controllers\Public\FlowController;
use App\Http\Controllers\Public\ForBusinessController;
use App\Http\Controllers\Public\HeroController;
use App\Http\Controllers\Public\IndustriesController;
use App\Http\Controllers\Public\ServicesController as PublicServicesController;
use App\Http\Controllers\Public\ShowcasesController;
use App\Http\Controllers\Public\WhySaeeController;
use Illuminate\Support\Facades\Route;

// Fallback login route — prevents Laravel redirecting API clients to a web login page.
Route::get('login', fn () => response()->json(['message' => 'Unauthenticated.'], 401))->name('login');

// WhatsApp webhook endpoints (no auth — called by Meta/provider)
Route::prefix('webhooks')->group(function () {
    Route::get('whatsapp',  [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
    Route::post('whatsapp', [WhatsAppWebhookController::class, 'receive'])->name('webhooks.whatsapp.receive');
});

// Public legal content endpoints (no auth required)
Route::get('legal/terms',   [LegalController::class, 'terms'])->name('api.legal.terms');
Route::get('legal/privacy', [LegalController::class, 'privacy'])->name('api.legal.privacy');

// Public website content endpoints (no auth — consumed by the marketing website)
Route::prefix('public')->name('api.public.')->group(function () {
    Route::get('hero', [HeroController::class, 'show'])->name('hero');
    Route::get('services', [PublicServicesController::class, 'show'])->name('services');
    Route::get('flow', [FlowController::class, 'show'])->name('flow');
    Route::get('industries', [IndustriesController::class, 'show'])->name('industries');
    Route::get('showcases', [ShowcasesController::class, 'show'])->name('showcases');
    Route::get('why-saee', [WhySaeeController::class, 'show'])->name('why-saee');
    Route::get('customer-stories', [CustomerStoriesController::class, 'show'])->name('customer-stories');
    Route::get('faq', [PublicFaqController::class, 'show'])->name('faq');
    Route::get('for-business', [ForBusinessController::class, 'show'])->name('for-business');
    Route::get('about', [AboutController::class, 'show'])->name('about');
    Route::get('contact-information', [ContactInformationController::class, 'show'])->name('contact-information');

    Route::post('contact-submissions', [ContactSubmissionController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('contact-submissions.store');
});

// Public order tracking (no auth — end-customers can track by name, reference, or phone)
Route::get('track', [TrackOrderController::class, 'track'])
    ->middleware('throttle:30,1')
    ->name('api.track');

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

// Public chatbot endpoints
Route::prefix('chatbot')->middleware('throttle:30,1')->group(function () {
    Route::post('message', [ChatbotController::class, 'message'])->name('api.chatbot.message');
    Route::get('history/{session_id}', [ChatbotController::class, 'history'])->name('api.chatbot.history');
});

Route::middleware(['auth:sanctum', 'client.api.permission'])->group(function () {
    Route::get('home', [HomeController::class, 'index'])
        ->name('api.home');

    Route::post('auth/logout', [AuthController::class, 'logout'])
        ->name('api.auth.logout');

    Route::post('auth/change-password', [AuthController::class, 'changePassword'])
        ->middleware('throttle:10,1')
        ->name('api.auth.change-password');

    Route::get('profile', [ProfileController::class, 'show'])
        ->name('api.profile.show');

    Route::match(['put', 'post'], 'profile/company',  [ProfileController::class, 'updateCompany'])
        ->name('api.profile.company.update');

    Route::match(['put', 'post'], 'profile/personal', [ProfileController::class, 'updatePersonal'])
        ->name('api.profile.personal.update');

    Route::get('wallet', [WalletController::class, 'index'])
        ->name('api.wallet.index');

    Route::post('location', [LocationController::class, 'update'])
        ->name('api.location.update');

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

    Route::post('attendance/check-out/confirm', [AttendanceController::class, 'confirmCheckOut'])
        ->name('api.attendance.check-out.confirm');

    Route::get('ratings', [RatingController::class, 'index'])
        ->name('api.ratings.index');

    Route::get('cities', [CityController::class, 'index'])
        ->name('api.cities.index');

    Route::get('rejection-reasons', [RejectionReasonController::class, 'index'])
        ->name('api.rejection-reasons.index');

    Route::get('finances', [FinanceController::class, 'index'])
        ->name('api.finances.index');

    Route::get('reports', [ReportController::class, 'index'])
        ->name('api.reports.index');

    // Orders — static/action routes MUST come before the {order} wildcard
    Route::get('orders/import/template', [OrderController::class, 'downloadImportTemplate'])
        ->name('api.orders.import.template');

    Route::post('orders/import', [OrderController::class, 'importOrders'])
        ->name('api.orders.import');

    Route::get('orders', [OrderController::class, 'index'])
        ->name('api.orders.index');

    Route::post('orders', [OrderController::class, 'store'])
        ->name('api.orders.store');

    Route::get('orders/by-reference/{reference_code?}', [OrderController::class, 'showByReference'])
        ->name('api.orders.by-reference');

    Route::get('orders/{order}', [OrderController::class, 'show'])
        ->name('api.orders.show');

    Route::put('orders/{order}', [OrderController::class, 'update'])
        ->name('api.orders.update');

    Route::delete('orders/{order}', [OrderController::class, 'destroy'])
        ->name('api.orders.destroy');

    Route::post('orders/{order}/cancel',  [OrderController::class, 'cancel'])
        ->name('api.orders.cancel');

    Route::post('orders/{order}/deliver', [OrderController::class, 'deliver'])
        ->name('api.orders.deliver');

    Route::post('orders/{order}/reject', [OrderController::class, 'reject'])
        ->name('api.orders.reject');

    Route::post('orders/{order}/return', [OrderController::class, 'returnOrder'])
        ->name('api.orders.return');

    Route::post('driver/confirm-handover', [OrderController::class, 'confirmHandover'])
        ->name('api.driver.confirm-handover');

    Route::post('driver/pickup', [OrderController::class, 'pickup'])
        ->name('api.driver.pickup');

    Route::get('driver/route',              [RouteController::class, 'show'])
        ->name('api.driver.route.show');

    Route::post('driver/route/recalculate', [RouteController::class, 'recalculate'])
        ->name('api.driver.route.recalculate');

    // Bank details (read: master + employee; write: master only)
    Route::get('bank-details',  [BankDetailController::class, 'show'])->name('api.bank-details.show');
    Route::put('bank-details',  [BankDetailController::class, 'update'])->name('api.bank-details.update');

    // Client user management (employees)
    Route::get('users/permissions', [ClientUserController::class, 'permissions'])->name('api.users.permissions');
    Route::get('users',          [ClientUserController::class, 'index'])->name('api.users.index');
    Route::post('users',         [ClientUserController::class, 'store'])->name('api.users.store');
    Route::put('users/{employee}',    [ClientUserController::class, 'update'])->name('api.users.update');
    Route::delete('users/{employee}', [ClientUserController::class, 'destroy'])->name('api.users.destroy');

    // Client invoices
    Route::get('client/invoices', [InvoiceController::class, 'index'])->name('api.client.invoices.index');
    Route::get('client/invoices/{invoice}', [InvoiceController::class, 'show'])->name('api.client.invoices.show');

    // Client delivery billing invoices
    Route::get('client/billing', [BillingController::class, 'index'])->name('api.client.billing.index');
    Route::get('client/billing/{invoice}', [BillingController::class, 'show'])->name('api.client.billing.show');
    Route::post('client/billing/{invoice}/pay', [BillingController::class, 'pay'])->name('api.client.billing.pay');

    Route::get('support', [SupportController::class, 'index'])
        ->name('api.support.index');

    Route::post('support', [SupportController::class, 'store'])
        ->name('api.support.store');

    Route::get('support/{id}', [SupportController::class, 'show'])
        ->name('api.support.show');

    Route::post('support/{id}/messages', [SupportController::class, 'sendMessage'])
        ->name('api.support.messages.store');

    Route::post('support/{id}/close', [SupportController::class, 'close'])
        ->name('api.support.close');
});
