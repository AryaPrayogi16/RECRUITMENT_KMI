<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    PersonalData, 
    Position,
    FamilyMember,
    FormalEducation,
    NonFormalEducation,
    WorkExperience,
    LanguageSkill,
    ComputerSkill,
    SocialActivity,
    Achievement,
    DrivingLicense,
    GeneralInformation,
    DocumentUpload,
    ApplicationLog,
    OtherSkill,
    KraeplinTestSession,
    // ✅ PERBAIKAN: Gunakan model DISC 3D yang baru
    Disc3DTestSession,
    Disc3DResult
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Requests\JobApplicationRequest;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class JobApplicationController extends Controller
{
    public function showForm()
    {
        $positions = Position::where('is_active', true)->get();
        return view('job-application.form', compact('positions'));
    }

    public function getPositions()
    {
        $positions = Position::where('is_active', true)
            ->select('id', 'position_name', 'department', 'salary_range_min', 'salary_range_max')
            ->get();
        return response()->json($positions);
    }

    public function submitApplication(JobApplicationRequest $request)
    {
        $validated = $request->validated();
        $uploadedFiles = [];
        
        try {
            DB::beginTransaction();
            
            Log::info('Starting job application submission', [
                'position_applied' => $validated['position_applied'],
                'email' => $validated['email']
            ]);
            
            // Get position_id from position_name
            $position = Position::where('position_name', $validated['position_applied'])->first();
            if (!$position) {
                throw new ModelNotFoundException("Position '{$validated['position_applied']}' not found");
            }
            
            // 1. Create Candidate
            $candidate = $this->createCandidate($validated, $position->id);
            Log::info('Candidate created', ['candidate_id' => $candidate->id, 'candidate_code' => $candidate->candidate_code]);
            
            // 2. Create Personal Data
            $this->createPersonalData($candidate, $validated);
            Log::info('Personal data created for candidate', ['candidate_id' => $candidate->id]);
            
            // 3. Create Family Members
            if (!empty($validated['family_members'])) {
                $this->createFamilyMembers($candidate, $validated['family_members']);
                Log::info('Family members created', ['candidate_id' => $candidate->id, 'count' => count($validated['family_members'])]);
            }
            
            // 4. Create Education Records
            if (!empty($validated['formal_education'])) {
                $this->createFormalEducation($candidate, $validated['formal_education']);
                Log::info('Formal education created', ['candidate_id' => $candidate->id, 'count' => count($validated['formal_education'])]);
            }
            
            if (!empty($validated['non_formal_education'])) {
                $this->createNonFormalEducation($candidate, $validated['non_formal_education']);
                Log::info('Non-formal education created', ['candidate_id' => $candidate->id, 'count' => count($validated['non_formal_education'])]);
            }
            
            // 5. Create Work Experience
            if (!empty($validated['work_experiences'])) {
                $this->createWorkExperiences($candidate, $validated['work_experiences']);
                Log::info('Work experiences created', ['candidate_id' => $candidate->id, 'count' => count($validated['work_experiences'])]);
            }
            
            // 6. Create Skills
            if (!empty($validated['language_skills'])) {
                $this->createLanguageSkills($candidate, $validated['language_skills']);
                Log::info('Language skills created', ['candidate_id' => $candidate->id, 'count' => count($validated['language_skills'])]);
            }
            
            // Create Computer Skills
            $this->createComputerSkills($candidate, $validated);
            Log::info('Computer skills processed', ['candidate_id' => $candidate->id]);
            
            // Create Other Skills
            $this->createOtherSkills($candidate, $validated);
            Log::info('Other skills processed', ['candidate_id' => $candidate->id]);
            
            // 7. Create Social Activities
            if (!empty($validated['social_activities'])) {
                $this->createSocialActivities($candidate, $validated['social_activities']);
                Log::info('Social activities created', ['candidate_id' => $candidate->id, 'count' => count($validated['social_activities'])]);
            }
            
            // 8. Create Achievements
            if (!empty($validated['achievements'])) {
                $this->createAchievements($candidate, $validated['achievements']);
                Log::info('Achievements created', ['candidate_id' => $candidate->id, 'count' => count($validated['achievements'])]);
            }
            
            // 9. Create Driving Licenses
            if (!empty($validated['driving_licenses'])) {
                $this->createDrivingLicenses($candidate, $validated['driving_licenses']);
                Log::info('Driving licenses created', ['candidate_id' => $candidate->id, 'count' => count($validated['driving_licenses'])]);
            }
            
            // 10. Create General Information
            $this->createGeneralInformation($candidate, $validated);
            Log::info('General information created', ['candidate_id' => $candidate->id]);
            
            // 11. Handle File Uploads
            $uploadedFiles = $this->handleDocumentUploads($candidate, $request);
            Log::info('Document uploads processed', ['candidate_id' => $candidate->id, 'files_count' => count($uploadedFiles)]);
            
            // 12. Create Application Log
            ApplicationLog::create([
                'candidate_id' => $candidate->id,
                'user_id' => null, // No user for public submission
                'action_type' => 'document_upload',
                'action_description' => 'Application submitted via online form'
            ]);
            Log::info('Application log created', ['candidate_id' => $candidate->id]);
            
            DB::commit();
            Log::info('Job application submitted successfully', ['candidate_code' => $candidate->candidate_code]);
            
            // Clear form data from session/cache after successful submission
            session()->flash('form_submitted', true);
            
            // REDIRECT TO KRAEPLIN TEST - First test in the flow
            return redirect()->route('kraeplin.instructions', $candidate->candidate_code)
                ->with('success', 'Form lamaran berhasil dikirim. Silakan lanjutkan dengan mengerjakan Test Kraeplin.');
                
        } catch (\Exception $e) {
            DB::rollback();
            $this->cleanupUploadedFiles($uploadedFiles);
            
            Log::error('Error during job application submission', [
                'error' => $e->getMessage(),
                'email' => $validated['email'] ?? null
            ]);
            
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem. Silakan coba lagi.'])->withInput();
        }
    }

    public function success()
    {
        // Get candidate code from URL parameter 
        $candidateCode = request()->get('candidate_code') ?: session('candidate_code');
        
        if (!$candidateCode) {
            return redirect()->route('job.application.form')
                ->with('error', 'Sesi tidak valid. Silakan isi form lamaran kembali.');
        }
        
        // Verify candidate exists
        $candidate = Candidate::where('candidate_code', $candidateCode)->first();
        if (!$candidate) {
            return redirect()->route('job.application.form')
                ->with('error', 'Data kandidat tidak ditemukan.');
        }
        
        // ✅ PERBAIKAN: Check test completion status dengan model yang benar
        $kraeplinTest = KraeplinTestSession::where('candidate_id', $candidate->id)
            ->where('status', 'completed')
            ->first();
            
        // ✅ PERBAIKAN: Gunakan Disc3DTestSession (bukan DiscTestSession)
        $disc3dTest = Disc3DTestSession::where('candidate_id', $candidate->id)
            ->where('status', 'completed')
            ->first();
        
        // ✅ PERBAIKAN: Log untuk debugging
        Log::info('Success page accessed', [
            'candidate_code' => $candidateCode,
            'kraeplin_completed' => (bool) $kraeplinTest,
            'disc3d_completed' => (bool) $disc3dTest,
            'url' => request()->fullUrl()
        ]);
        
        // Determine where to redirect based on test completion
        if (!$kraeplinTest) {
            return redirect()->route('kraeplin.instructions', $candidateCode)
                ->with('warning', 'Anda perlu menyelesaikan Test Kraeplin terlebih dahulu.');
        }
        
        if (!$disc3dTest) {
            // ✅ PERBAIKAN: Redirect ke DISC 3D route yang benar
            return redirect()->route('disc3d.instructions', $candidateCode)
                ->with('warning', 'Anda perlu menyelesaikan Test DISC 3D untuk melengkapi proses lamaran.');
        }
        
        // Both tests completed - show success page
        // ✅ PERBAIKAN: Get DISC 3D result
        $disc3dResult = null;
        if ($disc3dTest) {
            $disc3dResult = Disc3DResult::where('candidate_id', $candidate->id)
                ->where('test_session_id', $disc3dTest->id)
                ->first();
        }
        
        return view('job-application.success', compact(
            'candidateCode', 
            'candidate', 
            'kraeplinTest', 
            'disc3dTest',
            'disc3dResult'
        ));
    }

    /**
     * Get candidate test status - NEW METHOD for checking test progress
     */
    public function getTestStatus($candidateCode)
    {
        try {
            $candidate = Candidate::where('candidate_code', $candidateCode)->first();
            
            if (!$candidate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate not found'
                ], 404);
            }
            
            $kraeplinTest = KraeplinTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            $disc3dTest = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->where('status', 'completed')
                ->first();
                
            $disc3dInProgress = Disc3DTestSession::where('candidate_id', $candidate->id)
                ->whereIn('status', ['not_started', 'in_progress'])
                ->first();
            
            return response()->json([
                'success' => true,
                'candidate_code' => $candidateCode,
                'tests' => [
                    'kraeplin' => [
                        'completed' => (bool) $kraeplinTest,
                        'completed_at' => $kraeplinTest?->completed_at,
                        'status' => $kraeplinTest?->status ?? 'not_started'
                    ],
                    'disc3d' => [
                        'completed' => (bool) $disc3dTest,
                        'completed_at' => $disc3dTest?->completed_at,
                        'in_progress' => (bool) $disc3dInProgress,
                        'progress_percentage' => $disc3dInProgress?->progress ?? 0,
                        'sections_completed' => $disc3dInProgress?->sections_completed ?? 0,
                        'status' => $disc3dTest?->status ?? $disc3dInProgress?->status ?? 'not_started'
                    ]
                ],
                'next_step' => $this->determineNextStep($kraeplinTest, $disc3dTest, $disc3dInProgress, $candidateCode)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting test status', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving test status'
            ], 500);
        }
    }

    /**
     * Determine next step for candidate - NEW HELPER METHOD
     */
    private function determineNextStep($kraeplinTest, $disc3dTest, $disc3dInProgress, $candidateCode)
    {
        if (!$kraeplinTest) {
            return [
                'action' => 'kraeplin_test',
                'url' => route('kraeplin.instructions', $candidateCode),
                'message' => 'Lanjutkan dengan Test Kraeplin'
            ];
        }
        
        if (!$disc3dTest) {
            if ($disc3dInProgress) {
                return [
                    'action' => 'continue_disc3d',
                    'url' => route('disc3d.start', $candidateCode),
                    'message' => 'Lanjutkan Test DISC 3D yang tertunda',
                    'progress' => $disc3dInProgress->progress,
                    'sections_completed' => $disc3dInProgress->sections_completed
                ];
            } else {
                return [
                    'action' => 'disc3d_test',
                    'url' => route('disc3d.instructions', $candidateCode),
                    'message' => 'Lanjutkan dengan Test DISC 3D'
                ];
            }
        }
        
        return [
            'action' => 'completed',
            'url' => route('job.application.success', ['candidate_code' => $candidateCode]),
            'message' => 'Semua test telah selesai'
        ];
    }

    /**
     * NEW: Get candidate summary for dashboard/HR
     */
    public function getCandidateSummary($candidateCode)
    {
        try {
            $candidate = Candidate::with([
                'personalData',
                'position',
                'kraeplinTestSession' => function($query) {
                    $query->where('status', 'completed');
                },
                'disc3dTestSession' => function($query) {
                    $query->where('status', 'completed');
                },
                'disc3dResult'
            ])->where('candidate_code', $candidateCode)->first();
            
            if (!$candidate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Candidate not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'candidate' => [
                    'code' => $candidate->candidate_code,
                    'name' => $candidate->personalData?->full_name,
                    'email' => $candidate->personalData?->email,
                    'position' => $candidate->position?->position_name,
                    'application_date' => $candidate->application_date,
                    'status' => $candidate->application_status,
                    'tests' => [
                        'kraeplin' => [
                            'completed' => (bool) $candidate->kraeplinTestSession,
                            'completed_at' => $candidate->kraeplinTestSession?->completed_at
                        ],
                        'disc3d' => [
                            'completed' => (bool) $candidate->disc3dTestSession,
                            'completed_at' => $candidate->disc3dTestSession?->completed_at,
                            'primary_type' => $candidate->disc3dResult?->primary_type,
                            'personality_profile' => $candidate->disc3dResult?->personality_profile,
                            'summary' => $candidate->disc3dResult?->summary
                        ]
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting candidate summary', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving candidate summary'
            ], 500);
        }
    }

    private function getSpecificErrorMessage(QueryException $e): string
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        // Check for specific database constraint violations
        if (str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'email')) {
                return 'Email sudah terdaftar dalam sistem. Silakan gunakan email lain.';
            }
            if (str_contains($errorMessage, 'candidate_code')) {
                return 'Terjadi kesalahan dalam pembuatan kode kandidat. Silakan coba lagi.';
            }
            return 'Data yang dimasukkan sudah ada dalam sistem.';
        }
        
        if (str_contains($errorMessage, 'foreign key constraint')) {
            return 'Terjadi kesalahan relasi data. Silakan periksa kembali data yang diisi.';
        }
        
        if (str_contains($errorMessage, 'Data too long')) {
            return 'Salah satu data yang diisi terlalu panjang. Silakan periksa kembali input Anda.';
        }
        
        if (str_contains($errorMessage, 'cannot be null')) {
            return 'Ada data wajib yang belum diisi. Silakan periksa kembali form.';
        }
        
        // Generic database error
        return 'Terjadi kesalahan database. Silakan coba lagi dalam beberapa saat.';
    }

    private function cleanupUploadedFiles(array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $filePath) {
            try {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                    Log::info('Cleaned up uploaded file', ['file_path' => $filePath]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to cleanup uploaded file', [
                    'file_path' => $filePath,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function createCandidate($validated, $positionId)
    {
        try {
            $candidateData = [
                'candidate_code' => $this->generateCandidateCode(),
                'position_id' => $positionId,
                'position_applied' => $validated['position_applied'],
                'expected_salary' => $validated['expected_salary'] ?? null,
                'application_status' => 'submitted',
                'application_date' => now()
            ];
            
            Log::info('Creating candidate with data', $candidateData);
            
            return Candidate::create($candidateData);
        } catch (\Exception $e) {
            Log::error('Error creating candidate', [
                'error' => $e->getMessage(),
                'data' => $candidateData ?? []
            ]);
            throw $e;
        }
    }

    private function createPersonalData($candidate, $validated)
    {
        try {
            $personalData = [
                'candidate_id' => $candidate->id,
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'phone_alternative' => $validated['phone_alternative'] ?? null,
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'ethnicity' => $validated['ethnicity'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'current_address' => $validated['current_address'] ?? null,
                'current_address_status' => $validated['current_address_status'] ?? null,
                'ktp_address' => $validated['ktp_address'] ?? null,
                'height_cm' => $validated['height_cm'] ?? null,
                'weight_kg' => $validated['weight_kg'] ?? null,
                'vaccination_status' => $validated['vaccination_status'] ?? null,
            ];
            
            Log::info('Creating personal data', ['candidate_id' => $candidate->id, 'email' => $validated['email']]);
            
            return PersonalData::create($personalData);
        } catch (\Exception $e) {
            Log::error('Error creating personal data', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createFamilyMembers($candidate, $familyMembers)
    {
        foreach ($familyMembers as $index => $member) {
            // Skip if all fields are empty
            if (empty($member['relationship']) && empty($member['name'])) {
                continue;
            }
            
            try {
                FamilyMember::create([
                    'candidate_id' => $candidate->id,
                    'relationship' => $member['relationship'],
                    'name' => $member['name'] ?? null,
                    'age' => $member['age'] ?? null,
                    'education' => $member['education'] ?? null,
                    'occupation' => $member['occupation'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating family member', [
                    'candidate_id' => $candidate->id,
                    'member_index' => $index,
                    'error' => $e->getMessage(),
                    'member_data' => $member
                ]);
                throw $e;
            }
        }
    }

    private function createFormalEducation($candidate, $educations)
    {
        foreach ($educations as $index => $education) {
            // Skip if all fields are empty
            if (empty($education['education_level']) && empty($education['institution_name'])) {
                continue;
            }
            
            try {
                FormalEducation::create([
                    'candidate_id' => $candidate->id,
                    'education_level' => $education['education_level'],
                    'institution_name' => $education['institution_name'] ?? null,
                    'major' => $education['major'] ?? null,
                    'start_year' => $education['start_year'] ?? null,
                    'end_year' => $education['end_year'] ?? null,
                    'gpa' => $education['gpa'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating formal education', [
                    'candidate_id' => $candidate->id,
                    'education_index' => $index,
                    'error' => $e->getMessage(),
                    'education_data' => $education
                ]);
                throw $e;
            }
        }
    }

    private function createNonFormalEducation($candidate, $educations)
    {
        foreach ($educations as $index => $education) {
            // Skip if all fields are empty
            if (empty($education['course_name'])) {
                continue;
            }
            
            try {
                NonFormalEducation::create([
                    'candidate_id' => $candidate->id,
                    'course_name' => $education['course_name'],
                    'organizer' => $education['organizer'] ?? null,
                    'date' => $education['date'] ?? null,
                    'description' => $education['description'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating non-formal education', [
                    'candidate_id' => $candidate->id,
                    'education_index' => $index,
                    'error' => $e->getMessage(),
                    'education_data' => $education
                ]);
                throw $e;
            }
        }
    }

    private function createWorkExperiences($candidate, $experiences)
    {
        foreach ($experiences as $index => $experience) {
            // Skip if all fields are empty
            if (empty($experience['company_name'])) {
                continue;
            }
            
            try {
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
                    'supervisor_contact' => $experience['supervisor_contact'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating work experience', [
                    'candidate_id' => $candidate->id,
                    'experience_index' => $index,
                    'error' => $e->getMessage(),
                    'experience_data' => $experience
                ]);
                throw $e;
            }
        }
    }

    private function createLanguageSkills($candidate, $skills)
    {
        foreach ($skills as $index => $skill) {
            // Skip if all fields are empty
            if (empty($skill['language'])) {
                continue;
            }
            
            try {
                LanguageSkill::create([
                    'candidate_id' => $candidate->id,
                    'language' => $skill['language'],
                    'speaking_level' => $skill['speaking_level'] ?? null,
                    'writing_level' => $skill['writing_level'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating language skill', [
                    'candidate_id' => $candidate->id,
                    'skill_index' => $index,
                    'error' => $e->getMessage(),
                    'skill_data' => $skill
                ]);
                throw $e;
            }
        }
    }

    private function createComputerSkills($candidate, $validated)
    {
        // Only create if at least one skill is provided
        if (!empty($validated['hardware_skills']) || !empty($validated['software_skills'])) {
            try {
                ComputerSkill::create([
                    'candidate_id' => $candidate->id,
                    'hardware_skills' => $validated['hardware_skills'] ?? null,
                    'software_skills' => $validated['software_skills'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating computer skills', [
                    'candidate_id' => $candidate->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    private function createOtherSkills($candidate, $validated)
    {
        // Only create if other skills is provided
        if (!empty($validated['other_skills'])) {
            try {
                OtherSkill::create([
                    'candidate_id' => $candidate->id,
                    'other_skills' => $validated['other_skills'],
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating other skills', [
                    'candidate_id' => $candidate->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    private function createSocialActivities($candidate, $activities)
    {
        foreach ($activities as $index => $activity) {
            // Skip if all fields are empty
            if (empty($activity['organization_name'])) {
                continue;
            }
            
            try {
                SocialActivity::create([
                    'candidate_id' => $candidate->id,
                    'organization_name' => $activity['organization_name'],
                    'field' => $activity['field'] ?? null,
                    'period' => $activity['period'] ?? null,
                    'description' => $activity['description'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating social activity', [
                    'candidate_id' => $candidate->id,
                    'activity_index' => $index,
                    'error' => $e->getMessage(),
                    'activity_data' => $activity
                ]);
                throw $e;
            }
        }
    }

    private function createAchievements($candidate, $achievements)
    {
        foreach ($achievements as $index => $achievement) {
            // Skip if all fields are empty
            if (empty($achievement['achievement'])) {
                continue;
            }
            
            try {
                Achievement::create([
                    'candidate_id' => $candidate->id,
                    'achievement' => $achievement['achievement'],
                    'year' => $achievement['year'] ?? null,
                    'description' => $achievement['description'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating achievement', [
                    'candidate_id' => $candidate->id,
                    'achievement_index' => $index,
                    'error' => $e->getMessage(),
                    'achievement_data' => $achievement
                ]);
                throw $e;
            }
        }
    }

    private function createDrivingLicenses($candidate, $licenses)
    {
        foreach ($licenses as $index => $license) {
            try {
                DrivingLicense::create([
                    'candidate_id' => $candidate->id,
                    'license_type' => $license,
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating driving license', [
                    'candidate_id' => $candidate->id,
                    'license_index' => $index,
                    'error' => $e->getMessage(),
                    'license' => $license
                ]);
                throw $e;
            }
        }
    }

    private function createGeneralInformation($candidate, $validated)
    {
        try {
            GeneralInformation::create([
                'candidate_id' => $candidate->id,
                'willing_to_travel' => $validated['willing_to_travel'] ?? false,
                'has_vehicle' => $validated['has_vehicle'] ?? false,
                'vehicle_types' => $validated['vehicle_types'] ?? null,
                'motivation' => $validated['motivation'] ?? null,
                'strengths' => $validated['strengths'] ?? null,
                'weaknesses' => $validated['weaknesses'] ?? null,
                'other_income' => $validated['other_income'] ?? null,
                'has_police_record' => $validated['has_police_record'] ?? false,
                'police_record_detail' => $validated['police_record_detail'] ?? null,
                'has_serious_illness' => $validated['has_serious_illness'] ?? false,
                'illness_detail' => $validated['illness_detail'] ?? null,
                'has_tattoo_piercing' => $validated['has_tattoo_piercing'] ?? false,
                'tattoo_piercing_detail' => $validated['tattoo_piercing_detail'] ?? null,
                'has_other_business' => $validated['has_other_business'] ?? false,
                'other_business_detail' => $validated['other_business_detail'] ?? null,
                'absence_days' => $validated['absence_days'] ?? null,
                'start_work_date' => $validated['start_work_date'] ?? null,
                'information_source' => $validated['information_source'] ?? null,
                'agreement' => $validated['agreement'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating general information', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function handleDocumentUploads($candidate, $request)
    {
        $uploadedFiles = [];
        
        try {
            // Handle CV
            if ($request->hasFile('cv')) {
                $file = $request->file('cv');
                $filename = 'cv_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('documents/' . $candidate->candidate_code, $filename, 'public');
                $uploadedFiles[] = $path;
                
                DocumentUpload::create([
                    'candidate_id' => $candidate->id,
                    'document_type' => 'cv',
                    'document_name' => 'CV',
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
                
                Log::info('CV uploaded', ['candidate_id' => $candidate->id, 'path' => $path]);
            }
            
            // Handle Photo
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = 'photo_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('documents/' . $candidate->candidate_code, $filename, 'public');
                $uploadedFiles[] = $path;
                
                DocumentUpload::create([
                    'candidate_id' => $candidate->id,
                    'document_type' => 'photo',
                    'document_name' => 'Photo',
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
                
                Log::info('Photo uploaded', ['candidate_id' => $candidate->id, 'path' => $path]);
            }
            
            // Handle Transcript
            if ($request->hasFile('transcript')) {
                $file = $request->file('transcript');
                $filename = 'transcript_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('documents/' . $candidate->candidate_code, $filename, 'public');
                $uploadedFiles[] = $path;
                
                DocumentUpload::create([
                    'candidate_id' => $candidate->id,
                    'document_type' => 'transcript',
                    'document_name' => 'Transcript',
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
                
                Log::info('Transcript uploaded', ['candidate_id' => $candidate->id, 'path' => $path]);
            }
            
            // Handle Certificates (multiple)
            if ($request->hasFile('certificates')) {
                $certificates = $request->file('certificates');
                
                foreach ($certificates as $index => $certificate) {
                    $filename = 'certificate_' . time() . '_' . ($index + 1) . '_' . Str::slug(pathinfo($certificate->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $certificate->getClientOriginalExtension();
                    $path = $certificate->storeAs('documents/' . $candidate->candidate_code, $filename, 'public');
                    $uploadedFiles[] = $path;
                    
                    DocumentUpload::create([
                        'candidate_id' => $candidate->id,
                        'document_type' => 'certificates',
                        'document_name' => 'Certificate ' . ($index + 1),
                        'original_filename' => $certificate->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $certificate->getSize(),
                        'mime_type' => $certificate->getMimeType(),
                    ]);
                    
                    Log::info('Certificate uploaded', ['candidate_id' => $candidate->id, 'index' => $index + 1, 'path' => $path]);
                }
            }
            
            return $uploadedFiles;
            
        } catch (\Exception $e) {
            Log::error('Error during file upload', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage(),
                'uploaded_files' => $uploadedFiles
            ]);
            
            // Clean up any files that were uploaded before the error
            $this->cleanupUploadedFiles($uploadedFiles);
            
            throw $e;
        }
    }

    private function generateCandidateCode()
    {
        $prefix = 'KMI' . date('Y');
        $lastCandidate = Candidate::where('candidate_code', 'like', $prefix . '%')
            ->orderBy('candidate_code', 'desc')
            ->first();
        
        if ($lastCandidate) {
            $lastNumber = (int) substr($lastCandidate->candidate_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}