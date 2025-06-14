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
    Organization,
    Achievement,
    DrivingLicense,
    GeneralInformation,
    DocumentUpload,
    ApplicationLog
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function submitApplication(Request $request)
    {
        $validated = $this->validateApplication($request);
        
        try {
            DB::beginTransaction();
            
            // 1. Create Candidate
            $candidate = $this->createCandidate($validated);
            
            // 2. Create Personal Data
            $this->createPersonalData($candidate, $validated);
            
            // 3. Create Family Members
            if (!empty($validated['family_members'])) {
                $this->createFamilyMembers($candidate, $validated['family_members']);
            }
            
            // 4. Create Education Records
            if (!empty($validated['formal_education'])) {
                $this->createFormalEducation($candidate, $validated['formal_education']);
            }
            
            if (!empty($validated['non_formal_education'])) {
                $this->createNonFormalEducation($candidate, $validated['non_formal_education']);
            }
            
            // 5. Create Work Experience
            if (!empty($validated['work_experiences'])) {
                $this->createWorkExperiences($candidate, $validated['work_experiences']);
            }
            
            // 6. Create Skills
            if (!empty($validated['language_skills'])) {
                $this->createLanguageSkills($candidate, $validated['language_skills']);
            }
            
            if (!empty($validated['computer_skills'])) {
                $this->createComputerSkills($candidate, $validated['computer_skills']);
            }
            
            // 7. Create Organizations
            if (!empty($validated['organizations'])) {
                $this->createOrganizations($candidate, $validated['organizations']);
            }
            
            // 8. Create Achievements
            if (!empty($validated['achievements'])) {
                $this->createAchievements($candidate, $validated['achievements']);
            }
            
            // 9. Create Driving Licenses
            if (!empty($validated['driving_licenses'])) {
                $this->createDrivingLicenses($candidate, $validated['driving_licenses']);
            }
            
            // 10. Create General Information
            $this->createGeneralInformation($candidate, $validated);
            
            // 11. Handle File Uploads
            if ($request->hasFile('documents')) {
                $this->handleDocumentUploads($candidate, $request);
            }
            
            // 12. Create Application Log
            ApplicationLog::create([
                'candidate_id' => $candidate->id,
                'action' => 'application_submitted',
                'notes' => 'Lamaran kerja berhasil disubmit melalui form online'
            ]);
            
            DB::commit();
            
            return redirect()->route('job.application.success')
                ->with('candidate_code', $candidate->candidate_code);
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.'])
                ->withInput();
        }
    }

    private function validateApplication(Request $request)
    {
        return $request->validate([
            // Candidate basic info
            'position_applied' => 'required|string|max:100',
            'expected_salary' => 'nullable|numeric|min:0',
            
            // Personal Data
            'full_name' => 'required|string|max:100',
            'birth_place' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:Laki-laki,Perempuan',
            'religion' => 'nullable|string|max:30',
            'ethnicity' => 'nullable|string|max:50',
            'marital_status' => 'nullable|in:Lajang,Menikah,Janda,Duda',
            'email' => 'required|email|max:100|unique:personal_data,email',
            'current_address' => 'nullable|string',
            'ktp_address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'residence_status' => 'nullable|in:Milik Sendiri,Orang Tua,Kontrak,Sewa',
            'height_cm' => 'nullable|integer|min:100|max:250',
            'weight_kg' => 'nullable|integer|min:30|max:200',
            'vaccination_status' => 'nullable|in:Vaksin 1,Vaksin 2,Vaksin 3,Booster',
            
            // Family Members (array)
            'family_members' => 'nullable|array',
            'family_members.*.relationship' => 'required|string',
            'family_members.*.name' => 'nullable|string|max:100',
            'family_members.*.age' => 'nullable|integer|min:0|max:120',
            'family_members.*.education' => 'nullable|string|max:50',
            'family_members.*.occupation' => 'nullable|string|max:100',
            
            // Education
            'formal_education' => 'nullable|array',
            'formal_education.*.education_level' => 'required|in:SMA/SMK,Diploma,S1,S2,S3',
            'formal_education.*.institution_name' => 'nullable|string|max:100',
            'formal_education.*.major' => 'nullable|string|max:100',
            'formal_education.*.start_month' => 'nullable|integer|min:1|max:12',
            'formal_education.*.start_year' => 'nullable|integer|min:1950|max:2030',
            'formal_education.*.end_month' => 'nullable|integer|min:1|max:12',
            'formal_education.*.end_year' => 'nullable|integer|min:1950|max:2030',
            'formal_education.*.gpa' => 'nullable|numeric|min:0|max:4',
            
            // Work Experience
            'work_experiences' => 'nullable|array',
            'work_experiences.*.company_name' => 'required|string|max:100',
            'work_experiences.*.position' => 'nullable|string|max:100',
            'work_experiences.*.start_month' => 'nullable|integer|min:1|max:12',
            'work_experiences.*.start_year' => 'nullable|integer|min:1950|max:2030',
            'work_experiences.*.end_month' => 'nullable|integer|min:1|max:12',
            'work_experiences.*.end_year' => 'nullable|integer|min:1950|max:2030',
            'work_experiences.*.salary' => 'nullable|numeric|min:0',
            
            // Skills
            'language_skills' => 'nullable|array',
            'computer_skills' => 'nullable|array',
            
            // Organizations
            'organizations' => 'nullable|array',
            
            // Achievements  
            'achievements' => 'nullable|array',
            
            // Driving licenses
            'driving_licenses' => 'nullable|array',
            'driving_licenses.*' => 'in:A,B1,B2,C',
            
            // General Information
            'willing_to_travel' => 'boolean',
            'has_vehicle' => 'boolean',
            'vehicle_types' => 'nullable|string|max:100',
            'motivation' => 'nullable|string',
            'strengths' => 'nullable|string',
            'weaknesses' => 'nullable|string',
            'start_work_date' => 'nullable|date',
            'information_source' => 'nullable|string|max:100',
            
            // Documents
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
    }

    private function createCandidate($validated)
    {
        return Candidate::create([
            'candidate_code' => $this->generateCandidateCode(),
            'position_applied' => $validated['position_applied'],
            'expected_salary' => $validated['expected_salary'] ?? null,
            'application_status' => 'pending'
        ]);
    }

    private function createPersonalData($candidate, $validated)
    {
        $age = null;
        if (!empty($validated['birth_date'])) {
            $age = \Carbon\Carbon::parse($validated['birth_date'])->age;
        }

        PersonalData::create([
            'candidate_id' => $candidate->id,
            'full_name' => $validated['full_name'],
            'birth_place' => $validated['birth_place'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'age' => $age,
            'gender' => $validated['gender'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'ethnicity' => $validated['ethnicity'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'email' => $validated['email'],
            'current_address' => $validated['current_address'] ?? null,
            'ktp_address' => $validated['ktp_address'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'residence_status' => $validated['residence_status'] ?? null,
            'height_cm' => $validated['height_cm'] ?? null,
            'weight_kg' => $validated['weight_kg'] ?? null,
            'vaccination_status' => $validated['vaccination_status'] ?? null,
        ]);
    }

    private function createFamilyMembers($candidate, $familyMembers)
    {
        foreach ($familyMembers as $index => $member) {
            FamilyMember::create([
                'candidate_id' => $candidate->id,
                'relationship' => $member['relationship'],
                'name' => $member['name'] ?? null,
                'age' => $member['age'] ?? null,
                'education' => $member['education'] ?? null,
                'occupation' => $member['occupation'] ?? null,
                'sequence_number' => $index + 1,
            ]);
        }
    }

    private function createFormalEducation($candidate, $educations)
    {
        foreach ($educations as $education) {
            FormalEducation::create([
                'candidate_id' => $candidate->id,
                'education_level' => $education['education_level'],
                'institution_name' => $education['institution_name'] ?? null,
                'major' => $education['major'] ?? null,
                'start_month' => $education['start_month'] ?? null,
                'start_year' => $education['start_year'] ?? null,
                'end_month' => $education['end_month'] ?? null,
                'end_year' => $education['end_year'] ?? null,
                'gpa' => $education['gpa'] ?? null,
            ]);
        }
    }

    private function createNonFormalEducation($candidate, $educations)
    {
        foreach ($educations as $education) {
            NonFormalEducation::create([
                'candidate_id' => $candidate->id,
                'course_name' => $education['course_name'],
                'organizer' => $education['organizer'] ?? null,
                'date_completed' => $education['date_completed'] ?? null,
                'description' => $education['description'] ?? null,
            ]);
        }
    }

    private function createWorkExperiences($candidate, $experiences)
    {
        foreach ($experiences as $index => $experience) {
            WorkExperience::create([
                'candidate_id' => $candidate->id,
                'company_name' => $experience['company_name'],
                'company_address' => $experience['company_address'] ?? null,
                'business_field' => $experience['business_field'] ?? null,
                'position' => $experience['position'] ?? null,
                'start_month' => $experience['start_month'] ?? null,
                'start_year' => $experience['start_year'] ?? null,
                'end_month' => $experience['end_month'] ?? null,
                'end_year' => $experience['end_year'] ?? null,
                'salary' => $experience['salary'] ?? null,
                'reason_for_leaving' => $experience['reason_for_leaving'] ?? null,
                'supervisor_name' => $experience['supervisor_name'] ?? null,
                'supervisor_phone' => $experience['supervisor_phone'] ?? null,
                'sequence_order' => $index + 1,
            ]);
        }
    }

    private function createLanguageSkills($candidate, $skills)
    {
        foreach ($skills as $skill) {
            LanguageSkill::create([
                'candidate_id' => $candidate->id,
                'language' => $skill['language'],
                'speaking_level' => $skill['speaking_level'] ?? null,
                'writing_level' => $skill['writing_level'] ?? null,
                'other_language_name' => $skill['other_language_name'] ?? null,
            ]);
        }
    }

    private function createComputerSkills($candidate, $skills)
    {
        foreach ($skills as $skill) {
            ComputerSkill::create([
                'candidate_id' => $candidate->id,
                'skill_type' => $skill['skill_type'],
                'skill_description' => $skill['skill_description'] ?? null,
                'proficiency_level' => $skill['proficiency_level'] ?? null,
            ]);
        }
    }

    private function createOrganizations($candidate, $organizations)
    {
        foreach ($organizations as $org) {
            Organization::create([
                'candidate_id' => $candidate->id,
                'organization_name' => $org['organization_name'],
                'field' => $org['field'] ?? null,
                'participation_period' => $org['participation_period'] ?? null,
                'description' => $org['description'] ?? null,
            ]);
        }
    }

    private function createAchievements($candidate, $achievements)
    {
        foreach ($achievements as $achievement) {
            Achievement::create([
                'candidate_id' => $candidate->id,
                'achievement_name' => $achievement['achievement_name'],
                'year' => $achievement['year'] ?? null,
                'description' => $achievement['description'] ?? null,
            ]);
        }
    }

    private function createDrivingLicenses($candidate, $licenses)
    {
        foreach ($licenses as $license) {
            DrivingLicense::create([
                'candidate_id' => $candidate->id,
                'license_type' => $license,
            ]);
        }
    }

    private function createGeneralInformation($candidate, $validated)
    {
        GeneralInformation::create([
            'candidate_id' => $candidate->id,
            'willing_to_travel' => $validated['willing_to_travel'] ?? false,
            'has_vehicle' => $validated['has_vehicle'] ?? false,
            'vehicle_types' => $validated['vehicle_types'] ?? null,
            'motivation' => $validated['motivation'] ?? null,
            'strengths' => $validated['strengths'] ?? null,
            'weaknesses' => $validated['weaknesses'] ?? null,
            'start_work_date' => $validated['start_work_date'] ?? null,
            'information_source' => $validated['information_source'] ?? null,
        ]);
    }

    private function handleDocumentUploads($candidate, $request)
    {
        $documentTypes = ['cv', 'photo', 'certificate', 'transcript', 'portfolio'];
        
        foreach ($documentTypes as $type) {
            if ($request->hasFile("documents.{$type}")) {
                $file = $request->file("documents.{$type}");
                $filename = time() . '_' . $type . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('documents/' . $candidate->candidate_code, $filename, 'public');
                
                DocumentUpload::create([
                    'candidate_id' => $candidate->id,
                    'document_type' => $type,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
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
        return view('job-application.success', compact('candidateCode'));
    }
}