<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\ItemController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthenticateClientPortal;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Super Admin Auth
Route::get('/', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', function (Request $request) {
    $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
    if (Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
});
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    // User Management
    Route::middleware(CheckRole::class.':admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');

        // EXPLICIT bulk-action routes BEFORE {user} parameterized routes
        Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::get('/users/bulk-action', function () {
            return redirect()->route('users.index')->with('error', 'Use the form on the Users page.');
        });

        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');
    });

    // Client Management
    Route::middleware(CheckRole::class.':client_manager')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

        // EXPLICIT bulk-action routes BEFORE {client} parameterized routes
        Route::post('/clients/bulk-action', [ClientController::class, 'bulkAction'])->name('clients.bulk-action');
        Route::get('/clients/bulk-action', function () {
            return redirect()->route('clients.index')->with('error', 'Use the form on the Clients page.');
        });
        Route::post('/clients/{id}/restore', [ClientController::class, 'restore'])->name('clients.restore');

        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('/clients/{client}/reprovision', [ClientController::class, 'reprovision'])->name('clients.reprovision');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::post('/clients/{client}/change-password', [ClientController::class, 'changePassword'])->name('clients.change-password');
    });

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});

// Client Portal Routes (MUST BE AT THE VERY END)
Route::get('/{slug}', [ClientPortalController::class, 'showLogin'])->name('client.portal.login');
Route::post('/{slug}/login', [ClientPortalController::class, 'authenticate']);
Route::prefix('{slug}')
    ->middleware(AuthenticateClientPortal::class)
    ->name('client.portal.')
    ->group(function () {
        Route::get('/dashboard', [ClientPortalController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [ClientPortalController::class, 'logout'])->name('logout');

        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::get('/accounts/lookup', [AccountController::class, 'lookup'])->name('accounts.lookup');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])
            ->whereNumber('account')->name('accounts.destroy');

        Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
        Route::get('/items/lookup', [ItemController::class, 'lookup'])->name('items.lookup');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])->whereNumber('item')->name('items.destroy');

        Route::get('/bills', [ClientPortalController::class, 'page'])
            ->defaults('page', 'bills.index')->name('bills.index');
        Route::get('/bills/sale', [ClientPortalController::class, 'page'])
            ->defaults('page', 'bills.sale')->name('bills.sale');
        Route::post('/bills/sale', [ClientPortalController::class, 'storeBill'])
            ->defaults('billType', 1)->name('bills.sale.store');
        Route::get('/bills/khareed', [ClientPortalController::class, 'page'])
            ->defaults('page', 'bills.khareed')->name('bills.khareed');
        Route::post('/bills/khareed', [ClientPortalController::class, 'storeBill'])
            ->defaults('billType', 2)->name('bills.khareed.store');
        Route::get('/bills/customer', [ClientPortalController::class, 'page'])
            ->defaults('page', 'bills.customer')->name('bills.customer');
        Route::post('/bills/customer', [ClientPortalController::class, 'storeBill'])
            ->defaults('billType', 3)->name('bills.customer.store');

        Route::get('/vouchers/debit', [ClientPortalController::class, 'page'])
            ->defaults('page', 'vouchers.debit')->name('vouchers.debit');
        Route::post('/vouchers/debit', [ClientPortalController::class, 'storeVoucher'])
            ->defaults('voucherType', 'debit')->name('vouchers.debit.store');
        Route::get('/vouchers/credit', [ClientPortalController::class, 'page'])
            ->defaults('page', 'vouchers.credit')->name('vouchers.credit');
        Route::post('/vouchers/credit', [ClientPortalController::class, 'storeVoucher'])
            ->defaults('voucherType', 'credit')->name('vouchers.credit.store');
        Route::get('/vouchers/agrai', [ClientPortalController::class, 'page'])
            ->defaults('page', 'vouchers.agrai')->name('vouchers.agrai');
        Route::post('/vouchers/agrai', [ClientPortalController::class, 'storeVoucher'])
            ->defaults('voucherType', 'agrai')->name('vouchers.agrai.store');
        Route::get('/vouchers/jv', [ClientPortalController::class, 'page'])
            ->defaults('page', 'vouchers.jv')->name('vouchers.jv');
        Route::post('/vouchers/jv', [ClientPortalController::class, 'storeVoucher'])
            ->defaults('voucherType', 'jv')->name('vouchers.jv.store');

        Route::get('/ledger/party', [ClientPortalController::class, 'page'])
            ->defaults('page', 'ledger.party')->name('ledger.party');
        Route::get('/ledger/banam', [ClientPortalController::class, 'page'])
            ->defaults('page', 'ledger.banam')->name('ledger.banam');
        Route::get('/stock/ledger', [ClientPortalController::class, 'page'])
            ->defaults('page', 'stock.ledger')->name('stock.ledger');

        Route::get('/reports/dailybook', [ClientPortalController::class, 'page'])
            ->defaults('page', 'reports.dailybook')->name('reports.dailybook');
        Route::get('/reports/cash', [ClientPortalController::class, 'page'])
            ->defaults('page', 'reports.cash')->name('reports.cash');
        Route::get('/reports/balance', [ClientPortalController::class, 'page'])
            ->defaults('page', 'reports.balance')->name('reports.balance');
        Route::get('/reports/agrai', [ClientPortalController::class, 'page'])
            ->defaults('page', 'reports.agrai')->name('reports.agrai');

        Route::get('/settings', [ClientPortalController::class, 'page'])
            ->defaults('page', 'settings')->name('settings');
        Route::post('/settings', [ClientPortalController::class, 'updateSettings'])->name('settings.update');
        Route::get('/ai-settings', [ClientPortalController::class, 'page'])
            ->defaults('page', 'ai-settings')->name('ai-settings');
        Route::post('/ai-settings', [ClientPortalController::class, 'updateAiSettings'])->name('ai-settings.update');
        Route::post('/ai-chat', [ClientPortalController::class, 'chat'])->name('ai-chat');
    });
