<?php

namespace App\Http\Controllers;

use App\Models\{Candidate, DiscTestResult, DiscProfileDescription, KraeplinTestSession, DiscTestSession};
use App\Services\DiscTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiscController extends Controller
{
    protected $discTestService;

    public function __construct(DiscTestService $discTestService)
    {
        $this->discTestService = $discTestService;
    }

    /**
     * Show DISC test instructions page
     */
    public function showInstructions($candidateCode)
    {
        try {
            $candidate = Candidate::where('candidate_code', $candidateCode)->firstOrFail();
            
            // Check if candidate completed Kraeplin test first
            $kraeplinSession = KraeplinTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            if (!$kraeplinSession) {
                return redirect()->route('kraeplin.instructions', $candidateCode)
                    ->with('warning', 'Anda harus menyelesaikan Test Kraeplin terlebih dahulu.');
            }
            
            // Check if candidate already completed DISC test
            $existingSession = DiscTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            if ($existingSession) {
                return redirect()->route('job.application.success', ['candidate_code' => $candidateCode])
                    ->with('warning', 'Anda sudah menyelesaikan Test DISC sebelumnya.');
            }
            
            return view('disc.instructions', compact('candidate'));
            
        } catch (\Exception $e) {
            Log::error('Error in DISC instructions', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Start the DISC test using service
     */
    public function startTest($candidateCode, Request $request)
    {
        $candidate = Candidate::where('candidate_code', $candidateCode)->firstOrFail();
        $testType = $request->input('test_type', 'core_16');
        
        // Validate test type
        if (!in_array($testType, ['core_16', 'full_50'])) {
            $testType = 'core_16';
        }
        
        try {
            // Create test session using service
            $session = $this->discTestService->createTestSession($candidate, $testType);
            
            // Get test questions using service
            $questions = $this->discTestService->getTestQuestions($testType);
            
            // Calculate questions count for view
            $questionsCount = $questions->count();
            
            Log::info('DISC test started successfully', [
                'candidate_code' => $candidateCode,
                'session_id' => $session->id,
                'test_code' => $session->test_code,
                'questions_count' => $questionsCount
            ]);
            
            return view('disc.test', compact('candidate', 'session', 'questions', 'questionsCount'));
            
        } catch (\Exception $e) {
            Log::error('Error starting DISC test', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('disc.instructions', $candidateCode)
                ->with('error', 'Terjadi kesalahan saat memulai test: ' . $e->getMessage());
        }
    }

    /**
     * Submit DISC test answers using service
     */
    public function submitTest(Request $request)
    {
        try {
            Log::info('DISC test submission started', [
                'session_id' => $request->session_id,
                'answers_count' => count($request->answers ?? [])
            ]);

            // Validate request
            $validated = $request->validate([
                'session_id' => 'required|integer|exists:disc_test_sessions,id',
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer|exists:disc_questions,id',
                'answers.*.response' => 'required|integer|between:1,5',
                'answers.*.time_spent' => 'required|integer|min:0',
                'total_duration' => 'required|integer|min:1'
            ], [
                'answers.*.response.between' => 'Jawaban harus antara 1-5 (Strongly Disagree - Strongly Agree)',
                'answers.*.question_id.exists' => 'Question ID tidak valid'
            ]);

            // Find the session
            $session = DiscTestSession::findOrFail($validated['session_id']);
            
            // Process answers using service
            $result = $this->discTestService->processTestAnswers(
                $session,
                $validated['answers'],
                $validated['total_duration']
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Test DISC berhasil diselesaikan',
                'redirect_url' => route('job.application.success', [
                    'candidate_code' => $session->candidate->candidate_code
                ])
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in DISC test submission', [
                'errors' => $e->errors(),
                'session_id' => $request->session_id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', array_map(function($messages) {
                    return implode(', ', $messages);
                }, $e->errors())),
                'validation_errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error submitting DISC test', [
                'session_id' => $request->session_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan saat menyimpan jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show DISC test results (for HR)
     */
    public function showResult($candidateCode)
    {
        $candidate = Candidate::where('candidate_code', $candidateCode)->firstOrFail();
        
        $discResult = DiscTestResult::where('candidate_id', $candidate->id)
            ->with('testSession')
            ->latest()
            ->first();
            
        if (!$discResult) {
            return redirect()->route('disc.instructions', $candidateCode)
                ->with('error', 'Hasil test DISC tidak ditemukan.');
        }
        
        // Get profile descriptions
        $profiles = DiscProfileDescription::all()->keyBy('dimension');
        
        return view('disc.result', compact('candidate', 'discResult', 'profiles'));
    }

    
}