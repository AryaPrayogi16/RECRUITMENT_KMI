<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Disc3DTestSession, 
    Disc3DSection,
    KraeplinTestSession
};
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
     * ✅ Start DISC 3D test - using DiscTestService
     */
    public function startTest($candidateCode, Request $request)
    {
        Log::info('=== DISC 3D START TEST (via Service) ===', [
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
            
            // Check for existing completed session
            $existingCompletedSession = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            if ($existingCompletedSession) {
                return redirect()->route('job.application.success')
                    ->with('candidate_code', $candidateCode)
                    ->with('success', 'Test DISC 3D sudah diselesaikan sebelumnya.');
            }
            
            // ✅ Use DiscTestService to create session (fresh start mode)
            $session = $this->discTestService->createTestSession($candidate, $request, true);
            
            // Get test sections
            $sections = $this->getCompleteTestSections();
            
            if ($sections->isEmpty()) {
                Log::error('No sections available');
                return redirect()->route('disc3d.instructions', $candidateCode)
                    ->with('error', 'Data test tidak tersedia. Silakan hubungi administrator.');
            }
            
            Log::info('✅ Test session created via DiscTestService', [
                'candidate_code' => $candidateCode,
                'session_id' => $session->id,
                'test_code' => $session->test_code,
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
            Log::error('Start test error', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('disc3d.instructions', $candidateCode)
                ->with('error', 'Terjadi kesalahan saat memulai test: ' . $e->getMessage());
        }
    }

    /**
     * ✅ BULK SUBMIT: Submit all responses at once - using DiscTestService
     */
    public function submitTest(Request $request)
    {
        Log::info('=== DISC BULK SUBMISSION (via Service) ===', [
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

            $session = Disc3DTestSession::findOrFail($validated['session_id']);
            
            if ($session->status !== 'in_progress') {
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

            Log::info('✅ DISC test completed via DiscTestService', [
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Bulk submission error via service', [
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
     * Get complete test sections (should be moved to service later)
     */
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

    /**
     * Create dummy sections if database sections are not available
     */
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

    /**
     * Generate choice weights for dummy data
     */
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
}