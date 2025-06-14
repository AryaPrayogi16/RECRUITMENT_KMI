<?php

use App\Http\Controllers\{AuthController, DashboardController, JobApplicationController, CandidateController};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

// PUBLIC ROUTES - Job Application Form
Route::get('/job-application-form', [JobApplicationController::class, 'showForm'])->name('job.application.form');
Route::post('/job-application-form', [JobApplicationController::class, 'submitApplication'])->name('job.application.submit');
Route::get('/job-application-success', [JobApplicationController::class, 'success'])->name('job.application.success');

// Get available positions for dropdown
Route::get('/api/positions', [JobApplicationController::class, 'getPositions'])->name('api.positions');

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
    
    // ===== DEBUG ROUTES - TAMBAHKAN INI =====
    Route::prefix('debug')->group(function () {
        Route::get('/step1-auth', function() {
            return response()->json([
                'step' => '1. Authentication Check',
                'authenticated' => Auth::check(),
                'user' => Auth::user() ? [
                    'id' => Auth::id(),
                    'name' => Auth::user()->full_name ?? 'N/A',
                    'role' => Auth::user()->role ?? 'N/A',
                    'is_active' => Auth::user()->is_active ?? 'N/A'
                ] : null,
                'status' => Auth::check() ? '✅ AUTHENTICATED' : '❌ NOT AUTHENTICATED'
            ]);
        });
        
        Route::get('/step2-gates', function() {
            $user = Auth::user();
            return response()->json([
                'step' => '2. Gates Check',
                'user_role' => $user?->role,
                'gates' => [
                    'admin_access' => Gate::allows('admin-access'),
                    'hr_access' => Gate::allows('hr-access'),
                    'interviewer_access' => Gate::allows('interviewer-access')
                ],
                'status' => Gate::allows('hr-access') ? '✅ HR ACCESS GRANTED' : '❌ HR ACCESS DENIED'
            ]);
        });
        
        Route::get('/step3-middleware', function() {
            return response()->json([
                'step' => '3. Role Middleware Test',
                'message' => 'Role middleware works!',
                'user_role' => Auth::user()?->role,
                'status' => '✅ ROLE MIDDLEWARE WORKS'
            ]);
        })->middleware('role:admin,hr');
        
        Route::get('/step4-database', function() {
            try {
                $candidateCount = \App\Models\Candidate::count();
                $positionCount = \App\Models\Position::count();
                $userCount = \App\Models\User::count();
                
                return response()->json([
                    'step' => '4. Database Check',
                    'database' => [
                        'users' => "✅ {$userCount} records",
                        'candidates' => "✅ {$candidateCount} records",
                        'positions' => "✅ {$positionCount} records"
                    ],
                    'status' => '✅ DATABASE OK'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'step' => '4. Database Check',
                    'error' => $e->getMessage(),
                    'status' => '❌ DATABASE ERROR'
                ]);
            }
        });
        
        Route::get('/simple', function() {
            return response()->json([
                'message' => 'Debug routes working!',
                'user' => Auth::user()?->full_name ?? 'Not authenticated',
                'timestamp' => now()
            ]);
        });
    });
    
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
        
        // Candidate Management Routes
        Route::prefix('candidates')->name('candidates.')->group(function () {
            Route::get('/', [CandidateController::class, 'index'])->name('index');
            Route::get('/search', [CandidateController::class, 'search'])->name('search');
            Route::get('/export', [CandidateController::class, 'export'])->name('export');
            Route::post('/bulk-action', [CandidateController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/{id}', [CandidateController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [CandidateController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CandidateController::class, 'update'])->name('update');
            Route::patch('/{id}/status', [CandidateController::class, 'updateStatus'])->name('update-status');
            Route::get('/{id}/schedule-interview', [CandidateController::class, 'scheduleInterview'])->name('schedule-interview');
        });
    });
    
    // Interviewer Routes
    Route::middleware('role:admin,hr,interviewer')->group(function () {
        Route::get('/interviewer/dashboard', [DashboardController::class, 'interviewer'])->name('interviewer.dashboard');
        Route::get('/interviewer/schedule', function() {
            return 'Interview Schedule - Coming Soon';
        })->name('interviewer.schedule');
    });
});

// Demo API untuk development
Route::prefix('api')->middleware(['throttle:10,1'])->group(function () {
    Route::get('/demo-users', [AuthController::class, 'getDemoUsers'])->name('api.demo-users');
});

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});