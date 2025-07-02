<?php

namespace App\Services;

use App\Models\{
    Candidate,
    Disc3DTestSession,
    Disc3DSection,
    Disc3DSectionChoice,
    Disc3DResponse,
    Disc3DResult,
    Disc3DTestAnalytics,
    Disc3DSectionAnalytics,
    Disc3DProfileInterpretation,
    Disc3DPatternCombination,
    Disc3DConfig
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DiscTestService
{
    /**
     * Create a new DISC 3D test session
     */
    public function createTestSession(Candidate $candidate, Request $request): Disc3DTestSession
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
            
            // Check for existing incomplete session (don't delete, resume instead)
            $existingIncomplete = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->first();
                
            if ($existingIncomplete) {
                // Update existing session with new request data
                $existingIncomplete->update([
                    'status' => 'in_progress',
                    'started_at' => $existingIncomplete->started_at ?: now(),
                    'last_activity_at' => now(),
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'device_info' => $this->extractDeviceInfo($request)
                ]);
                
                DB::commit();
                return $existingIncomplete;
            }
            
            // Get test configuration
            $testConfig = Disc3DConfig::getTestSettings();
            
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
                'time_limit_minutes' => $testConfig['time_limit_minutes'] ?? 60,
                'auto_save' => $testConfig['auto_save'] ?? true,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'session_token' => $this->generateSessionToken(),
                'device_info' => $this->extractDeviceInfo($request)
            ]);
            
            // Create analytics record
            Disc3DTestAnalytics::create([
                'candidate_id' => $candidate->id,
                'test_session_id' => $session->id,
                'total_sections' => 24,
                'completed_sections' => 0,
                'completion_rate' => 0
            ]);
            
            DB::commit();
            
            Log::info('DISC 3D test session created', [
                'candidate_id' => $candidate->id,
                'session_id' => $session->id,
                'test_code' => $session->test_code,
                'ip_address' => $request->ip()
            ]);
            
            return $session;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating DISC 3D test session', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process single section response
     */
    public function processSectionResponse(Disc3DTestSession $session, array $validated): Disc3DResponse
    {
        DB::beginTransaction();
        
        try {
            // Get choices to validate they belong to the same section
            $mostChoice = Disc3DSectionChoice::findOrFail($validated['most_choice_id']);
            $leastChoice = Disc3DSectionChoice::findOrFail($validated['least_choice_id']);
            
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
            
            // Calculate scores
            $mostScores = $this->calculateChoiceScores($mostChoice, 'most');
            $leastScores = $this->calculateChoiceScores($leastChoice, 'least');
            $netScores = $this->calculateNetScores($mostScores, $leastScores);
            
            $responseData = [
                'test_session_id' => $session->id,
                'candidate_id' => $session->candidate_id,
                'section_id' => $validated['section_id'],
                'section_code' => $mostChoice->section_code,
                'section_number' => $mostChoice->section_number,
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
            
            // Create or update section analytics
            $this->updateSectionAnalytics($session, $validated, $response);
            
            DB::commit();
            
            Log::info('DISC 3D section response processed', [
                'session_id' => $session->id,
                'section_id' => $validated['section_id'],
                'most_choice' => $mostChoice->choice_dimension,
                'least_choice' => $leastChoice->choice_dimension,
                'is_update' => $existingResponse !== null
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error processing DISC 3D section response', [
                'session_id' => $session->id,
                'section_id' => $validated['section_id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Complete the DISC 3D test session
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
            
            // Update session status
            $session->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_duration_seconds' => $totalDuration,
                'sections_completed' => 24,
                'progress' => 100
            ]);
            
            // Calculate comprehensive results
            $result = $this->calculateDisc3DResults($session);
            
            // Update analytics
            $this->updateTestAnalytics($session, $totalDuration);
            
            // Validate result quality
            $this->validateResultQuality($result);
            
            DB::commit();
            
            Log::info('DISC 3D test completed successfully', [
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
            Log::error('Error completing DISC 3D test', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate comprehensive DISC 3D results
     */
    private function calculateDisc3DResults(Disc3DTestSession $session): Disc3DResult
    {
        $responses = $session->responses()->with(['mostChoice', 'leastChoice'])->get();
        
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
            'test_duration_seconds' => $session->total_duration_seconds,
            
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
            'primary_percentage' => $mostPercentages[$mostPattern['primary']],
            'summary' => $this->generateSummary($mostPattern, $mostPercentages, $changeSegments),
            
            // JSON data
            'graph_most_data' => $this->buildGraphData('MOST', $mostRawScores, $mostPercentages, $mostSegments),
            'graph_least_data' => $this->buildGraphData('LEAST', $leastRawScores, $leastPercentages, $leastSegments),
            'graph_change_data' => $this->buildGraphData('CHANGE', $changeRawScores, [], $changeSegments),
            'most_score_breakdown' => $responses->map(function($r) {
                return [
                    'section' => $r->section_number,
                    'scores' => $r->most_scores
                ];
            })->toArray(),
            'least_score_breakdown' => $responses->map(function($r) {
                return [
                    'section' => $r->section_number,
                    'scores' => $r->least_scores
                ];
            })->toArray(),
            
            // Interpretations
            'public_self_summary' => $interpretations['public_self'],
            'private_self_summary' => $interpretations['private_self'],
            'adaptation_summary' => $interpretations['adaptation'],
            'overall_profile' => $interpretations['overall'],
            
            // Analysis
            'section_responses' => $this->buildSectionResponsesData($responses),
            'stress_indicators' => $this->identifyStressIndicators($changeSegments),
            'behavioral_insights' => $this->generateBehavioralInsights($mostPercentages, $leastPercentages),
            'consistency_analysis' => $this->analyzeConsistency($responses),
            
            // Validity
            'consistency_score' => $consistencyScore,
            'is_valid' => $isValid,
            'validity_flags' => $validityFlags,
            
            // Performance
            'response_consistency' => $this->calculateResponseConsistency($responses),
            'average_response_time' => $responses->avg('time_spent_seconds'),
            'timing_analysis' => $this->analyzeTimingPatterns($responses)
        ]);
        
        return $result;
    }

    /**
     * Calculate choice scores for most/least selection
     */
    private function calculateChoiceScores(Disc3DSectionChoice $choice, string $type): array
    {
        $multiplier = $type === 'most' ? 1 : -1;
        
        return [
            'D' => $choice->weight_d * $multiplier,
            'I' => $choice->weight_i * $multiplier,
            'S' => $choice->weight_s * $multiplier,
            'C' => $choice->weight_c * $multiplier
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
     * Calculate raw scores for a graph (MOST/LEAST)
     */
    private function calculateGraphRawScores($responses, string $graphType): array
    {
        $scores = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
        
        foreach ($responses as $response) {
            $scoreField = $graphType . '_score_';
            $scores['D'] += $response->{$scoreField . 'd'};
            $scores['I'] += $response->{$scoreField . 'i'};
            $scores['S'] += $response->{$scoreField . 's'};
            $scores['C'] += $response->{$scoreField . 'c'};
        }
        
        return $scores;
    }

    /**
     * Calculate change scores (MOST - LEAST)
     */
    private function calculateChangeScores(array $mostScores, array $leastScores): array
    {
        return [
            'D' => $mostScores['D'] - $leastScores['D'],
            'I' => $mostScores['I'] - $leastScores['I'],
            'S' => $mostScores['S'] - $leastScores['S'],
            'C' => $mostScores['C'] - $leastScores['C']
        ];
    }

    /**
     * Convert raw scores to percentages
     */
    private function convertToPercentages(array $rawScores): array
    {
        $maxPossible = 24 * 1.0; // Assuming max weight is 1.0 per section
        
        return [
            'D' => ($rawScores['D'] / $maxPossible) * 100,
            'I' => ($rawScores['I'] / $maxPossible) * 100,
            'S' => ($rawScores['S'] / $maxPossible) * 100,
            'C' => ($rawScores['C'] / $maxPossible) * 100
        ];
    }

    /**
     * Convert percentages to segments (1-7)
     */
    private function convertToSegments(array $percentages): array
    {
        $segments = [];
        $segmentConfig = Disc3DConfig::getSegmentThresholds();
        
        foreach ($percentages as $dimension => $percentage) {
            $segments[$dimension] = $this->calculateSegment($percentage, $segmentConfig);
        }
        
        return $segments;
    }

    /**
     * Convert change scores to segments (-4 to +4)
     */
    private function convertChangeToSegments(array $changeScores): array
    {
        $segments = [];
        $changeConfig = Disc3DConfig::getValue('change_segment_conversion', []);
        
        foreach ($changeScores as $dimension => $score) {
            $segments[$dimension] = $this->calculateChangeSegment($score, $changeConfig);
        }
        
        return $segments;
    }

    /**
     * Calculate segment based on percentage
     */
    private function calculateSegment(float $percentage, array $config): int
    {
        if (empty($config)) {
            // Default segments if no config
            if ($percentage >= 85.72) return 7;
            if ($percentage >= 71.44) return 6;
            if ($percentage >= 57.15) return 5;
            if ($percentage >= 42.87) return 4;
            if ($percentage >= 28.58) return 3;
            if ($percentage >= 14.29) return 2;
            return 1;
        }
        
        foreach ($config as $segment => $range) {
            if ($percentage >= $range['min'] && $percentage <= $range['max']) {
                return (int) $segment;
            }
        }
        
        return 1; // Default
    }

    /**
     * Calculate change segment
     */
    private function calculateChangeSegment(float $score, array $config): int
    {
        if (empty($config)) {
            // Default change segments
            if ($score >= 75) return 4;
            if ($score >= 50) return 3;
            if ($score >= 25) return 2;
            if ($score >= 1) return 1;
            if ($score == 0) return 0;
            if ($score >= -24) return -1;
            if ($score >= -49) return -2;
            if ($score >= -74) return -3;
            return -4;
        }
        
        foreach ($config as $segment => $range) {
            if ($score >= $range['min'] && $score <= $range['max']) {
                return (int) $segment;
            }
        }
        
        return 0; // Default
    }

    /**
     * Determine personality pattern
     */
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

    /**
     * Determine adaptation pattern
     */
    private function determineAdaptationPattern(array $mostPattern, array $leastPattern): string
    {
        if ($mostPattern['code'] === $leastPattern['code']) {
            return 'consistent_' . $mostPattern['code'];
        }
        
        return $leastPattern['code'] . '_to_' . $mostPattern['code'];
    }

    /**
     * Generate personality profile description
     */
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

    /**
     * Generate summary
     */
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

    /**
     * Calculate stress level from change segments
     */
    private function calculateStressLevel(array $changeSegments): string
    {
        $maxChange = max(array_map('abs', $changeSegments));
        
        return match(true) {
            $maxChange >= 3 => 'tinggi',
            $maxChange >= 2 => 'sedang',
            default => 'rendah'
        };
    }

    /**
     * Update section analytics
     */
    private function updateSectionAnalytics(Disc3DTestSession $session, array $validated, Disc3DResponse $response): void
    {
        Disc3DSectionAnalytics::updateOrCreate(
            [
                'test_session_id' => $session->id,
                'section_id' => $validated['section_id']
            ],
            [
                'candidate_id' => $session->candidate_id,
                'section_number' => $response->section_number,
                'time_to_completion' => $validated['time_spent'],
                'most_choice_changes' => $response->revision_count,
                'least_choice_changes' => 0, // Could be tracked separately
                'rushed_response' => $validated['time_spent'] < 5,
                'delayed_response' => $validated['time_spent'] > 120,
                'confidence_score' => $this->calculateConfidenceScore($validated['time_spent'], $response->revision_count)
            ]
        );
    }

    /**
     * Calculate confidence score based on response behavior
     */
    private function calculateConfidenceScore(int $timeSpent, int $revisionCount): float
    {
        $timeScore = match(true) {
            $timeSpent < 5 => 20,
            $timeSpent > 120 => 40,
            default => 100
        };
        
        $revisionScore = max(0, 100 - ($revisionCount * 20));
        
        return ($timeScore + $revisionScore) / 2;
    }

    /**
     * Update test analytics
     */
    private function updateTestAnalytics(Disc3DTestSession $session, int $totalDuration): void
    {
        $responses = $session->responses;
        $analytics = $session->analytics;
        
        if ($analytics) {
            $analytics->update([
                'completed_sections' => 24,
                'completion_rate' => 100,
                'total_time_seconds' => $totalDuration,
                'average_time_per_section' => $totalDuration / 24,
                'fastest_section_time' => $responses->min('time_spent_seconds'),
                'slowest_section_time' => $responses->max('time_spent_seconds'),
                'revisions_made' => $responses->sum('revision_count'),
                'response_variance' => $this->calculateResponseVariance($responses),
                'engagement_score' => $this->calculateEngagementScore($responses, $totalDuration)
            ]);
        }
    }

    /**
     * Calculate response variance
     */
    private function calculateResponseVariance($responses): float
    {
        $times = $responses->pluck('time_spent_seconds');
        $mean = $times->avg();
        $variance = $times->map(fn($time) => pow($time - $mean, 2))->avg();
        
        return sqrt($variance);
    }

    /**
     * Calculate engagement score
     */
    private function calculateEngagementScore($responses, int $totalDuration): float
    {
        $factors = [
            'completion' => 100, // Completed
            'time_appropriateness' => $this->calculateTimeAppropriatenessScore($totalDuration),
            'consistency' => max(0, 100 - $this->calculateResponseVariance($responses))
        ];
        
        return array_sum($factors) / count($factors);
    }

    /**
     * Calculate time appropriateness score
     */
    private function calculateTimeAppropriatenessScore(int $totalDuration): float
    {
        $idealTime = 30 * 60; // 30 minutes
        $deviation = abs($totalDuration - $idealTime);
        
        return max(0, 100 - ($deviation / 60)); // Penalty per minute deviation
    }

    /**
     * Generate interpretations
     */
    private function generateInterpretations(array $mostSegments, array $leastSegments, array $changeSegments): array
    {
        return [
            'public_self' => $this->generatePublicSelfSummary($mostSegments),
            'private_self' => $this->generatePrivateSelfSummary($leastSegments),
            'adaptation' => $this->generateAdaptationSummary($changeSegments),
            'overall' => $this->generateOverallProfile($mostSegments, $leastSegments, $changeSegments)
        ];
    }

    /**
     * Generate public self summary
     */
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

    /**
     * Generate private self summary
     */
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

    /**
     * Generate adaptation summary
     */
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

    /**
     * Generate overall profile
     */
    private function generateOverallProfile(array $mostSegments, array $leastSegments, array $changeSegments): string
    {
        $publicSummary = $this->generatePublicSelfSummary($mostSegments);
        $privateSummary = $this->generatePrivateSelfSummary($leastSegments);
        $adaptationSummary = $this->generateAdaptationSummary($changeSegments);
        
        return $publicSummary . ' ' . $privateSummary . ' ' . $adaptationSummary;
    }

    /**
     * Additional helper methods for comprehensive analysis
     */
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

    private function buildSectionResponsesData($responses): array
    {
        return $responses->map(function($response) {
            return [
                'section' => $response->section_number,
                'most_choice' => $response->most_choice,
                'least_choice' => $response->least_choice,
                'time_spent' => $response->time_spent_seconds,
                'revision_count' => $response->revision_count
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
        $timeConsistency = $this->calculateTimeConsistency($responses);
        $choiceConsistency = $this->calculateChoiceConsistency($responses);
        
        return [
            'time_consistency' => $timeConsistency,
            'choice_consistency' => $choiceConsistency,
            'overall_consistency' => ($timeConsistency + $choiceConsistency) / 2
        ];
    }

    private function calculateTimeConsistency($responses): float
    {
        $times = $responses->pluck('time_spent_seconds');
        $mean = $times->avg();
        $variance = $times->map(fn($time) => pow($time - $mean, 2))->avg();
        $coefficient = $variance > 0 ? sqrt($variance) / $mean : 0;
        
        return max(0, 100 - ($coefficient * 100));
    }

    private function calculateChoiceConsistency($responses): float
    {
        $distribution = [
            'most' => $responses->groupBy('most_choice')->map->count(),
            'least' => $responses->groupBy('least_choice')->map->count()
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

    private function calculateConsistencyScore($responses): float
    {
        $consistencyAnalysis = $this->analyzeConsistency($responses);
        return $consistencyAnalysis['overall_consistency'];
    }

    private function calculateResponseConsistency($responses): float
    {
        return $this->calculateChoiceConsistency($responses);
    }

    private function analyzeTimingPatterns($responses): array
    {
        $times = $responses->pluck('time_spent_seconds');
        
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
        $orderedResponses = $responses->sortBy('response_order');
        $firstHalf = $orderedResponses->take(12)->avg('time_spent_seconds');
        $secondHalf = $orderedResponses->skip(12)->avg('time_spent_seconds');
        
        $change = (($secondHalf - $firstHalf) / $firstHalf) * 100;
        
        return match(true) {
            $change > 20 => 'slowing_down',
            $change < -20 => 'speeding_up',
            default => 'consistent'
        };
    }

    private function performValidityChecks($responses, Disc3DTestSession $session): array
    {
        $flags = ['critical_flags' => [], 'warning_flags' => []];
        $validityConfig = Disc3DConfig::getValidityChecks();
        
        // Check minimum time per section
        $minTime = $validityConfig['minimum_time_per_section'] ?? 3;
        $tooFastCount = $responses->where('time_spent_seconds', '<', $minTime)->count();
        
        if ($tooFastCount > 5) {
            $flags['critical_flags'][] = 'too_many_fast_responses';
        } elseif ($tooFastCount > 2) {
            $flags['warning_flags'][] = 'some_fast_responses';
        }
        
        // Check maximum time per section
        $maxTime = $validityConfig['maximum_time_per_section'] ?? 300;
        $tooSlowCount = $responses->where('time_spent_seconds', '>', $maxTime)->count();
        
        if ($tooSlowCount > 3) {
            $flags['warning_flags'][] = 'some_slow_responses';
        }
        
        // Check response distribution
        $distribution = $responses->groupBy('most_choice')->map->count();
        $maxDimensionCount = $distribution->max();
        
        if ($maxDimensionCount > 18) { // More than 75% of responses
            $flags['critical_flags'][] = 'extreme_response_bias';
        } elseif ($maxDimensionCount > 15) { // More than 62.5% of responses
            $flags['warning_flags'][] = 'response_bias';
        }
        
        return $flags;
    }

    private function validateResultQuality(Disc3DResult $result): void
    {
        if (!$result->is_valid) {
            Log::warning('DISC 3D result marked as invalid', [
                'result_id' => $result->id,
                'validity_flags' => $result->validity_flags
            ]);
        }
        
        if ($result->consistency_score < 50) {
            Log::warning('DISC 3D result has low consistency', [
                'result_id' => $result->id,
                'consistency_score' => $result->consistency_score
            ]);
        }
    }

    /**
     * Generate PDF result
     */
    public function generateResultPdf(Candidate $candidate, Disc3DResult $result)
    {
        $data = compact('candidate', 'result');
        
        return PDF::loadView('disc3d.pdf.result', $data)
            ->setPaper('A4', 'portrait');
    }

    /**
     * Helper methods for generating test codes and device info
     */
    private function generateTestCode(): string
    {
        do {
            $code = 'D3D' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Disc3DTestSession::where('test_code', $code)->exists());
        
        return $code;
    }

    private function generateSessionToken(): string
    {
        return hash('sha256', uniqid() . time() . rand());
    }

    private function extractDeviceInfo(Request $request): array
    {
        return [
            'user_agent' => $request->userAgent(),
            'platform' => $this->detectPlatform($request->userAgent()),
            'browser' => $this->detectBrowser($request->userAgent()),
            'screen_resolution' => $request->input('screen_resolution'),
            'timezone' => $request->input('timezone'),
            'language' => $request->getPreferredLanguage(['en', 'id'])
        ];
    }

    private function detectPlatform(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/Tablet/', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    private function detectBrowser(string $userAgent): string
    {
        if (preg_match('/Chrome/', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/', $userAgent)) return 'Safari';
        if (preg_match('/Edge/', $userAgent)) return 'Edge';
        
        return 'Unknown';
    }
}