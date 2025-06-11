<?php

use App\Http\Controllers\{AuthController, DashboardController};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

// Define Gates (Laravel 11 style - bisa juga di AppServiceProvider)
Gate::define('admin-access', function ($user) {
    return $user->role === 'admin';
});

Gate::define('hr-access', function ($user) {
    return in_array($user->role, ['admin', 'hr']);
});

Gate::define('interviewer-access', function ($user) {
    return in_array($user->role, ['admin', 'hr', 'interviewer']);
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // General Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
        Route::get('/admin/users', function() {
            return 'User Management - Coming Soon';
        })->name('admin.users');
    });
    
    // HR Routes  
    Route::middleware('role:admin,hr')->group(function () {
        Route::get('/hr/dashboard', [DashboardController::class, 'hr'])->name('hr.dashboard');
        Route::get('/hr/candidates', function() {
            return 'Candidate Management - Coming Soon';
        })->name('hr.candidates');
    });
    
    // Interviewer Routes
    Route::middleware('role:admin,hr,interviewer')->group(function () {
        Route::get('/interviewer/dashboard', [DashboardController::class, 'interviewer'])->name('interviewer.dashboard');
        Route::get('/interviewer/schedule', function() {
            return 'Interview Schedule - Coming Soon';
        })->name('interviewer.schedule');
    });
});

// Demo API untuk development (Laravel 11 style)
Route::prefix('api')->middleware(['throttle:10,1'])->group(function () {
    Route::get('/demo-users', [AuthController::class, 'getDemoUsers'])->name('api.demo-users');
});

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});
