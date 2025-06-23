<?php

namespace App\Services;

use App\Models\{
    Candidate,
    DiscQuestion,
    DiscTestSession,
    DiscAnswer,
    DiscTestResult,
    DiscProfileDescription
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiscTestService
{
    /**
     * Create a new DISC test session
     */
    public function createTestSession(Candidate $candidate, string $testType = 'core_16'): DiscTestSession
    {
        DB::beginTransaction();
        
        try {
            // Check if already completed
            $existingCompleted = DiscTestSession::where('candidate_id', $candidate->id)
                ->where('status', DiscTestSession::STATUS_COMPLETED)
                ->first();
                
            if ($existingCompleted) {
                throw new \Exception('Candidate sudah menyelesaikan DISC test sebelumnya.');
            }
            
            // Delete any incomplete sessions
            DiscTestSession::where('candidate_id', $candidate->id)
                ->whereIn('status', [DiscTestSession::STATUS_NOT_STARTED, DiscTestSession::STATUS_IN_PROGRESS])
                ->delete();
            
            // Create new session
            $session = DiscTestSession::create([
                'candidate_id' => $candidate->id,
                'test_code' => $this->generateTestCode(),
                'test_type' => $testType,
                'status' => DiscTestSession::STATUS_IN_PROGRESS,
                'started_at' => now(),
                'language' => 'id'
            ]);
            
            DB::commit();
            
            Log::info('DISC test session created', [
                'candidate_id' => $candidate->id,
                'session_id' => $session->id,
                'test_code' => $session->test_code,
                'test_type' => $testType
            ]);
            
            return $session;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating DISC test session', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get test questions based on test type
     */
    public function getTestQuestions(string $testType = 'core_16')
    {
        $query = DiscQuestion::where('is_active', true)
            ->orderBy('order_number');
            
        if ($testType === DiscTestSession::TYPE_CORE_16) {
            $query->where('is_core_16', true);
        }
        
        return $query->get();
    }

    /**
     * Process and save test answers
     */
    public function processTestAnswers(DiscTestSession $session, array $answers, int $totalDuration): array
    {
        DB::beginTransaction();
        
        try {
            // Verify session status
            if ($session->status !== DiscTestSession::STATUS_IN_PROGRESS) {
                throw new \Exception('Test session tidak valid atau sudah selesai');
            }
            
            // Validate and process answers
            $processedCount = $this->processBulkAnswers($session, $answers);
            
            // Update session status
            $session->update([
                'status' => DiscTestSession::STATUS_COMPLETED,
                'completed_at' => now(),
                'total_duration_seconds' => $totalDuration
            ]);
            
            // Calculate test results
            $result = $this->calculateTestResults($session);
            
            DB::commit();
            
            Log::info('DISC test completed successfully', [
                'session_id' => $session->id,
                'candidate_id' => $session->candidate_id,
                'processed_answers' => $processedCount,
                'result_id' => $result->id
            ]);
            
            return [
                'success' => true,
                'result' => $result,
                'processed_answers' => $processedCount
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error processing DISC test answers', [
                'session_id' => $session->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process bulk answers with improved validation
     */
    private function processBulkAnswers(DiscTestSession $session, array $answers): int
    {
        $bulkAnswers = [];
        $timestamp = now();
        $processedCount = 0;
        $errors = [];
        
        foreach ($answers as $index => $answer) {
            try {
                // Validate answer structure
                if (!isset($answer['question_id'], $answer['response'], $answer['time_spent'])) {
                    $errors[] = "Answer at index {$index} is missing required fields";
                    continue;
                }

                // Get question details
                $question = DiscQuestion::find($answer['question_id']);
                if (!$question) {
                    $errors[] = "Question ID {$answer['question_id']} not found";
                    continue;
                }
                
                // Validate response value
                $response = (int)$answer['response'];
                if ($response < 1 || $response > 5) {
                    $errors[] = "Invalid response value {$response} for question {$answer['question_id']}";
                    continue;
                }
                
                // Calculate weighted scores
                $weightedScores = $this->calculateWeightedScores($question, $response);
                
                $bulkAnswers[] = [
                    'test_session_id' => $session->id,
                    'question_id' => $question->id,
                    'item_code' => $question->item_code,
                    'response' => $response,
                    'weighted_score_d' => $weightedScores['D'],
                    'weighted_score_i' => $weightedScores['I'],
                    'weighted_score_s' => $weightedScores['S'],
                    'weighted_score_c' => $weightedScores['C'],
                    'time_spent_seconds' => max(0, (int)($answer['time_spent'] ?? 0)),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
                
                $processedCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Error processing answer at index {$index}: " . $e->getMessage();
                Log::error('Error processing individual DISC answer', [
                    'answer' => $answer,
                    'session_id' => $session->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Check if we have valid answers to process
        if (empty($bulkAnswers)) {
            throw new \Exception('No valid answers to process. Errors: ' . implode(', ', $errors));
        }
        
        // Log any errors found
        if (!empty($errors)) {
            Log::warning('DISC answer processing errors', [
                'session_id' => $session->id,
                'errors' => $errors,
                'processed_count' => $processedCount,
                'total_submitted' => count($answers)
            ]);
        }
        
        // Clear existing answers and insert new ones
        DiscAnswer::where('test_session_id', $session->id)->delete();
        
        // Insert in chunks for better performance
        $chunks = array_chunk($bulkAnswers, 50);
        foreach ($chunks as $chunk) {
            DiscAnswer::insert($chunk);
        }
        
        return $processedCount;
    }

    /**
     * Calculate weighted scores for a question and response
     */
    private function calculateWeightedScores(DiscQuestion $question, int $response): array
    {
        return [
            'D' => round(($question->weight_d ?? 0) * $response, 4),
            'I' => round(($question->weight_i ?? 0) * $response, 4),
            'S' => round(($question->weight_s ?? 0) * $response, 4),
            'C' => round(($question->weight_c ?? 0) * $response, 4)
        ];
    }

    /**
     * Calculate DISC test results
     */
    public function calculateTestResults(DiscTestSession $session): DiscTestResult
    {
        $answers = DiscAnswer::where('test_session_id', $session->id)->get();
        
        if ($answers->isEmpty()) {
            throw new \Exception('No answers found for this test session');
        }
        
        // Calculate raw scores (sum of all weighted scores)
        $rawScores = [
            'D' => $answers->sum(function($answer) { 
                return (float) ($answer->getAttributes()['weighted_score_d'] ?? 0); 
            }),
            'I' => $answers->sum(function($answer) { 
                return (float) ($answer->getAttributes()['weighted_score_i'] ?? 0); 
            }),
            'S' => $answers->sum(function($answer) { 
                return (float) ($answer->getAttributes()['weighted_score_s'] ?? 0); 
            }),
            'C' => $answers->sum(function($answer) { 
                return (float) ($answer->getAttributes()['weighted_score_c'] ?? 0); 
            })
        ];
        
        // Calculate maximum possible scores
        $maxScores = $this->calculateMaxScores($session->test_type);
        
        // Calculate percentages (0-100)
        $percentages = [];
        foreach (['D', 'I', 'S', 'C'] as $dimension) {
            $percentages[$dimension] = $maxScores[$dimension] > 0 
                ? ($rawScores[$dimension] / $maxScores[$dimension]) * 100 
                : 0;
        }
        
        // Determine primary and secondary types
        arsort($percentages);
        $sortedDimensions = array_keys($percentages);
        $primaryType = $sortedDimensions[0];
        $secondaryType = $sortedDimensions[1];
        
        // Calculate segments (1-7 scale)
        $segments = [];
        foreach (['D', 'I', 'S', 'C'] as $dimension) {
            $segments[$dimension] = $this->calculateSegment($percentages[$dimension]);
        }
        
        // Generate profile data
        $profileSummary = $this->generateProfileSummary($primaryType, $secondaryType, $percentages);
        $fullProfile = $this->generateFullProfile($percentages, $segments);
        
        // Create and save result
        $result = DiscTestResult::create([
            'test_session_id' => $session->id,
            'candidate_id' => $session->candidate_id,
            'd_raw_score' => $rawScores['D'],
            'i_raw_score' => $rawScores['I'],
            's_raw_score' => $rawScores['S'],
            'c_raw_score' => $rawScores['C'],
            'd_max_score' => $maxScores['D'],
            'i_max_score' => $maxScores['I'],
            's_max_score' => $maxScores['S'],
            'c_max_score' => $maxScores['C'],
            'd_percentage' => round($percentages['D'], 2),
            'i_percentage' => round($percentages['I'], 2),
            's_percentage' => round($percentages['S'], 2),
            'c_percentage' => round($percentages['C'], 2),
            'primary_type' => $primaryType,
            'primary_percentage' => round($percentages[$primaryType], 2),
            'secondary_type' => $secondaryType,
            'secondary_percentage' => round($percentages[$secondaryType], 2),
            'd_segment' => $segments['D'],
            'i_segment' => $segments['I'],
            's_segment' => $segments['S'],
            'c_segment' => $segments['C'],
            'graph_data' => [
                'percentages' => $percentages,
                'segments' => $segments,
                'raw_scores' => $rawScores,
                'max_scores' => $maxScores
            ],
            'profile_summary' => $profileSummary,
            'full_profile' => $fullProfile
        ]);
        
        Log::info('DISC test results calculated', [
            'session_id' => $session->id,
            'result_id' => $result->id,
            'primary_type' => $primaryType,
            'primary_percentage' => round($percentages[$primaryType], 2)
        ]);
        
        return $result;
    }

    /**
     * Calculate maximum possible scores for each dimension
     */
    private function calculateMaxScores(string $testType): array
    {
        $questions = $this->getTestQuestions($testType);
        $maxScores = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
        
        foreach ($questions as $question) {
            // Maximum response is 5 (Strongly Agree)
            $maxScores['D'] += max(0, ($question->weight_d ?? 0) * 5);
            $maxScores['I'] += max(0, ($question->weight_i ?? 0) * 5);
            $maxScores['S'] += max(0, ($question->weight_s ?? 0) * 5);
            $maxScores['C'] += max(0, ($question->weight_c ?? 0) * 5);
        }
        
        return $maxScores;
    }

    /**
     * Calculate segment (1-7) based on percentage
     */
    private function calculateSegment(float $percentage): int
    {
        if ($percentage >= 86) return 7;
        if ($percentage >= 72) return 6;
        if ($percentage >= 58) return 5;
        if ($percentage >= 43) return 4;
        if ($percentage >= 29) return 3;
        if ($percentage >= 15) return 2;
        return 1;
    }

    /**
     * Generate profile summary
     */
    private function generateProfileSummary(string $primaryType, string $secondaryType, array $percentages): string
    {
        $profiles = [
            'D' => 'Dominan - Tegas, berorientasi hasil, suka mengambil keputusan',
            'I' => 'Influence - Antusias, sosial, komunikatif, optimis',
            'S' => 'Steadiness - Sabar, stabil, loyal, bekerja sama',
            'C' => 'Conscientiousness - Teliti, analitis, sistematis, berkualitas'
        ];
        
        $primaryDesc = $profiles[$primaryType] ?? '';
        $secondaryDesc = $profiles[$secondaryType] ?? '';
        
        return "Tipe kepribadian utama: {$primaryType} ({$primaryDesc}). " .
               "Tipe sekunder: {$secondaryType} ({$secondaryDesc}).";
    }

    /**
     * Generate full profile analysis
     */
    private function generateFullProfile(array $percentages, array $segments): array
    {
        return [
            'analysis' => [
                'dominant_traits' => $this->getDominantTraits($percentages),
                'work_style' => $this->getWorkStyle($percentages),
                'communication_style' => $this->getCommunicationStyle($percentages),
                'strengths' => $this->getStrengths($percentages),
                'development_areas' => $this->getDevelopmentAreas($percentages)
            ],
            'scores' => $percentages,
            'segments' => $segments,
            'recommendations' => $this->getRecommendations($percentages)
        ];
    }

    /**
     * Generate unique test code
     */
    private function generateTestCode(): string
    {
        do {
            $code = 'DISC' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (DiscTestSession::where('test_code', $code)->exists());
        
        return $code;
    }

    /**
     * Helper methods for trait analysis
     */
    private function getDominantTraits(array $percentages): array
    {
        $traits = [];
        foreach ($percentages as $dimension => $percentage) {
            if ($percentage > 60) {
                $traits = array_merge($traits, $this->getDimensionTraits($dimension));
            }
        }
        return array_unique($traits);
    }

    private function getDimensionTraits(string $dimension): array
    {
        $traits = [
            'D' => ['Tegas', 'Kompetitif', 'Berorientasi hasil', 'Suka tantangan'],
            'I' => ['Antusias', 'Sosial', 'Optimis', 'Komunikatif'],
            'S' => ['Sabar', 'Loyal', 'Stabil', 'Mendukung'],
            'C' => ['Teliti', 'Analitis', 'Sistematis', 'Berkualitas']
        ];
        
        return $traits[$dimension] ?? [];
    }

    private function getWorkStyle(array $percentages): string
    {
        $dominantDimension = array_keys($percentages, max($percentages))[0];
        
        $workStyles = [
            'D' => 'Cepat, independen, fokus pada hasil dan pencapaian target',
            'I' => 'Kolaboratif, kreatif, suka bekerja dengan orang lain',
            'S' => 'Konsisten, sabar, metodis, suka rutinitas yang jelas',
            'C' => 'Hati-hati, detail-oriented, sistematis, mengutamakan kualitas'
        ];
        
        return $workStyles[$dominantDimension] ?? 'Seimbang dalam berbagai gaya kerja';
    }

    private function getCommunicationStyle(array $percentages): string
    {
        $dominantDimension = array_keys($percentages, max($percentages))[0];
        
        $commStyles = [
            'D' => 'Langsung, singkat, fokus pada hasil dan tindakan',
            'I' => 'Antusias, ekspresif, suka diskusi dan brainstorming',
            'S' => 'Mendengarkan dengan baik, diplomatis, menghindari konflik',
            'C' => 'Detail, faktual, logical, suka data dan analisis'
        ];
        
        return $commStyles[$dominantDimension] ?? 'Adaptif dalam berbagai situasi komunikasi';
    }

private function getStrengths(array $percentages): array
{
    $strengths = [];
    
    // Urutkan dari percentage tertinggi
    arsort($percentages);
    $topDimensions = array_slice($percentages, 0, 2, true);
    
    // Ambil strengths dari 2 dimensi teratas dengan threshold yang lebih rendah
    foreach ($topDimensions as $dimension => $percentage) {
        if ($percentage > 25) { // Threshold diturunkan dari 50% ke 25%
            $dimensionStrengths = $this->getDimensionStrengths($dimension);
            $strengths = array_merge($strengths, $dimensionStrengths);
        }
    }
    
    // Fallback: Selalu ambil dari dimensi tertinggi jika kosong
    if (empty($strengths)) {
        $highestDimension = array_key_first($percentages);
        $strengths = $this->getDimensionStrengths($highestDimension);
    }
    
    // Log untuk monitoring
    Log::info('DISC strengths calculated', [
        'percentages' => $percentages,
        'strengths_count' => count(array_unique($strengths)),
        'strengths' => array_unique($strengths)
    ]);
    
    return array_unique($strengths);
}
    private function getDimensionStrengths(string $dimension): array
    {
        $strengths = [
            'D' => ['Kepemimpinan', 'Pengambilan keputusan', 'Orientasi hasil', 'Inisiatif'],
            'I' => ['Komunikasi', 'Motivasi tim', 'Networking', 'Kreativitas'],
            'S' => ['Kerja tim', 'Konsistensi', 'Keandalan', 'Kesabaran'],
            'C' => ['Analisis', 'Perencanaan', 'Kontrol kualitas', 'Pemecahan masalah']
        ];
        
        return $strengths[$dimension] ?? [];
    }

    private function getDevelopmentAreas(array $percentages): array
    {
        $developmentAreas = [];
        foreach ($percentages as $dimension => $percentage) {
            if ($percentage < 30) {
                $developmentAreas = array_merge($developmentAreas, $this->getDimensionDevelopmentAreas($dimension));
            }
        }
        return array_unique($developmentAreas);
    }

    private function getDimensionDevelopmentAreas(string $dimension): array
    {
        $areas = [
            'D' => ['Meningkatkan assertiveness', 'Mengembangkan kepemimpinan', 'Berani mengambil risiko'],
            'I' => ['Meningkatkan kemampuan presentasi', 'Mengembangkan network', 'Komunikasi persuasif'],
            'S' => ['Meningkatkan kesabaran', 'Mengembangkan kerja tim', 'Konsistensi dalam kinerja'],
            'C' => ['Meningkatkan perhatian pada detail', 'Mengembangkan analisis', 'Perencanaan sistematis']
        ];
        
        return $areas[$dimension] ?? [];
    }

    private function getRecommendations(array $percentages): array
    {
        $dominantDimension = array_keys($percentages, max($percentages))[0];
        
        $recommendations = [
            'D' => [
                'roles' => ['Leader', 'Manager', 'Decision Maker', 'Entrepreneur'],
                'environments' => ['Fast-paced', 'Competitive', 'Results-oriented', 'Independent'],
                'development' => ['Patience', 'Listening skills', 'Team collaboration']
            ],
            'I' => [
                'roles' => ['Sales', 'Marketing', 'Public Relations', 'Team Builder'],
                'environments' => ['Social', 'Collaborative', 'Creative', 'People-focused'],
                'development' => ['Attention to detail', 'Follow-through', 'Time management']
            ],
            'S' => [
                'roles' => ['Support', 'Team Player', 'Customer Service', 'Coordinator'],
                'environments' => ['Stable', 'Supportive', 'Team-oriented', 'Structured'],
                'development' => ['Assertiveness', 'Change adaptability', 'Decision speed']
            ],
            'C' => [
                'roles' => ['Analyst', 'Quality Control', 'Researcher', 'Specialist'],
                'environments' => ['Detail-oriented', 'Analytical', 'Quality-focused', 'Systematic'],
                'development' => ['Flexibility', 'Speed', 'Risk-taking', 'Social interaction']
            ]
        ];
        
        return $recommendations[$dominantDimension] ?? [];
    }

    private function safeColumnSum($collection, $column): float
{
    $total = 0;
    foreach ($collection as $item) {
        $value = $item->getAttributes()[$column] ?? 0;
        
        if (is_array($value)) {
            Log::error("Column {$column} contains array, expected decimal", [
                'item_id' => $item->id,
                'value' => $value
            ]);
            continue;
        }
        
        $total += (float) $value;
    }
    
    return $total;
}





}