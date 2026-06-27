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
use App\Http\Controllers\Admin\DriverPayrollController;
use App\Http\Controllers\Admin\ClientBillingController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\PublicOrderLocationController;
use App\Http\Controllers\Admin\WhatsAppTemplateController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\AiConversationController;
use App\Http\Controllers\PublicSupportController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\LegalContentController;
use App\Http\Controllers\PublicCmsController;
use App\Http\Controllers\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Portal\AuthController as PortalAuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Client\SupportController as ClientSupportController;
use App\Http\Controllers\Client\NotificationController as ClientNotificationController;
use App\Http\Controllers\Client\FinanceController as ClientFinanceController;
use App\Http\Controllers\Client\BillingController as ClientPortalBillingController;
use App\Http\Controllers\Client\AccountController as ClientAccountController;
use App\Http\Controllers\Client\BankingDetailsController as ClientBankingController;
use App\Http\Controllers\Client\CompanyController as ClientCompanyController;
use App\Http\Controllers\Client\ReportController as ClientReportController;

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

Route::get('/', [PublicCmsController::class, 'home'])->name('public.home');
Route::get('/page/{slug}', [PublicCmsController::class, 'showPage'])->name('public.page');

// ─── Public Order Location Sharing & Support Chat ──────────────────────────────
Route::get('/order/{order_number}/share-location',  [PublicOrderLocationController::class, 'show'])->name('public.share-location');
Route::post('/order/{order_number}/share-location', [PublicOrderLocationController::class, 'update'])->name('public.share-location.update');
Route::get('/support/ticket/{token}',               [PublicSupportController::class, 'show'])->name('public.support');
Route::post('/support/ticket/{token}',              [PublicSupportController::class, 'store'])->name('public.support.send');
Route::get('/support/ticket/{token}/messages',     [PublicSupportController::class, 'getMessages'])->name('public.support.messages');

// ─── Set Password (invitation flow) ───────────────────────────────────────────
Route::get('/set-password',         [SetPasswordController::class, 'show'])->name('set-password');
Route::post('/set-password',        [SetPasswordController::class, 'store'])->name('set-password.store');
Route::get('/set-password/success', [SetPasswordController::class, 'success'])->name('set-password.success');

// Required by Password::sendResetLink() to build the reset URL in the email
Route::get('/password/reset/{token}', [SetPasswordController::class, 'show'])->name('password.reset');

// ─── Unified Portal (login & forgot-password for all roles) ───────────────────
Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('portal.guest')->group(function () {
        Route::get('login',            [PortalAuthController::class, 'showLogin'])->name('login');
        Route::post('login',           [PortalAuthController::class, 'login']);
        Route::get('forgot-password',  [PortalAuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('forgot-password', [PortalAuthController::class, 'sendResetLink'])->name('forgot-password.send');
    });
});

// ─── Admin ────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Protected — must be authenticated admin/superadmin
    Route::middleware('admin.auth')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout',   [AuthController::class, 'logout'])->name('logout');

        // CMS Management
        Route::resource('cms/pages', PageController::class)->names('cms.pages');
        Route::resource('cms/banners', BannerController::class)->names('cms.banners');
        Route::resource('cms/services', ServiceController::class)->names('cms.services');
        Route::resource('cms/faqs', FaqController::class)->names('cms.faqs');
        Route::get('settings/site', [SiteSettingController::class, 'index'])->name('settings.site.index');
        Route::post('settings/site', [SiteSettingController::class, 'update'])->name('settings.site.update');
        Route::get('settings/legal', [LegalContentController::class, 'index'])->name('settings.legal.index');
        Route::post('settings/legal', [LegalContentController::class, 'update'])->name('settings.legal.update');

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

        // AI Conversations
        Route::get('ai-conversations',                      [AiConversationController::class, 'index'])->name('ai-conversations.index');
        Route::get('ai-conversations/{aiConversation}',     [AiConversationController::class, 'show'])->name('ai-conversations.show');
        Route::delete('ai-conversations/{aiConversation}',  [AiConversationController::class, 'destroy'])->name('ai-conversations.destroy');

        // Support Tickets & Chat Center
        Route::get('support',                     [SupportController::class, 'index'])->name('support.index');
        Route::get('support/create',              [SupportController::class, 'create'])->name('support.create');
        Route::post('support',                    [SupportController::class, 'store'])->name('support.store');
        Route::post('support/{ticket}/send',      [SupportController::class, 'sendMessage'])->name('support.send');
        Route::post('support/{ticket}/resolve',   [SupportController::class, 'resolveTicket'])->name('support.resolve');
        Route::get('support/{ticket}/messages',   [SupportController::class, 'getMessages'])->name('support.messages');

        // Notifications Center
        Route::get('notifications',               [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications',              [NotificationController::class, 'store'])->name('notifications.store');
        Route::get('notifications/unread',        [NotificationController::class, 'getLatestUnread'])->name('notifications.unread');
        Route::post('notifications/clear',        [NotificationController::class, 'markAllRead'])->name('notifications.clear');
        Route::patch('notifications/{id}/read',   [NotificationController::class, 'markOneRead'])->name('notifications.read-one');

        // Reports & Exports Center
        Route::get('reports',                          [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/kpis',                     [ReportController::class, 'kpis'])->name('reports.kpis');
        Route::get('reports/ratings',                  [ReportController::class, 'ratings'])->name('reports.ratings');
        Route::get('reports/drivers/{driver}/kpi',     [ReportController::class, 'driverKpi'])->name('reports.driver-kpi');
        Route::get('reports/export/{table}',           [ReportController::class, 'export'])->name('reports.export');

        // Users management
        Route::prefix('users')->name('')->group(function () {
            Route::resource('clients', ClientController::class)->names('clients');
            Route::post('clients/{client}/resend-invitation',           [ClientController::class, 'resendInvitation'])->name('clients.resend-invitation');
            Route::post('clients/{client}/toggle-notifications',        [ClientController::class, 'toggleNotifications'])->name('clients.toggle-notifications');
            Route::patch('clients/{client}/toggle-status',              [ClientController::class, 'toggleStatus'])->name('clients.toggle-status');
            Route::get('clients/{client}/employees/create',                    [ClientEmployeeController::class, 'create'])->name('clients.employees.create');
            Route::post('clients/{client}/employees',                          [ClientEmployeeController::class, 'store'])->name('clients.employees.store');
            Route::patch('clients/{client}/employees/{employee}/status',       [ClientEmployeeController::class, 'updateStatus'])->name('clients.employees.status');
            Route::delete('clients/{client}/employees/{employee}',             [ClientEmployeeController::class, 'destroy'])->name('clients.employees.destroy');
            Route::get('api/areas', [ClientController::class, 'areas'])->name('api.areas');

            Route::resource('drivers', DriverController::class)->names('drivers');
            Route::post('drivers/{driver}/resend-invitation', [DriverController::class, 'resendInvitation'])->name('drivers.resend-invitation');
            Route::get('drivers/{driver}/location-history',   [DriverController::class, 'locationHistory'])->name('drivers.location-history');
            Route::get('drivers/{driver}/bank-details',       [DriverController::class, 'bankDetails'])->name('drivers.bank-details');
            Route::get('drivers-live-map',                    [DriverController::class, 'liveMap'])->name('drivers.live-map');
            Route::patch('drivers/{driver}/toggle-status',    [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status');

            Route::resource('admins',  AdminUserController::class)->except(['show'])->names('admins');
            Route::post('admins/{admin}/resend-invitation', [AdminUserController::class, 'resendInvitation'])->name('admins.resend-invitation');
        });

        // Orders Management
        Route::get('orders/import',            [BulkOrderController::class, 'showImport'])->name('orders.import');
        Route::get('orders/import/template',   [BulkOrderController::class, 'downloadTemplate'])->name('orders.import.template');
        Route::post('orders/import/upload',    [BulkOrderController::class, 'import'])->name('orders.import.upload');
        Route::get('orders/import/review',     [BulkOrderController::class, 'showReview'])->name('orders.import.review');
        Route::post('orders/import/confirm',   [BulkOrderController::class, 'storeConfirmed'])->name('orders.import.confirm');
        Route::post('orders/calculate-price',  [OrderController::class, 'calculatePrice'])->name('orders.calculate-price');
        Route::post('orders/assign-driver',    [OrderController::class, 'assignDriver'])->name('orders.assign-driver');
        Route::resource('orders', OrderController::class)->names('orders');

        // Financial Ledger & Settlements
        Route::get('financials/invoices',                [FinancialController::class, 'invoices'])->name('financials.invoices');
        Route::get('financials/invoices/{invoice}',      [FinancialController::class, 'showInvoice'])->name('financials.invoices.show');
        Route::get('financials/reconciliation',          [FinancialController::class, 'reconciliation'])->name('financials.reconciliation');
        Route::get('financials',                         [FinancialController::class, 'index'])->name('financials.index');
        Route::get('financials/settle-driver/{driver}',  [FinancialController::class, 'driverSettlementForm'])->name('financials.settle-driver');
        Route::post('financials/settle-driver/{driver}', [FinancialController::class, 'settleDriver'])->name('financials.settle-driver.submit');
        Route::get('financials/payout-client/{client}',  [FinancialController::class, 'clientPayoutForm'])->name('financials.payout-client');
        Route::post('financials/payout-client/{client}', [FinancialController::class, 'payoutClient'])->name('financials.payout-client.submit');

        // Driver Payroll (Saee → Driver compensation)
        Route::get('payroll',                              [DriverPayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/drivers/{driver}/new',         [DriverPayrollController::class, 'create'])->name('payroll.create');
        Route::post('payroll/drivers/{driver}',            [DriverPayrollController::class, 'store'])->name('payroll.store');
        Route::get('payroll/{payment}',                    [DriverPayrollController::class, 'show'])->name('payroll.show');
        Route::post('payroll/{payment}/pay',               [DriverPayrollController::class, 'pay'])->name('payroll.pay');
        Route::delete('payroll/{payment}',                 [DriverPayrollController::class, 'destroy'])->name('payroll.destroy');

        // Client Delivery Fee Billing (Client → Saee invoicing)
        Route::get('billing',                              [ClientBillingController::class, 'index'])->name('billing.index');
        Route::get('billing/clients/{client}/new',         [ClientBillingController::class, 'create'])->name('billing.create');
        Route::post('billing/clients/{client}',            [ClientBillingController::class, 'store'])->name('billing.store');
        Route::get('billing/{invoice}',                    [ClientBillingController::class, 'show'])->name('billing.show');
        Route::post('billing/{invoice}/issue',             [ClientBillingController::class, 'issue'])->name('billing.issue');
        Route::post('billing/{invoice}/pay',               [ClientBillingController::class, 'pay'])->name('billing.pay');
        Route::delete('billing/{invoice}',                 [ClientBillingController::class, 'destroy'])->name('billing.destroy');

        // Expenses
        Route::get('expenses',                             [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create',                      [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses',                            [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}',                   [ExpenseController::class, 'show'])->name('expenses.show');
        Route::delete('expenses/{expense}',                [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });
});

// ─── Client Portal ────────────────────────────────────────────────────────────
Route::prefix('client')->name('client.')->group(function () {

    // Authenticated clients
    Route::middleware('client.auth')->group(function () {
        Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');

        // Dashboard & tracking
        Route::get('/',     [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('track', [ClientDashboardController::class, 'track'])->name('track');

        // Orders
        Route::get('orders/import/template',   [ClientOrderController::class, 'downloadTemplate'])->name('orders.template');
        Route::get('orders/import/review',     [ClientOrderController::class, 'showReview'])->name('orders.import.review');
        Route::post('orders/import/confirm',   [ClientOrderController::class, 'storeConfirmed'])->name('orders.import.confirm');
        Route::get('orders/import',            [ClientOrderController::class, 'showImport'])->name('orders.import');
        Route::post('orders/import',           [ClientOrderController::class, 'import'])->name('orders.import.submit');
        Route::get('orders/create',            [ClientOrderController::class, 'create'])->name('orders.create');
        Route::post('orders',                  [ClientOrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{order}/edit',      [ClientOrderController::class, 'edit'])->name('orders.edit');
        Route::patch('orders/{order}',         [ClientOrderController::class, 'update'])->name('orders.update');
        Route::get('orders/{order}',           [ClientOrderController::class, 'show'])->name('orders.show');
        Route::delete('orders/{order}',        [ClientOrderController::class, 'destroy'])->name('orders.destroy');
        Route::get('orders',                   [ClientOrderController::class, 'index'])->name('orders.index');

        // Support
        Route::get('support',                          [ClientSupportController::class, 'index'])->name('support.index');
        Route::post('support',                         [ClientSupportController::class, 'store'])->name('support.store');
        Route::get('support/{ticket}',                 [ClientSupportController::class, 'show'])->name('support.show');
        Route::post('support/{ticket}/messages',       [ClientSupportController::class, 'sendMessage'])->name('support.message');
        Route::get('support/{ticket}/messages',        [ClientSupportController::class, 'getMessages'])->name('support.messages');
        Route::post('support/{ticket}/close',          [ClientSupportController::class, 'close'])->name('support.close');

        // Notifications
        Route::get('notifications',                    [ClientNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/read',         [ClientNotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all',          [ClientNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('notifications/unread',             [ClientNotificationController::class, 'unreadCount'])->name('notifications.unread');

        // Finances
        Route::get('finances', [ClientFinanceController::class, 'index'])->name('finances.index');

        // Financials (read-only overview for client)
        Route::get('financials',          [ClientFinanceController::class, 'index'])->name('financials.index');
        Route::get('financials/invoices', [ClientFinanceController::class, 'invoices'])->name('financials.invoices');

        // Billing (read-only delivery invoices for client)
        Route::get('billing',           [ClientPortalBillingController::class, 'index'])->name('billing.index');
        Route::get('billing/{invoice}', [ClientPortalBillingController::class, 'show'])->name('billing.show');

        // Reports
        Route::get('reports',        [ClientReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ClientReportController::class, 'export'])->name('reports.export');

        // Helper: areas for a city (used by create/edit forms)
        Route::get('api/areas', function (\Illuminate\Http\Request $request) {
            return response()->json(
                \App\Models\Area::where('city_id', $request->city_id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name'])
            );
        })->name('api.areas');

        // Account
        Route::get('account',                  [ClientAccountController::class, 'index'])->name('account.index');
        Route::get('account/profile',          [ClientAccountController::class, 'editProfile'])->name('account.profile.edit');
        Route::post('account/profile',         [ClientAccountController::class, 'updateProfile'])->name('account.profile.update');
        Route::get('account/password',         [ClientAccountController::class, 'editPassword'])->name('account.password.edit');
        Route::post('account/password',        [ClientAccountController::class, 'updatePassword'])->name('account.password.update');
        Route::get('account/banking-details',  [ClientBankingController::class, 'index'])->name('account.banking');
        Route::post('account/banking-details', [ClientBankingController::class, 'save'])->name('account.banking.save');
        Route::get('account/company',               [ClientCompanyController::class, 'index'])->name('account.company');
        Route::post('account/company',              [ClientCompanyController::class, 'update'])->name('account.company.update');
        Route::post('account/notifications/toggle', [ClientAccountController::class, 'toggleNotifications'])->name('account.notifications.toggle');
    });
});
