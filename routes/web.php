<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowroomController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CRM\CustomerController;
use App\Http\Controllers\CRM\LeadController;
use App\Http\Controllers\Sales\SalesController;
use App\Http\Controllers\Sales\DocumentController;
use App\Http\Controllers\Accounting\AccountingController;
use App\Http\Controllers\Finance\CommissionController;
use App\Http\Controllers\Inventory\VehicleController;
use App\Http\Controllers\Inventory\VehicleImportExportController;
use App\Http\Controllers\WhatsApp\WhatsAppController;
use App\Http\Controllers\PartyController;




// SHOWROOM
Route::get('/showroom', [ShowroomController::class, 'home'])
    ->name('showroom.home');
Route::get('/showroom/inventory', [ShowroomController::class, 'inventory'])
    ->name('showroom.inventory');
Route::get('/showroom/about', [ShowroomController::class, 'about'])
    ->name('showroom.about');
Route::get('/showroom/contact', [ShowroomController::class, 'contact'])
    ->name('showroom.contact');
Route::post('/showroom/contact', [ShowroomController::class, 'submitContact'])
    ->name('showroom.contact.submit');
Route::get('/showroom/vehicles/{vehicle}', [ShowroomController::class, 'show'])
    ->name('showroom.vehicle');

// PUBLIC
Route::get('/v/{qrCode}', [VehicleController::class, 'publicQrPage'])->name('vehicles.qr.public');
Route::get('/verify/{code}', [DocumentController::class, 'verify'])->name('documents.verify');
Route::get('/webhook/whatsapp',  [WhatsAppController::class, 'webhookVerify'])->name('whatsapp.webhook.verify');
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'webhookReceive'])->name('whatsapp.webhook');

// AUTH
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/search', 
\App\Http\Controllers\GlobalSearchController::class)->name('search');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/2fa/verify',  [TwoFactorController::class, 'showVerifyForm'])->name('2fa.verify');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.post');
});

// AJAX
Route::middleware('auth')->prefix('ajax')->group(function () {
    Route::get('/vehicle-models',   [VehicleController::class, 'getModels'])->name('ajax.vehicle-models');
    Route::get('/vehicle-variants', [VehicleController::class, 'getVariants'])->name('ajax.vehicle-variants');
});

// MAIN APP
Route::middleware(['auth','2fa','tenant.active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile',          [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/2fa/enable',  [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/profile/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::resource('branches', BranchController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/assign-role',    [UserController::class, 'assignRole'])->name('users.assign-role');
    Route::resource('roles', RoleController::class)->except('show');
    Route::post('/roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions');
    Route::resource('vehicles', VehicleController::class)->middleware('plan.limits:vehicles');
    Route::patch('/vehicles/{vehicle}/status', [VehicleController::class, 'updateStatus'])->name('vehicles.status');
    Route::get('/vehicles/{vehicle}/import-costs', [VehicleController::class, 'editImportCosts'])->name('vehicles.import-costs.edit');
    Route::put('/vehicles/{vehicle}/import-costs', [VehicleController::class, 'updateImportCosts'])->name('vehicles.import-costs.update');
    Route::post('/vehicles/{vehicle}/documents', [VehicleController::class, 'uploadDocument'])->name('vehicles.documents.upload');
    Route::delete('/vehicles/{vehicle}/documents/{document}', [VehicleController::class, 'deleteDocument'])->name('vehicles.documents.delete');
    Route::patch('/vehicles/{vehicle}/documents/{document}/verify', [VehicleController::class, 'verifyDocument'])->name('vehicles.documents.verify');

    Route::post('/vehicles/{vehicle}/images', [VehicleController::class, 'uploadImages'])->name('vehicles.images.upload');
    Route::patch('/vehicles/{vehicle}/images/{media}/feature', [VehicleController::class, 'setFeaturedImage'])->name('vehicles.images.feature');
    Route::delete('/vehicles/{vehicle}/images/{media}', [VehicleController::class, 'deleteImage'])->name('vehicles.images.delete');
    Route::post('/vehicles/{vehicle}/transfers', [VehicleController::class, 'initiateTransfer'])->name('vehicles.transfers.initiate');
    Route::patch('/vehicles/transfers/{transfer}/approve', [VehicleController::class, 'approveTransfer'])->name('vehicles.transfers.approve');
    Route::patch('/vehicles/transfers/{transfer}/complete',[VehicleController::class, 'completeTransfer'])->name('vehicles.transfers.complete');
    Route::patch('/vehicles/transfers/{transfer}/reject',  [VehicleController::class, 'rejectTransfer'])->name('vehicles.transfers.reject');
    Route::post('/vehicles/{vehicle}/qr/regenerate', [VehicleController::class, 'regenerateQr'])->name('vehicles.qr.regenerate');
    Route::get('/vehicles-export',   [VehicleImportExportController::class, 'export'])->name('vehicles.export');
    Route::get('/vehicles-import',   [VehicleImportExportController::class, 'showImport'])->name('vehicles.import');
    Route::post('/vehicles-import',  [VehicleImportExportController::class, 'import'])->name('vehicles.import.store');
    Route::get('/vehicles-template', [VehicleImportExportController::class, 'downloadTemplate'])->name('vehicles.template');
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/activities', [CustomerController::class, 'storeActivity'])->name('customers.activities.store');
    Route::post('/customers/{customer}/documents',  [CustomerController::class, 'uploadDocument'])->name('customers.documents.upload');
    Route::delete('/customers/{customer}/documents/{document}', [CustomerController::class, 'deleteDocument'])->name('customers.documents.delete');
    Route::resource('leads', LeadController::class);
    Route::patch('/leads/{lead}/status',     [LeadController::class, 'updateStatus'])->name('leads.status');
    Route::patch('/leads/{lead}/assign',     [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('/leads/{lead}/follow-up',   [LeadController::class, 'scheduleFollowUp'])->name('leads.follow-up');
    Route::post('/leads/{lead}/convert',     [LeadController::class, 'convertToCustomer'])->name('leads.convert');
    Route::post('/leads/{lead}/activities', [LeadController::class, 'storeActivity'])->name('leads.activities.store');
    Route::get('/quotations',             [SalesController::class, 'quotationIndex'])->name('quotations.index');
    Route::get('/quotations/create',      [SalesController::class, 'quotationCreate'])->name('quotations.create');
    Route::post('/quotations',            [SalesController::class, 'quotationStore'])->name('quotations.store');
    Route::get('/quotations/{quotation}', [SalesController::class, 'quotationShow'])->name('quotations.show');
    Route::patch('/quotations/{quotation}/status', [SalesController::class, 'quotationUpdateStatus'])->name('quotations.status');
    Route::get('/bookings',           [SalesController::class, 'bookingIndex'])->name('bookings.index');
    Route::get('/bookings/create',    [SalesController::class, 'bookingCreate'])->name('bookings.create');
    Route::post('/bookings',          [SalesController::class, 'bookingStore'])->name('bookings.store');
    Route::get('/bookings/{booking}', [SalesController::class, 'bookingShow'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [SalesController::class, 'bookingCancel'])->name('bookings.cancel');
    Route::get('/invoices',           [SalesController::class, 'invoiceIndex'])->name('invoices.index');
    Route::get('/invoices/create',    [SalesController::class, 'invoiceCreate'])->name('invoices.create');
    Route::post('/invoices',          [SalesController::class, 'invoiceStore'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [SalesController::class, 'invoiceShow'])->name('invoices.show');
    Route::post('/invoices/{invoice}/payment', [SalesController::class, 'invoiceRecordPayment'])->name('invoices.payment');
    Route::post('/invoices/{invoice}/cancel',  [SalesController::class, 'invoiceCancel'])->name('invoices.cancel');
    Route::get('/deliveries/create',     [SalesController::class, 'deliveryCreate'])->name('deliveries.create');
    Route::post('/deliveries',           [SalesController::class, 'deliveryStore'])->name('deliveries.store');
    Route::get('/deliveries/{delivery}', [SalesController::class, 'deliveryShow'])->name('deliveries.show');
    Route::get('/trade-ins',           [SalesController::class, 'tradeInIndex'])->name('trade-ins.index');
    Route::get('/trade-ins/create',    [SalesController::class, 'tradeInCreate'])->name('trade-ins.create');
    Route::post('/trade-ins',          [SalesController::class, 'tradeInStore'])->name('trade-ins.store');
    Route::get('/trade-ins/{tradeIn}', [SalesController::class, 'tradeInShow'])->name('trade-ins.show');
    Route::post('/trade-ins/{tradeIn}/approve', [SalesController::class, 'tradeInApprove'])->name('trade-ins.approve');
    Route::get('/document-templates',      [DocumentController::class, 'templateIndex'])->name('document-templates.index');
    Route::get('/document-templates/create',[DocumentController::class, 'templateCreate'])->name('document-templates.create');
    Route::post('/document-templates',     [DocumentController::class, 'templateStore'])->name('document-templates.store');
    Route::get('/document-templates/{documentTemplate}/edit', [DocumentController::class, 'templateEdit'])->name('document-templates.edit');
    Route::put('/document-templates/{documentTemplate}',      [DocumentController::class, 'templateUpdate'])->name('document-templates.update');
    Route::post('/documents/generate',              [DocumentController::class, 'generate'])->name('documents.generate');
    Route::get('/documents/{document}',             [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/download',    [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/send-whatsapp', [DocumentController::class, 'sendWhatsApp'])->name('documents.send-whatsapp');
    Route::post('/documents/{document}/void',       [DocumentController::class, 'void'])->name('documents.void');
    Route::get('/documents-history',                [DocumentController::class, 'history'])->name('documents.history');
    Route::get('/accounts',        [AccountingController::class, 'accountIndex'])->name('accounts.index');
    Route::get('/accounts/create', [AccountingController::class, 'accountCreate'])->name('accounts.create');
    Route::post('/accounts',       [AccountingController::class, 'accountStore'])->name('accounts.store');
    Route::get('/accounts/{account}/edit', [AccountingController::class, 'accountEdit'])->name('accounts.edit');
    Route::put('/accounts/{account}',      [AccountingController::class, 'accountUpdate'])->name('accounts.update');
    Route::delete('/accounts/{account}',   [AccountingController::class, 'accountDestroy'])->name('accounts.destroy');

        // Parties & Debit/Credit Notes
    Route::get('/parties', [PartyController::class, 'index'])->name('parties.index');
    Route::post('/parties', [PartyController::class, 'store'])->name('parties.store');
    Route::get('/parties/{party}', [PartyController::class, 'show'])->name('parties.show');
    Route::post('/parties/{party}/notes', [PartyController::class, 'storeNote'])->name('parties.notes.store');
    Route::put('/parties/{party}/notes/{note}', [PartyController::class, 'updateNote'])->name('parties.notes.update');
    Route::delete('/parties/{party}/notes/{note}', [PartyController::class, 'destroyNote'])->name('parties.notes.destroy');




    Route::get('/journal-entries',        [AccountingController::class, 'journalIndex'])->name('journal-entries.index');
    Route::get('/journal-entries/create', [AccountingController::class, 'journalCreate'])->name('journal-entries.create');
    Route::post('/journal-entries',       [AccountingController::class, 'journalStore'])->name('journal-entries.store');
    Route::get('/journal-entries/{journalEntry}', [AccountingController::class, 'journalShow'])->name('journal-entries.show');
    Route::get('/payments',        [AccountingController::class, 'paymentIndex'])->name('payments.index');
    Route::get('/payments/create', [AccountingController::class, 'paymentCreate'])->name('payments.create');
    Route::post('/payments',       [AccountingController::class, 'paymentStore'])->name('payments.store');
    Route::get('/payments/{payment}/edit', [AccountingController::class, 'paymentEdit'])->name('payments.edit');
    Route::put('/payments/{payment}',      [AccountingController::class, 'paymentUpdate'])->name('payments.update');
    Route::delete('/payments/{payment}',   [AccountingController::class, 'paymentDestroy'])->name('payments.destroy');

    Route::get('/vendors',         [AccountingController::class, 'vendorIndex'])->name('vendors.index');
    Route::get('/vendors/create',  [AccountingController::class, 'vendorCreate'])->name('vendors.create');
    Route::post('/vendors',        [AccountingController::class, 'vendorStore'])->name('vendors.store');
    Route::get('/vendors/{vendor}/edit', [AccountingController::class, 'vendorEdit'])->name('vendors.edit');
    Route::put('/vendors/{vendor}',      [AccountingController::class, 'vendorUpdate'])->name('vendors.update');
    Route::delete('/vendors/{vendor}',   [AccountingController::class, 'vendorDestroy'])->name('vendors.destroy');

    Route::get('/expenses',        [AccountingController::class, 'expenseIndex'])->name('expenses.index');
    Route::get('/expenses/create', [AccountingController::class, 'expenseCreate'])->name('expenses.create');
    Route::post('/expenses',       [AccountingController::class, 'expenseStore'])->name('expenses.store');
    Route::get('/expenses/{expense}/edit', [AccountingController::class, 'expenseEdit'])->name('expenses.edit');
    Route::put('/expenses/{expense}',      [AccountingController::class, 'expenseUpdate'])->name('expenses.update');
    Route::delete('/expenses/{expense}',   [AccountingController::class, 'expenseDestroy'])->name('expenses.destroy');
    Route::get('/reports/trial-balance', [AccountingController::class, 'trialBalance'])->name('reports.trial-balance');
    Route::get('/reports/profit-loss',   [AccountingController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/reports/ledger',        [AccountingController::class, 'ledger'])->name('reports.ledger');
    Route::get('/commissions',        [CommissionController::class, 'index'])->name('commissions.index');
    Route::post('/commissions/{commission}/approve', [CommissionController::class, 'approve'])->name('commissions.approve');
    Route::post('/commissions/{commission}/pay',     [CommissionController::class, 'pay'])->name('commissions.pay');
    Route::get('/commission-rules',   [CommissionController::class, 'rules'])->name('commission-rules.index');
    Route::post('/commission-rules',  [CommissionController::class, 'storeRule'])->name('commission-rules.store');
    Route::post('/commission-rules/{rule}/toggle', [CommissionController::class, 'toggleRule'])->name('commission-rules.toggle');
    Route::get('/whatsapp',                 [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::get('/whatsapp/{conversation}',  [WhatsAppController::class, 'conversation'])->name('whatsapp.conversation');
    Route::post('/whatsapp/{conversation}/send',     [WhatsAppController::class, 'send'])->name('whatsapp.send');
    Route::post('/whatsapp/{conversation}/assign',   [WhatsAppController::class, 'assign'])->name('whatsapp.assign');
    Route::post('/whatsapp/{conversation}/resolve',  [WhatsAppController::class, 'resolve'])->name('whatsapp.resolve');
    Route::post('/whatsapp/{conversation}/create-lead', [WhatsAppController::class, 'createLead'])->name('whatsapp.create-lead');
    Route::get('/whatsapp-quick-replies',  [WhatsAppController::class, 'quickReplies'])->name('whatsapp.quick-replies');
    Route::post('/whatsapp-quick-replies', [WhatsAppController::class, 'storeQuickReply'])->name('whatsapp.quick-replies.store');
    Route::get('/whatsapp-settings',       [WhatsAppController::class, 'settings'])->name('whatsapp.settings');
    Route::post('/whatsapp-settings',      [WhatsAppController::class, 'saveSettings'])->name('whatsapp.settings.save');
});
