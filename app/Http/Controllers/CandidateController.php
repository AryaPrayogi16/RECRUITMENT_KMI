<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Position, 
    ApplicationLog,
    FamilyMember,
    FormalEducation,
    NonFormalEducation,
    WorkExperience,
    LanguageSkill,
    CandidateAdditionalInfo,
    Activity,
    DrivingLicense,
    DocumentUpload,
    Interview,
    KraeplinTestSession,
    KraeplinTestResult,
    KraeplinAnswer,
    Disc3DTestSession,
    Disc3DResult,
    Disc3DPatternCombination,  // ✅ NEW: Added pattern combination model
    Disc3DProfileInterpretation
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class CandidateController extends Controller
{
    /**
     * ✅ UPDATED: Display a listing of candidates with pattern combination filter support
     */
    public function index(Request $request)
    {
        Gate::authorize('hr-access');
        
        $query = Candidate::with([
            'position',
            // ✅ FIXED: Include test results relationships with correct column names
            'kraeplinTestResult' => function($query) {
                $query->select('candidate_id', 'overall_score', 'performance_category'); // ← FIXED: overall_score instead of total_score
            },
            'disc3DResult' => function($query) {
                $query->select('candidate_id', 'primary_type', 'primary_percentage', 'most_pattern', 'least_pattern');
            }
        ])->latest();
        
        // Search functionality - sesuai dengan struktur baru (data di candidates table)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('application_status', $request->status);
        }
        
        // Filter by position
        if ($request->filled('position')) {
            $query->where('position_applied', $request->position);
        }
        
        // ✅ NEW: Filter by test completion status
        if ($request->filled('test_status')) {
            switch ($request->test_status) {
                case 'kraeplin_completed':
                    $query->whereHas('kraeplinTestResult');
                    break;
                case 'disc_completed':
                    $query->whereHas('disc3DResult');
                    break;
                case 'all_tests_completed':
                    $query->whereHas('kraeplinTestResult')
                          ->whereHas('disc3DResult');
                    break;
                case 'no_tests':
                    $query->whereDoesntHave('kraeplinTestResult')
                          ->whereDoesntHave('disc3DResult');
                    break;
            }
        }
        
        // ✅ NEW: Filter by Kraeplin performance category
        if ($request->filled('kraeplin_category')) {
            $query->whereHas('kraeplinTestResult', function($q) use ($request) {
                $q->where('performance_category', $request->kraeplin_category);
            });
        }
        
        // ✅ NEW: Filter by DISC primary type
        if ($request->filled('disc_type')) {
            $query->whereHas('disc3DResult', function($q) use ($request) {
                $q->where('primary_type', $request->disc_type);
            });
        }
        
        // ✅ NEW: Filter by pattern combination
        if ($request->filled('pattern_combination')) {
            $query->whereHas('disc3DResult', function($q) use ($request) {
                $q->where('most_pattern', $request->pattern_combination)
                  ->orWhere('least_pattern', $request->pattern_combination);
            });
        }
        
        $candidates = $query->paginate(15)->withQueryString();
        
        // Get all active positions for filter dropdown
        $positions = Position::where('is_active', true)
            ->orderBy('position_name')
            ->get();
        
        // ✅ NEW: Get available test data for filters
        $kraeplinCategories = KraeplinTestResult::select('performance_category')
            ->distinct()
            ->whereNotNull('performance_category')
            ->pluck('performance_category')
            ->sort()
            ->values();
            
        $discTypes = Disc3DResult::select('primary_type')
            ->distinct()
            ->whereNotNull('primary_type')
            ->pluck('primary_type')
            ->sort()
            ->values();
        
        // ✅ NEW: Get available pattern combinations for filter
        $patternCombinations = Disc3DPatternCombination::select('pattern_code', 'pattern_name')
            ->orderBy('pattern_name')
            ->get();
        
        // Count new applications for notification badge
        $newApplicationsCount = Candidate::where('application_status', 'submitted')
            ->whereDate('created_at', today())
            ->count();
        
        return view('candidates.index', compact(
            'candidates', 
            'positions', 
            'newApplicationsCount',
            'kraeplinCategories',
            'discTypes',
            'patternCombinations'
        ));
    }

    /**
     * ✅ FIXED: Ensure search method exists
     */
    public function search(Request $request)
    {
        Gate::authorize('hr-access');
        
        return $this->index($request);
    }

    /**
     * ✅ FIXED: Ensure bulkAction method exists
     */
    public function bulkAction(Request $request)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'action' => 'required|in:delete,export,update_status',
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:candidates,id'
        ]);
        
        $action = $request->action;
        $selectedIds = $request->selected_ids;
        
        try {
            switch ($action) {
                case 'delete':
                    return $this->bulkDelete($request);
                    
                case 'export':
                    return $this->exportMultiple($request);
                    
                case 'update_status':
                    $request->validate(['status' => 'required|in:draft,submitted,screening,interview,offered,accepted,rejected']);
                    
                    $updatedCount = Candidate::whereIn('id', $selectedIds)
                        ->update(['application_status' => $request->status]);
                    
                    // Log bulk status change
                    foreach ($selectedIds as $candidateId) {
                        ApplicationLog::logAction(
                            $candidateId,
                            Auth::id(),
                            ApplicationLog::ACTION_STATUS_CHANGE,
                            'Status bulk updated to ' . $request->status . ' by ' . Auth::user()->full_name
                        );
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => "{$updatedCount} kandidat berhasil diperbarui statusnya"
                    ]);
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Aksi tidak valid'
                    ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in bulk action', [
                'action' => $action,
                'selected_ids' => $selectedIds,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan aksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Ensure missing test result methods exist
     */
    public function kraeplinResult($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'kraeplinTestSessions' => function($query) {
                $query->where('status', 'completed')->latest('completed_at');
            },
            'kraeplinTestResult'
        ])->findOrFail($id);
        
        $kraeplinSession = $candidate->kraeplinTestSessions->first();
        $kraeplinResult = $candidate->kraeplinTestResult;
        
        if (!$kraeplinSession) {
            return redirect()->route('candidates.show', $id)
                ->with('error', 'Kandidat belum menyelesaikan test Kraeplin');
        }
        
        return view('candidates.test-results.kraeplin', compact('candidate', 'kraeplinSession', 'kraeplinResult'));
    }

    /**
     * ✅ UPDATED: DISC 3D result method without eager loading pattern combination
     */
    public function disc3dResult($id)
    {
    Gate::authorize('hr-access');
    
    $candidate = Candidate::with([
        'disc3DTestSessions' => function($query) {
            $query->where('status', 'completed')->latest('completed_at');
        },
        'disc3DResult'  // Pattern combination will be queried separately when needed
    ])->findOrFail($id);
    
    $disc3dSession = $candidate->disc3DTestSessions->first();
    $disc3dResult = $candidate->disc3DResult;
    
    if (!$disc3dSession) {
        return redirect()->route('candidates.show', $id)
            ->with('error', 'Kandidat belum menyelesaikan test DISC 3D');
    }
    
    // ✅ NEW: Get dominant dimension interpretation
    $dominantInterpretation = null;
    if ($disc3dResult) {
        // Determine dominant dimension from MOST segments
        $segments = [
            'D' => $disc3dResult->most_d_segment ?? 1,
            'I' => $disc3dResult->most_i_segment ?? 1,  
            'S' => $disc3dResult->most_s_segment ?? 1,
            'C' => $disc3dResult->most_c_segment ?? 1
        ];
        
        $dominantDimension = array_keys($segments, max($segments))[0];
        $dominantLevel = max($segments);
        
        // Get interpretation for dominant dimension
        $dominantInterpretation = Disc3DProfileInterpretation::where('dimension', $dominantDimension)
            ->where('graph_type', 'MOST')
            ->where('segment_level', $dominantLevel)
            ->first();
    }

    // Update compact() yang sudah ada - tambahkan 'dominantInterpretation'
    return view('candidates.test-results.disc3d', compact(
        'candidate', 
        'disc3dSession', 
        'disc3dResult',
        'dominantInterpretation'  // ← TAMBAH INI SAJA
    ));
    }

    /**
     * ✅ UPDATED: Export DISC 3D result method
     */
    public function exportDisc3dResult($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'disc3DTestSessions' => function($query) {
                $query->where('status', 'completed')->latest('completed_at');
            },
            'disc3DResult'
        ])->findOrFail($id);
        
        $disc3dResult = $candidate->disc3DResult;
        
        if (!$disc3dResult) {
            return redirect()->route('candidates.show', $id)
                ->with('error', 'Hasil test DISC 3D tidak ditemukan');
        }
        
        // Log export action
        ApplicationLog::logAction(
            $candidate->id,
            Auth::id(),
            ApplicationLog::ACTION_EXPORT,
            'DISC 3D result exported by ' . Auth::user()->full_name
        );
        
        $filename = 'DISC3D_' . str_replace(' ', '_', $candidate->full_name ?? 'Kandidat') . '_' . date('Ymd') . '.pdf';
        
        $pdf = PDF::loadView('candidates.pdf.disc3d-result', compact('candidate', 'disc3dResult'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download($filename);
    }

    /**
     * ✅ FIXED: Ensure legacy discResult method exists for backward compatibility
     */
    public function discResult($id)
    {
        Gate::authorize('hr-access');
        
        // Redirect to new disc3dResult method
        return $this->disc3dResult($id);
    }

    /**
     * ✅ NEW: Get pattern combination details via AJAX
     */
    public function getPatternCombination(Request $request)
    {
        Gate::authorize('hr-access');
        
        $patternCode = $request->input('pattern_code');
        
        if (!$patternCode) {
            return response()->json([
                'success' => false,
                'message' => 'Pattern code is required'
            ], 400);
        }
        
        $pattern = Disc3DPatternCombination::where('pattern_code', $patternCode)->first();
        
        if (!$pattern) {
            return response()->json([
                'success' => false,
                'message' => 'Pattern combination not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'pattern_code' => $pattern->pattern_code,
                'pattern_name' => $pattern->pattern_name,
                'description' => $pattern->description,
                'strengths' => $pattern->strengths ?? [],
                'weaknesses' => $pattern->weaknesses ?? [],
                'ideal_environment' => $pattern->ideal_environment ?? [],
                'communication_tips' => $pattern->communication_tips ?? [],
                'career_matches' => $pattern->career_matches ?? []
            ]
        ]);
    }

    /**
     * ✅ NEW: Get all pattern combinations for management
     */
    public function getPatternCombinations()
    {
        Gate::authorize('hr-access');
        
        $patterns = Disc3DPatternCombination::select([
            'id', 'pattern_code', 'pattern_name', 'description', 'created_at'
        ])->orderBy('pattern_code')->get();
        
        return response()->json([
            'success' => true,
            'data' => $patterns,
            'total' => $patterns->count()
        ]);
    }

    /**
     * ✅ UPDATED: Show candidate details with correct education relationships
     */
    public function show($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models instead of unified education
            'formalEducation' => function($query) {
                $query->orderBy('education_level')->orderBy('end_year', 'desc');
            },
            'nonFormalEducation' => function($query) {
                $query->orderBy('date', 'desc');
            },
            'workExperiences' => function($query) {
                $query->orderBy('end_year', 'desc');
            },
            'languageSkills',
            'additionalInfo',
            'activities' => function($query) {
                $query->orderBy('activity_type')->orderBy('field_or_year', 'desc');
            },
            'drivingLicenses',
            'documentUploads',
            'applicationLogs.user' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'interviews.interviewer',
            'position',
            // DISC 3D Test Relationships
            'disc3DTestSessions' => function($query) {
                $query->latest('completed_at');
            },
            'disc3DResult',
            'latestDisc3DTest',
            // KRAEPLIN TEST RELATIONSHIPS
            'kraeplinTestSessions',
            'kraeplinTestResult.testSession',
            'latestKraeplinTest'
        ])->findOrFail($id);
        
        // Log view action
        ApplicationLog::logAction(
            $candidate->id,
            Auth::id(),
            ApplicationLog::ACTION_DATA_UPDATE,
            'Profile viewed by ' . Auth::user()->full_name
        );
        
        return view('candidates.show', compact('candidate'));
    }

    /**
     * ✅ UPDATED: Edit form with correct education relationships
     */
    public function edit($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'additionalInfo',
            'activities',
            'drivingLicenses'
        ])->findOrFail($id);
        
        $positions = Position::where('is_active', true)->get();
        
        return view('candidates.edit', compact('candidate', 'positions'));
    }

    /**
     * ✅ UPDATED: Update candidate data with new education structure
     */
    public function update(Request $request, $id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::findOrFail($id);
        
        // Basic validation
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:candidates,email,' . $id,
            'nik' => 'required|string|size:16|unique:candidates,nik,' . $id,
            'position_applied' => 'required|string|max:255',
            'expected_salary' => 'nullable|numeric|min:0'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update candidate data (personal data now in same table)
            $candidate->update([
                'position_applied' => $request->position_applied,
                'expected_salary' => $request->expected_salary,
                'nik' => $request->nik,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'phone_alternative' => $request->phone_alternative,
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'religion' => $request->religion,
                'marital_status' => $request->marital_status,
                'ethnicity' => $request->ethnicity,
                'current_address' => $request->current_address,
                'current_address_status' => $request->current_address_status,
                'ktp_address' => $request->ktp_address,
                'height_cm' => $request->height_cm,
                'weight_kg' => $request->weight_kg,
                'vaccination_status' => $request->vaccination_status
            ]);
            
            // Update family members
            if ($request->has('family_members')) {
                $candidate->familyMembers()->delete();
                
                foreach ($request->family_members as $member) {
                    if (!empty($member['name']) || !empty($member['relationship'])) {
                        FamilyMember::create([
                            'candidate_id' => $candidate->id,
                            'relationship' => $member['relationship'] ?? null,
                            'name' => $member['name'] ?? null,
                            'age' => $member['age'] ?? null,
                            'education' => $member['education'] ?? null,
                            'occupation' => $member['occupation'] ?? null
                        ]);
                    }
                }
            }
            
            // ✅ UPDATED: Handle formal education with separate model
            if ($request->has('formal_education')) {
                $candidate->formalEducation()->delete();
                
                foreach ($request->formal_education as $education) {
                    if (!empty($education['education_level'])) {
                        FormalEducation::create([
                            'candidate_id' => $candidate->id,
                            'education_level' => $education['education_level'],
                            'institution_name' => $education['institution_name'] ?? null,
                            'major' => $education['major'] ?? null,
                            'start_year' => $education['start_year'] ?? null,
                            'end_year' => $education['end_year'] ?? null,
                            'gpa' => $education['gpa'] ?? null
                        ]);
                    }
                }
            }

            // ✅ UPDATED: Handle non-formal education with separate model
            if ($request->has('non_formal_education')) {
                $candidate->nonFormalEducation()->delete();
                
                foreach ($request->non_formal_education as $education) {
                    if (!empty($education['course_name'])) {
                        NonFormalEducation::create([
                            'candidate_id' => $candidate->id,
                            'course_name' => $education['course_name'],
                            'organizer' => $education['organizer'] ?? null,
                            'date' => $education['date'] ?? null,
                            'description' => $education['description'] ?? null
                        ]);
                    }
                }
            }
            
            // Update work experiences
            if ($request->has('work_experiences')) {
                $candidate->workExperiences()->delete();
                
                foreach ($request->work_experiences as $experience) {
                    if (!empty($experience['company_name'])) {
                        WorkExperience::create([
                            'candidate_id' => $candidate->id,
                            'company_name' => $experience['company_name'],
                            'company_address' => $experience['company_address'] ?? null,
                            'company_field' => $experience['company_field'] ?? null,
                            'position' => $experience['position'] ?? null,
                            'start_year' => $experience['start_year'] ?? null,
                            'end_year' => $experience['end_year'] ?? null,
                            'salary' => $experience['salary'] ?? null,
                            'reason_for_leaving' => $experience['reason_for_leaving'] ?? null,
                            'supervisor_contact' => $experience['supervisor_contact'] ?? null
                        ]);
                    }
                }
            }
            
            // Update language skills
            if ($request->has('language_skills')) {
                $candidate->languageSkills()->delete();
                
                foreach ($request->language_skills as $skill) {
                    if (!empty($skill['language'])) {
                        LanguageSkill::create([
                            'candidate_id' => $candidate->id,
                            'language' => $skill['language'],
                            'speaking_level' => $skill['speaking_level'] ?? null,
                            'writing_level' => $skill['writing_level'] ?? null
                        ]);
                    }
                }
            }
            
            // Update activities (social activities and achievements)
            if ($request->has('social_activities')) {
                $candidate->activities()->where('activity_type', 'social_activity')->delete();
                
                foreach ($request->social_activities as $activity) {
                    if (!empty($activity['title'])) {
                        Activity::create([
                            'candidate_id' => $candidate->id,
                            'activity_type' => 'social_activity',
                            'title' => $activity['title'],
                            'field_or_year' => $activity['field'] ?? null,
                            'period' => $activity['period'] ?? null,
                            'description' => $activity['description'] ?? null
                        ]);
                    }
                }
            }

            if ($request->has('achievements')) {
                $candidate->activities()->where('activity_type', 'achievement')->delete();
                
                foreach ($request->achievements as $achievement) {
                    if (!empty($achievement['name'])) {
                        Activity::create([
                            'candidate_id' => $candidate->id,
                            'activity_type' => 'achievement',
                            'title' => $achievement['name'],
                            'field_or_year' => $achievement['year'] ?? null,
                            'description' => $achievement['description'] ?? null
                        ]);
                    }
                }
            }

            // Update driving licenses
            if ($request->has('driving_licenses')) {
                $candidate->drivingLicenses()->delete();
                
                foreach ($request->driving_licenses as $license) {
                    if (!empty($license['license_type'])) {
                        DrivingLicense::create([
                            'candidate_id' => $candidate->id,
                            'license_type' => $license['license_type']
                        ]);
                    }
                }
            }

            // Update additional info (merged from computer_skills, other_skills, general_information)
            $additionalData = [];

            // Skills data
            if ($request->has('hardware_skills') || $request->has('software_skills') || $request->has('other_skills')) {
                $additionalData['hardware_skills'] = $request->hardware_skills;
                $additionalData['software_skills'] = $request->software_skills;
                $additionalData['other_skills'] = $request->other_skills;
            }

            // General information fields
            $generalFields = [
                'willing_to_travel', 'has_vehicle', 'vehicle_types', 'motivation', 
                'strengths', 'weaknesses', 'other_income', 'has_police_record', 
                'police_record_detail', 'has_serious_illness', 'illness_detail', 
                'has_tattoo_piercing', 'tattoo_piercing_detail', 'has_other_business', 
                'other_business_detail', 'absence_days', 'start_work_date', 'information_source',
                'agreement'
            ];

            foreach ($generalFields as $field) {
                if ($request->has($field)) {
                    if (in_array($field, ['willing_to_travel', 'has_vehicle', 'has_police_record', 'has_serious_illness', 'has_tattoo_piercing', 'has_other_business', 'agreement'])) {
                        $additionalData[$field] = $request->boolean($field);
                    } else {
                        $additionalData[$field] = $request->$field;
                    }
                }
            }

            if (!empty($additionalData)) {
                $candidate->additionalInfo()->updateOrCreate(
                    ['candidate_id' => $candidate->id],
                    $additionalData
                );
            }
            
            // Log update action
            ApplicationLog::logAction(
                $candidate->id,
                Auth::id(),
                ApplicationLog::ACTION_DATA_UPDATE,
                'Profile updated by ' . Auth::user()->full_name
            );
            
            DB::commit();
            
            return redirect()->route('candidates.show', $candidate->id)
                ->with('success', 'Data kandidat berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating candidate', [
                'candidate_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return back()->with('error', 'Gagal memperbarui data kandidat: ' . $e->getMessage());
        }
    }

    /**
     * Update candidate status
     */
    public function updateStatus(Request $request, $id)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'status' => 'required|in:draft,submitted,screening,interview,offered,accepted,rejected'
        ]);
        
        $candidate = Candidate::findOrFail($id);
        $oldStatus = $candidate->application_status;
        
        $candidate->update([
            'application_status' => $request->status
        ]);
        
        // Log status change
        ApplicationLog::logAction(
            $candidate->id,
            Auth::id(),
            ApplicationLog::ACTION_STATUS_CHANGE,
            sprintf(
                'Status changed from %s to %s by %s',
                ucfirst($oldStatus),
                ucfirst($request->status),
                Auth::user()->full_name
            )
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }

    /**
     * Show interview scheduling form
     */
    public function scheduleInterview($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::findOrFail($id);
        
        // Get available interviewers
        $interviewers = \App\Models\User::whereIn('role', ['interviewer', 'hr', 'admin'])
            ->where('is_active', true)
            ->get();
        
        return view('candidates.schedule-interview', compact('candidate', 'interviewers'));
    }

    /**
     * Store interview schedule
     */
    public function storeInterview(Request $request, $id)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'interview_date' => 'required|date|after:today',
            'interview_time' => 'required',
            'location' => 'nullable|string|max:255',
            'interviewer_id' => 'required|exists:users,id',
            'notes' => 'nullable|string'
        ]);
        
        $candidate = Candidate::findOrFail($id);
        
        try {
            $interview = Interview::create([
                'candidate_id' => $candidate->id,
                'interview_date' => $request->interview_date,
                'interview_time' => $request->interview_time,
                'location' => $request->location,
                'interviewer_id' => $request->interviewer_id,
                'notes' => $request->notes,
                'status' => Interview::STATUS_SCHEDULED
            ]);
            
            // Update candidate status to interview
            $candidate->update(['application_status' => 'interview']);
            
            // Log interview scheduling
            ApplicationLog::logAction(
                $candidate->id,
                Auth::id(),
                ApplicationLog::ACTION_STATUS_CHANGE,
                'Interview scheduled for ' . $request->interview_date . ' by ' . Auth::user()->full_name
            );
            
            return redirect()->route('candidates.show', $candidate->id)
                ->with('success', 'Interview berhasil dijadwalkan');
                
        } catch (\Exception $e) {
            Log::error('Error scheduling interview', [
                'candidate_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return back()->with('error', 'Gagal menjadwalkan interview: ' . $e->getMessage());
        }
    }

    /**
     * ✅ UPDATED: Preview with correct education relationships
     */
    public function preview($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'additionalInfo',
            'activities',
            'drivingLicenses',
            'documentUploads',
            'position'
        ])->findOrFail($id);
        
        return view('candidates.preview', compact('candidate'));
    }

    /**
     * ✅ UPDATED: Generate PDF preview with correct education relationships
     */
    public function previewPdf($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'additionalInfo',
            'activities',
            'drivingLicenses',
            'documentUploads',
            'position'
        ])->findOrFail($id);
        
        $pdf = PDF::loadView('candidates.pdf.complete', compact('candidate'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('preview.pdf', array('Attachment' => false));
    }
    
    /**
     * ✅ UPDATED: Generate HTML preview with correct education relationships
     */
    public function previewHtml($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'additionalInfo',
            'activities',
            'drivingLicenses',
            'documentUploads',
            'position'
        ])->findOrFail($id);
        
        return view('candidates.preview-html', compact('candidate'));
    }

    /**
     * ✅ UPDATED: Export single candidate to PDF with correct education relationships
     */
    public function exportSingle($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'additionalInfo',
            'activities',
            'drivingLicenses',
            'documentUploads',
            'position'
        ])->findOrFail($id);
        
        // Log export action
        ApplicationLog::logAction(
            $candidate->id,
            Auth::id(),
            ApplicationLog::ACTION_EXPORT,
            'Profile exported to PDF by ' . Auth::user()->full_name
        );
        
        $filename = 'FLK_' . str_replace(' ', '_', $candidate->full_name ?? 'Kandidat') . '_' . date('Ymd') . '.pdf';
        
        $pdf = PDF::loadView('candidates.pdf.complete', compact('candidate'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download($filename);
    }

    /**
     * Export multiple candidates to PDF (summary)
     */
    public function exportMultiple(Request $request)
    {
        Gate::authorize('hr-access');
        
        $query = Candidate::with(['position']);
        
        // Apply the same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('application_status', $request->status);
        }
        
        if ($request->filled('position')) {
            $query->where('position_applied', $request->position);
        }
        
        // Get selected candidates or all filtered
        if ($request->filled('selected_ids')) {
            $selectedIds = is_array($request->selected_ids) 
                ? $request->selected_ids 
                : explode(',', $request->selected_ids);
            $query->whereIn('id', $selectedIds);
        }
        
        $candidates = $query->orderBy('created_at', 'desc')->get();
        
        $pdf = PDF::loadView('candidates.pdf.multiple', compact('candidates'));
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'Kandidat_Summary_' . date('Ymd_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * ✅ UPDATED: Export to Word format with correct education relationships
     */
    public function exportWord($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'additionalInfo',
            'activities',
            'drivingLicenses',
            'documentUploads',
            'position'
        ])->findOrFail($id);
        
        // Log export action
        ApplicationLog::logAction(
            $candidate->id,
            Auth::id(),
            ApplicationLog::ACTION_EXPORT,
            'Profile exported to Word by ' . Auth::user()->full_name
        );
        
        $filename = 'FLK_' . str_replace(' ', '_', $candidate->full_name ?? 'Kandidat') . '_' . date('Ymd') . '.doc';
        
        $headers = [
            "Content-type" => "text/html",
            "Content-Disposition" => "attachment;Filename={$filename}"
        ];
        
        $content = view('candidates.word.single', compact('candidate'))->render();
        
        return response($content, 200, $headers);
    }

    /**
     * 🆕 NEW: Display document file
     */
    public function viewDocument($id, $documentId)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::findOrFail($id);
        $document = DocumentUpload::where('candidate_id', $candidate->id)
                                ->findOrFail($documentId);
        
        // Pastikan file ada di storage yang benar (storage/app/public)
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan di storage utama');
        }
        
        // Log view action
        ApplicationLog::logAction(
            $candidate->id,
            Auth::id(),
            ApplicationLog::ACTION_DATA_UPDATE,
            "Document {$document->document_name} viewed by " . Auth::user()->full_name
        );
        
        // Return file response menggunakan storage disk
        return Storage::disk('public')->response($document->file_path, $document->original_filename);
    }

    /**
     * 🆕 NEW: Download document file
     */
    public function downloadDocument($id, $documentId)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::findOrFail($id);
        $document = DocumentUpload::where('candidate_id', $candidate->id)
                                ->findOrFail($documentId);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan di storage utama');
        }
        
        // Log download action
        ApplicationLog::logAction(
            $candidate->id,
            Auth::id(),
            ApplicationLog::ACTION_EXPORT,
            "Document {$document->document_name} downloaded by " . Auth::user()->full_name
        );
        
        return Storage::disk('public')->download($document->file_path, $document->original_filename);
    }

    /**
     * Soft delete candidate
     */
    public function destroy($id)
    {
        Gate::authorize('hr-access');
        
        try {
            $candidate = Candidate::findOrFail($id);
            $candidateName = $candidate->full_name ?? 'Unknown';
            
            $candidate->delete(); // Soft delete
            
            // Log delete action
            ApplicationLog::logAction(
                $candidate->id,
                Auth::id(),
                ApplicationLog::ACTION_DATA_UPDATE,
                'Candidate soft deleted by ' . Auth::user()->full_name
            );
            
            return response()->json([
                'success' => true,
                'message' => "Kandidat {$candidateName} berhasil dihapus dan dapat dipulihkan dari menu kandidat terhapus"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting candidate', [
                'candidate_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kandidat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk soft delete candidates
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:candidates,id'
        ]);
        
        try {
            $candidateIds = $request->ids;
            $candidates = Candidate::whereIn('id', $candidateIds)->get();
            
            foreach ($candidates as $candidate) {
                $candidate->delete(); // Soft delete
                
                // Log delete action
                ApplicationLog::logAction(
                    $candidate->id,
                    Auth::id(),
                    ApplicationLog::ACTION_DATA_UPDATE,
                    'Candidate bulk soft deleted by ' . Auth::user()->full_name
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => count($candidateIds) . ' kandidat berhasil dihapus dan dapat dipulihkan dari menu kandidat terhapus'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error bulk deleting candidates', [
                'candidate_ids' => $request->ids,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kandidat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show trashed candidates
     */
    public function trashed(Request $request)
    {
        Gate::authorize('hr-access');
        
        $query = Candidate::onlyTrashed()->with(['position'])
            ->latest('deleted_at');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $candidates = $query->paginate(15)->withQueryString();
        
        return view('candidates.trashed', compact('candidates'));
    }

    /**
     * Restore soft deleted candidate
     */
    public function restore($id)
    {
        Gate::authorize('hr-access');
        
        try {
            $candidate = Candidate::onlyTrashed()->findOrFail($id);
            $candidateName = $candidate->full_name ?? 'Unknown';
            
            $candidate->restore();
            
            // Log restore action
            ApplicationLog::logAction(
                $candidate->id,
                Auth::id(),
                ApplicationLog::ACTION_DATA_UPDATE,
                'Candidate restored by ' . Auth::user()->full_name
            );
            
            return response()->json([
                'success' => true,
                'message' => "Kandidat {$candidateName} berhasil dipulihkan"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error restoring candidate', [
                'candidate_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan kandidat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete candidate permanently
     */
    public function forceDelete($id)
    {
        Gate::authorize('hr-access');
        
        try {
            DB::beginTransaction();
            
            $candidate = Candidate::onlyTrashed()
                ->with(['documentUploads'])
                ->findOrFail($id);
            
            $candidateName = $candidate->full_name ?? 'Unknown';
            $candidateCode = $candidate->candidate_code;
            
            // 1. Hapus semua file documents dari storage
            $this->deleteDocumentFiles($candidate);
            
            // 2. Hapus folder kandidat jika ada
            $this->deleteCandidateFolder($candidateCode);
            
            // 3. Force delete dari database (ini akan otomatis hapus relasi karena foreign key cascade)
            $candidate->forceDelete();
            
            DB::commit();
            
            Log::info('Candidate permanently deleted with file cleanup', [
                'candidate_id' => $id,
                'candidate_code' => $candidateCode,
                'candidate_name' => $candidateName,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Kandidat {$candidateName} berhasil dihapus permanen beserta semua filenya"
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error force deleting candidate', [
                'candidate_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kandidat secara permanen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk force delete candidates dengan cleanup file storage
     */
    public function bulkForceDelete(Request $request)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:candidates,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $candidateIds = $request->ids;
            $candidates = Candidate::onlyTrashed()
                ->with(['documentUploads'])
                ->whereIn('id', $candidateIds)
                ->get();
            
            $deletedCount = 0;
            
            foreach ($candidates as $candidate) {
                // 1. Hapus semua file documents dari storage
                $this->deleteDocumentFiles($candidate);
                
                // 2. Hapus folder kandidat jika ada
                $this->deleteCandidateFolder($candidate->candidate_code);
                
                // 3. Force delete dari database
                $candidate->forceDelete();
                
                $deletedCount++;
                
                Log::info('Candidate bulk permanently deleted with file cleanup', [
                    'candidate_id' => $candidate->id,
                    'candidate_code' => $candidate->candidate_code,
                    'candidate_name' => $candidate->full_name,
                    'user_id' => Auth::id()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} kandidat berhasil dihapus permanen beserta semua filenya"
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error bulk force deleting candidates', [
                'candidate_ids' => $request->ids,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kandidat secara permanen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus semua file documents dari storage berdasarkan DocumentUpload records
     */
    private function deleteDocumentFiles($candidate)
    {
        try {
            foreach ($candidate->documentUploads as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                    Log::info('Document file deleted', [
                        'file_path' => $document->file_path,
                        'document_id' => $document->id,
                        'candidate_id' => $candidate->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error deleting document files', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ UPDATED: Hapus folder kandidat dari storage dengan struktur baru
     */
    private function deleteCandidateFolder($candidateCode)
    {
        try {
            if (!$candidateCode) {
                return;
            }
            
            // ✅ UPDATED: Sesuaikan dengan struktur folder baru
            $folderPath = "documents/candidates/{$candidateCode}"; // ← UPDATED: New structure
            
            // Hapus menggunakan Storage facade
            if (Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->deleteDirectory($folderPath);
                Log::info('Candidate folder deleted from storage', [
                    'folder_path' => $folderPath,
                    'candidate_code' => $candidateCode
                ]);
            }
            
            // ✅ BACKWARD COMPATIBILITY: Also check old structure
            $oldFolderPath = "documents/{$candidateCode}";
            if (Storage::disk('public')->exists($oldFolderPath)) {
                Storage::disk('public')->deleteDirectory($oldFolderPath);
                Log::info('Old candidate folder deleted from storage', [
                    'folder_path' => $oldFolderPath,
                    'candidate_code' => $candidateCode
                ]);
            }
            
            // Juga hapus dari file system langsung sebagai backup
            $fullPath = storage_path("app/public/{$folderPath}");
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
                Log::info('Candidate folder deleted from file system', [
                    'full_path' => $fullPath,
                    'candidate_code' => $candidateCode
                ]);
            }
            
        } catch (\Exception $e) {
            Log::warning('Error deleting candidate folder', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ UPDATED: Cleanup orphaned folders with new structure support
     */
    public function cleanupOrphanedFolders()
    {
        Gate::authorize('hr-access');
        
        try {
            // ✅ UPDATED: Cek di kedua lokasi (backward compatibility)
            $paths = [
                'documents/candidates',  // ← Struktur baru
                'documents'              // ← Struktur lama (untuk cleanup)
            ];
            
            $deletedFolders = [];
            
            foreach ($paths as $basePath) {
                $documentsPath = storage_path("app/public/{$basePath}");
                
                if (!File::exists($documentsPath)) {
                    continue;
                }
                
                $folders = File::directories($documentsPath);
                
                foreach ($folders as $folder) {
                    $folderName = basename($folder);
                    
                    // Skip jika folder adalah 'candidates' di level documents/
                    if ($basePath === 'documents' && $folderName === 'candidates') {
                        continue;
                    }
                    
                    // Cek apakah kandidat dengan kode ini masih ada
                    $candidateExists = Candidate::withTrashed()
                        ->where('candidate_code', $folderName)
                        ->exists();
                    
                    if (!$candidateExists) {
                        // Folder yatim, hapus
                        File::deleteDirectory($folder);
                        $deletedFolders[] = $folderName;
                        
                        Log::info('Orphaned folder deleted', [
                            'folder_name' => $folderName,
                            'folder_path' => $folder,
                            'base_path' => $basePath,
                            'user_id' => Auth::id()
                        ]);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => count($deletedFolders) > 0 
                    ? 'Berhasil menghapus ' . count($deletedFolders) . ' folder yatim: ' . implode(', ', $deletedFolders)
                    : 'Tidak ada folder yatim yang ditemukan'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error cleaning up orphaned folders', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan folder yatim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get storage usage statistics
     */
    public function getStorageStats()
    {
        Gate::authorize('hr-access');
        
        try {
            $documentsPath = storage_path('app/public/documents');
            $totalSize = 0;
            $totalFiles = 0;
            $totalFolders = 0;
            $orphanedFolders = 0;
            
            if (File::exists($documentsPath)) {
                $folders = File::directories($documentsPath);
                $totalFolders = count($folders);
                
                foreach ($folders as $folder) {
                    $folderName = basename($folder);
                    
                    // Skip 'candidates' subfolder for counting
                    if ($folderName === 'candidates') {
                        // Count subfolders in candidates/
                        $candidateFolders = File::directories($folder);
                        $totalFolders += count($candidateFolders);
                        
                        foreach ($candidateFolders as $candidateFolder) {
                            $candidateCode = basename($candidateFolder);
                            
                            // Check if candidate exists
                            $candidateExists = Candidate::withTrashed()
                                ->where('candidate_code', $candidateCode)
                                ->exists();
                            
                            if (!$candidateExists) {
                                $orphanedFolders++;
                            }
                            
                            // Count files and size
                            $files = File::allFiles($candidateFolder);
                            foreach ($files as $file) {
                                $totalSize += $file->getSize();
                                $totalFiles++;
                            }
                        }
                        continue;
                    }
                    
                    // Cek apakah kandidat dengan kode ini masih ada (old structure)
                    $candidateExists = Candidate::withTrashed()
                        ->where('candidate_code', $folderName)
                        ->exists();
                    
                    if (!$candidateExists) {
                        $orphanedFolders++;
                    }
                    
                    // Hitung ukuran folder
                    $files = File::allFiles($folder);
                    foreach ($files as $file) {
                        $totalSize += $file->getSize();
                        $totalFiles++;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_size' => $this->formatBytes($totalSize),
                    'total_size_bytes' => $totalSize,
                    'total_files' => $totalFiles,
                    'total_folders' => $totalFolders,
                    'orphaned_folders' => $orphanedFolders,
                    'active_candidates' => Candidate::count(),
                    'trashed_candidates' => Candidate::onlyTrashed()->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting storage stats', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik storage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format bytes ke human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}