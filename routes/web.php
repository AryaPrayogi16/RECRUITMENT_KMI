<?php

use App\Http\Controllers\{AuthController, DashboardController, JobApplicationController, CandidateController, KraeplinController, DiscController};
use Illuminate\Support\Facades\Route;

// PUBLIC ROUTES - Job Application Form
Route::get('/job-application-form', [JobApplicationController::class, 'showForm'])->name('job.application.form');
Route::post('/job-application-form', [JobApplicationController::class, 'submitApplication'])->name('job.application.submit');
Route::get('/job-application-success', [JobApplicationController::class, 'success'])->name('job.application.success');

// KRAEPLIN TEST ROUTES (Public - for candidates)
Route::prefix('kraeplin')->name('kraeplin.')->group(function () {
    Route::get('/{candidateCode}/instructions', [KraeplinController::class, 'showInstructions'])->name('instructions');
    Route::post('/{candidateCode}/start', [KraeplinController::class, 'startTest'])->name('start');
    Route::post('/submit-test', [KraeplinController::class, 'submitTest'])->name('submit.test');
    Route::get('/{candidateCode}/result', [KraeplinController::class, 'showResult'])->name('result');
});

// DISC TEST ROUTES (Public - for candidates)
Route::prefix('disc')->name('disc.')->group(function () {
    Route::get('/{candidateCode}/instructions', [DiscController::class, 'showInstructions'])->name('instructions');
    Route::post('/{candidateCode}/start', [DiscController::class, 'startTest'])->name('start');
    Route::post('/submit-test', [DiscController::class, 'submitTest'])->name('submit.test');
    Route::get('/{candidateCode}/result', [DiscController::class, 'showResult'])->name('result');
});

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
            Route::get('/export', [CandidateController::class, 'exportMultiple'])->name('export.multiple');
            Route::post('/bulk-action', [CandidateController::class, 'bulkAction'])->name('bulk-action');
            
            // Individual candidate routes
            Route::get('/{id}', [CandidateController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [CandidateController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CandidateController::class, 'update'])->name('update');
            Route::patch('/{id}/status', [CandidateController::class, 'updateStatus'])->name('update-status');
            Route::get('/{id}/schedule-interview', [CandidateController::class, 'scheduleInterview'])->name('schedule-interview');
            
            // Preview and export routes
            Route::get('/{id}/preview', [CandidateController::class, 'preview'])->name('preview');
            Route::get('/{id}/preview/pdf', [CandidateController::class, 'previewPdf'])->name('preview.pdf');
            Route::get('/{id}/preview/html', [CandidateController::class, 'previewHtml'])->name('preview.html');
            Route::get('/{id}/export/pdf', [CandidateController::class, 'exportSingle'])->name('export.single.pdf');
            Route::get('/{id}/export/word', [CandidateController::class, 'exportWord'])->name('export.single.word');
            
            // Test results for HR
            Route::get('/{id}/kraeplin-result', [CandidateController::class, 'kraeplinResult'])->name('kraeplin.result');
            Route::get('/{id}/disc-result', [CandidateController::class, 'discResult'])->name('disc.result');
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

// API Routes with throttling
Route::prefix('api')->middleware(['throttle:10,1'])->group(function () {
    Route::get('/demo-users', [AuthController::class, 'getDemoUsers'])->name('api.demo-users');
});

// ============================================
// DEVELOPMENT/STAGING ONLY ROUTES
// ============================================
if (app()->environment(['local', 'testing', 'staging'])) {
    // Debug routes for development
    Route::prefix('debug')->name('debug.')->group(function () {
        // System health check
        Route::get('/health', function() {
            return response()->json([
                'status' => 'healthy',
                'environment' => app()->environment(),
                'database' => [
                    'users' => \App\Models\User::count(),
                    'candidates' => \App\Models\Candidate::count(),
                    'positions' => \App\Models\Position::count()
                ],
                'timestamp' => now()
            ]);
        });
        
        // DISC specific debug routes
        Route::prefix('disc')->name('disc.')->group(function () {
            Route::get('/result/{candidate}', [DiscController::class, 'debugResult'])->name('result');
            Route::post('/recalculate/{candidate}', [DiscController::class, 'recalculate'])->name('recalculate');
            Route::get('/test-strengths', [DiscController::class, 'testStrengths'])->name('test');
        });
    });
}

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});