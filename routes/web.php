<?php

use App\Http\Controllers\{AuthController, DashboardController, JobApplicationController, CandidateController, DiscController, KraeplinController};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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


// DISC 3D SPECIFIC ROUTES - NEW SECTION
Route::prefix('disc3d')->name('disc3d.')->group(function () {
    Route::get('/{candidateCode}/instructions', [DiscController::class, 'showInstructions'])->name('instructions');
    Route::post('/{candidateCode}/start', [DiscController::class, 'startTest'])->name('start');
    Route::post('/submit-section', [DiscController::class, 'submitSection'])->name('submit.section');
    Route::post('/complete-test', [DiscController::class, 'completeTest'])->name('complete');
    Route::get('/{candidateCode}/result', [DiscController::class, 'showResult'])->name('result');
    
    // Debug endpoint (development only)
    Route::get('/debug-session', [DiscController::class, 'debugSession'])->name('debug');
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
        
        // DISC 3D Admin Management
        Route::prefix('admin/disc3d')->name('admin.disc3d.')->group(function () {
            Route::get('/sections', function() {
                return 'DISC 3D Section Management - Coming Soon';
            })->name('sections');
            Route::get('/config', function() {
                return 'DISC 3D Configuration - Coming Soon';
            })->name('config');
            Route::get('/analytics', function() {
                return 'DISC 3D Analytics - Coming Soon';
            })->name('analytics');
        });
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
            
            // Test results for HR - UPDATED FOR DISC 3D
            Route::get('/{id}/kraeplin-result', [CandidateController::class, 'kraeplinResult'])->name('kraeplin.result');
            Route::get('/{id}/disc3d-result', [CandidateController::class, 'disc3dResult'])->name('disc3d.result');
            Route::get('/{id}/disc3d-export', [CandidateController::class, 'exportDisc3dResult'])->name('export.disc3d.result');
            
            // Keep legacy disc result route for backward compatibility
            Route::get('/{id}/disc-result', [CandidateController::class, 'discResult'])->name('disc.result');
            
            // Soft delete routes
            Route::delete('/{id}', [CandidateController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [CandidateController::class, 'bulkDelete'])->name('bulk-delete');

            // Trashed candidates routes
            Route::get('/trashed', [CandidateController::class, 'trashed'])->name('trashed');
            Route::post('/{id}/restore', [CandidateController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force', [CandidateController::class, 'forceDelete'])->name('force-delete');
        });
        
        // HR DISC 3D Management
        Route::prefix('hr/disc3d')->name('hr.disc3d.')->group(function () {
            Route::get('/results', function() {
                return 'DISC 3D Results Overview - Coming Soon';
            })->name('results');
            Route::get('/reports', function() {
                return 'DISC 3D Reports - Coming Soon';
            })->name('reports');
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
    
    // DISC 3D API endpoints
    Route::prefix('disc3d')->name('api.disc3d.')->group(function () {
        Route::get('/sections', function() {
            return \App\Models\Disc3DSection::with('choices')->active()->ordered()->get();
        })->name('sections');
        
        Route::get('/stats', function() {
            return response()->json([
                'total_sessions' => \App\Models\Disc3DTestSession::count(),
                'completed_sessions' => \App\Models\Disc3DTestSession::where('status', 'completed')->count(),
                'total_results' => \App\Models\Disc3DResult::count(),
                'average_duration' => \App\Models\Disc3DResult::avg('test_duration_seconds')
            ]);
        })->name('stats');
    });
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
                    'positions' => \App\Models\Position::count(),
                    'disc3d_sessions' => \App\Models\Disc3DTestSession::count(),
                    'disc3d_results' => \App\Models\Disc3DResult::count()
                ],
                'timestamp' => now()
            ]);
        });
        
        // DISC 3D debug routes
        Route::prefix('disc3d')->name('disc3d.debug.')->group(function () {
        Route::get('/test-data', function() {
            return response()->json([
                'sections' => \App\Models\Disc3DSection::with('choices')->get(),
                'total_sections' => \App\Models\Disc3DSection::count(),
                'total_choices' => \App\Models\Disc3DSectionChoice::count()
            ]);
        })->name('test.data');
        // Add debug route for development
        Route::get('/debug-session', [DiscController::class, 'debugSession'])->name('debug-session');
        // ✅ Database status checker
        Route::get('/database-status', function() {
                try {
                    $requiredTables = [
                        'disc_3d_test_sessions',
                        'disc_3d_sections', 
                        'disc_3d_section_choices',
                        'disc_3d_responses',
                        'disc_3d_results'
                    ];
                    
                    $tableStatus = [];
                    $allExist = true;
                    
                    foreach ($requiredTables as $table) {
                        try {
                            $exists = DB::getSchemaBuilder()->hasTable($table);
                            $count = $exists ? DB::table($table)->count() : 0;
                            
                            $tableStatus[$table] = [
                                'exists' => $exists,
                                'count' => $count,
                                'status' => $exists ? 'OK' : 'MISSING'
                            ];
                            
                            if (!$exists) $allExist = false;
                            
                        } catch (\Exception $e) {
                            $tableStatus[$table] = [
                                'exists' => false,
                                'count' => 0,
                                'status' => 'ERROR: ' . $e->getMessage()
                            ];
                            $allExist = false;
                        }
                    }
                    
                    // Check migration status
                    $migrationStatus = 'UNKNOWN';
                    try {
                        $migrations = DB::table('migrations')
                            ->where('migration', 'LIKE', '%disc%')
                            ->get();
                        $migrationStatus = $migrations->count() > 0 ? 'APPLIED' : 'NOT_APPLIED';
                    } catch (\Exception $e) {
                        $migrationStatus = 'ERROR: ' . $e->getMessage();
                    }
                    
                    // Check existing data
                    $candidateCount = DB::table('candidates')->count();
                    $kraeplinSessions = DB::table('kraeplin_test_sessions')->count();
                    
                    return response()->json([
                        'system_status' => $allExist ? 'READY' : 'INCOMPLETE',
                        'migration_status' => $migrationStatus,
                        'tables' => $tableStatus,
                        'existing_data' => [
                            'candidates' => $candidateCount,
                            'kraeplin_sessions' => $kraeplinSessions
                        ],
                        'recommendations' => $allExist ? 
                            ['System ready for DISC 3D testing'] : 
                            [
                                'Run: php artisan migrate',
                                'Check migration file exists',
                                'Verify database connection',
                                'Check MySQL permissions'
                            ],
                        'next_steps' => [
                            'Visit: /debug/disc3d/test-candidate-flow',
                            'Test with existing candidate codes',
                            'Check logs in storage/logs/laravel.log'
                        ],
                    ]);
                    
                } catch (\Exception $e) {
                    return response()->json([
                        'system_status' => 'ERROR',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ], 500);
                }
            })->name('database.status');
        });
    });
}

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// DISC 3D Routes (Single Run Mode)
Route::prefix('disc3d')->group(function () {
    // Instructions page
    Route::get('/instructions/{candidateCode}', [DiscController::class, 'showInstructions'])
        ->name('disc3d.instructions');
    
    // Start test (both GET and POST)
    Route::match(['GET', 'POST'], '/start/{candidateCode}', [DiscController::class, 'startTest'])
        ->name('disc3d.start');
    
    // SINGLE RUN: Submit all responses at once (like Kraeplin)
    Route::post('/submit-test', [DiscController::class, 'submitTest'])
        ->name('disc3d.submit.test');
    
    // Legacy endpoints for backward compatibility (optional)
    Route::post('/submit-section', [DiscController::class, 'submitSection'])
        ->name('disc3d.submit.section'); // Still works but not used in single run
    
    Route::post('/complete-test', [DiscController::class, 'completeTest'])
        ->name('disc3d.complete'); // Still works but not used in single run
    
    // Debug endpoint
    Route::get('/debug-session', [DiscController::class, 'debugSession'])
        ->name('disc3d.debug');
});

// Kraeplin Routes (for reference/comparison)
Route::prefix('kraeplin')->group(function () {
    Route::get('/instructions/{candidateCode}', [KraeplinController::class, 'showInstructions'])
        ->name('kraeplin.instructions');
    
    Route::match(['GET', 'POST'], '/start/{candidateCode}', [KraeplinController::class, 'startTest'])
        ->name('kraeplin.start');
    
    // Kraeplin bulk submit (pattern we're following)
    Route::post('/submit-test', [KraeplinController::class, 'submitTest'])
        ->name('kraeplin.submit.test');
});

// Job Application Routes
Route::prefix('job')->group(function () {
    // Application form
    Route::get('/application', function () {
        return view('job.application');
    })->name('job.application.form');
    
    // Success page after all tests completed
    Route::get('/success', function () {
        $candidateCode = request('candidate_code') ?? session('candidate_code');
        return view('job-application.success', compact('candidateCode'));
    })->name('job.application.success');
});

// Test flow redirects
Route::get('/test/{candidateCode}', function ($candidateCode) {
    // Start with Kraeplin first
    return redirect()->route('kraeplin.instructions', $candidateCode);
})->name('test.start');