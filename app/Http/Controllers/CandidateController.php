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
            'position'
        ])->findOrFail($id);
        
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
                $computerSkill = $candidate->computerSkills()->first();
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
                $otherSkill = $candidate->otherSkills()->first();
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
     * Export candidates to Excel/PDF
     */
    public function export(Request $request)
    {
        Gate::authorize('hr-access');
        
        // Implementation for export functionality
        // You can use Laravel Excel or similar package
        
        return response()->json([
            'message' => 'Export functionality coming soon'
        ]);
    }

    /**
     * Bulk actions on candidates
     */
    public function bulkAction(Request $request)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'candidate_ids' => 'required|array',
            'action' => 'required|in:delete,update_status,export'
        ]);
        
        switch ($request->action) {
            case 'delete':
                Candidate::whereIn('id', $request->candidate_ids)->delete();
                $message = 'Kandidat berhasil dihapus';
                break;
                
            case 'update_status':
                Candidate::whereIn('id', $request->candidate_ids)
                    ->update(['application_status' => $request->new_status]);
                $message = 'Status kandidat berhasil diperbarui';
                break;
                
            default:
                $message = 'Aksi tidak valid';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Search candidates (AJAX)
     */
    public function search(Request $request)
    {
        Gate::authorize('hr-access');
        
        $query = Candidate::with(['personalData', 'position']);
        
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_code', 'like', "%{$search}%")
                  ->orWhereHas('personalData', function ($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $candidates = $query->limit(10)->get();
        
        return response()->json([
            'results' => $candidates->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    'text' => $candidate->candidate_code . ' - ' . 
                             ($candidate->personalData->full_name ?? 'N/A'),
                    'email' => $candidate->personalData->email ?? '',
                    'position' => $candidate->position_applied
                ];
            })
        ]);
    }
}