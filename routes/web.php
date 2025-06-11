<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\DashboardController;



// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard routes berdasarkan role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/hr/dashboard', [DashboardController::class, 'hr'])->name('hr.dashboard');
    Route::get('/interviewer/dashboard', [DashboardController::class, 'interviewer'])->name('interviewer.dashboard');
});

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});