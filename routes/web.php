<?php

use App\Http\Controllers\{AuthController, DashboardController, JobApplicationController, CandidateController, PositionController, DiscController, KraeplinController};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// ============================================
// ROOT REDIRECT
// ============================================
Route::get('/', function () {
    return redirect()->route('login');
});

// ============================================
// PUBLIC CAREER ROUTES - Active Positions
// ============================================

// Route untuk melihat semua lowongan aktif (publik)
Route::get('/careers', [PositionController::class, 'publicActivePositions'])
     ->name('careers.index');

// Route untuk melihat detail lowongan (publik)
Route::get('/careers/{id}', [PositionController::class, 'publicPositionDetail'])
     ->name('careers.show')
     ->where('id', '[0-9]+');

// API endpoint untuk mendapatkan lowongan aktif (untuk AJAX/JavaScript)
Route::get('/api/active-positions', [PositionController::class, 'publicActivePositions'])
     ->name('api.active-positions');

// ============================================
// PUBLIC ROUTES - Job Application Form
// ============================================

// Job Application Form
Route::get('/job-application-form', [JobApplicationController::class, 'showForm'])->name('job.application.form');
Route::post('/job-application-form', [JobApplicationController::class, 'submitApplication'])->name('job.application.submit');
Route::get('/job-application-success', [JobApplicationController::class, 'success'])->name('job.application.success');

// Get available positions for dropdown
Route::get('/api/positions', [JobApplicationController::class, 'getPositions'])->name('api.positions');

// ✅ NEW: KTP OCR Routes (Public - for job applicants)
Route::prefix('ktp-ocr')->name('ktp.ocr.')->group(function () {
    Route::post('/upload', [JobApplicationController::class, 'uploadKtpOcr'])->name('upload');
    Route::post('/clear-temp', [JobApplicationController::class, 'clearKtpTemp'])->name('clear.temp');
    
    // Debug routes for KTP processing
    Route::get('/debug-status', [JobApplicationController::class, 'debugKtpStatus'])->name('debug.status');
    Route::post('/clean-temp-files', [JobApplicationController::class, 'cleanTempKtpFiles'])->name('clean.temp.files');
    Route::get('/verify-integrity/{candidateCode?}', [JobApplicationController::class, 'verifyKtpIntegrity'])->name('verify.integrity');
    Route::post('/force-process', [JobApplicationController::class, 'forceProcessKtpFromSession'])->name('force.process');
});

// Additional routes for job application form validation
Route::post('/check-email', [JobApplicationController::class, 'checkEmailExists'])->name('check.email');
Route::post('/check-nik', [JobApplicationController::class, 'checkNikExists'])->name('check.nik');

// ============================================
// RESUME TEST PAGE - Untuk kandidat yang terputus
// ============================================
Route::get('/continue-test', function () {
    return view('resume-test');
})->name('resume.test');


// ============================================
// KRAEPLIN TEST ROUTES (Public - for candidates)
// ============================================
Route::prefix('kraeplin')->name('kraeplin.')->group(function () {
    Route::get('/{candidateCode}/instructions', [KraeplinController::class, 'showInstructions'])->name('instructions');
    Route::post('/{candidateCode}/start', [KraeplinController::class, 'startTest'])->name('start');
    Route::post('/submit-test', [KraeplinController::class, 'submitTest'])->name('submit.test');
    Route::get('/{candidateCode}/result', [KraeplinController::class, 'showResult'])->name('result');
});

// ============================================
// DISC 3D TEST ROUTES (Public - for candidates)
// ============================================
Route::prefix('disc3d')->name('disc3d.')->group(function () {
    Route::get('/{candidateCode}/instructions', [DiscController::class, 'showInstructions'])->name('instructions');
    Route::post('/{candidateCode}/start', [DiscController::class, 'startTest'])->name('start');
    Route::post('/submit-test', [DiscController::class, 'submitTest'])->name('submit.test');
    Route::get('/{candidateCode}/result', [DiscController::class, 'showResult'])->name('result');
    
    // Legacy endpoints for backward compatibility (optional)
    Route::post('/submit-section', [DiscController::class, 'submitSection'])->name('submit.section');
    Route::post('/complete-test', [DiscController::class, 'completeTest'])->name('complete');
    
    // Debug endpoint (development only)
    Route::get('/debug-session', [DiscController::class, 'debugSession'])->name('debug');
});

// ============================================
// AUTHENTICATION ROUTES
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ============================================
// AUTHENTICATED ROUTES
// ============================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // General Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // ============================================
    // ADMIN ROUTES
    // ============================================
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
    
    // ============================================
    // HR ROUTES
    // ============================================
    Route::middleware('role:admin,hr')->group(function () {
        Route::get('/hr/dashboard', [DashboardController::class, 'hr'])->name('hr.dashboard');
        
        // POSITIONS MANAGEMENT
        Route::prefix('positions')->name('positions.')->group(function () {
            // Basic CRUD routes
            Route::get('/', [PositionController::class, 'index'])->name('index');
            Route::get('/create', [PositionController::class, 'create'])->name('create');
            Route::post('/', [PositionController::class, 'store'])->name('store');
            Route::get('/trashed', [PositionController::class, 'trashed'])->name('trashed');
            Route::get('/{position}', [PositionController::class, 'show'])->name('show');
            Route::get('/{position}/edit', [PositionController::class, 'edit'])->name('edit');
            Route::put('/{position}', [PositionController::class, 'update'])->name('update');
            Route::delete('/{position}', [PositionController::class, 'destroy'])->name('destroy');
            
            // Status management
            Route::post('/{position}/toggle-status', [PositionController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{position}/close', [PositionController::class, 'close'])->name('close');
            Route::post('/{position}/open', [PositionController::class, 'open'])->name('open');
            
            // Transfer candidates before deletion
            Route::post('/{position}/transfer-candidates', [PositionController::class, 'transferCandidates'])->name('transfer-candidates');
            
            // Restore and force delete routes
            Route::post('/{id}/restore', [PositionController::class, 'restore'])->name('restore')
                ->where('id', '[0-9]+');
            Route::delete('/{id}/force-delete', [PositionController::class, 'forceDelete'])->name('force-delete')
                ->where('id', '[0-9]+');
            
            // Statistics and utilities
            Route::get('/{position}/statistics', [PositionController::class, 'statistics'])->name('statistics');
            Route::post('/auto-close-expired', [PositionController::class, 'autoCloseExpired'])->name('auto-close-expired');
        });
        
        // ============================================
        // CANDIDATE MANAGEMENT (CONSOLIDATED & FIXED)
        // ============================================
        Route::prefix('candidates')->name('candidates.')->group(function () {
            // Basic listing routes
            Route::get('/', [CandidateController::class, 'index'])->name('index');
            Route::get('/search', [CandidateController::class, 'search'])->name('search');
            Route::get('/export', [CandidateController::class, 'exportMultiple'])->name('export.multiple');
            Route::post('/bulk-action', [CandidateController::class, 'bulkAction'])->name('bulk-action');
            
            // ✅ TRASHED CANDIDATES ROUTES - Must be BEFORE individual routes
            Route::get('/trashed', [CandidateController::class, 'trashed'])->name('trashed');
            Route::post('/{id}/restore', [CandidateController::class, 'restore'])->name('restore')
                ->where('id', '[0-9]+');
            Route::delete('/{id}/force', [CandidateController::class, 'forceDelete'])->name('force-delete')
                ->where('id', '[0-9]+');
            Route::post('/bulk-force-delete', [CandidateController::class, 'bulkForceDelete'])->name('bulk-force-delete');
            
            // Storage management routes
            Route::get('/storage/stats', [CandidateController::class, 'getStorageStats'])->name('storage-stats');
            Route::post('/storage/cleanup-orphaned', [CandidateController::class, 'cleanupOrphanedFolders'])->name('cleanup-orphaned');
            
            // Individual candidate routes
            Route::get('/{id}', [CandidateController::class, 'show'])->name('show')
                ->where('id', '[0-9]+');
            Route::get('/{id}/edit', [CandidateController::class, 'edit'])->name('edit')
                ->where('id', '[0-9]+');
            Route::put('/{id}', [CandidateController::class, 'update'])->name('update')
                ->where('id', '[0-9]+');
            Route::patch('/{id}/status', [CandidateController::class, 'updateStatus'])->name('update-status')
                ->where('id', '[0-9]+');
            Route::get('/{id}/schedule-interview', [CandidateController::class, 'scheduleInterview'])->name('schedule-interview')
                ->where('id', '[0-9]+');
            Route::post('/{id}/store-interview', [CandidateController::class, 'storeInterview'])->name('store-interview')
                ->where('id', '[0-9]+');
            
            // Preview and export routes
            Route::get('/{id}/preview', [CandidateController::class, 'preview'])->name('preview')
                ->where('id', '[0-9]+');
            Route::get('/{id}/preview/pdf', [CandidateController::class, 'previewPdf'])->name('preview.pdf')
                ->where('id', '[0-9]+');
            Route::get('/{id}/preview/html', [CandidateController::class, 'previewHtml'])->name('preview.html')
                ->where('id', '[0-9]+');
            Route::get('/{id}/export/pdf', [CandidateController::class, 'exportSingle'])->name('export.single.pdf')
                ->where('id', '[0-9]+');
            Route::get('/{id}/export/word', [CandidateController::class, 'exportWord'])->name('export.single.word')
                ->where('id', '[0-9]+');
            Route::post('/export-multiple', [CandidateController::class, 'exportMultiple'])->name('export-multiple');
            
            // Test results for HR
            Route::get('/{id}/kraeplin-result', [CandidateController::class, 'kraeplinResult'])->name('kraeplin.result')
                ->where('id', '[0-9]+');
            Route::get('/{id}/disc3d-result', [CandidateController::class, 'disc3dResult'])->name('disc3d.result')
                ->where('id', '[0-9]+');
            Route::get('/{id}/disc3d-export', [CandidateController::class, 'exportDisc3dResult'])->name('export.disc3d.result')
                ->where('id', '[0-9]+');
            
            // Keep legacy disc result route for backward compatibility
            Route::get('/{id}/disc-result', [CandidateController::class, 'discResult'])->name('disc.result')
                ->where('id', '[0-9]+');
            
            // Soft delete routes
            Route::delete('/{id}', [CandidateController::class, 'destroy'])->name('destroy')
                ->where('id', '[0-9]+');
            Route::post('/bulk-delete', [CandidateController::class, 'bulkDelete'])->name('bulk-delete');
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
    
    // ============================================
    // INTERVIEWER ROUTES
    // ============================================
    Route::middleware('role:admin,hr,interviewer')->group(function () {
        Route::get('/interviewer/dashboard', [DashboardController::class, 'interviewer'])->name('interviewer.dashboard');
        Route::get('/interviewer/schedule', function() {
            return 'Interview Schedule - Coming Soon';
        })->name('interviewer.schedule');
    });
});

// ============================================
// API ROUTES
// ============================================
Route::prefix('api')->middleware(['throttle:10,1'])->group(function () {
    Route::get('/demo-users', [AuthController::class, 'getDemoUsers'])->name('api.demo-users');
    
    // ✅ NEW: Job Application Test Status API
    Route::get('/test-status/{candidateCode}', [JobApplicationController::class, 'getTestStatus'])->name('api.test-status');
    Route::get('/candidate-summary/{candidateCode}', [JobApplicationController::class, 'getCandidateSummary'])->name('api.candidate-summary');
    
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

// API routes untuk AJAX calls (authenticated)
Route::prefix('api')->middleware(['auth'])->group(function () {
    // Storage stats untuk dashboard
    Route::get('/storage-stats', [CandidateController::class, 'getStorageStats'])->name('api.storage-stats');
    
    // Cleanup orphaned folders
    Route::post('/cleanup-orphaned-folders', [CandidateController::class, 'cleanupOrphanedFolders'])->name('api.cleanup-orphaned-folders');
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
            
            Route::get('/debug-session', [DiscController::class, 'debugSession'])->name('debug-session');
            
            // Database status checker
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
                    ]);
                }
            })->name('database.status');
        });
    });

    // ====== DISC 3D DEBUGGING ROUTES (TEMPORARY) ======
    Route::get('/debug-disc-complete/{candidateCode}', function($candidateCode) {
        try {
            $results = [];
            
            // 1. Check candidate
            $candidate = \App\Models\Candidate::where('candidate_code', $candidateCode)->first();
            $results['candidate'] = [
                'exists' => !!$candidate,
                'id' => $candidate?->id,
                'name' => $candidate?->full_name,
                'email' => $candidate?->email
            ];
            
            if (!$candidate) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Candidate not found',
                    'results' => $results
                ]);
            }
            
            // 2. Check Kraeplin completion
            $kraeplinSession = \App\Models\KraeplinTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            $results['kraeplin'] = [
                'completed' => !!$kraeplinSession,
                'session_id' => $kraeplinSession?->id,
                'completed_at' => $kraeplinSession?->completed_at
            ];
            
            // 3. Check existing DISC sessions
            $discSessions = \App\Models\Disc3DTestSession::where('candidate_id', $candidate->id)->get();
            $results['disc_sessions'] = $discSessions->map(function($session) {
                return [
                    'id' => $session->id,
                    'status' => $session->status,
                    'created_at' => $session->created_at,
                    'started_at' => $session->started_at,
                    'completed_at' => $session->completed_at
                ];
            });
            
            // 4. Check database structure
            $results['database'] = [
                'sections_count' => \App\Models\Disc3DSection::count(),
                'choices_count' => \App\Models\Disc3DSectionChoice::count(),
                'active_sections' => \App\Models\Disc3DSection::where('is_active', true)->count(),
                'sections_with_choices' => \App\Models\Disc3DSection::whereHas('choices', function($query) {
                    $query->where('is_active', true);
                })->count()
            ];
            
            // 5. Test sections loading
            try {
                $testSections = \App\Models\Disc3DSection::with(['choices' => function($query) {
                    $query->where('is_active', true)
                          ->orderBy('choice_dimension')
                          ->select('*');
                }])
                ->where('is_active', true)
                ->orderBy('order_number')
                ->get();
                
                $results['sections_test'] = [
                    'loadable' => true,
                    'count' => $testSections->count(),
                    'sample_section' => $testSections->first() ? [
                        'id' => $testSections->first()->id,
                        'order_number' => $testSections->first()->order_number,
                        'choices_count' => $testSections->first()->choices->count(),
                        'dimensions' => $testSections->first()->choices->pluck('choice_dimension')->sort()->values()->toArray()
                    ] : null
                ];
                
            } catch (\Exception $e) {
                $results['sections_test'] = [
                    'loadable' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            // 6. Check DiscTestService
            try {
                $serviceClass = '\App\Services\DiscTestService';
                $serviceExists = class_exists($serviceClass);
                
                if ($serviceExists) {
                    $service = app($serviceClass);
                    $serviceMethods = get_class_methods($service);
                    
                    $results['service'] = [
                        'exists' => true,
                        'class' => $serviceClass,
                        'methods' => $serviceMethods,
                        'has_create_session' => in_array('createTestSession', $serviceMethods),
                        'has_process_bulk' => in_array('processBulkResponses', $serviceMethods),
                        'has_complete_test' => in_array('completeTestSession', $serviceMethods)
                    ];
                } else {
                    $results['service'] = [
                        'exists' => false,
                        'error' => 'DiscTestService class not found'
                    ];
                }
                
            } catch (\Exception $e) {
                $results['service'] = [
                    'exists' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            // 7. Check routes
            $results['routes'] = [
                'instructions' => route('disc3d.instructions', $candidateCode),
                'start' => route('disc3d.start', $candidateCode),
                'submit' => route('disc3d.submit.test'),
                'routes_exist' => true
            ];
            
            // 8. Test controller method existence
            try {
                $controller = new \App\Http\Controllers\DiscController(app('\App\Services\DiscTestService'));
                $controllerMethods = get_class_methods($controller);
                
                $results['controller'] = [
                    'exists' => true,
                    'methods' => $controllerMethods,
                    'has_show_instructions' => in_array('showInstructions', $controllerMethods),
                    'has_start_test' => in_array('startTest', $controllerMethods),
                    'has_submit_test' => in_array('submitTest', $controllerMethods)
                ];
                
            } catch (\Exception $e) {
                $results['controller'] = [
                    'exists' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            // 9. Test request validation
            try {
                $requestClass = '\App\Http\Requests\DiscTestStartRequest';
                $requestExists = class_exists($requestClass);
                
                if ($requestExists) {
                    $request = new $requestClass();
                    $rules = $request->rules();
                    
                    $results['request_validation'] = [
                        'exists' => true,
                        'class' => $requestClass,
                        'rules' => $rules,
                        'has_candidate_code_rule' => array_key_exists('candidate_code', $rules)
                    ];
                } else {
                    $results['request_validation'] = [
                        'exists' => false,
                        'error' => 'DiscTestStartRequest class not found'
                    ];
                }
                
            } catch (\Exception $e) {
                $results['request_validation'] = [
                    'exists' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            // 10. Final recommendations
            $recommendations = [];
            
            if (!$results['candidate']['exists']) {
                $recommendations[] = '❌ Candidate not found - check candidate_code';
            }
            
            if (!$results['kraeplin']['completed']) {
                $recommendations[] = '⚠️ Kraeplin test not completed';
            }
            
            if ($results['database']['sections_count'] < 24) {
                $recommendations[] = '❌ Need more sections in database (current: ' . $results['database']['sections_count'] . ', need: 24)';
            }
            
            if ($results['database']['choices_count'] < 96) {
                $recommendations[] = '❌ Need more choices in database (current: ' . $results['database']['choices_count'] . ', need: 96+)';
            }
            
            if (!$results['service']['exists']) {
                $recommendations[] = '❌ DiscTestService not found - create the service class';
            }
            
            if (!$results['sections_test']['loadable']) {
                $recommendations[] = '❌ Cannot load sections from database - check relationships';
            }
            
            if (isset($results['request_validation']['has_candidate_code_rule']) && $results['request_validation']['has_candidate_code_rule']) {
                $recommendations[] = '⚠️ DiscTestStartRequest has candidate_code validation - should be removed';
            }
            
            if (empty($recommendations)) {
                $recommendations[] = '✅ All checks passed - system should be ready';
            }
            
            return response()->json([
                'status' => 'success',
                'candidate_code' => $candidateCode,
                'timestamp' => now(),
                'results' => $results,
                'recommendations' => $recommendations,
                'next_steps' => [
                    '1. Fix any issues mentioned in recommendations',
                    '2. Test form submission with browser dev tools',
                    '3. Check Laravel logs during form submission',
                    '4. Use /debug-form-submit route to test POST data'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
        
    })->name('debug.disc.complete');

    // Test form submission route
    Route::post('/debug-form-submit/{candidateCode}', function(\Illuminate\Http\Request $request, $candidateCode) {
        \Log::info('🧪 DEBUG FORM SUBMISSION', [
            'candidate_code' => $candidateCode,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'all_data' => $request->all(),
            'headers' => $request->headers->all(),
            'session_data' => session()->all(),
            'csrf_token' => csrf_token(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'route_name' => \Route::currentRouteName(),
            'timestamp' => now()
        ]);
        
        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'candidate_code' => 'required|string',
                'has_csrf_token' => $request->hasHeader('X-CSRF-TOKEN') || $request->has('_token')
            ]);
            
            return response()->json([
                'status' => 'validation_passed',
                'message' => 'Form validation successful',
                'validated_data' => $validatedData,
                'candidate_code' => $candidateCode,
                'recommendations' => [
                    'Form data is valid',
                    'Try calling actual startTest method',
                    'Check DiscTestService availability'
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'validation_failed',
                'message' => 'Form validation failed',
                'errors' => $e->errors(),
                'candidate_code' => $candidateCode
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
        
    })->name('debug.form.submit');
}

Route::get('/healthz', function () {
    return response()->json(['status' => 'ok']);
});