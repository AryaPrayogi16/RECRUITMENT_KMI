<?php

namespace App\Services;

use App\Models\{
    Candidate,
    Disc3DTestSession,
    Disc3DSection,
    Disc3DSectionChoice,
    Disc3DResponse,
    Disc3DResult,
    Disc3DConfig
};
use App\Helpers\DeviceInfoHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Disc3DProfileInterpretation;

class DiscTestService
{
    /**
     * ✅ UPDATED: Create test session with simplified structure
     */
    public function createTestSession(Candidate $candidate, Request $request, bool $freshStart = true): Disc3DTestSession
    {
        DB::beginTransaction();
        
        try {
            // Check if already completed
            $existingCompleted = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            if ($existingCompleted) {
                throw new \Exception('Candidate sudah menyelesaikan DISC 3D test sebelumnya.');
            }
            
            if ($freshStart) {
                // Delete any incomplete sessions (fresh start)
                Disc3DTestSession::where('candidate_id', $candidate->id)
                    ->whereIn('status', ['not_started', 'in_progress'])
                    ->delete();
            } else {
                // Check for existing incomplete session (resume mode)
                $existingIncomplete = Disc3DTestSession::where('candidate_id', $candidate->id)
                    ->whereIn('status', ['not_started', 'in_progress'])
                    ->first();
                    
                if ($existingIncomplete) {
                    // Update existing session
                    $existingIncomplete->update([
                        'status' => 'in_progress',
                        'started_at' => $existingIncomplete->started_at ?: now(),
                        'updated_at' => now()
                    ]);
                    
                    DB::commit();
                    return $existingIncomplete;
                }
            }
            
            // ✅ Create new session with SIMPLIFIED structure
            $session = Disc3DTestSession::create([
                'candidate_id' => $candidate->id,
                'test_code' => $this->generateTestCode(),
                'status' => 'not_started',
                'started_at' => null, // Will be set when first section is answered
                'completed_at' => null,
                'total_duration_seconds' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::commit();
            
            Log::info('✅ DISC test session created with simplified structure', [
                'candidate_id' => $candidate->id,
                'session_id' => $session->id,
                'test_code' => $session->test_code,
                'mode' => $freshStart ? 'fresh_start' : 'resume'
            ]);
            
            return $session;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating DISC test session', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * ✅ UPDATED: Process single section response
     */
    public function processSectionResponse(Disc3DTestSession $session, array $validated): Disc3DResponse
    {
        DB::beginTransaction();
        
        try {
            // ✅ Update session status if first response
            if ($session->status === 'not_started') {
                $session->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Get REAL choices from database
            $mostChoice = Disc3DSectionChoice::with('section')
                ->where('id', $validated['most_choice_id'])
                ->first();
                
            $leastChoice = Disc3DSectionChoice::with('section')
                ->where('id', $validated['least_choice_id'])
                ->first();
            
            // Validate choices exist in database
            if (!$mostChoice || !$leastChoice) {
                throw new \Exception('Invalid choice IDs. Choices not found in database.');
            }
            
            // Validate section consistency
            if ($mostChoice->section_id !== $validated['section_id'] || 
                $leastChoice->section_id !== $validated['section_id']) {
                throw new \Exception('Choices do not belong to the specified section');
            }
            
            // Validate different dimensions
            if ($mostChoice->choice_dimension === $leastChoice->choice_dimension) {
                throw new \Exception('MOST and LEAST choices cannot be the same dimension');
            }
            
            // Check if response already exists (update vs create)
            $existingResponse = Disc3DResponse::where('test_session_id', $session->id)
                ->where('section_id', $validated['section_id'])
                ->first();
            
            // Calculate scores using REAL DATABASE weights
            $mostScores = $this->calculateRealChoiceScores($mostChoice, 'most');
            $leastScores = $this->calculateRealChoiceScores($leastChoice, 'least');
            $netScores = $this->calculateNetScores($mostScores, $leastScores);
            
            $responseData = [
                'test_session_id' => $session->id,
                'candidate_id' => $session->candidate_id,
                'section_id' => $validated['section_id'],
                'section_code' => sprintf('SEC%02d', $validated['section_id']),
                'section_number' => $validated['section_id'],
                'most_choice_id' => $validated['most_choice_id'],
                'least_choice_id' => $validated['least_choice_id'],
                'most_choice' => $mostChoice->choice_dimension,
                'least_choice' => $leastChoice->choice_dimension,
                'most_score_d' => $mostScores['D'],
                'most_score_i' => $mostScores['I'],
                'most_score_s' => $mostScores['S'],
                'most_score_c' => $mostScores['C'],
                'least_score_d' => $leastScores['D'],
                'least_score_i' => $leastScores['I'],
                'least_score_s' => $leastScores['S'],
                'least_score_c' => $leastScores['C'],
                'net_score_d' => $netScores['D'],
                'net_score_i' => $netScores['I'],
                'net_score_s' => $netScores['S'],
                'net_score_c' => $netScores['C'],
                'time_spent_seconds' => $validated['time_spent'],
                'answered_at' => now(),
                'revision_count' => $validated['revision_count'] ?? 0
            ];
            
            if ($existingResponse) {
                // Update existing response and increment revision count
                $responseData['revision_count'] = $existingResponse->revision_count + 1;
                $existingResponse->update($responseData);
                $response = $existingResponse;
            } else {
                // Create new response
                $response = Disc3DResponse::create($responseData);
                
                // Set response order if not set
                if (!$response->response_order) {
                    $maxOrder = Disc3DResponse::where('test_session_id', $session->id)
                        ->max('response_order') ?? 0;
                    $response->update(['response_order' => $maxOrder + 1]);
                }
            }
            
            DB::commit();
            
            Log::info('✅ DISC section response processed', [
                'session_id' => $session->id,
                'section_id' => $validated['section_id'],
                'most_choice' => $mostChoice->choice_dimension,
                'least_choice' => $leastChoice->choice_dimension,
                'is_update' => $existingResponse !== null
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error processing DISC section response', [
                'session_id' => $session->id,
                'section_id' => $validated['section_id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * ✅ UPDATED: Process bulk responses
     */
    public function processBulkResponses(Disc3DTestSession $session, array $responses): int
    {
        $processedCount = 0;
        
        DB::beginTransaction();
        
        try {
            // ✅ Update session status if first batch
            if ($session->status === 'not_started') {
                $session->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            foreach ($responses as $index => $responseData) {
                try {
                    // Prepare data for individual processing
                    $sectionData = [
                        'section_id' => $responseData['section_id'],
                        'most_choice_id' => $responseData['most_choice_id'],
                        'least_choice_id' => $responseData['least_choice_id'],
                        'time_spent' => $responseData['time_spent'],
                        'revision_count' => 0
                    ];
                    
                    // Use internal processing method
                    $this->processSectionResponseInternal($session, $sectionData, $index + 1);
                    $processedCount++;
                    
                } catch (\Exception $e) {
                    Log::error('Error processing bulk response item', [
                        'session_id' => $session->id,
                        'index' => $index,
                        'section_id' => $responseData['section_id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    // Continue processing other responses
                }
            }
            
            DB::commit();
            
            Log::info('✅ Bulk responses processed', [
                'session_id' => $session->id,
                'total_responses' => count($responses),
                'processed_count' => $processedCount
            ]);
            
            return $processedCount;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * ✅ UPDATED: Complete the DISC test session with simplified structure
     */
    public function completeTestSession(Disc3DTestSession $session, int $totalDuration): Disc3DResult
    {
        DB::beginTransaction();
        
        try {
            // Verify all sections are completed
            $completedSections = $session->responses()->count();
            if ($completedSections < 24) {
                throw new \Exception("Test not complete. Only {$completedSections} of 24 sections completed.");
            }
            
            // ✅ Update session status with simplified structure
            $session->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_duration_seconds' => $totalDuration,
                'updated_at' => now()
            ]);
            
            // Calculate comprehensive results
            $result = $this->calculateDisc3DResults($session, $totalDuration);
            
            // Validate result quality
            $this->validateResultQuality($result);
            
            DB::commit();
            
            Log::info('✅ DISC test completed with simplified session', [
                'session_id' => $session->id,
                'candidate_id' => $session->candidate_id,
                'result_id' => $result->id,
                'total_duration' => $totalDuration,
                'primary_type' => $result->primary_type,
                'is_valid' => $result->is_valid
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error completing DISC test', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * ✅ Generate PDF result (unchanged)
     */
    public function generateResultPdf(Candidate $candidate, Disc3DResult $result)
    {
        try {
            $data = compact('candidate', 'result');
            
            return PDF::loadView('disc3d.pdf.result', $data)
                ->setPaper('A4', 'portrait');
                
        } catch (\Exception $e) {
            Log::error('Error generating DISC PDF', [
                'candidate_id' => $candidate->id,
                'result_id' => $result->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // ===== PRIVATE HELPER METHODS =====

    /**
     * ✅ Internal method using REAL DATABASE
     */
    private function processSectionResponseInternal(Disc3DTestSession $session, array $validated, int $responseOrder): Disc3DResponse
    {
        // Get REAL choices from database
        $mostChoice = Disc3DSectionChoice::findOrFail($validated['most_choice_id']);
        $leastChoice = Disc3DSectionChoice::findOrFail($validated['least_choice_id']);
        
        // Calculate scores using REAL database weights
        $mostScores = $this->calculateRealChoiceScores($mostChoice, 'most');
        $leastScores = $this->calculateRealChoiceScores($leastChoice, 'least');
        $netScores = $this->calculateNetScores($mostScores, $leastScores);
        
        $responseData = [
            'test_session_id' => $session->id,
            'candidate_id' => $session->candidate_id,
            'section_id' => $validated['section_id'],
            'section_code' => sprintf('SEC%02d', $validated['section_id']),
            'section_number' => $validated['section_id'],
            'most_choice_id' => $validated['most_choice_id'],
            'least_choice_id' => $validated['least_choice_id'],
            'most_choice' => $mostChoice->choice_dimension,
            'least_choice' => $leastChoice->choice_dimension,
            'most_score_d' => $mostScores['D'],
            'most_score_i' => $mostScores['I'],
            'most_score_s' => $mostScores['S'],
            'most_score_c' => $mostScores['C'],
            'least_score_d' => $leastScores['D'],
            'least_score_i' => $leastScores['I'],
            'least_score_s' => $leastScores['S'],
            'least_score_c' => $leastScores['C'],
            'net_score_d' => $netScores['D'],
            'net_score_i' => $netScores['I'],
            'net_score_s' => $netScores['S'],
            'net_score_c' => $netScores['C'],
            'time_spent_seconds' => $validated['time_spent'],
            'response_order' => $responseOrder,
            'answered_at' => now(),
            'revision_count' => 0
        ];
        
        // Create proper Disc3DResponse model instance
        $response = Disc3DResponse::create($responseData);
        
        return $response;
    }

    /**
     * ✅ Calculate choice scores using REAL database weights
     */
    private function calculateRealChoiceScores(Disc3DSectionChoice $choice, string $type): array
    {
        $multiplier = $type === 'most' ? 1 : -1;
        
        return [
            'D' => (float) $choice->weight_d * $multiplier,
            'I' => (float) $choice->weight_i * $multiplier,
            'S' => (float) $choice->weight_s * $multiplier,
            'C' => (float) $choice->weight_c * $multiplier
        ];
    }

    /**
     * Calculate net scores (most + least)
     */
    private function calculateNetScores(array $mostScores, array $leastScores): array
    {
        return [
            'D' => $mostScores['D'] + $leastScores['D'],
            'I' => $mostScores['I'] + $leastScores['I'],
            'S' => $mostScores['S'] + $leastScores['S'],
            'C' => $mostScores['C'] + $leastScores['C']
        ];
    }

    /**
     * ✅ Calculate comprehensive DISC results (core algorithm unchanged)
     */
    private function calculateDisc3DResults(Disc3DTestSession $session, int $totalDuration): Disc3DResult
    {
        // Load responses using DB query to ensure fresh data
        $responses = DB::table('disc_3d_responses')
            ->where('test_session_id', $session->id)
            ->orderBy('response_order')
            ->get();
        
        if ($responses->count() !== 24) {
            throw new \Exception('Incomplete responses for result calculation');
        }
        
        // Calculate raw scores for each graph
        $mostRawScores = $this->calculateGraphRawScores($responses, 'most');
        $leastRawScores = $this->calculateGraphRawScores($responses, 'least');
        $changeRawScores = $this->calculateChangeScores($mostRawScores, $leastRawScores);
        
        // Convert to percentages
        $mostPercentages = $this->convertToPercentages($mostRawScores);
        $leastPercentages = $this->convertToPercentages($leastRawScores);
        
        // Calculate segments
        $mostSegments = $this->convertToSegments($mostPercentages);
        $leastSegments = $this->convertToSegments($leastPercentages);
        $changeSegments = $this->convertChangeToSegments($changeRawScores);
        
        // Determine patterns
        $mostPattern = $this->determinePattern($mostPercentages);
        $leastPattern = $this->determinePattern($leastPercentages);
        $adaptationPattern = $this->determineAdaptationPattern($mostPattern, $leastPattern);
        
        // Generate interpretations
        $interpretations = $this->generateInterpretations($mostSegments, $leastSegments, $changeSegments);
        $profileInterpretations = $this->getProfileInterpretations($mostSegments, $leastSegments, $changeSegments);
        
        // Calculate consistency and validity
        $consistencyScore = $this->calculateConsistencyScore($responses);
        $validityFlags = $this->performValidityChecks($responses, $session);
        $isValid = empty($validityFlags['critical_flags']);
        
        // Create result record
        $result = Disc3DResult::create([
            'test_session_id' => $session->id,
            'candidate_id' => $session->candidate_id,
            'test_code' => $session->test_code,
            'test_completed_at' => now(),
            'test_duration_seconds' => $totalDuration,
            
            // MOST scores
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
            
            // LEAST scores
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
            
            // CHANGE scores
            'change_d_raw' => $changeRawScores['D'],
            'change_i_raw' => $changeRawScores['I'],
            'change_s_raw' => $changeRawScores['S'],
            'change_c_raw' => $changeRawScores['C'],
            'change_d_segment' => $changeSegments['D'],
            'change_i_segment' => $changeSegments['I'],
            'change_s_segment' => $changeSegments['S'],
            'change_c_segment' => $changeSegments['C'],
            
            // Patterns
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
            
            // JSON data
            'graph_most_data' => json_encode($this->buildGraphData('MOST', $mostRawScores, $mostPercentages, $mostSegments)),
            'graph_least_data' => json_encode($this->buildGraphData('LEAST', $leastRawScores, $leastPercentages, $leastSegments)),
            'graph_change_data' => json_encode($this->buildGraphData('CHANGE', $changeRawScores, [], $changeSegments)),
            'most_score_breakdown' => json_encode($this->buildScoreBreakdown($responses, 'most')),
            'least_score_breakdown' => json_encode($this->buildScoreBreakdown($responses, 'least')),
            
            // Interpretations
            'public_self_summary' => $interpretations['public_self'],
            'private_self_summary' => $interpretations['private_self'],
            'adaptation_summary' => $interpretations['adaptation'],
            'overall_profile' => $interpretations['overall'],
            
            // Analysis
            'section_responses' => json_encode($this->buildSectionResponsesData($responses)),
            'stress_indicators' => json_encode($this->identifyStressIndicators($changeSegments)),
            'behavioral_insights' => json_encode($this->generateBehavioralInsights($mostPercentages, $leastPercentages)),
            'consistency_analysis' => json_encode($this->analyzeConsistency($responses)),
            
            // Validity
            'consistency_score' => $consistencyScore,
            'is_valid' => $isValid,
            'validity_flags' => json_encode($validityFlags),
            
            // Performance
            'response_consistency' => $this->calculateResponseConsistency($responses),
            'average_response_time' => round(collect($responses)->avg('time_spent_seconds')),
            'timing_analysis' => json_encode($this->analyzeTimingPatterns($responses)),

            // Work style interpretations
            'work_style_most' => json_encode($profileInterpretations['work_style']['most']),
            'work_style_least' => json_encode($profileInterpretations['work_style']['least']),
            'work_style_adaptation' => json_encode($profileInterpretations['work_style']['adaptation']),
            // Communication style interpretations  
            'communication_style_most' => json_encode($profileInterpretations['communication']['most']),
            'communication_style_least' => json_encode($profileInterpretations['communication']['least']),
            // Stress behavior patterns
            'stress_behavior_most' => json_encode($profileInterpretations['stress']['most']),
            'stress_behavior_least' => json_encode($profileInterpretations['stress']['least']),
            'stress_behavior_change' => json_encode($profileInterpretations['stress']['change']),
            // Motivators and fears
            'motivators_most' => json_encode($profileInterpretations['motivators']['most']),
            'motivators_least' => json_encode($profileInterpretations['motivators']['least']),
            'fears_most' => json_encode($profileInterpretations['fears']['most']),
            'fears_least' => json_encode($profileInterpretations['fears']['least']),
            // Compiled interpretations for easy access
            'work_style_summary' => $this->compileWorkStyleSummary($profileInterpretations['work_style']),
            'communication_summary' => $this->compileCommunicationSummary($profileInterpretations['communication']),
            'motivators_summary' => $this->compileMotivatorsSummary($profileInterpretations['motivators']),
            'stress_management_summary' => $this->compileStressSummary($profileInterpretations['stress']),
        ]);
        
        return $result;
    }

    /**
     * Generate test code (unchanged)
     */
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

    // ===== ALL OTHER CALCULATION METHODS REMAIN THE SAME =====
    // (Include all other helper methods for calculation, scoring, etc.)
    
    private function calculateGraphRawScores($responses, string $graphType): array
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

    private function calculateChangeScores(array $mostScores, array $leastScores): array
    {
        return [
            'D' => $mostScores['D'] - $leastScores['D'],
            'I' => $mostScores['I'] - $leastScores['I'],
            'S' => $mostScores['S'] - $leastScores['S'],
            'C' => $mostScores['C'] - $leastScores['C']
        ];
    }

    private function convertToPercentages(array $rawScores): array
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

    private function convertToSegments(array $percentages): array
    {
        $segments = [];
        foreach ($percentages as $dimension => $percentage) {
            $segments[$dimension] = $this->calculateSegment($percentage);
        }
        return $segments;
    }

    private function calculateSegment(float $percentage): int
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

    private function convertChangeToSegments(array $changeScores): array
    {
        $segments = [];
        foreach ($changeScores as $dimension => $score) {
            $segments[$dimension] = $this->calculateChangeSegment($score);
        }
        return $segments;
    }

    private function calculateChangeSegment(float $score): int
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

    private function determinePattern(array $percentages): array
    {
        arsort($percentages);
        $dimensions = array_keys($percentages);
        
        return [
            'primary' => $dimensions[0],
            'secondary' => $dimensions[1],
            'code' => $dimensions[0] . $dimensions[1]
        ];
    }

    private function determineAdaptationPattern(array $mostPattern, array $leastPattern): string
    {
        if ($mostPattern['code'] === $leastPattern['code']) {
            return 'consistent_' . $mostPattern['code'];
        }
        
        return $leastPattern['code'] . '_to_' . $mostPattern['code'];
    }

    private function generatePersonalityProfile(array $pattern): string
    {
        $profiles = [
            'DI' => 'Dynamic Leader',
            'DC' => 'Decisive Analyst', 
            'DS' => 'Steady Director',
            'ID' => 'Inspiring Motivator',
            'IS' => 'Interactive Supporter',
            'IC' => 'Influential Communicator',
            'SD' => 'Supportive Leader',
            'SI' => 'Stable Collaborator',
            'SC' => 'Systematic Coordinator',
            'CD' => 'Careful Decider',
            'CI' => 'Conscientious Influencer',
            'CS' => 'Compliant Supporter'
        ];
        
        return $profiles[$pattern['code']] ?? $pattern['code'] . ' Type';
    }

    private function generateSummary(array $pattern, array $percentages, array $changeSegments): string
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

    private function calculateStressLevel(array $changeSegments): string
    {
        $maxChange = max(array_map('abs', $changeSegments));
        
        return match(true) {
            $maxChange >= 3 => 'tinggi',
            $maxChange >= 2 => 'sedang',
            default => 'rendah'
        };
    }

    private function calculateConsistencyScore($responses): float
    {
        $timeConsistency = $this->calculateTimeConsistency($responses);
        $choiceConsistency = $this->calculateChoiceConsistency($responses);
        
        return ($timeConsistency + $choiceConsistency) / 2;
    }

    private function calculateTimeConsistency($responses): float
    {
        if (count($responses) < 2) return 100;
        
        $times = collect($responses)->pluck('time_spent_seconds');
        $mean = $times->avg();
        $variance = $times->map(fn($time) => pow($time - $mean, 2))->avg();
        $coefficient = $variance > 0 ? sqrt($variance) / $mean : 0;
        
        return max(0, 100 - ($coefficient * 100));
    }

    private function calculateChoiceConsistency($responses): float
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

    private function calculateResponseConsistency($responses): float
    {
        return $this->calculateChoiceConsistency($responses);
    }

    private function performValidityChecks($responses, Disc3DTestSession $session): array
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

    private function validateResultQuality(Disc3DResult $result): void
    {
        if (!$result->is_valid) {
            Log::warning('DISC result marked as invalid', [
                'result_id' => $result->id,
                'validity_flags' => $result->validity_flags
            ]);
        }
        
        if ($result->consistency_score < 50) {
            Log::warning('DISC result has low consistency', [
                'result_id' => $result->id,
                'consistency_score' => $result->consistency_score
            ]);
        }
    }

    // ===== INCLUDE ALL OTHER HELPER METHODS =====
    // (Add all remaining calculation, validation, and interpretation methods)
    
    private function generateInterpretations(array $mostSegments, array $leastSegments, array $changeSegments): array
    {
        return [
            'public_self' => $this->generatePublicSelfSummary($mostSegments),
            'private_self' => $this->generatePrivateSelfSummary($leastSegments),
            'adaptation' => $this->generateAdaptationSummary($changeSegments),
            'overall' => $this->generateOverallProfile($mostSegments, $leastSegments, $changeSegments)
        ];
    }

    private function generatePublicSelfSummary(array $segments): string
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

    private function generatePrivateSelfSummary(array $segments): string
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

    private function generateAdaptationSummary(array $changeSegments): string
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

    private function generateOverallProfile(array $mostSegments, array $leastSegments, array $changeSegments): string
    {
        $publicSummary = $this->generatePublicSelfSummary($mostSegments);
        $privateSummary = $this->generatePrivateSelfSummary($leastSegments);
        $adaptationSummary = $this->generateAdaptationSummary($changeSegments);
        
        return $publicSummary . ' ' . $privateSummary . ' ' . $adaptationSummary;
    }

    private function buildGraphData(string $graphType, array $rawScores, array $percentages, array $segments): array
    {
        return [
            'graph_type' => $graphType,
            'raw_scores' => $rawScores,
            'percentages' => $percentages,
            'segments' => $segments,
            'generated_at' => now()->toISOString()
        ];
    }

    private function buildScoreBreakdown($responses, string $type): array
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

    private function buildSectionResponsesData($responses): array
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

    private function identifyStressIndicators(array $changeSegments): array
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
            } elseif (abs($change) >= 2) {
                $indicators[] = [
                    'dimension' => $dimension,
                    'change_level' => $change,
                    'stress_type' => $change > 0 ? 'adaptation' : 'restraint',
                    'severity' => 'moderate'
                ];
            }
        }
        
        return $indicators;
    }

    private function generateBehavioralInsights(array $mostPercentages, array $leastPercentages): array
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

    private function analyzeConsistency($responses): array
    {
        return [
            'time_consistency' => $this->calculateTimeConsistency($responses),
            'choice_consistency' => $this->calculateChoiceConsistency($responses),
            'overall_consistency' => $this->calculateConsistencyScore($responses)
        ];
    }

    private function analyzeTimingPatterns($responses): array
    {
        if (count($responses) == 0) return [];
        
        $times = collect($responses)->pluck('time_spent_seconds');
        
        return [
            'mean_time' => $times->avg(),
            'median_time' => $times->median(),
            'min_time' => $times->min(),
            'max_time' => $times->max(),
            'std_deviation' => sqrt($times->map(fn($time) => pow($time - $times->avg(), 2))->avg()),
            'trend' => $this->calculateTimeTrend($responses)
        ];
    }

    private function calculateTimeTrend($responses): string
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

    private function getProfileInterpretations(array $mostSegments, array $leastSegments, array $changeSegments): array
    {
        $interpretations = [
            'work_style' => ['most' => [], 'least' => [], 'adaptation' => []],
            'communication' => ['most' => [], 'least' => []],
            'stress' => ['most' => [], 'least' => [], 'change' => []],
            'motivators' => ['most' => [], 'least' => []],
            'fears' => ['most' => [], 'least' => []]
        ];
        
        foreach (['D', 'I', 'S', 'C'] as $dimension) {
            // MOST
            $mostInterpretation = $this->getInterpretation($dimension, 'MOST', $mostSegments[$dimension]);
            if ($mostInterpretation) {
                $interpretations['work_style']['most'][$dimension] = $this->decodeJsonField($mostInterpretation->work_style);
                $interpretations['communication']['most'][$dimension] = $this->decodeJsonField($mostInterpretation->communication_style);
                $interpretations['stress']['most'][$dimension] = $this->decodeJsonField($mostInterpretation->stress_behavior);
                $interpretations['motivators']['most'][$dimension] = $this->decodeJsonField($mostInterpretation->motivators);
                $interpretations['fears']['most'][$dimension] = $this->decodeJsonField($mostInterpretation->fears);
            }
            // LEAST
            $leastInterpretation = $this->getInterpretation($dimension, 'LEAST', $leastSegments[$dimension]);
            if ($leastInterpretation) {
                $interpretations['work_style']['least'][$dimension] = $this->decodeJsonField($leastInterpretation->work_style);
                $interpretations['communication']['least'][$dimension] = $this->decodeJsonField($leastInterpretation->communication_style);
                $interpretations['stress']['least'][$dimension] = $this->decodeJsonField($leastInterpretation->stress_behavior);
                $interpretations['motivators']['least'][$dimension] = $this->decodeJsonField($leastInterpretation->motivators);
                $interpretations['fears']['least'][$dimension] = $this->decodeJsonField($leastInterpretation->fears);
            }
            // CHANGE
            $changeInterpretation = $this->getInterpretation($dimension, 'CHANGE', $changeSegments[$dimension]);
            if ($changeInterpretation) {
                $interpretations['work_style']['adaptation'][$dimension] = $this->decodeJsonField($changeInterpretation->work_style);
                $interpretations['stress']['change'][$dimension] = $this->decodeJsonField($changeInterpretation->stress_behavior);
            }
        }
        return $interpretations;
    }

    private function getInterpretation(string $dimension, string $graphType, int $segmentLevel)
    {
        try {
            return Disc3DProfileInterpretation::where('dimension', $dimension)
                ->where('graph_type', $graphType)
                ->where('segment_level', $segmentLevel)
                ->first();
        } catch (\Exception $e) {
            Log::warning('Could not load interpretation', [
                'dimension' => $dimension,
                'graph_type' => $graphType,
                'segment_level' => $segmentLevel,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function decodeJsonField($field): array
    {
        if (is_null($field)) return [];
        if (is_array($field)) return $field;
        if (is_string($field)) {
            $decoded = json_decode($field, true);
            return is_array($decoded) ? $decoded : [$field];
        }
        return [];
    }

    private function compileWorkStyleSummary(array $workStyleData): string
    {
        $summary = [];
        if (!empty($workStyleData['most'])) {
            $mostStyles = $this->extractMainPoints($workStyleData['most']);
            if (!empty($mostStyles)) $summary[] = "Gaya kerja publik: " . implode(', ', $mostStyles);
        }
        if (!empty($workStyleData['least'])) {
            $leastStyles = $this->extractMainPoints($workStyleData['least']);
            if (!empty($leastStyles)) $summary[] = "Gaya kerja alami: " . implode(', ', $leastStyles);
        }
        if (!empty($workStyleData['adaptation'])) {
            $adaptationStyles = $this->extractMainPoints($workStyleData['adaptation']);
            if (!empty($adaptationStyles)) $summary[] = "Adaptasi: " . implode(', ', $adaptationStyles);
        }
        return !empty($summary) ? implode('. ', $summary) . '.' : 'Gaya kerja yang seimbang dan fleksibel.';
    }

    private function compileCommunicationSummary(array $communicationData): string
    {
        $summary = [];
        if (!empty($communicationData['most'])) {
            $mostComm = $this->extractMainPoints($communicationData['most']);
            if (!empty($mostComm)) $summary[] = "Komunikasi publik: " . implode(', ', $mostComm);
        }
        if (!empty($communicationData['least'])) {
            $leastComm = $this->extractMainPoints($communicationData['least']);
            if (!empty($leastComm)) $summary[] = "Komunikasi alami: " . implode(', ', $leastComm);
        }
        return !empty($summary) ? implode('. ', $summary) . '.' : 'Gaya komunikasi yang adaptif sesuai situasi.';
    }

    private function compileMotivatorsSummary(array $motivatorsData): string
    {
        $motivators = [];
        if (!empty($motivatorsData['most'])) {
            $motivators = array_merge($motivators, $this->extractMainPoints($motivatorsData['most']));
        }
        if (!empty($motivatorsData['least'])) {
            $motivators = array_merge($motivators, $this->extractMainPoints($motivatorsData['least']));
        }
        $uniqueMotivators = array_unique($motivators);
        return !empty($uniqueMotivators) 
            ? "Dimotivasi oleh: " . implode(', ', array_slice($uniqueMotivators, 0, 5)) . "."
            : "Motivasi yang beragam dan situasional.";
    }

    private function compileStressSummary(array $stressData): string
    {
        $stressPoints = [];
        if (!empty($stressData['change'])) {
            $changeStress = $this->extractMainPoints($stressData['change']);
            if (!empty($changeStress)) $stressPoints[] = "Tekanan adaptasi: " . implode(', ', $changeStress);
        }
        if (!empty($stressData['most'])) {
            $publicStress = $this->extractMainPoints($stressData['most']);
            if (!empty($publicStress)) $stressPoints[] = "Manajemen stress publik: " . implode(', ', $publicStress);
        }
        if (!empty($stressData['least'])) {
            $privateStress = $this->extractMainPoints($stressData['least']);
            if (!empty($privateStress)) $stressPoints[] = "Pola stress alami: " . implode(', ', $privateStress);
        }
        return !empty($stressPoints) 
            ? implode('. ', $stressPoints) . '.'
            : "Manajemen stress yang seimbang dan adaptif.";
    }

    private function extractMainPoints(array $data, int $limit = 3): array
    {
        $points = [];
        foreach ($data as $dimension => $content) {
            if (is_string($content)) {
                $points[] = trim($content);
            } elseif (is_array($content)) {
                foreach ($content as $item) {
                    if (is_string($item)) {
                        $points[] = trim($item);
                    }
                }
            }
        }
        $points = array_filter(array_unique($points), function($point) {
            return !empty(trim($point));
        });
        return array_slice($points, 0, $limit);
    }
}