<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Position, 
    ApplicationLog,
    PersonalData,
    FamilyMember,
    FormalEducation,
    NonFormalEducation,
    WorkExperience,
    LanguageSkill,
    ComputerSkill,
    OtherSkill,
    SocialActivity,
    Achievement,
    DrivingLicense,
    GeneralInformation,
    DocumentUpload,
    Interview
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Models\DiscProfileInrepresentation;

class CandidateController extends Controller
{
    /**
     * Display a listing of candidates
     */
    public function index(Request $request)
    {
        Gate::authorize('hr-access');
        
        $query = Candidate::with(['personalData', 'position'])
            ->latest();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_code', 'like', "%{$search}%")
                  ->orWhereHas('personalData', function ($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
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
        
        $candidates = $query->paginate(15)->withQueryString();
        
        // Get all active positions for filter dropdown
        $positions = Position::where('is_active', true)
            ->orderBy('position_name')
            ->get();
        
        // Count new applications for notification badge
        $newApplicationsCount = Candidate::where('application_status', 'submitted')
            ->whereDate('created_at', today())
            ->count();
        
        return view('candidates.index', compact('candidates', 'positions', 'newApplicationsCount'));
    }

    /**
     * Show candidate details
     */
    public function show($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'personalData',
            'familyMembers',
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'computerSkills',
            'otherSkills',
            'socialActivities',
            'achievements',
            'drivingLicenses',
            'generalInformation',
            'documentUploads',
            'applicationLogs.user',
            'interviews.interviewer',
            'position',
            'kraeplinTestResult',
            'kraeplinTestResult.testSession',
            'latestKraeplinTest'
        ])->findOrFail($id);
        
         // Debug Kraeplin data dengan detail
            if ($candidate->kraeplinTestResult) {
                $testResult = $candidate->kraeplinTestResult;
                
                // Log raw data from database
                $rawData = DB::table('kraeplin_test_results')
                    ->where('id', $testResult->id)
                    ->first();
                    
                Log::info('Kraeplin Raw DB Data', [
                    'raw_column_correct_count' => $rawData->column_correct_count,
                    'is_string' => is_string($rawData->column_correct_count)
                ]);
                
                // Log after model processing
                Log::info('Kraeplin Model Data', [
                    'column_correct_count' => $testResult->column_correct_count,
                    'type' => gettype($testResult->column_correct_count),
                    'is_array' => is_array($testResult->column_correct_count)
                ]);
                
                // Force refresh jika perlu
                $testResult->refresh();
            }
        // Log view action
            ApplicationLog::create([
                'candidate_id' => $candidate->id,
                'user_id' => Auth::id(),
                'action_type' => 'data_update',
                'action_description' => 'Profile viewed by ' . Auth::user()->full_name
            ]);
        
         return view('candidates.show', compact('candidate'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'personalData',
            'familyMembers',
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'computerSkills',
            'otherSkills',
            'socialActivities',
            'achievements',
            'drivingLicenses',
            'generalInformation'
        ])->findOrFail($id);
        
        $positions = Position::where('is_active', true)->get();
        
        return view('candidates.edit', compact('candidate', 'positions'));
    }

    /**
     * Update candidate data
     */
    public function update(Request $request, $id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::findOrFail($id);
        
        // Validation rules would go here
        
        try {
            DB::beginTransaction();
            
            // Update candidate basic info
            $candidate->update([
                'position_applied' => $request->position_applied,
                'expected_salary' => $request->expected_salary,
            ]);
            
            // Update personal data
            if ($candidate->personalData) {
                $candidate->personalData->update([
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
            }
            
            // Update family members
            if ($request->has('family_members')) {
                // Delete existing family members
                $candidate->familyMembers()->delete();
                
                // Create new family members
                foreach ($request->family_members as $member) {
                    if (!empty($member['name']) || !empty($member['relationship'])) {
                        FamilyMember::create([
                            'candidate_id' => $candidate->id,
                            'relationship' => $member['relationship'],
                            'name' => $member['name'],
                            'age' => $member['age'],
                            'education' => $member['education'],
                            'occupation' => $member['occupation']
                        ]);
                    }
                }
            }
            
            // Update formal education
            if ($request->has('formal_education')) {
                $candidate->formalEducation()->delete();
                
                foreach ($request->formal_education as $education) {
                    if (!empty($education['education_level'])) {
                        FormalEducation::create([
                            'candidate_id' => $candidate->id,
                            'education_level' => $education['education_level'],
                            'institution_name' => $education['institution_name'],
                            'major' => $education['major'],
                            'start_year' => $education['start_year'],
                            'end_year' => $education['end_year'],
                            'gpa' => $education['gpa']
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
                            'company_address' => $experience['company_address'],
                            'company_field' => $experience['company_field'],
                            'position' => $experience['position'],
                            'start_year' => $experience['start_year'],
                            'end_year' => $experience['end_year'],
                            'salary' => $experience['salary'],
                            'reason_for_leaving' => $experience['reason_for_leaving'],
                            'supervisor_contact' => $experience['supervisor_contact']
                        ]);
                    }
                }
            }
            
            // Update skills
            if ($request->has('hardware_skills') || $request->has('software_skills')) {
                // DIPERBAIKI: Tidak perlu first() untuk hasOne relationship
                $computerSkill = $candidate->computerSkills;
                if ($computerSkill) {
                    $computerSkill->update([
                        'hardware_skills' => $request->hardware_skills,
                        'software_skills' => $request->software_skills
                    ]);
                } else {
                    ComputerSkill::create([
                        'candidate_id' => $candidate->id,
                        'hardware_skills' => $request->hardware_skills,
                        'software_skills' => $request->software_skills
                    ]);
                }
            }
            if ($request->has('other_skills')) {
                // DIPERBAIKI: Tidak perlu first() untuk hasOne relationship
                $otherSkill = $candidate->otherSkills;
                if ($otherSkill) {
                    $otherSkill->update([
                        'other_skills' => $request->other_skills
                    ]);
                } else {
                    OtherSkill::create([
                        'candidate_id' => $candidate->id,
                        'other_skills' => $request->other_skills
                    ]);
                }
            }
            
            // Update general information
            if ($candidate->generalInformation) {
                $candidate->generalInformation->update([
                    'willing_to_travel' => $request->boolean('willing_to_travel'),
                    'has_vehicle' => $request->boolean('has_vehicle'),
                    'vehicle_types' => $request->vehicle_types,
                    'motivation' => $request->motivation,
                    'strengths' => $request->strengths,
                    'weaknesses' => $request->weaknesses,
                    'other_income' => $request->other_income,
                    'has_police_record' => $request->boolean('has_police_record'),
                    'police_record_detail' => $request->police_record_detail,
                    'has_serious_illness' => $request->boolean('has_serious_illness'),
                    'illness_detail' => $request->illness_detail,
                    'has_tattoo_piercing' => $request->boolean('has_tattoo_piercing'),
                    'tattoo_piercing_detail' => $request->tattoo_piercing_detail,
                    'has_other_business' => $request->boolean('has_other_business'),
                    'other_business_detail' => $request->other_business_detail,
                    'absence_days' => $request->absence_days,
                    'start_work_date' => $request->start_work_date,
                    'information_source' => $request->information_source
                ]);
            }
            
            // Log update action
            ApplicationLog::create([
                'candidate_id' => $candidate->id,
                'user_id' => Auth::id(),
                'action_type' => 'data_update',
                'action_description' => 'Profile updated by ' . Auth::user()->full_name
            ]);
            
            DB::commit();
            
            return redirect()->route('candidates.show', $candidate->id)
                ->with('success', 'Data kandidat berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollback();
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
            'status' => 'required|in:submitted,screening,interview,offered,accepted,rejected,withdrawn'
        ]);
        
        $candidate = Candidate::findOrFail($id);
        $oldStatus = $candidate->application_status;
        
        $candidate->update([
            'application_status' => $request->status
        ]);
        
        // Log status change
        ApplicationLog::create([
            'candidate_id' => $candidate->id,
            'user_id' => Auth::id(),
            'action_type' => 'status_change',
            'action_description' => sprintf(
                'Status changed from %s to %s by %s',
                $oldStatus,
                $request->status,
                Auth::user()->full_name
            )
        ]);
        
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
        
        $candidate = Candidate::with('personalData')->findOrFail($id);
        
        // Get available interviewers
        $interviewers = \App\Models\User::whereIn('role', ['interviewer', 'hr', 'admin'])
            ->where('is_active', true)
            ->get();
        
        return view('candidates.schedule-interview', compact('candidate', 'interviewers'));
    }

    /**
     * Show preview page
     */
    public function preview($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'personalData',
            'familyMembers',
            'formalEducation',
            'nonFormalEducation', 
            'workExperiences',
            'languageSkills',
            'computerSkills',
            'otherSkills',
            'socialActivities',
            'achievements',
            'drivingLicenses',
            'generalInformation',
            'position'
        ])->findOrFail($id);
        
        return view('candidates.preview', compact('candidate'));
    }

      /* Generate PDF for preview
     */
    public function previewPdf($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'personalData',
            'familyMembers',
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'computerSkills',
            'otherSkills',
            'socialActivities',
            'achievements',
            'drivingLicenses',
            'generalInformation',
            'position'
        ])->findOrFail($id);
        
        $pdf = PDF::loadView('candidates.pdf.complete', compact('candidate'));
        $pdf->setPaper('A4', 'portrait');
        
        // Alternative approach: Use stream() instead of output()
        return $pdf->stream('preview.pdf', array('Attachment' => false));
    }
    
    /**
     * Generate HTML preview (alternative to PDF preview)
     */
    public function previewHtml($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'personalData',
            'familyMembers',
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'computerSkills',
            'otherSkills',
            'socialActivities',
            'achievements',
            'drivingLicenses',
            'generalInformation',
            'position',
            'documentUploads'
        ])->findOrFail($id);
        
        return view('candidates.preview-html', compact('candidate'));
    }
    /**
     * Export single candidate to PDF
     */
    public function exportSingle($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'personalData',
            'familyMembers',
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'computerSkills',
            'otherSkills',
            'socialActivities',
            'achievements',
            'drivingLicenses',
            'generalInformation',
            'position'
        ])->findOrFail($id);
        
        // Log export action
        ApplicationLog::create([
            'candidate_id' => $candidate->id,
            'user_id' => Auth::id(),
            'action_type' => 'export',
            'action_description' => 'Profile exported to PDF by ' . Auth::user()->full_name
        ]);
        
        $pdf = PDF::loadView('candidates.pdf.complete', compact('candidate'));
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'FLK_' . str_replace(' ', '_', $candidate->personalData->full_name ?? 'Kandidat') . '_' . date('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export multiple candidates to PDF (summary)
     */
    public function exportMultiple(Request $request)
    {
        Gate::authorize('hr-access');
        
        $query = Candidate::with(['personalData', 'position']);
        
        // Apply the same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_code', 'like', "%{$search}%")
                  ->orWhereHas('personalData', function ($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
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
            $query->whereIn('id', $request->selected_ids);
        }
        
        $candidates = $query->orderBy('created_at', 'desc')->get();
        
        $pdf = PDF::loadView('candidates.pdf.multiple', compact('candidates'));
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'Kandidat_Summary_' . date('Ymd_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export to Word format (using HTML)
     */
    public function exportWord($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'personalData',
            'familyMembers',
            'formalEducation',
            'nonFormalEducation',
            'workExperiences',
            'languageSkills',
            'computerSkills',
            'otherSkills',
            'generalInformation',
            'position'
        ])->findOrFail($id);
        
        // Log export action
        ApplicationLog::create([
            'candidate_id' => $candidate->id,
            'user_id' => Auth::id(),
            'action_type' => 'export',
            'action_description' => 'Profile exported to Word by ' . Auth::user()->full_name
        ]);
        
        $filename = 'FLK_' . str_replace(' ', '_', $candidate->personalData->full_name ?? 'Kandidat') . '_' . date('Ymd') . '.doc';
        
        $headers = [
            "Content-type" => "text/html",
            "Content-Disposition" => "attachment;Filename={$filename}"
        ];
        
        $content = view('candidates.word.single', compact('candidate'))->render();
        
        return response($content, 200, $headers);
    }

    public function discResult($id)
    {
        try {
            $candidate = Candidate::with([
                'personalData',
                'position',
                'discTestSessions' => function($query) {
                    $query->latest();
                },
                'discTestResults' => function($query) {
                    $query->latest();
                }
            ])->findOrFail($id);
            
            // Get latest DISC test result
            $discResult = $candidate->discTestResults()->latest()->first();
            
            if (!$discResult) {
                return redirect()->route('candidates.show', $id)
                    ->with('error', 'Kandidat belum menyelesaikan test DISC.');
            }
            
            // Get DISC profile descriptions
            $profiles = \App\Models\Disc3DProfileInterpretation::all()->keyBy('dimension');
            
            // Get test session info
            $discSession = $candidate->discTestSessions()->latest()->first();
            
            return view('hr.candidates.disc-result', compact('candidate', 'discResult', 'profiles', 'discSession'));
            
        } catch (\Exception $e) {
            Log::error('Error displaying DISC result for HR', [
                'candidate_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('candidates.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan hasil DISC test.');
        }
    }
}