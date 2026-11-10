<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;

// Super Admin Auth
Route::get('/', function () { return view('auth.login'); })->name('login');
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
    Route::middleware(\App\Http\Middleware\CheckRole::class . ':admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        
        // EXPLICIT bulk-action routes BEFORE {user} parameterized routes
        Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::get('/users/bulk-action', function() { return redirect()->route('users.index')->with('error', 'Use the form on the Users page.'); });
        
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');
    });

    // Client Management
    Route::middleware(\App\Http\Middleware\CheckRole::class . ':client_manager')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        
        // EXPLICIT bulk-action routes BEFORE {client} parameterized routes
        Route::post('/clients/bulk-action', [ClientController::class, 'bulkAction'])->name('clients.bulk-action');
        Route::get('/clients/bulk-action', function() { return redirect()->route('clients.index')->with('error', 'Use the form on the Clients page.'); });
        Route::post('/clients/{id}/restore', [ClientController::class, 'restore'])->name('clients.restore');
        
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
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
Route::get('/{slug}/dashboard', [ClientPortalController::class, 'dashboard'])->name('client.portal.dashboard');
Route::post('/{slug}/logout', [ClientPortalController::class, 'logout']);