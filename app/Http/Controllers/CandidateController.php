<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Position, 
    ApplicationLog,
    FamilyMember,
    Education,
    WorkExperience,
    LanguageSkill,
    CandidateAdditionalInfo,
    Activity,
    DrivingLicense,
    DocumentUpload,
    Interview,
    KraeplinTestSession,
    KraeplinTestResult,
    KraeplinAnswer
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class CandidateController extends Controller
{
    /**
     * Display a listing of candidates
     */
    public function index(Request $request)
    {
        Gate::authorize('hr-access');
        
        $query = Candidate::with(['position'])
            ->latest();
        
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
            'familyMembers',
            'education' => function($query) {
                $query->orderBy('education_type')->orderBy('end_year', 'desc');
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
     * Show edit form
     */
    public function edit($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            'education',
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
     * Update candidate data
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
            
            // Update education (using merged Education model)
            if ($request->has('formal_education')) {
                $candidate->education()->where('education_type', 'formal')->delete();
                
                foreach ($request->formal_education as $education) {
                    if (!empty($education['education_level'])) {
                        Education::create([
                            'candidate_id' => $candidate->id,
                            'education_type' => 'formal',
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

            // Update non-formal education
            if ($request->has('non_formal_education')) {
                $candidate->education()->where('education_type', 'non_formal')->delete();
                
                foreach ($request->non_formal_education as $education) {
                    if (!empty($education['course_name'])) {
                        Education::create([
                            'candidate_id' => $candidate->id,
                            'education_type' => 'non_formal',
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
     * Show preview page
     */
    public function preview($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            'education',
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
     * Generate PDF preview
     */
    public function previewPdf($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            'education',
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
     * Generate HTML preview
     */
    public function previewHtml($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            'education',
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
     * Export single candidate to PDF
     */
    public function exportSingle($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            'education',
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
     * Export to Word format (using HTML)
     */
    public function exportWord($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            'education',
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
            $candidate = Candidate::onlyTrashed()->findOrFail($id);
            $candidateName = $candidate->full_name ?? 'Unknown';
            
            $candidate->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => "Kandidat {$candidateName} berhasil dihapus permanen"
            ]);
            
        } catch (\Exception $e) {
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
}