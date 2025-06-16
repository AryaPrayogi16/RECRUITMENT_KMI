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
    OtherSkill
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
        $uploadedFiles = []; // Track uploaded files for cleanup on error
        
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
            
            Log::info('Position found', ['position_id' => $position->id, 'position_name' => $position->position_name]);
            
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
            
            return redirect()->route('job.application.success')
                ->with('candidate_code', $candidate->candidate_code);
                
        } catch (QueryException $e) {
            DB::rollback();
            
            // Clean up uploaded files on database error
            $this->cleanupUploadedFiles($uploadedFiles);
            
            Log::error('Database Query Error during job application submission', [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? null,
                'error_info' => $e->errorInfo ?? null,
                'position_applied' => $validated['position_applied'] ?? null,
                'email' => $validated['email'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            // Specific error messages based on error codes
            $errorMessage = $this->getSpecificErrorMessage($e);
            
            return back()->withErrors(['error' => $errorMessage])->withInput();
            
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            
            // Clean up uploaded files
            $this->cleanupUploadedFiles($uploadedFiles);
            
            Log::error('Model Not Found Error during job application submission', [
                'error_message' => $e->getMessage(),
                'position_applied' => $validated['position_applied'] ?? null,
                'email' => $validated['email'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Posisi yang dipilih tidak ditemukan. Silakan pilih posisi yang valid.'])->withInput();
            
        } catch (\Illuminate\Database\Eloquent\MassAssignmentException $e) {
            DB::rollback();
            
            // Clean up uploaded files
            $this->cleanupUploadedFiles($uploadedFiles);
            
            Log::error('Mass Assignment Error during job application submission', [
                'error_message' => $e->getMessage(),
                'email' => $validated['email'] ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Terjadi kesalahan dalam pengisian data. Silakan periksa kembali data yang diisi.'])->withInput();
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Clean up uploaded files
            $this->cleanupUploadedFiles($uploadedFiles);
            
            Log::error('General Error during job application submission', [
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'email' => $validated['email'] ?? null,
                'stack_trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.'])->withInput();
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

    public function success()
    {
        $candidateCode = session('candidate_code');
        if (!$candidateCode) {
            return redirect()->route('job.application.form');
        }
        
        return view('job-application.success', compact('candidateCode'));
    }
}