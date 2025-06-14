<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Required fields
            'position_applied' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:personal_data,email',
            'agreement' => 'required|accepted',
            
            // Personal Data
            'expected_salary' => 'nullable|numeric|min:0',
            'phone_number' => 'nullable|string|max:20',
            'phone_alternative' => 'nullable|string|max:20',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Laki-laki,Perempuan',
            'religion' => 'nullable|string|max:50',
            'marital_status' => 'nullable|in:Lajang,Menikah,Janda,Duda',
            'ethnicity' => 'nullable|string|max:50',
            'current_address' => 'nullable|string',
            'current_address_status' => 'nullable|in:Milik Sendiri,Orang Tua,Kontrak,Sewa',
            'ktp_address' => 'nullable|string',
            'height_cm' => 'nullable|integer|min:100|max:250',
            'weight_kg' => 'nullable|integer|min:30|max:200',
            'vaccination_status' => 'nullable|in:Vaksin 1,Vaksin 2,Vaksin 3,Booster',
            
            // Family Members
            'family_members' => 'nullable|array',
            'family_members.*.relationship' => 'nullable|in:Pasangan,Anak,Ayah,Ibu,Saudara',
            'family_members.*.name' => 'nullable|string|max:255',
            'family_members.*.age' => 'nullable|integer|min:0|max:120',
            'family_members.*.education' => 'nullable|string|max:100',
            'family_members.*.occupation' => 'nullable|string|max:100',
            
            // Formal Education
            'formal_education' => 'nullable|array',
            'formal_education.*.education_level' => 'nullable|in:SMA/SMK,Diploma,S1,S2,S3',
            'formal_education.*.institution_name' => 'nullable|string|max:255',
            'formal_education.*.major' => 'nullable|string|max:100',
            'formal_education.*.start_year' => 'nullable|integer|min:1950|max:2030',
            'formal_education.*.end_year' => 'nullable|integer|min:1950|max:2030',
            'formal_education.*.gpa' => 'nullable|numeric|min:0|max:4',
            
            // Non-Formal Education
            'non_formal_education' => 'nullable|array',
            'non_formal_education.*.course_name' => 'nullable|string|max:255',
            'non_formal_education.*.organizer' => 'nullable|string|max:255',
            'non_formal_education.*.date' => 'nullable|date',
            'non_formal_education.*.description' => 'nullable|string',
            
            // Skills
            'driving_licenses' => 'nullable|array',
            'driving_licenses.*' => 'in:A,B1,B2,C',
            'hardware_skills' => 'nullable|string',
            'software_skills' => 'nullable|string',
            'other_skills' => 'nullable|string',
            
            // Language Skills
            'language_skills' => 'nullable|array',
            'language_skills.*.language' => 'nullable|string|max:50',
            'language_skills.*.speaking_level' => 'nullable|in:Pemula,Menengah,Mahir',
            'language_skills.*.writing_level' => 'nullable|in:Pemula,Menengah,Mahir',
            
            // Work Experiences
            'work_experiences' => 'nullable|array',
            'work_experiences.*.company_name' => 'nullable|string|max:255',
            'work_experiences.*.company_address' => 'nullable|string|max:255',
            'work_experiences.*.company_field' => 'nullable|string|max:100',
            'work_experiences.*.position' => 'nullable|string|max:100',
            'work_experiences.*.start_year' => 'nullable|integer|min:1950|max:2030',
            'work_experiences.*.end_year' => 'nullable|integer|min:1950|max:2030',
            'work_experiences.*.salary' => 'nullable|numeric|min:0',
            'work_experiences.*.reason_for_leaving' => 'nullable|string|max:255',
            'work_experiences.*.supervisor_contact' => 'nullable|string|max:255',
            
            // Social Activities
            'social_activities' => 'nullable|array',
            'social_activities.*.organization_name' => 'nullable|string|max:255',
            'social_activities.*.field' => 'nullable|string|max:100',
            'social_activities.*.period' => 'nullable|string|max:50',
            'social_activities.*.description' => 'nullable|string',
            
            // Achievements
            'achievements' => 'nullable|array',
            'achievements.*.achievement' => 'nullable|string|max:255',
            'achievements.*.year' => 'nullable|integer|min:1950|max:2030',
            'achievements.*.description' => 'nullable|string',
            
            // General Information
            'willing_to_travel' => 'nullable|boolean',
            'has_vehicle' => 'nullable|boolean',
            'vehicle_types' => 'nullable|string|max:100',
            'motivation' => 'nullable|string',
            'strengths' => 'nullable|string',
            'weaknesses' => 'nullable|string',
            'other_income' => 'nullable|string|max:255',
            'has_police_record' => 'nullable|boolean',
            'police_record_detail' => 'nullable|string|max:255',
            'has_serious_illness' => 'nullable|boolean',
            'illness_detail' => 'nullable|string|max:255',
            'has_tattoo_piercing' => 'nullable|boolean',
            'tattoo_piercing_detail' => 'nullable|string|max:255',
            'has_other_business' => 'nullable|boolean',
            'other_business_detail' => 'nullable|string|max:255',
            'absence_days' => 'nullable|integer|min:0|max:365',
            'start_work_date' => 'nullable|date|after:today',
            'information_source' => 'nullable|string|max:255',
            
            // Document Uploads
            'documents.cv' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'documents.photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'documents.certificates' => 'nullable|array',
            'documents.certificates.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
            'documents.transcript' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'position_applied.required' => 'Posisi yang dilamar harus dipilih.',
            'full_name.required' => 'Nama lengkap harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar dalam sistem.',
            'agreement.required' => 'Anda harus menyetujui pernyataan.',
            'agreement.accepted' => 'Anda harus menyetujui pernyataan.',
            'birth_date.before' => 'Tanggal lahir tidak valid.',
            'start_work_date.after' => 'Tanggal mulai kerja harus setelah hari ini.',
            'documents.*.mimes' => 'Format file harus PDF, JPG, atau PNG.',
            'documents.*.max' => 'Ukuran file maksimal 2MB.',
        ];
    }
}