<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Admin\AboutValueController;
use App\Http\Controllers\Admin\AiConversationController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BulkOrderController;
use App\Http\Controllers\Admin\BusinessBenefitController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ClientBillingController;
use App\Http\Controllers\Admin\ContactFormSubmissionController;
use App\Http\Controllers\Admin\ContactInformationController;
use App\Http\Controllers\Admin\CustomerStorySectionController;
use App\Http\Controllers\Admin\CustomerTestimonialController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientEmployeeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\DriverPayrollController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FaqPageController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\FlowSectionController;
use App\Http\Controllers\Admin\FlowStepController;
use App\Http\Controllers\Admin\ForBusinessPageController;
use App\Http\Controllers\Admin\HandoverRequestController;
use App\Http\Controllers\Admin\HeroSectionController;
use App\Http\Controllers\Admin\HeroStatController;
use App\Http\Controllers\Admin\IndustryController;
use App\Http\Controllers\Admin\IndustrySectionController;
use App\Http\Controllers\Admin\LegalContentController;
 use App\Http\Controllers\Admin\LoginPageController;
use App\Http\Controllers\Admin\WhySaeeReasonController;
use App\Http\Controllers\Admin\WhySaeeSectionController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RejectionReasonController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServicesPageController;
use App\Http\Controllers\Admin\ShowcaseCapabilityController;
use App\Http\Controllers\Admin\ShowcaseHowItWorkController;
use App\Http\Controllers\Admin\ShowcaseMetricController;
use App\Http\Controllers\Admin\ShowcasePageController;
use App\Http\Controllers\Admin\ShowcaseScreenshotController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\Client\AccountController as ClientAccountController;
use App\Http\Controllers\Client\AiChatController;
use App\Http\Controllers\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Client\BankingDetailsController as ClientBankingController;
use App\Http\Controllers\Client\BillingController as ClientPortalBillingController;
use App\Http\Controllers\Client\CompanyController as ClientCompanyController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\FinanceController as ClientFinanceController;
use App\Http\Controllers\Client\NotificationController as ClientNotificationController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Client\ReportController as ClientReportController;
use App\Http\Controllers\Client\SupportController as ClientSupportController;
use App\Http\Controllers\Client\UserManagementController;
use App\Http\Controllers\Portal\AuthController as PortalAuthController;
use App\Http\Controllers\PublicCmsController;
use App\Http\Controllers\PublicOrderLocationController;
use App\Http\Controllers\PublicSupportController;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session()->put('locale', $locale);
    }

    return redirect()->back();
})->name('lang.switch');

Route::get('/', [PublicCmsController::class, 'home'])->name('public.home');

// ─── Public Order Location Sharing & Support Chat ──────────────────────────────
Route::get('/order/{order_number}/share-location', [PublicOrderLocationController::class, 'show'])->name('public.share-location');
Route::post('/order/{order_number}/share-location', [PublicOrderLocationController::class, 'update'])->name('public.share-location.update');
Route::get('/support/ticket/{token}', [PublicSupportController::class, 'show'])->name('public.support');
Route::post('/support/ticket/{token}', [PublicSupportController::class, 'store'])->name('public.support.send');
Route::get('/support/ticket/{token}/messages', [PublicSupportController::class, 'getMessages'])->name('public.support.messages');

// ─── Set Password (invitation flow) ───────────────────────────────────────────
Route::get('/set-password', [SetPasswordController::class, 'show'])->name('set-password');
Route::post('/set-password', [SetPasswordController::class, 'store'])->name('set-password.store');
Route::get('/set-password/success', [SetPasswordController::class, 'success'])->name('set-password.success');

// Required by Password::sendResetLink() to build the reset URL in the email
Route::get('/password/reset/{token}', [SetPasswordController::class, 'show'])->name('password.reset');

// ─── Unified Portal (login & forgot-password for all roles) ───────────────────
Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('portal.guest')->group(function () {
        Route::get('login', [PortalAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [PortalAuthController::class, 'login']);
        Route::get('forgot-password', [PortalAuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('forgot-password', [PortalAuthController::class, 'sendResetLink'])->name('forgot-password.send');
        Route::get('forgot-password/verify-otp', [PortalAuthController::class, 'showVerifyOtp'])->name('forgot-password.verify-otp');
        Route::post('forgot-password/verify-otp', [PortalAuthController::class, 'verifyOtp'])->name('forgot-password.verify-otp.submit');
    });
});

// ─── Admin ────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Admin login is handled by the unified portal — redirect legacy URLs
    Route::middleware('admin.guest')->group(function () {
        Route::get('login', fn() => redirect()->route('portal.login'))->name('login');
        Route::get('forgot-password', fn() => redirect()->route('portal.forgot-password'))->name('forgot-password');
        Route::get('forgot-password/verify-otp', fn() => redirect()->route('portal.forgot-password.verify-otp'))->name('forgot-password.verify-otp');
    });

    // Protected — must be authenticated admin/superadmin
    Route::middleware(['admin.auth', 'admin.permission'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // CMS Management
        Route::get('cms/services-page', [ServicesPageController::class, 'index'])->name('cms.services-page.index');
        Route::put('cms/services-page', [ServicesPageController::class, 'update'])->name('cms.services-page.update');
        Route::resource('cms/services', ServiceController::class)->names('cms.services');
        Route::get('cms/faq-page', [FaqPageController::class, 'index'])->name('cms.faq-page.index');
        Route::put('cms/faq-page', [FaqPageController::class, 'update'])->name('cms.faq-page.update');
        Route::resource('cms/faqs', FaqController::class)->names('cms.faqs');
        Route::get('cms/login-page', [LoginPageController::class, 'index'])->name('cms.login-page.index');
        Route::put('cms/login-page', [LoginPageController::class, 'update'])->name('cms.login-page.update');

        // Website CMS — Hero Section
        Route::get('cms/hero', [HeroSectionController::class, 'index'])->name('cms.hero.index');
        Route::put('cms/hero', [HeroSectionController::class, 'update'])->name('cms.hero.update');
        Route::resource('cms/hero-stats', HeroStatController::class)->names('cms.hero-stats');

        // Website CMS — Flow
        Route::get('cms/flow', [FlowSectionController::class, 'index'])->name('cms.flow.index');
        Route::put('cms/flow', [FlowSectionController::class, 'update'])->name('cms.flow.update');
        Route::resource('cms/flow-steps', FlowStepController::class)->names('cms.flow-steps');

        // Website CMS — Industries
        Route::get('cms/industries-page', [IndustrySectionController::class, 'index'])->name('cms.industries-page.index');
        Route::put('cms/industries-page', [IndustrySectionController::class, 'update'])->name('cms.industries-page.update');
        Route::resource('cms/industries', IndustryController::class)->names('cms.industries');

        // Website CMS — Customer Stories
        Route::get('cms/customer-stories-page', [CustomerStorySectionController::class, 'index'])->name('cms.customer-stories-page.index');
        Route::put('cms/customer-stories-page', [CustomerStorySectionController::class, 'update'])->name('cms.customer-stories-page.update');
        Route::resource('cms/customer-testimonials', CustomerTestimonialController::class)->names('cms.customer-testimonials');

        // Website CMS — Showcases
        Route::get('cms/showcase-page', [ShowcasePageController::class, 'index'])->name('cms.showcase-page.index');
        Route::put('cms/showcase-page', [ShowcasePageController::class, 'update'])->name('cms.showcase-page.update');
        Route::resource('cms/showcase-capabilities', ShowcaseCapabilityController::class)->names('cms.showcase-capabilities');
        Route::resource('cms/showcase-how-it-works', ShowcaseHowItWorkController::class)->names('cms.showcase-how-it-works');
        Route::resource('cms/showcase-metrics', ShowcaseMetricController::class)->names('cms.showcase-metrics');
        Route::resource('cms/showcase-screenshots', ShowcaseScreenshotController::class)->names('cms.showcase-screenshots');

        // Website CMS — Why Sa'ee
        Route::get('cms/why-saee-page', [WhySaeeSectionController::class, 'index'])->name('cms.why-saee-page.index');
        Route::put('cms/why-saee-page', [WhySaeeSectionController::class, 'update'])->name('cms.why-saee-page.update');
        Route::resource('cms/why-saee-reasons', WhySaeeReasonController::class)->names('cms.why-saee-reasons');

        // Website CMS — Contact Information
        Route::get('cms/contact-information', [ContactInformationController::class, 'index'])->name('cms.contact-information.index');
        Route::put('cms/contact-information', [ContactInformationController::class, 'update'])->name('cms.contact-information.update');

        // Website CMS — Contact Form Submissions
        Route::get('cms/contact-submissions', [ContactFormSubmissionController::class, 'index'])->name('cms.contact-submissions.index');
        Route::get('cms/contact-submissions/{contactFormSubmission}', [ContactFormSubmissionController::class, 'show'])->name('cms.contact-submissions.show');
        Route::patch('cms/contact-submissions/{contactFormSubmission}/status', [ContactFormSubmissionController::class, 'updateStatus'])->name('cms.contact-submissions.update-status');
        Route::delete('cms/contact-submissions/{contactFormSubmission}', [ContactFormSubmissionController::class, 'destroy'])->name('cms.contact-submissions.destroy');

        // Website CMS — For Businesses Page
        Route::get('cms/for-business-page', [ForBusinessPageController::class, 'index'])->name('cms.for-business-page.index');
        Route::put('cms/for-business-page', [ForBusinessPageController::class, 'update'])->name('cms.for-business-page.update');
        Route::resource('cms/business-benefits', BusinessBenefitController::class)->names('cms.business-benefits');

        // Website CMS — About Page
        Route::get('cms/about-page', [AboutPageController::class, 'index'])->name('cms.about-page.index');
        Route::put('cms/about-page', [AboutPageController::class, 'update'])->name('cms.about-page.update');
        Route::resource('cms/about-values', AboutValueController::class)->names('cms.about-values');
        Route::get('settings/site', [SiteSettingController::class, 'index'])->name('settings.site.index');
        Route::post('settings/site', [SiteSettingController::class, 'update'])->name('settings.site.update');
        Route::get('settings/legal', [LegalContentController::class, 'index'])->name('settings.legal.index');
        Route::post('settings/legal', [LegalContentController::class, 'update'])->name('settings.legal.update');

        // Locations management
        Route::resource('cities', CityController::class)->names('cities');
        Route::post('cities/{city}/areas', [CityController::class, 'storeArea'])->name('cities.areas.store');
        Route::delete('cities/{city}/areas/{area}', [CityController::class, 'destroyArea'])->name('cities.areas.destroy');
        Route::patch('cities/{city}/toggle', [CityController::class, 'toggle'])->name('cities.toggle');

        // Settings
        Route::resource('rejection-reasons', RejectionReasonController::class)->names('rejection-reasons');
        Route::patch('rejection-reasons/{rejectionReason}/toggle', [RejectionReasonController::class, 'toggle'])->name('rejection-reasons.toggle');

        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::patch('attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

        // AI Conversations
        Route::get('ai-conversations', [AiConversationController::class, 'index'])->name('ai-conversations.index');
        Route::get('ai-conversations/{aiConversation}', [AiConversationController::class, 'show'])->name('ai-conversations.show');
        Route::delete('ai-conversations/{aiConversation}', [AiConversationController::class, 'destroy'])->name('ai-conversations.destroy');

        // Support Tickets & Chat Center
        Route::get('support', [SupportController::class, 'index'])->name('support.index');
        Route::get('support/unread-count', [SupportController::class, 'unreadCount'])->name('support.unread-count');
        Route::get('support/create', [SupportController::class, 'create'])->name('support.create');
        Route::post('support', [SupportController::class, 'store'])->name('support.store');
        Route::post('support/{ticket}/send', [SupportController::class, 'sendMessage'])->name('support.send');
        Route::post('support/{ticket}/resolve', [SupportController::class, 'resolveTicket'])->name('support.resolve');
        Route::get('support/{ticket}/messages', [SupportController::class, 'getMessages'])->name('support.messages');

        // Notifications Center
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications', [NotificationController::class, 'store'])->name('notifications.store');
        Route::get('notifications/unread', [NotificationController::class, 'getLatestUnread'])->name('notifications.unread');
        Route::post('notifications/clear', [NotificationController::class, 'markAllRead'])->name('notifications.clear');
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markOneRead'])->name('notifications.read-one');

        // Reports & Exports Center
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/kpis', [ReportController::class, 'kpis'])->name('reports.kpis');
        Route::get('reports/ratings', [ReportController::class, 'ratings'])->name('reports.ratings');
        Route::get('reports/drivers/{driver}/kpi', [ReportController::class, 'driverKpi'])->name('reports.driver-kpi');
        Route::get('reports/export/{table}', [ReportController::class, 'export'])->name('reports.export');

        // Users management
        Route::prefix('users')->name('')->group(function () {
            Route::resource('clients', ClientController::class)->names('clients');
            Route::post('clients/{client}/resend-invitation', [ClientController::class, 'resendInvitation'])->name('clients.resend-invitation');
            Route::post('clients/{client}/reset-password', [ClientController::class, 'resetPassword'])->name('clients.reset-password');
            Route::post('clients/{client}/toggle-notifications', [ClientController::class, 'toggleNotifications'])->name('clients.toggle-notifications');
            Route::patch('clients/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('clients.toggle-status');
            Route::get('clients/{client}/employees/create', [ClientEmployeeController::class, 'create'])->name('clients.employees.create');
            Route::post('clients/{client}/employees', [ClientEmployeeController::class, 'store'])->name('clients.employees.store');
            Route::patch('clients/{client}/employees/{employee}/status', [ClientEmployeeController::class, 'updateStatus'])->name('clients.employees.status');
            Route::delete('clients/{client}/employees/{employee}', [ClientEmployeeController::class, 'destroy'])->name('clients.employees.destroy');
            Route::get('api/areas', [ClientController::class, 'areas'])->name('api.areas');

            Route::resource('drivers', DriverController::class)->names('drivers');
            Route::post('drivers/{driver}/resend-invitation', [DriverController::class, 'resendInvitation'])->name('drivers.resend-invitation');
            Route::post('drivers/{driver}/reset-password', [DriverController::class, 'resetPassword'])->name('drivers.reset-password');
            Route::get('drivers/{driver}/location-history', [DriverController::class, 'locationHistory'])->name('drivers.location-history');
            Route::get('drivers/{driver}/bank-details', [DriverController::class, 'bankDetails'])->name('drivers.bank-details');
            Route::get('drivers-live-map', [DriverController::class, 'liveMap'])->name('drivers.live-map');
            Route::patch('drivers/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status');

            Route::resource('admins', AdminUserController::class)->except(['show'])->names('admins');
            Route::post('admins/{admin}/resend-invitation', [AdminUserController::class, 'resendInvitation'])->name('admins.resend-invitation');
            Route::post('admins/{admin}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admins.reset-password');
        });

        // Orders Management
        Route::get('orders/import', [BulkOrderController::class, 'showImport'])->name('orders.import');
        Route::get('orders/import/template', [BulkOrderController::class, 'downloadTemplate'])->name('orders.import.template');
        Route::post('orders/import/upload', [BulkOrderController::class, 'import'])->name('orders.import.upload');
        Route::get('orders/import/review', [BulkOrderController::class, 'showReview'])->name('orders.import.review');
        Route::post('orders/import/confirm', [BulkOrderController::class, 'storeConfirmed'])->name('orders.import.confirm');
        Route::get('orders/import-image', [BulkOrderController::class, 'showImportImage'])->name('orders.import-image');
        Route::post('orders/import-image', [BulkOrderController::class, 'importImage'])->name('orders.import-image.upload');
        Route::post('orders/calculate-price', [OrderController::class, 'calculatePrice'])->name('orders.calculate-price');
        Route::post('orders/assign-driver', [OrderController::class, 'assignDriver'])->name('orders.assign-driver');
        Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
        Route::get('orders/print-all', [OrderController::class, 'printAll'])->name('orders.print-all');
        Route::get('orders/{order}/print', [OrderController::class, 'printOrder'])->name('orders.print');
        Route::resource('orders', OrderController::class)->names('orders');

        // Financial Ledger & Settlements
        Route::get('financials/invoices', [FinancialController::class, 'invoices'])->name('financials.invoices');
        Route::get('financials/invoices/{invoice}', [FinancialController::class, 'showInvoice'])->name('financials.invoices.show');
        Route::get('financials/reconciliation', [FinancialController::class, 'reconciliation'])->name('financials.reconciliation');
        Route::get('financials', [FinancialController::class, 'index'])->name('financials.index');
        Route::get('financials/settle-driver/{driver}', [FinancialController::class, 'driverSettlementForm'])->name('financials.settle-driver');
        Route::post('financials/settle-driver/{driver}', [FinancialController::class, 'settleDriver'])->name('financials.settle-driver.submit');
        Route::get('financials/payout-client/{client}', [FinancialController::class, 'clientPayoutForm'])->name('financials.payout-client');
        Route::post('financials/payout-client/{client}', [FinancialController::class, 'payoutClient'])->name('financials.payout-client.submit');

        // Checkout Approvals (Driver Handover Requests)
        Route::get('financials/handover-requests', [HandoverRequestController::class, 'index'])->name('financials.handover-requests.index');
        Route::get('financials/handover-requests/{handoverRequest}', [HandoverRequestController::class, 'show'])->name('financials.handover-requests.show');
        Route::post('financials/handover-requests/{handoverRequest}/approve', [HandoverRequestController::class, 'approve'])->name('financials.handover-requests.approve');

        // Driver Payroll (Saee → Driver compensation)
        Route::get('payroll', [DriverPayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/drivers/{driver}/new', [DriverPayrollController::class, 'create'])->name('payroll.create');
        Route::post('payroll/drivers/{driver}', [DriverPayrollController::class, 'store'])->name('payroll.store');
        Route::get('payroll/{payment}', [DriverPayrollController::class, 'show'])->name('payroll.show');
        Route::post('payroll/{payment}/pay', [DriverPayrollController::class, 'pay'])->name('payroll.pay');
        Route::delete('payroll/{payment}', [DriverPayrollController::class, 'destroy'])->name('payroll.destroy');

        // Client Delivery Fee Billing (Client → Saee invoicing)
        Route::get('billing', [ClientBillingController::class, 'index'])->name('billing.index');
        Route::get('billing/clients/{client}/new', [ClientBillingController::class, 'create'])->name('billing.create');
        Route::post('billing/clients/{client}', [ClientBillingController::class, 'store'])->name('billing.store');
        Route::get('billing/{invoice}', [ClientBillingController::class, 'show'])->name('billing.show');
        Route::post('billing/{invoice}/issue', [ClientBillingController::class, 'issue'])->name('billing.issue');
        Route::post('billing/{invoice}/pay', [ClientBillingController::class, 'pay'])->name('billing.pay');
        Route::delete('billing/{invoice}', [ClientBillingController::class, 'destroy'])->name('billing.destroy');

        // Expenses
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });
});

// ─── Client Portal ────────────────────────────────────────────────────────────
Route::prefix('client')->name('client.')->group(function () {

    // Authenticated clients
    Route::middleware(['client.auth', 'client.permission'])->group(function () {
        Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');

        // Dashboard & tracking
        Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('track', [ClientDashboardController::class, 'track'])->name('track');

        // Orders
        Route::get('orders/import/template', [ClientOrderController::class, 'downloadTemplate'])->name('orders.template');
        Route::get('orders/import/review', [ClientOrderController::class, 'showReview'])->name('orders.import.review');
        Route::post('orders/import/confirm', [ClientOrderController::class, 'storeConfirmed'])->name('orders.import.confirm');
        Route::get('orders/import', [ClientOrderController::class, 'showImport'])->name('orders.import');
        Route::post('orders/import', [ClientOrderController::class, 'import'])->name('orders.import.submit');
        Route::get('orders/import-image', [ClientOrderController::class, 'showImportImage'])->name('orders.import-image');
        Route::post('orders/import-image', [ClientOrderController::class, 'importImage'])->name('orders.import-image.submit');
        Route::get('orders/export', [ClientOrderController::class, 'export'])->name('orders.export');
        Route::get('orders/print-all', [ClientOrderController::class, 'printAll'])->name('orders.print-all');
        Route::get('orders/{order}/print', [ClientOrderController::class, 'printOrder'])->name('orders.print');
        Route::get('orders/create', [ClientOrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [ClientOrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{order}/edit', [ClientOrderController::class, 'edit'])->name('orders.edit');
        Route::patch('orders/{order}', [ClientOrderController::class, 'update'])->name('orders.update');
        Route::get('orders/{order}', [ClientOrderController::class, 'show'])->name('orders.show');
        Route::delete('orders/{order}', [ClientOrderController::class, 'destroy'])->name('orders.destroy');
        Route::get('orders', [ClientOrderController::class, 'index'])->name('orders.index');

        // Support
        Route::get('support', [ClientSupportController::class, 'index'])->name('support.index');
        Route::get('support/unread-count', [ClientSupportController::class, 'unreadCount'])->name('support.unread-count');
        Route::post('support', [ClientSupportController::class, 'store'])->name('support.store');
        Route::get('support/{ticket}', [ClientSupportController::class, 'show'])->name('support.show');
        Route::post('support/{ticket}/messages', [ClientSupportController::class, 'sendMessage'])->name('support.message');
        Route::get('support/{ticket}/messages', [ClientSupportController::class, 'getMessages'])->name('support.messages');
        Route::post('support/{ticket}/close', [ClientSupportController::class, 'close'])->name('support.close');

        // Notifications
        Route::get('notifications', [ClientNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/read', [ClientNotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [ClientNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('notifications/unread', [ClientNotificationController::class, 'unreadCount'])->name('notifications.unread');

        // Financials (read-only overview for client)
        Route::get('financials/invoices', [ClientFinanceController::class, 'invoices'])->name('financials.invoices');
        Route::get('financials/invoices/{invoice}', [ClientFinanceController::class, 'showInvoice'])->name('financials.invoices.show');

        // Billing (read-only delivery invoices for client)
        Route::get('billing', [ClientPortalBillingController::class, 'index'])->name('billing.index');
        Route::get('billing/{invoice}', [ClientPortalBillingController::class, 'show'])->name('billing.show');
        Route::post('billing/{invoice}/pay', [ClientPortalBillingController::class, 'pay'])->name('billing.pay');

        // Reports
        Route::get('reports', [ClientReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ClientReportController::class, 'export'])->name('reports.export');
        Route::get('reports/print', [ClientReportController::class, 'print'])->name('reports.print');

        // AI Chatbot Assistant
        Route::get('ai-assistant', [AiChatController::class, 'index'])->name('ai-chat.index');
        Route::post('ai-assistant/reset', [AiChatController::class, 'reset'])->name('ai-chat.reset');

        // Helper: areas for a city (used by create/edit forms)
        Route::get('api/areas', function (Request $request) {
            return response()->json(
                Area::where('city_id', $request->city_id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name'])
            );
        })->name('api.areas');

        // User Management (Team)
        Route::resource('users', UserManagementController::class)->except(['show'])->names('users');

        // Account
        Route::get('account', [ClientAccountController::class, 'index'])->name('account.index');
        Route::get('account/profile', [ClientAccountController::class, 'editProfile'])->name('account.profile.edit');
        Route::post('account/profile', [ClientAccountController::class, 'updateProfile'])->name('account.profile.update');
        Route::get('account/password', [ClientAccountController::class, 'editPassword'])->name('account.password.edit');
        Route::post('account/password', [ClientAccountController::class, 'updatePassword'])->name('account.password.update');
        Route::get('account/banking-details', [ClientBankingController::class, 'index'])->name('account.banking');
        Route::post('account/banking-details', [ClientBankingController::class, 'save'])->name('account.banking.save');
        Route::get('account/company', [ClientCompanyController::class, 'index'])->name('account.company');
        Route::post('account/company', [ClientCompanyController::class, 'update'])->name('account.company.update');
        Route::post('account/notifications/toggle', [ClientAccountController::class, 'toggleNotifications'])->name('account.notifications.toggle');
    });
});
