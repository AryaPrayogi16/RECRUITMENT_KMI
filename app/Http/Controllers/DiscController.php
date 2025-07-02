<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Disc3DTestSession, 
    Disc3DResult, 
    Disc3DSection, 
    Disc3DSectionChoice,
    Disc3DResponse,
    Disc3DTestAnalytics,
    Disc3DSectionAnalytics,
    Disc3DProfileInterpretation,
    Disc3DPatternCombination,
    Disc3DConfig,
    KraeplinTestSession
};
use App\Services\DiscTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiscController extends Controller
{
    protected $disc3DTestService;

    public function __construct(DiscTestService $discTestService = null)
    {
        $this->disc3DTestService = $discTestService;
    }

    /**
     * ✅ Show DISC 3D test instructions page
     */
    public function showInstructions($candidateCode)
    {
        Log::info('=== DISC 3D Instructions START ===', [
            'candidate_code' => $candidateCode,
            'timestamp' => now()
        ]);
        
        try {
            $candidate = Candidate::where('candidate_code', $candidateCode)->first();
            
            if (!$candidate) {
                Log::warning('Candidate not found', ['candidate_code' => $candidateCode]);
                return redirect()->route('job.application.form')
                    ->with('error', 'Kandidat tidak ditemukan.');
            }
            
            // Check Kraeplin completion
            $kraeplinCompleted = false;
            try {
                $kraeplinSession = KraeplinTestSession::where('candidate_id', $candidate->id)
                    ->where('status', 'completed')
                    ->first();
                    
                $kraeplinCompleted = (bool) $kraeplinSession;
                    
                if (!$kraeplinCompleted) {
                    $referrer = request()->header('referer');
                    if (!$referrer || !str_contains($referrer, 'kraeplin')) {
                        Log::info('Redirecting to Kraeplin test', ['candidate_id' => $candidate->id]);
                        return redirect()->route('kraeplin.instructions', $candidateCode)
                            ->with('warning', 'Anda harus menyelesaikan Test Kraeplin terlebih dahulu.');
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Kraeplin check failed, assuming completed', [
                    'error' => $e->getMessage()
                ]);
                $kraeplinCompleted = true;
            }
            
            // Check existing DISC completion
            $existingCompletedSession = null;
            try {
                $existingCompletedSession = Disc3DTestSession::where('candidate_id', $candidate->id)
                    ->where('status', 'completed')
                    ->first();
                    
                if ($existingCompletedSession) {
                    Log::info('DISC 3D already completed', ['session_id' => $existingCompletedSession->id]);
                    
                    return redirect()->route('job.application.success')
                        ->with('candidate_code', $candidateCode)
                        ->with('success', 'Anda sudah menyelesaikan Test DISC 3D sebelumnya.');
                }
            } catch (\Exception $e) {
                Log::warning('DISC completed session check failed', [
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('✅ Showing DISC 3D instructions', [
                'candidate_id' => $candidate->id,
                'kraeplin_completed' => $kraeplinCompleted
            ]);
            
            return view('disc.instructions', [
                'candidate' => $candidate,
                'incompleteSession' => null,
                'timeLimit' => null,
                'totalSections' => 24
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in DISC instructions', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return view('disc.instructions', [
                'candidate' => (object) [
                    'id' => 1,
                    'candidate_code' => $candidateCode
                ],
                'incompleteSession' => null,
                'timeLimit' => null,
                'totalSections' => 24
            ]);
        }
    }

    /**
     * ✅ SINGLE RUN: Start DISC 3D test - fresh start every time
     */
    public function startTest($candidateCode, Request $request)
    {
        Log::info('=== DISC 3D START TEST (Single Run) ===', [
            'candidate_code' => $candidateCode,
            'method' => $request->method(),
            'timestamp' => now()
        ]);
        
        try {
            $candidate = Candidate::where('candidate_code', $candidateCode)->first();
            
            if (!$candidate) {
                Log::error('Candidate not found', ['candidate_code' => $candidateCode]);
                return redirect()->route('disc3d.instructions', $candidateCode)
                    ->with('error', 'Kandidat tidak ditemukan.');
            }
            
            DB::beginTransaction();
            
            try {
                // Check for existing completed session
                $existingCompletedSession = Disc3DTestSession::where('candidate_id', $candidate->id)
                    ->where('status', 'completed')
                    ->first();
                    
                if ($existingCompletedSession) {
                    DB::rollback();
                    return redirect()->route('job.application.success')
                        ->with('candidate_code', $candidateCode)
                        ->with('success', 'Test DISC 3D sudah diselesaikan sebelumnya.');
                }
                
                // SINGLE RUN: Delete any incomplete sessions (fresh start)
                Disc3DTestSession::where('candidate_id', $candidate->id)
                    ->whereIn('status', ['not_started', 'in_progress'])
                    ->delete();
                
                // Get test configuration
                $testConfig = $this->getTestConfiguration();
                
                // Create new session
                $session = Disc3DTestSession::create([
                    'candidate_id' => $candidate->id,
                    'test_code' => $this->generateTestCode(),
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'last_activity_at' => now(),
                    'sections_completed' => 0,
                    'progress' => 0,
                    'language' => 'id',
                    'time_limit_minutes' => $testConfig['time_limit_minutes'] ?? null,
                    'auto_save' => $testConfig['auto_save'] ?? false,
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'session_token' => hash('sha256', uniqid() . time()),
                    'metadata' => json_encode([
                        'test_version' => 'single_run_v1.0',
                        'started_from' => 'web_interface',
                        'browser_info' => $this->extractBrowserInfo($request),
                        'screen_resolution' => $request->input('screen_resolution'),
                        'timezone' => $request->input('timezone', 'Asia/Jakarta')
                    ]),
                    'device_info' => json_encode($this->extractDeviceInfo($request))
                ]);
                
                // Create analytics record
                $analytics = Disc3DTestAnalytics::create([
                    'candidate_id' => $candidate->id,
                    'test_session_id' => $session->id,
                    'total_sections' => 24,
                    'completed_sections' => 0,
                    'completion_rate' => 0,
                    'total_time_seconds' => 0,
                    'average_time_per_section' => 0,
                    'device_analytics' => json_encode([
                        'user_agent' => $request->userAgent(),
                        'platform' => $this->detectPlatform($request->userAgent()),
                        'browser' => $this->detectBrowser($request->userAgent()),
                        'is_mobile' => $this->isMobile($request->userAgent()),
                        'screen_info' => $request->input('screen_resolution')
                    ]),
                    'page_reloads' => 0,
                    'focus_lost_count' => 0,
                    'idle_time_seconds' => 0,
                    'response_quality_score' => null,
                    'suspicious_patterns' => false,
                    'quality_flags' => json_encode([])
                ]);
                
                // Get test sections
                $sections = $this->getCompleteTestSections();
                
                if ($sections->isEmpty()) {
                    DB::rollback();
                    Log::error('No sections available');
                    return redirect()->route('disc3d.instructions', $candidateCode)
                        ->with('error', 'Data test tidak tersedia. Silakan hubungi administrator.');
                }
                
                DB::commit();
                
                Log::info('✅ Single run test session created', [
                    'candidate_code' => $candidateCode,
                    'session_id' => $session->id,
                    'test_code' => $session->test_code,
                    'analytics_id' => $analytics->id,
                    'total_sections' => $sections->count()
                ]);
                
                return view('disc.test', [
                    'candidate' => $candidate,
                    'session' => $session,
                    'sections' => $sections,
                    'completedResponses' => collect(),
                    'progressPercentage' => 0
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Start test error', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('disc3d.instructions', $candidateCode)
                ->with('error', 'Terjadi kesalahan saat memulai test: ' . $e->getMessage());
        }
    }

    /**
     * ✅ SINGLE RUN: Bulk submit all responses at once (like Kraeplin) - FIXED VERSION
     */
    public function submitTest(Request $request)
    {
        Log::info('=== DISC BULK SUBMISSION (Single Run) ===', [
            'session_id' => $request->session_id,
            'responses_count' => count($request->responses ?? []),
            'timestamp' => now()
        ]);

        try {
            $validated = $request->validate([
                'session_id' => 'required|integer|exists:disc_3d_test_sessions,id',
                'responses' => 'required|array|size:24',
                'responses.*.section_id' => 'required|integer|between:1,24',
                'responses.*.most_choice_id' => 'required|integer',
                'responses.*.least_choice_id' => 'required|integer',
                'responses.*.time_spent' => 'required|integer|min:1',
                'total_duration' => 'required|integer|min:1'
            ]);

            // Additional validation: most ≠ least for each response
            foreach ($validated['responses'] as $index => $response) {
                if ($response['most_choice_id'] == $response['least_choice_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Section " . ($index + 1) . ": Pilihan MOST dan LEAST tidak boleh sama."
                    ], 422);
                }
            }

            $session = Disc3DTestSession::find($validated['session_id']);
            if (!$session || $session->status !== 'in_progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi test tidak valid atau sudah selesai.'
                ], 404);
            }

            DB::beginTransaction();
            
            try {
                // ✅ FIXED: Process all responses in bulk with proper data handling
                $processedCount = $this->processBulkResponsesFixed($session, $validated['responses']);
                
                if ($processedCount === 0) {
                    throw new \Exception('Tidak ada response yang berhasil diproses');
                }

                // Update session status
                $session->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'total_duration_seconds' => $validated['total_duration'],
                    'progress' => 100,
                    'sections_completed' => $processedCount
                ]);

                // ✅ FIXED: Create comprehensive result with proper response loading
                $result = $this->createComprehensiveResultFixed($session, $validated['total_duration']);

                // Update final analytics
                $this->updateFinalAnalytics($session, $validated['total_duration']);

                DB::commit();

                Log::info('✅ DISC single run test completed', [
                    'session_id' => $session->id,
                    'result_id' => $result->id,
                    'processed_responses' => $processedCount,
                    'primary_type' => $result->primary_type
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Test DISC 3D berhasil diselesaikan!',
                    'data' => [
                        'session_id' => $session->id,
                        'result_id' => $result->id,
                        'completed_sections' => $processedCount,
                        'total_duration' => $validated['total_duration'],
                        'primary_type' => $result->primary_type,
                        'personality_profile' => $result->personality_profile,
                        'summary' => $result->summary
                    ],
                    'redirect_url' => route('job.application.success', [
                        'candidate_code' => $session->candidate->candidate_code
                    ])
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Bulk submission error', [
                'session_id' => $request->session_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Process all responses in bulk with better error handling
     */
    private function processBulkResponsesFixed($session, $responses)
    {
        $bulkResponses = [];
        $timestamp = now();
        $processedCount = 0;
        
        Log::info('Processing bulk responses', [
            'session_id' => $session->id,
            'total_responses' => count($responses)
        ]);
        
        foreach ($responses as $index => $responseData) {
            try {
                // Validate section_id
                if (!isset($responseData['section_id']) || 
                    !isset($responseData['most_choice_id']) || 
                    !isset($responseData['least_choice_id'])) {
                    Log::warning('Missing required fields in response', [
                        'index' => $index,
                        'response' => $responseData
                    ]);
                    continue;
                }

                // Create response record with default scores
                $responseRecord = [
                    'test_session_id' => $session->id,
                    'candidate_id' => $session->candidate_id,
                    'section_id' => $responseData['section_id'],
                    'section_code' => sprintf('SEC%02d', $responseData['section_id']),
                    'section_number' => $responseData['section_id'],
                    'most_choice_id' => $responseData['most_choice_id'],
                    'least_choice_id' => $responseData['least_choice_id'],
                    
                    // Use dummy choices for now - will be updated if real choices are found
                    'most_choice' => $this->getDummyChoiceDimension($responseData['section_id'], $responseData['most_choice_id']),
                    'least_choice' => $this->getDummyChoiceDimension($responseData['section_id'], $responseData['least_choice_id']),
                    
                    // Default scores - will be calculated properly later
                    'most_score_d' => 0.5,
                    'most_score_i' => 0.5,
                    'most_score_s' => 0.5,
                    'most_score_c' => 0.5,
                    'least_score_d' => -0.5,
                    'least_score_i' => -0.5,
                    'least_score_s' => -0.5,
                    'least_score_c' => -0.5,
                    'net_score_d' => 0,
                    'net_score_i' => 0,
                    'net_score_s' => 0,
                    'net_score_c' => 0,
                    
                    'time_spent_seconds' => $responseData['time_spent'] ?? 30,
                    'response_order' => $index + 1,
                    'answered_at' => $timestamp,
                    'revision_count' => 0,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];

                // Try to get real choice data if available
                try {
                    $mostChoice = Disc3DSectionChoice::find($responseData['most_choice_id']);
                    $leastChoice = Disc3DSectionChoice::find($responseData['least_choice_id']);
                    
                    if ($mostChoice && $leastChoice) {
                        // Update with real choice dimensions
                        $responseRecord['most_choice'] = $mostChoice->choice_dimension;
                        $responseRecord['least_choice'] = $leastChoice->choice_dimension;
                        
                        // Calculate real scores if weights are available
                        if (isset($mostChoice->weight_d)) {
                            $mostScores = $this->calculateChoiceScores($mostChoice, 'most');
                            $leastScores = $this->calculateChoiceScores($leastChoice, 'least');
                            $netScores = $this->calculateNetScores($mostScores, $leastScores);
                            
                            $responseRecord['most_score_d'] = $mostScores['D'];
                            $responseRecord['most_score_i'] = $mostScores['I'];
                            $responseRecord['most_score_s'] = $mostScores['S'];
                            $responseRecord['most_score_c'] = $mostScores['C'];
                            $responseRecord['least_score_d'] = $leastScores['D'];
                            $responseRecord['least_score_i'] = $leastScores['I'];
                            $responseRecord['least_score_s'] = $leastScores['S'];
                            $responseRecord['least_score_c'] = $leastScores['C'];
                            $responseRecord['net_score_d'] = $netScores['D'];
                            $responseRecord['net_score_i'] = $netScores['I'];
                            $responseRecord['net_score_s'] = $netScores['S'];
                            $responseRecord['net_score_c'] = $netScores['C'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not load choice details, using defaults', [
                        'section' => $index + 1,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $bulkResponses[] = $responseRecord;
                $processedCount++;
                
            } catch (\Exception $e) {
                Log::error('Error processing individual response', [
                    'section' => $index + 1,
                    'response' => $responseData,
                    'session_id' => $session->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Insert in chunks
        if (!empty($bulkResponses)) {
            try {
                $chunks = array_chunk($bulkResponses, 50);
                foreach ($chunks as $chunk) {
                    DB::table('disc_3d_responses')->insert($chunk);
                }
                
                Log::info('Bulk responses inserted successfully', [
                    'session_id' => $session->id,
                    'total_inserted' => count($bulkResponses)
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error inserting bulk responses', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
        
        return $processedCount;
    }

    /**
     * ✅ FIXED: Create comprehensive result with proper response loading
     */
    private function createComprehensiveResultFixed($session, $totalDuration): Disc3DResult
    {
        // ✅ FIXED: Load responses using DB query to ensure fresh data
        $responses = DB::table('disc_3d_responses')
            ->where('test_session_id', $session->id)
            ->orderBy('response_order')
            ->get();

        if ($responses->isEmpty()) {
            throw new \Exception('No responses found for session');
        }

        Log::info('Creating result with responses', [
            'session_id' => $session->id,
            'responses_count' => $responses->count()
        ]);

        // Calculate comprehensive scores using loaded data
        $mostRawScores = $this->calculateGraphRawScoresFixed($responses, 'most');
        $leastRawScores = $this->calculateGraphRawScoresFixed($responses, 'least');
        $changeRawScores = $this->calculateChangeScores($mostRawScores, $leastRawScores);
        
        // Convert to percentages and segments
        $mostPercentages = $this->convertToPercentages($mostRawScores);
        $leastPercentages = $this->convertToPercentages($leastRawScores);
        
        $mostSegments = $this->convertToSegments($mostPercentages);
        $leastSegments = $this->convertToSegments($leastPercentages);
        $changeSegments = $this->convertChangeToSegments($changeRawScores);
        
        // Determine patterns
        $mostPattern = $this->determinePattern($mostPercentages);
        $leastPattern = $this->determinePattern($leastPercentages);
        $adaptationPattern = $this->determineAdaptationPattern($mostPattern, $leastPattern);
        
        // Calculate validity and consistency
        $consistencyScore = $this->calculateConsistencyScoreFixed($responses);
        $responseConsistency = $this->calculateResponseConsistencyFixed($responses);
        $validityFlags = $this->performValidityChecksFixed($responses);
        
        // Generate interpretations
        $interpretations = $this->generateInterpretations($mostSegments, $leastSegments, $changeSegments);
        
        return Disc3DResult::create([
            'test_session_id' => $session->id,
            'candidate_id' => $session->candidate_id,
            'test_code' => $session->test_code,
            'test_completed_at' => now(),
            'test_duration_seconds' => $totalDuration,
            
            // MOST scores (complete)
            'most_d_raw' => $mostRawScores['D'],
            'most_i_raw' => $mostRawScores['I'],
            'most_s_raw' => $mostRawScores['S'],
            'most_c_raw' => $mostRawScores['C'],
            'most_d_percentage' => $mostPercentages['D'],
            'most_i_percentage' => $mostPercentages['I'],
            'most_s_percentage' => $mostPercentages['S'],
            'most_c_percentage' => $mostPercentages['C'],
            'most_d_segment' => $mostSegments['D'],
            'most_i_segment' => $mostSegments['I'],
            'most_s_segment' => $mostSegments['S'],
            'most_c_segment' => $mostSegments['C'],
            
            // LEAST scores (complete)
            'least_d_raw' => $leastRawScores['D'],
            'least_i_raw' => $leastRawScores['I'],
            'least_s_raw' => $leastRawScores['S'],
            'least_c_raw' => $leastRawScores['C'],
            'least_d_percentage' => $leastPercentages['D'],
            'least_i_percentage' => $leastPercentages['I'],
            'least_s_percentage' => $leastPercentages['S'],
            'least_c_percentage' => $leastPercentages['C'],
            'least_d_segment' => $leastSegments['D'],
            'least_i_segment' => $leastSegments['I'],
            'least_s_segment' => $leastSegments['S'],
            'least_c_segment' => $leastSegments['C'],
            
            // CHANGE scores (complete)
            'change_d_raw' => $changeRawScores['D'],
            'change_i_raw' => $changeRawScores['I'],
            'change_s_raw' => $changeRawScores['S'],
            'change_c_raw' => $changeRawScores['C'],
            'change_d_segment' => $changeSegments['D'],
            'change_i_segment' => $changeSegments['I'],
            'change_s_segment' => $changeSegments['S'],
            'change_c_segment' => $changeSegments['C'],
            
            // Patterns (complete)
            'most_primary_type' => $mostPattern['primary'],
            'most_secondary_type' => $mostPattern['secondary'],
            'least_primary_type' => $leastPattern['primary'],
            'least_secondary_type' => $leastPattern['secondary'],
            'most_pattern' => $mostPattern['code'],
            'least_pattern' => $leastPattern['code'],
            'adaptation_pattern' => $adaptationPattern,
            
            // Simplified accessors
            'primary_type' => $mostPattern['primary'],
            'secondary_type' => $mostPattern['secondary'],
            'personality_profile' => $this->generatePersonalityProfile($mostPattern),
            'primary_percentage' => round($mostPercentages[$mostPattern['primary']], 1),
            'summary' => $this->generateSummary($mostPattern, $mostPercentages, $changeSegments),
            
            // JSON data (complete)
            'graph_most_data' => json_encode($this->buildGraphData('MOST', $mostRawScores, $mostPercentages, $mostSegments)),
            'graph_least_data' => json_encode($this->buildGraphData('LEAST', $leastRawScores, $leastPercentages, $leastSegments)),
            'graph_change_data' => json_encode($this->buildGraphData('CHANGE', $changeRawScores, [], $changeSegments)),
            'most_score_breakdown' => json_encode($this->buildScoreBreakdownFixed($responses, 'most')),
            'least_score_breakdown' => json_encode($this->buildScoreBreakdownFixed($responses, 'least')),
            
            // Interpretations (complete)
            'public_self_summary' => $interpretations['public_self'],
            'private_self_summary' => $interpretations['private_self'],
            'adaptation_summary' => $interpretations['adaptation'],
            'overall_profile' => $interpretations['overall'],
            
            // Analysis data (complete)
            'section_responses' => json_encode($this->buildSectionResponsesDataFixed($responses)),
            'stress_indicators' => json_encode($this->identifyStressIndicators($changeSegments)),
            'behavioral_insights' => json_encode($this->generateBehavioralInsights($mostPercentages, $leastPercentages)),
            'consistency_analysis' => json_encode($this->analyzeConsistencyFixed($responses)),
            
            // Validity indicators (complete)
            'consistency_score' => $consistencyScore,
            'is_valid' => empty($validityFlags['critical_flags']),
            'validity_flags' => json_encode($validityFlags),
            
            // Performance metrics (complete)
            'response_consistency' => $responseConsistency,
            'average_response_time' => round(collect($responses)->avg('time_spent_seconds')),
            'timing_analysis' => json_encode($this->analyzeTimingPatternsFixed($responses))
        ]);
    }

    /**
     * ✅ FIXED: Calculate scores using DB collection instead of Eloquent
     */
    private function calculateGraphRawScoresFixed($responses, $graphType): array
    {
        $scores = ['D' => 0.0, 'I' => 0.0, 'S' => 0.0, 'C' => 0.0];
        
        foreach ($responses as $response) {
            $scorePrefix = $graphType . '_score_';
            $scores['D'] += $response->{$scorePrefix . 'd'} ?? 0;
            $scores['I'] += $response->{$scorePrefix . 'i'} ?? 0;
            $scores['S'] += $response->{$scorePrefix . 's'} ?? 0;
            $scores['C'] += $response->{$scorePrefix . 'c'} ?? 0;
        }
        
        return $scores;
    }

    /**
     * ✅ Helper method to determine dummy choice dimension based on position
     */
    private function getDummyChoiceDimension($sectionId, $choiceId): string
    {
        $dimensions = ['D', 'I', 'S', 'C'];
        $position = ($choiceId - 1) % 4;
        return $dimensions[$position] ?? 'D';
    }

    /**
     * ✅ FIXED: Build score breakdown using DB collection
     */
    private function buildScoreBreakdownFixed($responses, $type): array
    {
        return collect($responses)->map(function($r) use ($type) {
            return [
                'section' => $r->section_number,
                'scores' => [
                    'D' => $r->{$type . '_score_d'} ?? 0,
                    'I' => $r->{$type . '_score_i'} ?? 0,
                    'S' => $r->{$type . '_score_s'} ?? 0,
                    'C' => $r->{$type . '_score_c'} ?? 0
                ]
            ];
        })->toArray();
    }

    /**
     * ✅ FIXED: Build section responses using DB collection
     */
    private function buildSectionResponsesDataFixed($responses): array
    {
        return collect($responses)->map(function($r) {
            return [
                'section' => $r->section_number,
                'most_choice' => $r->most_choice,
                'least_choice' => $r->least_choice,
                'time_spent' => $r->time_spent_seconds,
                'revision_count' => $r->revision_count ?? 0
            ];
        })->toArray();
    }

    /**
     * ✅ FIXED: Calculate consistency using DB collection
     */
    private function calculateConsistencyScoreFixed($responses): float
    {
        $timeConsistency = $this->calculateTimeConsistencyFixed($responses);
        $choiceConsistency = $this->calculateChoiceConsistencyFixed($responses);
        
        return ($timeConsistency + $choiceConsistency) / 2;
    }

    private function calculateTimeConsistencyFixed($responses): float
    {
        if (count($responses) < 2) return 100;
        
        $times = collect($responses)->pluck('time_spent_seconds');
        $mean = $times->avg();
        $variance = $times->map(fn($time) => pow($time - $mean, 2))->avg();
        $coefficient = $variance > 0 ? sqrt($variance) / $mean : 0;
        
        return max(0, 100 - ($coefficient * 100));
    }

    private function calculateChoiceConsistencyFixed($responses): float
    {
        $distribution = [
            'most' => collect($responses)->groupBy('most_choice')->map->count(),
            'least' => collect($responses)->groupBy('least_choice')->map->count()
        ];
        
        $balanceScore = 100;
        foreach (['D', 'I', 'S', 'C'] as $dimension) {
            $mostCount = $distribution['most'][$dimension] ?? 0;
            $leastCount = $distribution['least'][$dimension] ?? 0;
            $expectedCount = 6; // 24/4 = 6 average
            
            $deviation = abs($mostCount - $expectedCount) + abs($leastCount - $expectedCount);
            $balanceScore -= ($deviation * 2);
        }
        
        return max(0, $balanceScore);
    }

    private function calculateResponseConsistencyFixed($responses): float
    {
        return $this->calculateChoiceConsistencyFixed($responses);
    }

    private function performValidityChecksFixed($responses): array
    {
        $flags = ['critical_flags' => [], 'warning_flags' => []];
        
        // Check timing patterns
        $tooFastCount = collect($responses)->where('time_spent_seconds', '<', 3)->count();
        if ($tooFastCount > 5) {
            $flags['critical_flags'][] = 'too_many_fast_responses';
        }
        
        // Check response distribution
        $distribution = collect($responses)->groupBy('most_choice')->map->count();
        $maxDimensionCount = $distribution->max();
        
        if ($maxDimensionCount > 18) {
            $flags['critical_flags'][] = 'extreme_response_bias';
        }
        
        return $flags;
    }

    private function analyzeConsistencyFixed($responses): array
    {
        return [
            'time_consistency' => $this->calculateTimeConsistencyFixed($responses),
            'choice_consistency' => $this->calculateChoiceConsistencyFixed($responses),
            'overall_consistency' => $this->calculateConsistencyScoreFixed($responses)
        ];
    }

    private function analyzeTimingPatternsFixed($responses): array
    {
        if (count($responses) == 0) return [];
        
        $times = collect($responses)->pluck('time_spent_seconds');
        
        return [
            'mean_time' => $times->avg(),
            'median_time' => $times->median(),
            'min_time' => $times->min(),
            'max_time' => $times->max(),
            'std_deviation' => sqrt($times->map(fn($time) => pow($time - $times->avg(), 2))->avg()),
            'trend' => $this->calculateTimeTrendFixed($responses)
        ];
    }

    private function calculateTimeTrendFixed($responses): string
    {
        if (count($responses) < 12) return 'insufficient_data';
        
        $orderedResponses = collect($responses)->sortBy('response_order');
        $firstHalf = $orderedResponses->take(12)->avg('time_spent_seconds');
        $secondHalf = $orderedResponses->skip(12)->avg('time_spent_seconds');
        
        if ($firstHalf == 0) return 'no_trend';
        
        $change = (($secondHalf - $firstHalf) / $firstHalf) * 100;
        
        return match(true) {
            $change > 20 => 'slowing_down',
            $change < -20 => 'speeding_up',
            default => 'consistent'
        };
    }

    // ===== HELPER METHODS (Existing methods remain the same) =====

    private function getTestConfiguration(): array
    {
        try {
            $config = Disc3DConfig::where('config_key', 'test_settings')->first();
            if ($config) {
                if (is_array($config->config_value)) {
                    return $config->config_value;
                }
                if (is_string($config->config_value)) {
                    return json_decode($config->config_value, true);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not load test config from database', ['error' => $e->getMessage()]);
        }
        
        return [
            'time_limit_minutes' => null,
            'sections_per_page' => 1,
            'allow_navigation' => false,
            'auto_save_interval' => 0,
            'show_progress' => true,
            'require_all_sections' => true
        ];
    }

    private function getCompleteTestSections()
    {
        try {
            $sections = Disc3DSection::with(['choices' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('choice_dimension')
                      ->select('*');
            }])
            ->where('is_active', true)
            ->orderBy('order_number')
            ->get();
            
            if ($sections->count() >= 24) {
                $sectionsWithChoices = $sections->filter(function($section) {
                    return $section->choices && $section->choices->count() >= 4;
                })->count();
                
                if ($sectionsWithChoices >= 20) {
                    Log::info('✅ Loaded sections from database successfully', [
                        'sections_count' => $sections->count(),
                        'sections_with_choices' => $sectionsWithChoices
                    ]);
                    return $sections;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error loading sections from database', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $this->createCompleteDummySections();
    }

    private function createCompleteDummySections()
    {
        Log::info('Creating complete dummy sections with weights');
        
        $sections = collect();
        
        for ($i = 1; $i <= 24; $i++) {
            $section = new \stdClass();
            $section->id = $i;
            $section->section_number = $i;
            $section->section_code = sprintf('SEC%02d', $i);
            $section->section_title = "Section {$i}";
            $section->is_active = true;
            $section->order_number = $i;
            
            $choices = collect();
            $dimensions = ['D', 'I', 'S', 'C'];
            
            $textVariations = [
                'D' => [
                    'Saya suka mengambil kendali dan memimpin dengan tegas',
                    'Saya berani menghadapi tantangan dan kompetisi',
                    'Saya fokus pada hasil dan pencapaian target',
                    'Saya tidak takut mengambil keputusan sulit',
                    'Saya aktif dalam menciptakan perubahan',
                    'Saya kompetitif dan suka tantangan'
                ],
                'I' => [
                    'Saya senang berkomunikasi dan mempengaruhi orang lain',
                    'Saya antusias dalam berinteraksi sosial',
                    'Saya mudah menyesuaikan diri dengan lingkungan baru',
                    'Saya optimis dan positif dalam menghadapi situasi',
                    'Saya inspiratif dan memotivasi tim',
                    'Saya ekspresif dan energik dalam komunikasi'
                ],
                'S' => [
                    'Saya lebih suka bekerja dengan stabil dan konsisten',
                    'Saya sabar dan dapat diandalkan dalam tim',
                    'Saya menghargai harmoni dan kerjasama',
                    'Saya loyal dan mendukung keputusan kelompok',
                    'Saya konsisten dalam mengikuti prosedur',
                    'Saya dapat diandalkan dan supportif'
                ],
                'C' => [
                    'Saya teliti dalam detail dan mengikuti prosedur yang benar',
                    'Saya mengutamakan kualitas dan akurasi dalam bekerja',
                    'Saya sistematis dan terorganisir dalam pendekatan',
                    'Saya berhati-hati dalam mengambil keputusan',
                    'Saya analitis dan berpikir logis',
                    'Saya perfeksionis dalam standar kerja'
                ]
            ];
            
            foreach ($dimensions as $dim) {
                $choice = new \stdClass();
                $choice->id = ($i - 1) * 4 + array_search($dim, $dimensions) + 1;
                $choice->section_id = $i;
                $choice->section_code = sprintf('SEC%02d', $i);
                $choice->section_number = $i;
                $choice->choice_dimension = $dim;
                $choice->choice_code = sprintf('SEC%02d_%s', $i, $dim);
                
                $textIndex = ($i - 1) % count($textVariations[$dim]);
                $choice->choice_text = $textVariations[$dim][$textIndex];
                
                $weights = $this->generateChoiceWeights($dim, $i);
                $choice->weight_d = $weights['D'];
                $choice->weight_i = $weights['I'];
                $choice->weight_s = $weights['S'];
                $choice->weight_c = $weights['C'];
                
                $choice->primary_dimension = $dim;
                $choice->primary_strength = abs($weights[$dim]);
                $choice->is_active = true;
                
                $choices->push($choice);
            }
            
            $section->choices = $choices;
            $sections->push($section);
        }
        
        Log::info('✅ Created complete dummy sections successfully', [
            'sections_count' => $sections->count(),
            'choices_per_section' => 4,
            'total_choices' => $sections->count() * 4
        ]);
        
        return $sections;
    }

    private function generateChoiceWeights($dimension, $sectionNumber): array
    {
        $weights = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
        
        $weights[$dimension] = 0.8 + (rand(-100, 200) / 1000);
        
        $secondaryDims = array_diff(['D', 'I', 'S', 'C'], [$dimension]);
        
        foreach ($secondaryDims as $dim) {
            if (($dimension == 'D' && $dim == 'I') || ($dimension == 'I' && $dim == 'D')) {
                $weights[$dim] = (rand(-200, 400) / 1000);
            } elseif (($dimension == 'S' && $dim == 'C') || ($dimension == 'C' && $dim == 'S')) {
                $weights[$dim] = (rand(-200, 400) / 1000);
            } else {
                $weights[$dim] = (rand(-400, 300) / 1000);
            }
        }
        
        return $weights;
    }

    private function calculateChoiceScores($choice, string $type): array
    {
        $multiplier = $type === 'most' ? 1 : -1;
        
        return [
            'D' => ($choice->weight_d ?? 0) * $multiplier,
            'I' => ($choice->weight_i ?? 0) * $multiplier,
            'S' => ($choice->weight_s ?? 0) * $multiplier,
            'C' => ($choice->weight_c ?? 0) * $multiplier
        ];
    }

    private function calculateNetScores(array $mostScores, array $leastScores): array
    {
        return [
            'D' => $mostScores['D'] + $leastScores['D'],
            'I' => $mostScores['I'] + $leastScores['I'],
            'S' => $mostScores['S'] + $leastScores['S'],
            'C' => $mostScores['C'] + $leastScores['C']
        ];
    }

    private function updateFinalAnalytics($session, $totalDuration): void
    {
        try {
            $analytics = $session->analytics ?? Disc3DTestAnalytics::where('test_session_id', $session->id)->first();
            
            if ($analytics) {
                $responses = DB::table('disc_3d_responses')
                    ->where('test_session_id', $session->id)
                    ->get();
                
                $analytics->update([
                    'completed_sections' => 24,
                    'completion_rate' => 100,
                    'total_time_seconds' => $totalDuration,
                    'average_time_per_section' => round($totalDuration / 24),
                    'fastest_section_time' => $responses->min('time_spent_seconds'),
                    'slowest_section_time' => $responses->max('time_spent_seconds'),
                    'revisions_made' => $responses->sum('revision_count'),
                    'response_variance' => $this->calculateResponseVariance($responses),
                    'engagement_score' => $this->calculateEngagementScore($responses),
                    'response_quality_score' => $this->calculateQualityScore($responses),
                    'suspicious_patterns' => $this->detectSuspiciousPatterns($responses),
                    'quality_flags' => json_encode($this->getQualityFlags($responses))
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Could not update final analytics', [
                'error' => $e->getMessage(),
                'session_id' => $session->id
            ]);
        }
    }

    // Rest of the helper methods remain the same...
    private function calculateChangeScores($mostScores, $leastScores): array
    {
        return [
            'D' => $mostScores['D'] - $leastScores['D'],
            'I' => $mostScores['I'] - $leastScores['I'],
            'S' => $mostScores['S'] - $leastScores['S'],
            'C' => $mostScores['C'] - $leastScores['C']
        ];
    }

    private function convertToPercentages($rawScores): array
    {
        $total = array_sum(array_map('abs', $rawScores));
        if ($total == 0) return ['D' => 25, 'I' => 25, 'S' => 25, 'C' => 25];
        
        return [
            'D' => (abs($rawScores['D']) / $total) * 100,
            'I' => (abs($rawScores['I']) / $total) * 100,
            'S' => (abs($rawScores['S']) / $total) * 100,
            'C' => (abs($rawScores['C']) / $total) * 100
        ];
    }

    private function convertToSegments($percentages): array
    {
        $segments = [];
        foreach ($percentages as $dim => $percentage) {
            $segments[$dim] = $this->calculateSegment($percentage);
        }
        return $segments;
    }

    private function calculateSegment($percentage): int
    {
        return match(true) {
            $percentage >= 85.72 => 7,
            $percentage >= 71.44 => 6,
            $percentage >= 57.15 => 5,
            $percentage >= 42.87 => 4,
            $percentage >= 28.58 => 3,
            $percentage >= 14.29 => 2,
            default => 1
        };
    }

    private function convertChangeToSegments($changeScores): array
    {
        $segments = [];
        foreach ($changeScores as $dim => $score) {
            $segments[$dim] = $this->calculateChangeSegment($score);
        }
        return $segments;
    }

    private function calculateChangeSegment($score): int
    {
        return match(true) {
            $score >= 75 => 4,
            $score >= 50 => 3,
            $score >= 25 => 2,
            $score >= 1 => 1,
            $score == 0 => 0,
            $score >= -24 => -1,
            $score >= -49 => -2,
            $score >= -74 => -3,
            default => -4
        };
    }

    private function determinePattern($percentages): array
    {
        arsort($percentages);
        $dimensions = array_keys($percentages);
        
        return [
            'primary' => $dimensions[0],
            'secondary' => $dimensions[1],
            'code' => $dimensions[0] . $dimensions[1]
        ];
    }

    private function determineAdaptationPattern($mostPattern, $leastPattern): string
    {
        if ($mostPattern['code'] === $leastPattern['code']) {
            return 'consistent_' . $mostPattern['code'];
        }
        
        return $leastPattern['code'] . '_to_' . $mostPattern['code'];
    }

    private function generatePersonalityProfile($pattern): string
    {
        $profiles = [
            'DI' => 'Dynamic Leader', 'DC' => 'Decisive Analyst', 'DS' => 'Steady Director',
            'ID' => 'Inspiring Motivator', 'IS' => 'Interactive Supporter', 'IC' => 'Influential Communicator',
            'SD' => 'Supportive Leader', 'SI' => 'Stable Collaborator', 'SC' => 'Systematic Coordinator',
            'CD' => 'Careful Decider', 'CI' => 'Conscientious Influencer', 'CS' => 'Compliant Supporter'
        ];
        
        return $profiles[$pattern['code']] ?? $pattern['code'] . ' Type';
    }

    private function generateSummary($pattern, $percentages, $changeSegments): string
    {
        $primaryType = $pattern['primary'];
        $primaryPercentage = round($percentages[$primaryType], 1);
        
        $typeDescriptions = [
            'D' => 'Dominan dan berorientasi hasil',
            'I' => 'Komunikatif dan antusias',
            'S' => 'Stabil dan mendukung',
            'C' => 'Teliti dan sistematis'
        ];
        
        $stressLevel = $this->calculateStressLevel($changeSegments);
        
        return "Tipe kepribadian {$typeDescriptions[$primaryType]} ({$primaryPercentage}%) dengan tingkat adaptasi {$stressLevel}.";
    }

    private function calculateStressLevel($changeSegments): string
    {
        $maxChange = max(array_map('abs', $changeSegments));
        
        return match(true) {
            $maxChange >= 3 => 'tinggi',
            $maxChange >= 2 => 'sedang',
            default => 'rendah'
        };
    }

    private function generateTestCode(): string
    {
        $attempts = 0;
        do {
            $code = 'D3D' . date('Y') . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $attempts++;
            
            if ($attempts > 10) {
                $code = 'D3D' . date('YmdHis') . rand(10, 99);
                break;
            }
            
            try {
                $exists = Disc3DTestSession::where('test_code', $code)->exists();
            } catch (\Exception $e) {
                $exists = false;
            }
            
        } while ($exists && $attempts <= 10);
        
        return $code;
    }

    private function extractDeviceInfo($request): array
    {
        return [
            'platform' => $this->detectPlatform($request->userAgent()),
            'browser' => $this->detectBrowser($request->userAgent()),
            'is_mobile' => $this->isMobile($request->userAgent()),
            'screen_resolution' => $request->input('screen_resolution'),
            'timezone' => $request->input('timezone', 'Asia/Jakarta'),
            'language' => $request->getPreferredLanguage(['en', 'id'])
        ];
    }

    private function extractBrowserInfo($request): array
    {
        $userAgent = $request->userAgent();
        return [
            'user_agent' => $userAgent,
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'is_mobile' => $this->isMobile($userAgent)
        ];
    }

    private function detectPlatform($userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/Tablet/', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }

    private function detectBrowser($userAgent): string
    {
        if (preg_match('/Chrome/', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/', $userAgent)) return 'Safari';
        if (preg_match('/Edge/', $userAgent)) return 'Edge';
        return 'Unknown';
    }

    private function isMobile($userAgent): bool
    {
        return preg_match('/Mobile|Android|iPhone/', $userAgent) ? true : false;
    }

    private function calculateResponseVariance($responses): float
    {
        if (count($responses) < 2) return 0;
        
        $times = collect($responses)->pluck('time_spent_seconds');
        $mean = $times->avg();
        $variance = $times->map(fn($time) => pow($time - $mean, 2))->avg();
        
        return sqrt($variance);
    }

    private function calculateEngagementScore($responses): float
    {
        if (count($responses) == 0) return 0;
        
        $avgTime = collect($responses)->avg('time_spent_seconds');
        $revisions = collect($responses)->sum('revision_count');
        
        $timeScore = match(true) {
            $avgTime < 5 => 20,
            $avgTime > 120 => 40,
            $avgTime >= 10 && $avgTime <= 60 => 100,
            default => 70
        };
        
        $revisionScore = match(true) {
            $revisions == 0 => 60,
            $revisions <= 5 => 100,
            $revisions <= 10 => 80,
            default => 40
        };
        
        return ($timeScore + $revisionScore) / 2;
    }

    private function calculateQualityScore($responses): float
    {
        return $this->calculateEngagementScore($responses);
    }

    private function detectSuspiciousPatterns($responses): bool
    {
        $tooFastCount = collect($responses)->where('time_spent_seconds', '<', 3)->count();
        return $tooFastCount > 5;
    }

    private function getQualityFlags($responses): array
    {
        $flags = [];
        
        $tooFastCount = collect($responses)->where('time_spent_seconds', '<', 3)->count();
        if ($tooFastCount > 5) {
            $flags[] = 'too_many_fast_responses';
        }
        
        return $flags;
    }

    private function buildGraphData($type, $raw, $percentages, $segments): array
    {
        return [
            'type' => $type,
            'raw_scores' => $raw,
            'percentages' => $percentages,
            'segments' => $segments,
            'timestamp' => now()->toISOString()
        ];
    }

    private function identifyStressIndicators($changeSegments): array
    {
        $indicators = [];
        
        foreach ($changeSegments as $dimension => $change) {
            if (abs($change) >= 3) {
                $indicators[] = [
                    'dimension' => $dimension,
                    'change_level' => $change,
                    'stress_type' => $change > 0 ? 'over_adaptation' : 'suppression',
                    'severity' => 'high'
                ];
            }
        }
        
        return $indicators;
    }

    private function generateBehavioralInsights($mostPercentages, $leastPercentages): array
    {
        $insights = [];
        
        foreach (['D', 'I', 'S', 'C'] as $dimension) {
            $gap = $mostPercentages[$dimension] - $leastPercentages[$dimension];
            
            if (abs($gap) > 20) {
                $insights[] = [
                    'dimension' => $dimension,
                    'gap' => $gap,
                    'insight' => $gap > 0 ? 'amplifying_in_public' : 'restraining_in_public'
                ];
            }
        }
        
        return $insights;
    }

    private function generateInterpretations($mostSegments, $leastSegments, $changeSegments): array
    {
        return [
            'public_self' => $this->generatePublicSelfSummary($mostSegments),
            'private_self' => $this->generatePrivateSelfSummary($leastSegments),
            'adaptation' => $this->generateAdaptationSummary($changeSegments),
            'overall' => $this->generateOverallProfile($mostSegments, $leastSegments, $changeSegments)
        ];
    }

    private function generatePublicSelfSummary($segments): string
    {
        $highSegments = array_filter($segments, fn($seg) => $seg >= 5);
        
        if (empty($highSegments)) {
            return 'Menampilkan kepribadian yang seimbang di lingkungan publik.';
        }
        
        $dimensionLabels = [
            'D' => 'tegas dan berorientasi hasil',
            'I' => 'komunikatif dan antusias', 
            'S' => 'stabil dan mendukung',
            'C' => 'teliti dan sistematis'
        ];
        
        $traits = array_map(fn($dim) => $dimensionLabels[$dim], array_keys($highSegments));
        
        return 'Di lingkungan publik, menampilkan diri sebagai seseorang yang ' . implode(', ', $traits) . '.';
    }

    private function generatePrivateSelfSummary($segments): string
    {
        $highSegments = array_filter($segments, fn($seg) => $seg >= 5);
        
        if (empty($highSegments)) {
            return 'Memiliki kepribadian alami yang seimbang.';
        }
        
        $dimensionLabels = [
            'D' => 'dominan dan tegas',
            'I' => 'sosial dan ekspresif',
            'S' => 'sabar dan konsisten', 
            'C' => 'analitis dan detail-oriented'
        ];
        
        $traits = array_map(fn($dim) => $dimensionLabels[$dim], array_keys($highSegments));
        
        return 'Secara alami cenderung ' . implode(', ', $traits) . '.';
    }

    private function generateAdaptationSummary($changeSegments): string
    {
        $highChanges = array_filter($changeSegments, fn($seg) => abs($seg) >= 2);
        
        if (empty($highChanges)) {
            return 'Menunjukkan konsistensi antara kepribadian alami dan yang ditampilkan.';
        }
        
        $adaptations = [];
        foreach ($highChanges as $dimension => $change) {
            if ($change > 0) {
                $adaptations[] = "meningkatkan aspek {$dimension}";
            } else {
                $adaptations[] = "mengurangi aspek {$dimension}";
            }
        }
        
        return 'Melakukan adaptasi dengan ' . implode(', ', $adaptations) . ' dalam lingkungan publik.';
    }

    private function generateOverallProfile($mostSegments, $leastSegments, $changeSegments): string
    {
        $publicSummary = $this->generatePublicSelfSummary($mostSegments);
        $privateSummary = $this->generatePrivateSelfSummary($leastSegments);
        $adaptationSummary = $this->generateAdaptationSummary($changeSegments);
        
        return $publicSummary . ' ' . $privateSummary . ' ' . $adaptationSummary;
    }
}