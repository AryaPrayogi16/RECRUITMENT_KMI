<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate,
    KraeplinQuestion,
    KraeplinTestSession,
    KraeplinAnswer,
    KraeplinTestResult
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KraeplinController extends Controller
{
    /**
     * Show test instructions page
     */
    public function showInstructions($candidateCode)
    {
        $candidate = Candidate::where('candidate_code', $candidateCode)->firstOrFail();
        
        // Check if candidate already completed test
        $existingSession = KraeplinTestSession::where('candidate_id', $candidate->id)
            ->where('status', 'completed')
            ->first();
            
        if ($existingSession) {
            // UPDATED: Redirect to DISC test instead of success page
            return redirect()->route('disc3d.instructions', $candidateCode)
                ->with('success', 'Test Kraeplin sudah selesai. Silakan lanjutkan dengan Test DISC.');
        }
        
        return view('kraeplin.instructions', compact('candidate'));
    }

    /**
     * Start the test and show test interface
     */
    public function startTest($candidateCode)
    {
        $candidate = Candidate::where('candidate_code', $candidateCode)->firstOrFail();
        
        try {
            DB::beginTransaction();
            
            // Double check if already completed
            $existingCompleted = KraeplinTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            if ($existingCompleted) {
                DB::rollback();
                // UPDATED: Redirect to DISC test instead of success page
                return redirect()->route('disc3d.instructions', $candidateCode)
                    ->with('success', 'Test Kraeplin sudah selesai. Silakan lanjutkan dengan Test DISC.');
            }
            
            // Delete any incomplete sessions
            KraeplinTestSession::where('candidate_id', $candidate->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->delete();
            
            // Create new test session
            $session = KraeplinTestSession::create([
                'candidate_id' => $candidate->id,
                'test_code' => $this->generateTestCode(),
                'status' => 'in_progress',
                'started_at' => now()
            ]);
            
            // Get test questions organized by columns
            $questions = $this->getTestQuestions();
            
            DB::commit();
            
            Log::info('Kraeplin test started', [
                'candidate_code' => $candidateCode,
                'session_id' => $session->id,
                'test_code' => $session->test_code
            ]);
            
            return view('kraeplin.test', compact('candidate', 'session', 'questions'));
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error starting kraeplin test', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('kraeplin.instructions', $candidateCode)
                ->with('error', 'Terjadi kesalahan saat memulai test. Silakan coba lagi.');
        }
    }

    /**
     * BULK SUBMIT - Submit all answers at once from localStorage
     */
    public function submitTest(Request $request)
    {
        try {
            Log::info('Kraeplin test submission started', [
                'request_data' => $request->all()
            ]);

            // Log incoming data untuk debugging
            Log::info('Raw request data', [
                'session_id' => $request->session_id,
                'answers_count' => count($request->answers ?? []),
                'sample_answer' => $request->answers[0] ?? null,
                'total_duration' => $request->total_duration
            ]);

            // Validate request dengan pesan error yang lebih jelas
            $validated = $request->validate([
                'session_id' => 'required|integer|exists:kraeplin_test_sessions,id',
                'answers' => 'required|array|min:1',
                'answers.*.column' => 'required|numeric|between:1,32',
                'answers.*.row' => 'required|numeric|between:1,26', 
                'answers.*.user_answer' => 'required|numeric|between:0,9',
                'answers.*.time_spent' => 'required|numeric|between:0,15',
                'total_duration' => 'required|integer|min:1'
            ], [
                'answers.*.column.between' => 'Kolom harus antara 1-32',
                'answers.*.row.between' => 'Row harus antara 1-26',
                'answers.*.user_answer.between' => 'Jawaban harus antara 0-9',
                'answers.*.time_spent.between' => 'Waktu harus antara 0-15 detik'
            ]);

            DB::beginTransaction();
            
            $session = KraeplinTestSession::findOrFail($validated['session_id']);
            
            // Verify session status
            if ($session->status !== 'in_progress') {
                DB::rollback();
                Log::warning('Invalid session status', [
                    'session_id' => $session->id,
                    'status' => $session->status
                ]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Test session tidak valid atau sudah selesai'
                ], 400);
            }
            
            Log::info('Processing bulk answer submission', [
                'session_id' => $session->id,
                'candidate_code' => $session->candidate->candidate_code,
                'answers_count' => count($validated['answers']),
                'total_duration' => $validated['total_duration']
            ]);
            
            // Process all answers
            $processedCount = $this->processBulkAnswers($session, $validated['answers']);
            
            // Update session status
            $session->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_duration_seconds' => $validated['total_duration']
            ]);
            
            // Calculate and save test results
            $this->calculateTestResults($session);
            
            DB::commit();
            
            Log::info('Kraeplin test completed successfully', [
                'session_id' => $session->id,
                'candidate_code' => $session->candidate->candidate_code,
                'processed_answers' => $processedCount
            ]);
            
            // UPDATED: Redirect to DISC test instead of success page
            return response()->json([
                'success' => true,
                'message' => 'Test Kraeplin berhasil diselesaikan. Lanjutkan dengan Test DISC.',
                'redirect_url' => route('disc3d.instructions', ['candidateCode' => $session->candidate->candidate_code])
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in kraeplin test submission', [
                'errors' => $e->errors(),
                'validator_messages' => $e->validator->getMessageBag()->toArray(),
                'failed_rules' => $e->validator->failed(),
                'request_data' => [
                    'session_id' => $request->session_id,
                    'answers_count' => count($request->answers ?? []),
                    'first_few_answers' => array_slice($request->answers ?? [], 0, 3),
                    'total_duration' => $request->total_duration
                ]
            ]);
            
            $errorMessage = 'Data tidak valid: ';
            $errorDetails = [];
            foreach ($e->errors() as $field => $messages) {
                $errorDetails[] = $field . ': ' . implode(', ', $messages);
            }
            $errorMessage .= implode('; ', $errorDetails);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'validation_errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error submitting kraeplin test', [
                'session_id' => $request->session_id ?? 'unknown',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan saat menyimpan jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process bulk answers from frontend
     */
    private function processBulkAnswers($session, $answers)
    {
        $bulkAnswers = [];
        $timestamp = now();
        $processedCount = 0;
        
        foreach ($answers as $answer) {
            try {
                // Get question values from database
                $question1 = KraeplinQuestion::where('column_number', $answer['column'])
                    ->where('row_number', $answer['row'])
                    ->first();
                    
                $question2 = KraeplinQuestion::where('column_number', $answer['column'])
                    ->where('row_number', $answer['row'] + 1)
                    ->first();
                    
                if (!$question1 || !$question2) {
                    Log::warning('Question not found', [
                        'column' => $answer['column'],
                        'row' => $answer['row'],
                        'session_id' => $session->id
                    ]);
                    continue;
                }
                
                // Calculate correct answer (last digit of sum)
                $correctAnswer = ($question1->value + $question2->value) % 10;
                $isCorrect = (int)$answer['user_answer'] === $correctAnswer;
                
                $bulkAnswers[] = [
                    'test_session_id' => $session->id,
                    'column_number' => $answer['column'],
                    'row_number' => $answer['row'],
                    'question_value_1' => $question1->value,
                    'question_value_2' => $question2->value,
                    'correct_answer' => $correctAnswer,
                    'user_answer' => $answer['user_answer'],
                    'is_correct' => $isCorrect,
                    'time_spent_seconds' => $answer['time_spent'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
                
                $processedCount++;
                
            } catch (\Exception $e) {
                Log::error('Error processing individual answer', [
                    'answer' => $answer,
                    'session_id' => $session->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // BULK INSERT for better performance
        if (!empty($bulkAnswers)) {
            try {
                // Use chunks to avoid memory issues
                $chunks = array_chunk($bulkAnswers, 100);
                foreach ($chunks as $chunk) {
                    KraeplinAnswer::insert($chunk);
                }
                
                Log::info('Bulk answers inserted successfully', [
                    'session_id' => $session->id,
                    'count' => count($bulkAnswers)
                ]);
            } catch (\Exception $e) {
                Log::error('Error inserting bulk answers', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                    'answers_count' => count($bulkAnswers)
                ]);
                throw $e;
            }
        }
        
        return $processedCount;
    }

    /**
     * Calculate test results and performance metrics
     */
    private function calculateTestResults($session)
    {
        try {
            $answers = KraeplinAnswer::where('test_session_id', $session->id)->get();
            
            Log::info('Calculating test results', [
                'session_id' => $session->id,
                'answers_count' => $answers->count()
            ]);
            
            // Basic calculations
            $totalAnswered = $answers->count();
            $totalCorrect = $answers->where('is_correct', true)->count();
            $totalWrong = $totalAnswered - $totalCorrect;
            $accuracyPercentage = $totalAnswered > 0 ? ($totalCorrect / $totalAnswered) * 100 : 0;
            
            // Column-wise performance for charts (32 columns)
            $columnAccuracy = [];
            $columnSpeed = [];
            $columnCorrectCount = [];
            $columnAnsweredCount = [];
            $columnAvgTime = [];
            
            for ($col = 1; $col <= 32; $col++) {
                $colAnswers = $answers->where('column_number', $col);
                $colTotal = $colAnswers->count();
                $colCorrect = $colAnswers->where('is_correct', true)->count();
                $colAvgTime = $colAnswers->avg('time_spent_seconds') ?: 0;
                
                // Store data for charts
                $columnAccuracy[] = $colTotal > 0 ? round(($colCorrect / $colTotal) * 100, 2) : 0;
                $columnSpeed[] = $colTotal; // Questions answered per column
                $columnCorrectCount[] = $colCorrect;
                $columnAnsweredCount[] = $colTotal;
                $columnAvgTime[] = round($colAvgTime, 2);
            }
            
            // Calculate overall performance metrics
            $avgSpeedPerColumn = $totalAnswered > 0 ? $totalAnswered / 32 : 0;
            $overallScore = $this->calculateOverallScore($accuracyPercentage, $avgSpeedPerColumn, $totalAnswered);
            $performanceCategory = $this->determinePerformanceCategory($overallScore);
            
            // Save test results
            $result = KraeplinTestResult::create([
                'test_session_id' => $session->id,
                'candidate_id' => $session->candidate_id,
                'total_questions_answered' => $totalAnswered,
                'total_correct_answers' => $totalCorrect,
                'total_wrong_answers' => $totalWrong,
                'accuracy_percentage' => round($accuracyPercentage, 2),
                'average_speed_per_column' => round($avgSpeedPerColumn, 2),
                'overall_score' => round($overallScore, 2),
                'performance_category' => $performanceCategory,
                'column_accuracy' => $columnAccuracy,
                'column_speed' => $columnSpeed,
                'column_correct_count' => $columnCorrectCount,
                'column_answered_count' => $columnAnsweredCount,
                'column_avg_time' => $columnAvgTime
            ]);
            
            Log::info('Test results calculated and saved', [
                'session_id' => $session->id,
                'result_id' => $result->id,
                'total_answered' => $totalAnswered,
                'accuracy' => round($accuracyPercentage, 2),
                'overall_score' => round($overallScore, 2),
                'category' => $performanceCategory
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error calculating test results', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate overall score based on multiple factors
     */
    private function calculateOverallScore($accuracy, $speed, $totalAnswered)
    {
        // Weights for different components
        $accuracyWeight = 0.6;   // 60% - most important
        $speedWeight = 0.3;      // 30% - second important  
        $completionWeight = 0.1;  // 10% - least important
        
        // Normalize components (0-100)
        $normalizedAccuracy = $accuracy;
        $normalizedSpeed = min(100, ($speed / 20) * 100); // Max 20 per column = 100%
        $normalizedCompletion = min(100, ($totalAnswered / 832) * 100); // Max 832 total questions = 100%
        
        $overallScore = ($normalizedAccuracy * $accuracyWeight) + 
                       ($normalizedSpeed * $speedWeight) + 
                       ($normalizedCompletion * $completionWeight);
        
        return max(0, min(100, $overallScore)); // Ensure score is between 0-100
    }

    /**
     * Determine performance category based on overall score
     */
    private function determinePerformanceCategory($overallScore)
    {
        if ($overallScore >= 85) return 'excellent';
        if ($overallScore >= 75) return 'good';
        if ($overallScore >= 65) return 'average';
        if ($overallScore >= 50) return 'below_average';
        return 'poor';
    }

    /**
     * Get test questions organized by columns
     */
    private function getTestQuestions()
    {
        $questions = KraeplinQuestion::orderBy('column_number')
            ->orderBy('row_number')
            ->get()
            ->groupBy('column_number');
            
        $organizedQuestions = [];
        
        foreach ($questions as $columnNumber => $columnQuestions) {
            $pairs = [];
            $values = $columnQuestions->pluck('value')->toArray();
            
            // Create pairs for addition (row 1+2, 2+3, 3+4, etc.)
            // Each column has 27 values, so 26 pairs (questions)
            for ($i = 0; $i < count($values) - 1; $i++) {
                $pairs[] = [
                    'row_number' => $i + 1,
                    'value1' => $values[$i],
                    'value2' => $values[$i + 1],
                    'correct_answer' => ($values[$i] + $values[$i + 1]) % 10
                ];
            }
            
            $organizedQuestions[$columnNumber] = $pairs;
        }
        
        return $organizedQuestions;
    }

    /**
     * Generate unique test code
     */
    private function generateTestCode()
    {
        do {
            $code = 'KT' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (KraeplinTestSession::where('test_code', $code)->exists());
        
        return $code;
    }
}