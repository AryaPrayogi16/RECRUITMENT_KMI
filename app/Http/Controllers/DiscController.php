<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Disc3DTestSession, 
    Disc3DSection,
    KraeplinTestSession
};
use App\Services\DiscTestService;
use App\Http\Requests\{DiscTestStartRequest, DiscTestSubmissionRequest};
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
            $kraeplinCompleted = $this->checkKraeplinCompletion($candidate);
            
            if (!$kraeplinCompleted) {
                $referrer = request()->header('referer');
                if (!$referrer || !str_contains($referrer, 'kraeplin')) {
                    Log::info('Redirecting to Kraeplin test', ['candidate_id' => $candidate->id]);
                    return redirect()->route('kraeplin.instructions', $candidateCode)
                        ->with('warning', 'Anda harus menyelesaikan Test Kraeplin terlebih dahulu.');
                }
            }
            
            // Check existing DISC completion
            $existingCompletedSession = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            if ($existingCompletedSession) {
                Log::info('DISC 3D already completed', ['session_id' => $existingCompletedSession->id]);
                
                return redirect()->route('job.application.success')
                    ->with('candidate_code', $candidateCode)
                    ->with('success', 'Anda sudah menyelesaikan Test DISC 3D sebelumnya.');
            }
            
            // ✅ UPDATED: Check for incomplete session with simplified structure
            $incompleteSession = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->first();
            
            Log::info('✅ Showing DISC 3D instructions', [
                'candidate_id' => $candidate->id,
                'kraeplin_completed' => $kraeplinCompleted,
                'incomplete_session' => $incompleteSession ? $incompleteSession->id : null
            ]);
            
            return view('disc3d.instructions', [
                'candidate' => $candidate,
                'incompleteSession' => $incompleteSession,
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
     * ✅ UPDATED: Start DISC 3D test with simplified session structure
     */
    public function startTest($candidateCode, DiscTestStartRequest $request)
    {
        Log::info('=== DISC 3D START TEST (Simplified Session) ===', [
            'candidate_code' => $candidateCode,
            'method' => $request->method(),
            'timestamp' => now()
        ]);
        
        try {
            $validated = $request->validated();
            $candidate = Candidate::where('candidate_code', $candidateCode)->first();
            
            if (!$candidate) {
                Log::error('Candidate not found', ['candidate_code' => $candidateCode]);
                return redirect()->route('disc3d.instructions', $candidateCode)
                    ->with('error', 'Kandidat tidak ditemukan.');
            }
            
            // Check for existing completed session
            $existingCompletedSession = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            if ($existingCompletedSession) {
                return redirect()->route('job.application.success')
                    ->with('candidate_code', $candidateCode)
                    ->with('success', 'Test DISC 3D sudah diselesaikan sebelumnya.');
            }
            
            // ✅ UPDATED: Use simplified DiscTestService to create session
            $testMode = $validated['test_mode'] ?? 'fresh_start';
            $session = $this->discTestService->createTestSession(
                $candidate, 
                $request, 
                $testMode === 'fresh_start'
            );
            
            // Get test sections from REAL DATABASE
            $sections = $this->getRealTestSections();
            
            if ($sections->isEmpty()) {
                Log::error('No sections available from database');
                return redirect()->route('disc3d.instructions', $candidateCode)
                    ->with('error', 'Data test tidak tersedia di database. Silakan hubungi administrator.');
            }
            
            // ✅ UPDATED: Get existing responses (for resume functionality)
            $completedResponses = $session->responses()->get();
            $progressPercentage = ($completedResponses->count() / 24) * 100;
            
            Log::info('✅ Test session created with simplified structure', [
                'candidate_code' => $candidateCode,
                'session_id' => $session->id,
                'test_code' => $session->test_code,
                'status' => $session->status,
                'total_sections' => $sections->count(),
                'completed_responses' => $completedResponses->count(),
                'progress' => $progressPercentage
            ]);
            
            return view('disc3d.test', [
                'candidate' => $candidate,
                'session' => $session,
                'sections' => $sections,
                'completedResponses' => $completedResponses,
                'progressPercentage' => $progressPercentage
            ]);
            
        } catch (\Exception $e) {
            Log::error('Start test error', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('disc3d.instructions', $candidateCode)
                ->with('error', 'Terjadi kesalahan saat memulai test: ' . $e->getMessage());
        }
    }

    /**
     * ✅ UPDATED: Submit all responses at once with simplified session
     */
    public function submitTest(DiscTestSubmissionRequest $request)
    {
        Log::info('=== DISC BULK SUBMISSION (Simplified Session) ===', [
            'session_id' => $request->session_id,
            'responses_count' => count($request->responses ?? []),
            'timestamp' => now()
        ]);

        try {
            $validated = $request->validated();
            
            // ✅ UPDATED: Use findOrFail with simplified session structure
            $session = Disc3DTestSession::findOrFail($validated['session_id']);
            
            // ✅ UPDATED: Check session status with simplified structure
            if (!in_array($session->status, ['not_started', 'in_progress'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi test tidak valid atau sudah selesai.'
                ], 404);
            }

            // ✅ Process bulk responses using DiscTestService
            $processedCount = $this->discTestService->processBulkResponses($session, $validated['responses']);
            
            if ($processedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada response yang berhasil diproses'
                ], 500);
            }

            // ✅ Complete test using DiscTestService
            $result = $this->discTestService->completeTestSession($session, $validated['total_duration']);

            Log::info('✅ DISC test completed via simplified session', [
                'session_id' => $session->id,
                'result_id' => $result->id,
                'processed_responses' => $processedCount,
                'primary_type' => $result->primary_type,
                'session_status' => $session->fresh()->status
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
            Log::error('Bulk submission error with simplified session', [
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
     * ✅ Generate and download PDF result
     */
    public function downloadResult($candidateCode)
    {
        try {
            $candidate = Candidate::where('candidate_code', $candidateCode)->firstOrFail();
            $result = $candidate->disc3DResults()->latest()->firstOrFail();
            
            // ✅ Use DiscTestService to generate PDF
            $pdf = $this->discTestService->generateResultPdf($candidate, $result);
            
            $filename = "DISC_3D_Result_{$candidateCode}_{$result->test_code}.pdf";
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('PDF generation error', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Gagal mengunduh hasil test.');
        }
    }

    /**
     * ✅ UPDATED: Resume test functionality with simplified session
     */
    public function resumeTest($candidateCode)
    {
        Log::info('=== DISC 3D RESUME TEST ===', [
            'candidate_code' => $candidateCode,
            'timestamp' => now()
        ]);
        
        try {
            $candidate = Candidate::where('candidate_code', $candidateCode)->firstOrFail();
            
            // ✅ UPDATED: Find incomplete session with simplified structure
            $session = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->first();
            
            if (!$session) {
                return redirect()->route('disc3d.instructions', $candidateCode)
                    ->with('error', 'Tidak ada sesi test yang dapat dilanjutkan.');
            }
            
            // Update session status to in_progress if not_started
            if ($session->status === 'not_started') {
                $session->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Get test sections and existing responses
            $sections = $this->getRealTestSections();
            $completedResponses = $session->responses()->get();
            $progressPercentage = ($completedResponses->count() / 24) * 100;
            
            Log::info('✅ Test session resumed', [
                'session_id' => $session->id,
                'completed_responses' => $completedResponses->count(),
                'progress' => $progressPercentage
            ]);
            
            return view('disc3d.test', [
                'candidate' => $candidate,
                'session' => $session,
                'sections' => $sections,
                'completedResponses' => $completedResponses,
                'progressPercentage' => $progressPercentage,
                'isResume' => true
            ]);
            
        } catch (\Exception $e) {
            Log::error('Resume test error', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('disc3d.instructions', $candidateCode)
                ->with('error', 'Terjadi kesalahan saat melanjutkan test: ' . $e->getMessage());
        }
    }

    /**
     * ✅ UPDATED: Get test session status (API endpoint)
     */
    public function getSessionStatus($sessionId)
    {
        try {
            $session = Disc3DTestSession::findOrFail($sessionId);
            $completedResponses = $session->responses()->count();
            $progressPercentage = ($completedResponses / 24) * 100;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'session_id' => $session->id,
                    'status' => $session->status,
                    'progress' => $progressPercentage,
                    'completed_sections' => $completedResponses,
                    'remaining_sections' => 24 - $completedResponses,
                    'started_at' => $session->started_at?->toDateTimeString(),
                    'duration_so_far' => $session->started_at ? 
                        now()->diffInSeconds($session->started_at) : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session tidak ditemukan'
            ], 404);
        }
    }

    /**
     * ✅ UPDATED: Save single section response (for progressive mode)
     */
    public function saveSectionResponse(Request $request)
    {
        Log::info('=== DISC SINGLE SECTION RESPONSE ===', [
            'session_id' => $request->session_id,
            'section_id' => $request->section_id,
            'timestamp' => now()
        ]);

        try {
            $validated = $request->validate([
                'session_id' => 'required|integer|exists:disc_3d_test_sessions,id',
                'section_id' => 'required|integer|between:1,24',
                'most_choice_id' => 'required|integer',
                'least_choice_id' => 'required|integer|different:most_choice_id',
                'time_spent' => 'required|integer|min:1|max:600'
            ]);

            $session = Disc3DTestSession::findOrFail($validated['session_id']);
            
            // Check if session is still active
            if (!in_array($session->status, ['not_started', 'in_progress'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi test tidak aktif'
                ], 400);
            }

            // Process single section response
            $response = $this->discTestService->processSectionResponse($session, $validated);
            
            // Get updated progress
            $completedResponses = $session->responses()->count();
            $progressPercentage = ($completedResponses / 24) * 100;
            
            Log::info('✅ Single section response saved', [
                'session_id' => $session->id,
                'section_id' => $validated['section_id'],
                'response_id' => $response->id,
                'progress' => $progressPercentage
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Response berhasil disimpan',
                'data' => [
                    'response_id' => $response->id,
                    'section_id' => $validated['section_id'],
                    'progress' => $progressPercentage,
                    'completed_sections' => $completedResponses,
                    'remaining_sections' => 24 - $completedResponses,
                    'is_completed' => $completedResponses >= 24
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Single section response error', [
                'session_id' => $request->session_id ?? 'unknown',
                'section_id' => $request->section_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan response: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ UPDATED: Delete incomplete session
     */
    public function deleteIncompleteSession($candidateCode)
    {
        try {
            $candidate = Candidate::where('candidate_code', $candidateCode)->firstOrFail();
            
            $deletedCount = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->delete();
            
            Log::info('✅ Incomplete sessions deleted', [
                'candidate_code' => $candidateCode,
                'deleted_count' => $deletedCount
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Sesi test yang tidak lengkap berhasil dihapus',
                'deleted_count' => $deletedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Delete incomplete session error', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus sesi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== PRIVATE HELPER METHODS =====

    /**
     * Check if candidate has completed Kraeplin test
     */
    private function checkKraeplinCompletion(Candidate $candidate): bool
    {
        try {
            $kraeplinSession = KraeplinTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            return (bool) $kraeplinSession;
            
        } catch (\Exception $e) {
            Log::warning('Kraeplin check failed, assuming completed', [
                'error' => $e->getMessage()
            ]);
            return true;
        }
    }

    /**
     * ✅ Get test sections from REAL DATABASE ONLY
     */
    private function getRealTestSections()
    {
        try {
            Log::info('🔄 Loading sections from REAL DATABASE...');
            
            $sections = Disc3DSection::with(['choices' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('choice_dimension')
                      ->select('*');
            }])
            ->where('is_active', true)
            ->orderBy('order_number')
            ->get();
            
            Log::info('📊 Database sections loaded', [
                'total_sections' => $sections->count(),
                'sections_with_choices' => $sections->filter(function($section) {
                    return $section->choices && $section->choices->count() >= 4;
                })->count()
            ]);
            
            // ✅ VALIDATION: Minimal 24 sections dengan 4 choices masing-masing
            if ($sections->count() < 24) {
                throw new \Exception("Database hanya memiliki {$sections->count()} sections, dibutuhkan minimal 24");
            }
            
            $sectionsWithInvalidChoices = $sections->filter(function($section) {
                return !$section->choices || $section->choices->count() < 4;
            });
            
            if ($sectionsWithInvalidChoices->count() > 0) {
                $invalidIds = $sectionsWithInvalidChoices->pluck('id')->toArray();
                throw new \Exception("Sections dengan choices tidak lengkap: " . implode(', ', $invalidIds));
            }
            
            // ✅ VALIDATION: Pastikan setiap section punya 4 dimensi (D, I, S, C)
            foreach ($sections as $section) {
                $dimensions = $section->choices->pluck('choice_dimension')->unique()->sort()->values()->toArray();
                $expectedDimensions = ['C', 'D', 'I', 'S'];
                
                if ($dimensions !== $expectedDimensions) {
                    throw new \Exception("Section {$section->id} tidak memiliki 4 dimensi lengkap. Ditemukan: " . implode(', ', $dimensions));
                }
            }
            
            Log::info('✅ Real database sections VALIDATED successfully', [
                'sections_count' => $sections->count(),
                'all_sections_valid' => true,
                'data_source' => 'real_database'
            ]);
            
            return $sections;
            
        } catch (\Exception $e) {
            Log::error('❌ Failed to load from real database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // ❌ TIDAK ADA FALLBACK - Force fix database
            throw new \Exception('Database sections tidak valid: ' . $e->getMessage() . '. Silakan perbaiki data di tabel disc_3d_sections dan disc_3d_section_choices.');
        }
    }
}