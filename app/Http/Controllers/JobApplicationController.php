<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Position,
    FamilyMember,
    Education,
    WorkExperience,
    LanguageSkill,
    Activity,
    DrivingLicense,
    CandidateAdditionalInfo, 
    DocumentUpload,
    ApplicationLog,
    KraeplinTestSession,
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
    /**
     * ✅ SIMPLE FIX: Hanya ubah query untuk menampilkan posisi aktif
     */
    public function showForm()
    {
        // Ubah dari: Position::where('is_active', true)->get();
        // Menjadi: menggunakan scope active() yang sudah ada
        $positions = Position::active()
                            ->orderBy('department')
                            ->orderBy('position_name')
                            ->get();
        
        return view('job-application.form', compact('positions'));
    }

    /**
     * ✅ SIMPLE FIX: Update API untuk konsistensi dengan filter aktif
     */
    public function getPositions()
    {
        // Ubah dari: Position::where('is_active', true)
        // Menjadi: menggunakan scope active()
        $positions = Position::active()
            ->select('id', 'position_name', 'department', 'salary_range_min', 'salary_range_max')
            ->orderBy('department')
            ->orderBy('position_name')
            ->get();
            
        return response()->json($positions);
    }

    public function submitApplication(JobApplicationRequest $request)
    {
        Log::info('=== DEBUGGING submitApplication START ===');
        Log::info('Available methods:', [
            'createLanguageSkills' => method_exists($this, 'createLanguageSkills'),
            'createWorkExperiences' => method_exists($this, 'createWorkExperiences'), 
            'createDrivingLicenses' => method_exists($this, 'createDrivingLicenses'),
            'createCandidateAdditionalInfo' => method_exists($this, 'createCandidateAdditionalInfo'),
        ]);

        $validated = $request->validated();
        $uploadedFiles = [];
        
        try {
            DB::beginTransaction();
            
            Log::info('Starting job application submission', [
                'position_applied' => $validated['position_applied'],
                'email' => $validated['email']
            ]);
            
            // ✅ TAMBAHAN SIMPLE: Validasi posisi masih aktif
            $position = Position::active()
                              ->where('position_name', $validated['position_applied'])
                              ->first();
                              
            if (!$position) {
                throw new \Exception("Posisi '{$validated['position_applied']}' tidak tersedia atau sudah tidak aktif");
            }
            
            // 1. Create Candidate
            $candidate = $this->createCandidate($validated, $position->id);
            Log::info('Candidate created', ['candidate_id' => $candidate->id, 'candidate_code' => $candidate->candidate_code]);
            
            // 2. Create Family Members
            if (!empty($validated['family_members'])) {
                $this->createFamilyMembers($candidate, $validated['family_members']);
                Log::info('Family members created', ['candidate_id' => $candidate->id, 'count' => count($validated['family_members'])]);
            }
            
            // 3. Create Education Records
            if (!empty($validated['formal_education'])) {
                $this->createEducation($candidate, $validated['formal_education'], 'formal');
                Log::info('Formal education created', ['candidate_id' => $candidate->id, 'count' => count($validated['formal_education'])]);
            }
            
            if (!empty($validated['non_formal_education'])) {
                $this->createEducation($candidate, $validated['non_formal_education'], 'non_formal');
                Log::info('Non-formal education created', ['candidate_id' => $candidate->id, 'count' => count($validated['non_formal_education'])]);
            }
            
            // 4. Create Work Experience
            if (!empty($validated['work_experiences'])) {
                $this->createWorkExperiences($candidate, $validated['work_experiences']);
                Log::info('Work experiences created', ['candidate_id' => $candidate->id, 'count' => count($validated['work_experiences'])]);
            }
            
            // 5. Create Skills
            if (!empty($validated['language_skills'])) {
                $this->createLanguageSkills($candidate, $validated['language_skills']);
                Log::info('Language skills created', ['candidate_id' => $candidate->id, 'count' => count($validated['language_skills'])]);
            }
            
            // Create Computer Skills
            $this->createCandidateAdditionalInfo($candidate, $validated);
            Log::info('Candidate additional information created', ['candidate_id' => $candidate->id]);
            
            // 6. Create Social Activities
            if (!empty($validated['social_activities'])) {
                $this->createActivities($candidate, $validated['social_activities'], 'social_activity');
                Log::info('Social activities created', ['candidate_id' => $candidate->id, 'count' => count($validated['social_activities'])]);
            }
            
            // 7. Create Achievements
            if (!empty($validated['achievements'])) {
                $this->createActivities($candidate, $validated['achievements'], 'achievement');
                Log::info('Achievements created', ['candidate_id' => $candidate->id, 'count' => count($validated['achievements'])]);
            }
            
            // 8. Create Driving Licenses
            if (!empty($validated['driving_licenses'])) {
                $this->createDrivingLicenses($candidate, $validated['driving_licenses']);
                Log::info('Driving licenses created', ['candidate_id' => $candidate->id, 'count' => count($validated['driving_licenses'])]);
            }
            
            // 10. Handle File Uploads
            $uploadedFiles = $this->handleDocumentUploads($candidate, $request);
            Log::info('Document uploads processed', ['candidate_id' => $candidate->id, 'files_count' => count($uploadedFiles)]);
            
            // 11. Create Application Log
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
                'application_date' => now(),

                // Data pribadi langsung di tabel candidates
                'nik' => $validated['nik'] ?? null,
                'full_name' => $validated['full_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'phone_alternative' => $validated['phone_alternative'] ?? null,
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'ethnicity' => $validated['ethnicity'] ?? null,
                'current_address' => $validated['current_address'] ?? null,
                'current_address_status' => $validated['current_address_status'] ?? null,
                'ktp_address' => $validated['ktp_address'] ?? null,
                'height_cm' => $validated['height_cm'] ?? null,
                'weight_kg' => $validated['weight_kg'] ?? null,
                'vaccination_status' => $validated['vaccination_status'] ?? null,
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

    private function createFamilyMembers($candidate, $familyMembers)
    {
        foreach ($familyMembers as $index => $member) {
            if (empty($member['relationship']) && empty($member['name'])) {
                continue;
            }
            try {
                FamilyMember::create([
                    'candidate_id' => $candidate->id,
                    'relationship' => !empty($member['relationship']) ? $member['relationship'] : null,
                    'name' => !empty($member['name']) ? $member['name'] : null,
                    'age' => $member['age'] ?? null,
                    'education' => !empty($member['education']) ? $member['education'] : null,
                    'occupation' => !empty($member['occupation']) ? $member['occupation'] : null,
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

    private function createEducation($candidate, $educations, $educationType = 'formal')
    {
        foreach ($educations as $index => $education) {
            // Skip jika field utama kosong
            if ($educationType === 'formal') {
                if (empty($education['education_level']) && empty($education['institution_name'])) {
                    continue;
                }
            } else {
                if (empty($education['course_name'])) {
                    continue;
                }
            }

            try {
                $educationData = [
                    'candidate_id' => $candidate->id,
                    'education_type' => $educationType,
                ];

                if ($educationType === 'formal') {
                    $educationData = array_merge($educationData, [
                        'education_level' => $education['education_level'] ?? null,
                        'institution_name' => $education['institution_name'] ?? null,
                        'major' => $education['major'] ?? null,
                        'start_year' => $education['start_year'] ?? null,
                        'end_year' => $education['end_year'] ?? null,
                        'gpa' => $education['gpa'] ?? null,
                    ]);
                } else {
                    $educationData = array_merge($educationData, [
                        'course_name' => $education['course_name'] ?? null,
                        'organizer' => $education['organizer'] ?? null,
                        'date' => $education['date'] ?? null,
                        'description' => $education['description'] ?? null,
                    ]);
                }

                Education::create($educationData);
            } catch (\Exception $e) {
                Log::error('Error creating education', [
                    'candidate_id' => $candidate->id,
                    'education_index' => $index,
                    'education_type' => $educationType,
                    'error' => $e->getMessage(),
                    'education_data' => $education
                ]);
                throw $e;
            }
        }
    }

    private function createActivities($candidate, $activities, $activityType)
    {
        foreach ($activities as $index => $activity) {
            $mainField = $activityType === 'social_activity' ? 'organization_name' : 'achievement';
            if (empty($activity[$mainField])) {
                continue;
            }

            try {
                $activityData = [
                    'candidate_id' => $candidate->id,
                    'activity_type' => $activityType,
                ];

                if ($activityType === 'social_activity') {
                    $activityData = array_merge($activityData, [
                        'title' => $activity['organization_name'] ?? null,
                        'field_or_year' => $activity['field'] ?? null,
                        'period' => $activity['period'] ?? null,
                        'description' => $activity['description'] ?? null,
                    ]);
                } else {
                    $activityData = array_merge($activityData, [
                        'title' => $activity['achievement'] ?? null,
                        'field_or_year' => $activity['year'] ?? null,
                        'period' => null,
                        'description' => $activity['description'] ?? null,
                    ]);
                }

                Activity::create($activityData);
            } catch (\Exception $e) {
                Log::error('Error creating activity', [
                    'candidate_id' => $candidate->id,
                    'activity_index' => $index,
                    'activity_type' => $activityType,
                    'error' => $e->getMessage(),
                    'activity_data' => $activity
                ]);
                throw $e;
            }
        }
    }

    private function createCandidateAdditionalInfo($candidate, $validated)
    {
        try {
            $additionalData = [
                'candidate_id' => $candidate->id,
                'hardware_skills' => !empty($validated['hardware_skills']) ? $validated['hardware_skills'] : null,
                'software_skills' => !empty($validated['software_skills']) ? $validated['software_skills'] : null,
                'other_skills' => !empty($validated['other_skills']) ? $validated['other_skills'] : null,
                'willing_to_travel' => $validated['willing_to_travel'] ?? false,
                'has_vehicle' => $validated['has_vehicle'] ?? false,
                'vehicle_types' => !empty($validated['vehicle_types']) ? $validated['vehicle_types'] : null,
                'motivation' => !empty($validated['motivation']) ? $validated['motivation'] : null,
                'strengths' => !empty($validated['strengths']) ? $validated['strengths'] : null,
                'weaknesses' => !empty($validated['weaknesses']) ? $validated['weaknesses'] : null,
                'other_income' => !empty($validated['other_income']) ? $validated['other_income'] : null,
                'has_police_record' => $validated['has_police_record'] ?? false,
                'police_record_detail' => !empty($validated['police_record_detail']) ? $validated['police_record_detail'] : null,
                'has_serious_illness' => $validated['has_serious_illness'] ?? false,
                'illness_detail' => !empty($validated['illness_detail']) ? $validated['illness_detail'] : null,
                'has_tattoo_piercing' => $validated['has_tattoo_piercing'] ?? false,
                'tattoo_piercing_detail' => !empty($validated['tattoo_piercing_detail']) ? $validated['tattoo_piercing_detail'] : null,
                'has_other_business' => $validated['has_other_business'] ?? false,
                'other_business_detail' => !empty($validated['other_business_detail']) ? $validated['other_business_detail'] : null,
                'absence_days' => $validated['absence_days'] ?? null,
                'start_work_date' => $validated['start_work_date'] ?? null,
                'information_source' => !empty($validated['information_source']) ? $validated['information_source'] : null,
                'agreement' => $validated['agreement'] ?? false,
            ];

            CandidateAdditionalInfo::create($additionalData);
        } catch (\Exception $e) {
            Log::error('Error creating candidate additional info', [
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

    private function createLanguageSkills($candidate, $skills)
    {
        foreach ($skills as $index => $skill) {
            // Skip if language is empty
            if (empty($skill['language'])) {
                continue;
            }
            
            try {
                LanguageSkill::create([
                    'candidate_id' => $candidate->id,
                    'language' => !empty($skill['language']) ? $skill['language'] : null,
                    'speaking_level' => !empty($skill['speaking_level']) ? $skill['speaking_level'] : null,
                    'writing_level' => !empty($skill['writing_level']) ? $skill['writing_level'] : null,
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

    private function createWorkExperiences($candidate, $experiences)
    {
        foreach ($experiences as $index => $experience) {
            if (empty($experience['company_name'])) {
                continue;
            }
            try {
                WorkExperience::create([
                    'candidate_id' => $candidate->id,
                    'company_name' => !empty($experience['company_name']) ? $experience['company_name'] : null,
                    'company_address' => !empty($experience['company_address']) ? $experience['company_address'] : null,
                    'company_field' => !empty($experience['company_field']) ? $experience['company_field'] : null,
                    'position' => !empty($experience['position']) ? $experience['position'] : null,
                    'start_year' => $experience['start_year'] ?? null,
                    'end_year' => $experience['end_year'] ?? null,
                    'salary' => $experience['salary'] ?? null,
                    'reason_for_leaving' => !empty($experience['reason_for_leaving']) ? $experience['reason_for_leaving'] : null,
                    'supervisor_contact' => !empty($experience['supervisor_contact']) ? $experience['supervisor_contact'] : null,
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

    private function createDrivingLicenses($candidate, $licenses)
    {
        foreach ($licenses as $index => $license) {
            if (empty($license)) {
                continue;
            }
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

    /**
     * Force create minimal records di semua tabel untuk menjaga konsistensi ID
     */
    private function forceCreateMinimalRecords($candidate, $validated)
    {
        // 1. Family Members - minimal 1 record
        $this->ensureFamilyMembers($candidate, $validated['family_members'] ?? []);
        
        // 2. Education - minimal 2 records (1 formal + 1 non-formal)
        $this->ensureEducation($candidate, $validated);
        
        // 3. Language Skills - minimal 1 record
        $this->ensureLanguageSkills($candidate, $validated['language_skills'] ?? []);
        
        // 4. Work Experience - minimal 1 record
        $this->ensureWorkExperience($candidate, $validated['work_experiences'] ?? []);
        
        // 5. Driving License - minimal 1 record
        $this->ensureDrivingLicense($candidate, $validated['driving_licenses'] ?? []);
        
        // 6. Activities - minimal 2 records (1 social + 1 achievement)
        $this->ensureActivities($candidate, $validated);
        
        // 7. Additional Info - selalu 1 record
        $this->createCandidateAdditionalInfo($candidate, $validated);
        
        Log::info('✅ All minimal records ensured for candidate', ['candidate_id' => $candidate->id]);
    }

    private function ensureFamilyMembers($candidate, $familyMembers)
    {
        if (!empty($familyMembers)) {
            $this->createFamilyMembers($candidate, $familyMembers);
            Log::info('Family members created from data', ['count' => count($familyMembers)]);
        } else {
            FamilyMember::create([
                'candidate_id' => $candidate->id,
                'relationship' => null,
                'name' => null,
                'age' => null,
                'education' => null,
                'occupation' => null,
            ]);
            Log::info('Empty family member record created for ID consistency');
        }
    }

    private function ensureEducation($candidate, $validated)
    {
        // Formal Education - minimal 1 record
        if (!empty($validated['formal_education'])) {
            $this->createEducation($candidate, $validated['formal_education'], 'formal');
            Log::info('Formal education created from data', ['count' => count($validated['formal_education'])]);
        } else {
            Education::create([
                'candidate_id' => $candidate->id,
                'education_type' => 'formal',
                'education_level' => null,
                'institution_name' => null,
                'major' => null,
                'start_year' => null,
                'end_year' => null,
                'gpa' => null,
            ]);
            Log::info('Empty formal education record created for ID consistency');
        }
        
        // Non-formal Education - minimal 1 record
        if (!empty($validated['non_formal_education'])) {
            $this->createEducation($candidate, $validated['non_formal_education'], 'non_formal');
            Log::info('Non-formal education created from data', ['count' => count($validated['non_formal_education'])]);
        } else {
            Education::create([
                'candidate_id' => $candidate->id,
                'education_type' => 'non_formal',
                'course_name' => null,
                'organizer' => null,
                'date' => null,
                'description' => null,
            ]);
            Log::info('Empty non-formal education record created for ID consistency');
        }
    }

    private function ensureLanguageSkills($candidate, $languageSkills)
    {
        if (!empty($languageSkills)) {
            $this->createLanguageSkills($candidate, $languageSkills);
            Log::info('Language skills created from data', ['count' => count($languageSkills)]);
        } else {
            LanguageSkill::create([
                'candidate_id' => $candidate->id,
                'language' => null,
                'speaking_level' => null,
                'writing_level' => null,
            ]);
            Log::info('Empty language skill record created for ID consistency');
        }
    }

    private function ensureWorkExperience($candidate, $workExperiences)
    {
        if (!empty($workExperiences)) {
            $this->createWorkExperiences($candidate, $workExperiences);
            Log::info('Work experiences created from data', ['count' => count($workExperiences)]);
        } else {
            WorkExperience::create([
                'candidate_id' => $candidate->id,
                'company_name' => null,
                'company_address' => null,
                'company_field' => null,
                'position' => null,
                'start_year' => null,
                'end_year' => null,
                'salary' => null,
                'reason_for_leaving' => null,
                'supervisor_contact' => null,
            ]);
            Log::info('Empty work experience record created for ID consistency');
        }
    }

    private function ensureDrivingLicense($candidate, $drivingLicenses)
    {
        if (!empty($drivingLicenses)) {
            $this->createDrivingLicenses($candidate, $drivingLicenses);
            Log::info('Driving licenses created from data', ['count' => count($drivingLicenses)]);
        } else {
            DrivingLicense::create([
                'candidate_id' => $candidate->id,
                'license_type' => null,
            ]);
            Log::info('Empty driving license record created for ID consistency');
        }
    }

    private function ensureActivities($candidate, $validated)
    {
        // Social Activities - minimal 1 record
        if (!empty($validated['social_activities'])) {
            $this->createActivities($candidate, $validated['social_activities'], 'social_activity');
            Log::info('Social activities created from data', ['count' => count($validated['social_activities'])]);
        } else {
            Activity::create([
                'candidate_id' => $candidate->id,
                'activity_type' => 'social_activity',
                'title' => null,
                'field_or_year' => null,
                'period' => null,
                'description' => null,
            ]);
            Log::info('Empty social activity record created for ID consistency');
        }
        
        // Achievements - minimal 1 record
        if (!empty($validated['achievements'])) {
            $this->createActivities($candidate, $validated['achievements'], 'achievement');
            Log::info('Achievements created from data', ['count' => count($validated['achievements'])]);
        } else {
            Activity::create([
                'candidate_id' => $candidate->id,
                'activity_type' => 'achievement',
                'title' => null,
                'field_or_year' => null,
                'period' => null,
                'description' => null,
            ]);
            Log::info('Empty achievement record created for ID consistency');
        }
    }
}