<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\SupportTicketController as ClientSupportTicketController;

use App\Http\Controllers\PaymentController;



// Removed duplicate checkout.summary route - now handled in checkout group
Route::get('/invoice/{invoice}', App\Livewire\InvoiceShow::class)->name('invoice.show');
Route::post('/payment/callback', [App\Http\Controllers\PaymentController::class, 'handleTripayCallback'])->name('payment.callback');

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/templates/{template}', [\App\Http\Controllers\TemplateController::class, 'show'])->name('templates.show');

// Payment routes (public for callback)
Route::post('/payment/callback', [PaymentController::class, 'handleCallback'])->name('payment.callback');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->middleware('guest');

// Password Reset routes
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Protected routes with role middleware
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    // Product Management Routes (controller-based, mirroring Users patterns)
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->names([
        'index' => 'admin.products.index',
        'create' => 'admin.products.create',
        'store' => 'admin.products.store',
        'show' => 'admin.products.show',
        'edit' => 'admin.products.edit',
        'update' => 'admin.products.update',
        'destroy' => 'admin.products.destroy'
    ]);
    Route::patch('/products/{product}/toggle-status', [\App\Http\Controllers\Admin\ProductController::class, 'toggleStatus'])->name('admin.products.toggle-status');
    Route::delete('/products/{product}/force-delete', [\App\Http\Controllers\Admin\ProductController::class, 'forceDestroy'])->name('admin.products.force-destroy');
    Route::post('/products/bulk-action', [\App\Http\Controllers\Admin\ProductController::class, 'bulkAction'])->name('admin.products.bulk-action');
    
    // User Management Routes
    Route::resource('users', AdminUserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy'
    ]);
    Route::post('/users/bulk-action', [AdminUserController::class, 'bulkAction'])->name('admin.users.bulk-action');
    
    Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::post('/users/bulk-action', [AdminUserController::class, 'bulkAction'])->name('admin.users.bulk-action');
    
    // Invoice Management Routes
    Route::resource('invoices', AdminInvoiceController::class)->names([
        'index' => 'admin.invoices.index',
        'create' => 'admin.invoices.create',
        'store' => 'admin.invoices.store',
        'show' => 'admin.invoices.show',
        'edit' => 'admin.invoices.edit',
        'update' => 'admin.invoices.update',
        'destroy' => 'admin.invoices.destroy'
    ]);
    Route::post('/invoices/{invoice}/send', [AdminInvoiceController::class, 'send'])->name('admin.invoices.send');
    Route::post('/invoices/{invoice}/mark-paid', [AdminInvoiceController::class, 'markAsPaid'])->name('admin.invoices.mark-paid');
    Route::post('/invoices/{invoice}/mark-overdue', [AdminInvoiceController::class, 'markAsOverdue'])->name('admin.invoices.mark-overdue');
    Route::post('/invoices/{invoice}/cancel', [AdminInvoiceController::class, 'cancel'])->name('admin.invoices.cancel');
    Route::get('/invoices/{invoice}/download', [AdminInvoiceController::class, 'download'])->name('admin.invoices.download');
    Route::post('/invoices/bulk-action', [AdminInvoiceController::class, 'bulkAction'])->name('admin.invoices.bulk-action');
    Route::get('/invoices-statistics', [AdminInvoiceController::class, 'statistics'])->name('admin.invoices.statistics');
    
    // Order Management Routes
    Route::resource('orders', AdminOrderController::class)->names([
        'index' => 'admin.orders.index',
        'create' => 'admin.orders.create',
        'store' => 'admin.orders.store',
        'show' => 'admin.orders.show',
        'edit' => 'admin.orders.edit',
        'update' => 'admin.orders.update',
        'destroy' => 'admin.orders.destroy'
    ]);
    Route::post('/orders/{order}/activate', [AdminOrderController::class, 'activate'])->name('admin.orders.activate');
    Route::post('/orders/{order}/suspend', [AdminOrderController::class, 'suspend'])->name('admin.orders.suspend');
    Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('admin.orders.cancel');
    Route::post('/orders/bulk-action', [AdminOrderController::class, 'bulkAction'])->name('admin.orders.bulk-action');
    Route::get('/orders-statistics', [AdminOrderController::class, 'statistics'])->name('admin.orders.statistics');
    
    // Project Management Routes
    Route::resource('projects', AdminProjectController::class)->names([
        'index' => 'admin.projects.index',
        'create' => 'admin.projects.create',
        'store' => 'admin.projects.store',
        'show' => 'admin.projects.show',
        'edit' => 'admin.projects.edit',
        'update' => 'admin.projects.update',
        'destroy' => 'admin.projects.destroy'
    ]);
    Route::post('/projects/{project}/assign', [AdminProjectController::class, 'assign'])->name('admin.projects.assign');
    Route::post('/projects/{project}/progress', [AdminProjectController::class, 'updateProgress'])->name('admin.projects.progress');
    Route::post('/projects/{project}/complete', [AdminProjectController::class, 'complete'])->name('admin.projects.complete');
    Route::post('/projects/{project}/hold', [AdminProjectController::class, 'hold'])->name('admin.projects.hold');
    Route::post('/projects/{project}/resume', [AdminProjectController::class, 'resume'])->name('admin.projects.resume');
    Route::post('/projects/bulk-action', [AdminProjectController::class, 'bulkAction'])->name('admin.projects.bulk-action');
    Route::get('/projects-statistics', [AdminProjectController::class, 'statistics'])->name('admin.projects.statistics');
    
    // Support Ticket Management Routes
    Route::resource('tickets', AdminSupportTicketController::class)->names([
        'index' => 'admin.tickets.index',
        'create' => 'admin.tickets.create',
        'store' => 'admin.tickets.store',
        'show' => 'admin.tickets.show',
        'edit' => 'admin.tickets.edit',
        'update' => 'admin.tickets.update',
        'destroy' => 'admin.tickets.destroy'
    ]);
    Route::post('/tickets/{ticket}/assign', [AdminSupportTicketController::class, 'assign'])->name('admin.tickets.assign');
    Route::post('/tickets/{ticket}/in-progress', [AdminSupportTicketController::class, 'markInProgress'])->name('admin.tickets.in-progress');
    Route::post('/tickets/{ticket}/resolve', [AdminSupportTicketController::class, 'resolve'])->name('admin.tickets.resolve');
    Route::post('/tickets/{ticket}/close', [AdminSupportTicketController::class, 'close'])->name('admin.tickets.close');
    Route::post('/tickets/{ticket}/reopen', [AdminSupportTicketController::class, 'reopen'])->name('admin.tickets.reopen');
    Route::post('/tickets/{ticket}/reply', [AdminSupportTicketController::class, 'reply'])->name('admin.tickets.reply');
    Route::post('/tickets/bulk-action', [AdminSupportTicketController::class, 'bulkAction'])->name('admin.tickets.bulk-action');
    Route::get('/tickets-statistics', [AdminSupportTicketController::class, 'statistics'])->name('admin.tickets.statistics');
    
    // Template Management Routes
    Route::resource('templates', AdminTemplateController::class)->names([
        'index' => 'admin.templates.index',
        'create' => 'admin.templates.create',
        'store' => 'admin.templates.store',
        'show' => 'admin.templates.show',
        'edit' => 'admin.templates.edit',
        'update' => 'admin.templates.update',
        'destroy' => 'admin.templates.destroy'
    ]);
    Route::post('/templates/{template}/toggle-status', [AdminTemplateController::class, 'toggleStatus'])->name('admin.templates.toggle-status');
    Route::post('/templates/{template}/duplicate', [AdminTemplateController::class, 'duplicate'])->name('admin.templates.duplicate');
    Route::post('/templates/bulk-action', [AdminTemplateController::class, 'bulkAction'])->name('admin.templates.bulk-action');
    Route::post('/templates/sort-order', [AdminTemplateController::class, 'updateSortOrder'])->name('admin.templates.sort-order');
    Route::get('/templates-statistics', [AdminTemplateController::class, 'statistics'])->name('admin.templates.statistics');
    Route::get('/templates-search', [AdminTemplateController::class, 'search'])->name('admin.templates.search');
    
    // Subscription Plan Management Routes
    Route::resource('subscription-plans', \App\Http\Controllers\Admin\SubscriptionPlanController::class)->names([
        'index' => 'admin.subscription-plans.index',
        'create' => 'admin.subscription-plans.create',
        'store' => 'admin.subscription-plans.store',
        'show' => 'admin.subscription-plans.show',
        'edit' => 'admin.subscription-plans.edit',
        'update' => 'admin.subscription-plans.update',
        'destroy' => 'admin.subscription-plans.destroy'
    ]);
    Route::post('/subscription-plans/{subscriptionPlan}/toggle-status', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'toggleStatus'])->name('admin.subscription-plans.toggle-status');
    
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminDashboardController::class, 'updateSettings'])->name('admin.settings.update');
});

Route::middleware(['auth', 'role:staff'])->prefix('staff')->group(function () {
    Route::get('/', [StaffDashboardController::class, 'index'])->name('staff.dashboard');
    Route::get('/projects', [StaffDashboardController::class, 'projects'])->name('staff.projects');
    Route::get('/support', [StaffDashboardController::class, 'support'])->name('staff.support');
    Route::get('/orders', [StaffDashboardController::class, 'orders'])->name('staff.orders');
    Route::get('/invoices', [StaffDashboardController::class, 'invoices'])->name('staff.invoices');
});

Route::middleware(['auth', 'role:client'])->prefix('client')->group(function () {
    Route::get('/', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    Route::get('/products', [ClientDashboardController::class, 'products'])->name('client.products');
    Route::get('/orders', [ClientDashboardController::class, 'orders'])->name('client.orders');
    Route::get('/projects', [ClientDashboardController::class, 'projects'])->name('client.projects.index');
    Route::get('/projects/{project}', [ClientDashboardController::class, 'showProject'])->name('client.projects.show');
    Route::get('/invoices', [ClientDashboardController::class, 'invoices'])->name('client.invoices.index');
    
    // Client Payment Routes
    Route::get('/invoices/{invoice}/payment', [PaymentController::class, 'showPayment'])->name('client.invoices.payment');
    Route::post('/invoices/{invoice}/payment', [PaymentController::class, 'createPayment'])->name('client.invoices.payment.create');
    Route::post('/invoices/{invoice}/pay', [PaymentController::class, 'createPayment'])->name('client.invoices.pay');
    Route::get('/invoices/{invoice}/payment/instructions', [PaymentController::class, 'showPaymentInstructions'])->name('client.invoices.payment.instructions');
    Route::get('/invoices/{invoice}/payment/status', [PaymentController::class, 'checkPaymentStatus'])->name('client.invoices.payment.status');
    Route::get('/invoices/{invoice}', [ClientDashboardController::class, 'showInvoice'])->name('client.invoices.show');
    
    // Client Support Ticket Routes
    Route::resource('tickets', ClientSupportTicketController::class)->names([
        'index' => 'client.tickets.index',
        'create' => 'client.tickets.create',
        'store' => 'client.tickets.store',
        'show' => 'client.tickets.show',
        'edit' => 'client.tickets.edit',
        'update' => 'client.tickets.update',
        'destroy' => 'client.tickets.destroy'
    ]);
    // Dedicated reply page (form)
    Route::get('/tickets/{ticket}/reply', [ClientSupportTicketController::class, 'replyForm'])->name('client.tickets.reply.form');
    Route::post('/tickets/{ticket}/reply', [ClientSupportTicketController::class, 'reply'])->name('client.tickets.reply');
    Route::post('/tickets/{ticket}/close', [ClientSupportTicketController::class, 'close'])->name('client.tickets.close');
    
    Route::get('/support', [ClientDashboardController::class, 'support'])->name('client.support');
    Route::get('/profile', [ClientDashboardController::class, 'profile'])->name('client.profile');
    Route::put('/profile', [ClientDashboardController::class, 'updateProfile'])->name('client.profile.update');
    Route::put('/password', [ClientDashboardController::class, 'updatePassword'])->name('client.password.update');
});

// Fallback dashboard route
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();
    switch ($user->role) {
        case 'admin':
            return redirect()->route('admin.dashboard');
        case 'staff':
            return redirect()->route('staff.dashboard');
        case 'client':
            return redirect()->route('client.dashboard');
        default:
            return view('dashboard');
    }
})->name('dashboard');

Route::prefix('checkout')->name('checkout.')->group(function () {
    // Step 1: Template Selection
    Route::get('/', [App\Http\Controllers\CheckoutController::class, 'index'])->name('index');
    Route::post('/step1', [App\Http\Controllers\CheckoutController::class, 'step1'])->name('step1');
    
    // Step 2: Configure - Billing cycle/subscription plan
    Route::get('/configure/', [App\Http\Controllers\CheckoutController::class, 'configure'])->name('configure');
    Route::post('/configure/', [App\Http\Controllers\CheckoutController::class, 'configure'])->name('configure.post');
    
    // Step 3: Addons - Product addon selection (optional)
    Route::get('/addon/', [App\Http\Controllers\CheckoutController::class, 'addons'])->name('addon');
    Route::post('/addon/', [App\Http\Controllers\CheckoutController::class, 'addons'])->name('addon.post');
    
    // Step 4: Personal Info - Domain and user data
    Route::get('/personal-info', [App\Http\Controllers\CheckoutController::class, 'personalInfo'])->name('personal-info');
    Route::post('/personal-info', [App\Http\Controllers\CheckoutController::class, 'personalInfo'])->name('personal-info.post');
    
    // Step 5: Summary - Order summary and payment method selection
    Route::get('/summary', [App\Http\Controllers\CheckoutController::class, 'summary'])->name('summary');
    Route::post('/summary', [App\Http\Controllers\CheckoutController::class, 'submit'])->name('submit');
    
    // Step 6: Billing - Payment information (VA/QR/payment guide)
    Route::get('/billing', [App\Http\Controllers\CheckoutController::class, 'billing'])->name('billing');
    
    // Test route for creating Tripay data
    Route::get('/test/create-tripay-data', [App\Http\Controllers\CheckoutController::class, 'createTestTripayData'])->name('test.tripay');
    
    // Step 7: Success
    Route::get('/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('success');
});
