<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

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
            'expected_salary' => 'required|numeric|min:0',
            'phone_number' => 'required|string|max:20',
            'phone_alternative' => 'required|string|max:20',
            'birth_place' => 'required|string|max:100',
            // ✅ PERUBAHAN: Explicit date validation dengan format yang jelas
            'birth_date' => 'required|date|before:' . now()->format('Y-m-d'),
            'gender' => 'required|in:Laki-laki,Perempuan',
            'religion' => 'required|string|max:50',
            'marital_status' => 'required|in:Lajang,Menikah,Janda,Duda',
            'ethnicity' => 'required|string|max:50',
            'current_address' => 'required|string',
            'current_address_status' => 'required|in:Milik Sendiri,Orang Tua,Kontrak,Sewa',
            'ktp_address' => 'required|string',
            'height_cm' => 'required|integer|min:100|max:250',
            'weight_kg' => 'required|integer|min:30|max:200',
            'vaccination_status' => 'nullable|in:Vaksin 1,Vaksin 2,Vaksin 3,Booster',
            
            // Family Members - At least one required
            'family_members' => 'required|array|min:1',
            'family_members.*.relationship' => 'required|in:Pasangan,Anak,Ayah,Ibu,Saudara',
            'family_members.*.name' => 'required|string|max:255',
            'family_members.*.age' => 'required|integer|min:0|max:120',
            'family_members.*.education' => 'required|string|max:100',
            'family_members.*.occupation' => 'required|string|max:100',
            
            // Formal Education - At least one required
            'formal_education' => 'required|array|min:1',
            'formal_education.*.education_level' => 'required|in:SMA/SMK,Diploma,S1,S2,S3',
            'formal_education.*.institution_name' => 'required|string|max:255',
            'formal_education.*.major' => 'required|string|max:100',
            'formal_education.*.start_year' => 'required|integer|min:1950|max:2030',
            'formal_education.*.end_year' => 'required|integer|min:1950|max:2030',
            'formal_education.*.gpa' => 'required|numeric|min:0|max:4',
            
            // Non-Formal Education - Optional
            'non_formal_education' => 'nullable|array',
            'non_formal_education.*.course_name' => 'required_with:non_formal_education.*|string|max:255',
            'non_formal_education.*.organizer' => 'nullable|string|max:255',
            'non_formal_education.*.date' => 'nullable|date',
            'non_formal_education.*.description' => 'nullable|string',
            
            // Skills
            'driving_licenses' => 'nullable|array',
            'driving_licenses.*' => 'in:A,B1,B2,C',
            'hardware_skills' => 'nullable|string',
            'software_skills' => 'nullable|string',
            'other_skills' => 'nullable|string',
            
            // Language Skills - At least one required
            'language_skills' => 'required|array|min:1',
            'language_skills.*.language' => 'required|string|max:50',
            'language_skills.*.speaking_level' => 'required|in:Pemula,Menengah,Mahir',
            'language_skills.*.writing_level' => 'required|in:Pemula,Menengah,Mahir',
            
            // Work Experiences - Optional
            'work_experiences' => 'nullable|array',
            'work_experiences.*.company_name' => 'required_with:work_experiences.*|string|max:255',
            'work_experiences.*.company_address' => 'nullable|string|max:255',
            'work_experiences.*.company_field' => 'nullable|string|max:100',
            'work_experiences.*.position' => 'nullable|string|max:100',
            'work_experiences.*.start_year' => 'nullable|integer|min:1950|max:2030',
            'work_experiences.*.end_year' => 'nullable|integer|min:1950|max:2030',
            'work_experiences.*.salary' => 'nullable|numeric|min:0',
            'work_experiences.*.reason_for_leaving' => 'nullable|string|max:255',
            'work_experiences.*.supervisor_contact' => 'nullable|string|max:255',
            
            // Social Activities - Optional
            'social_activities' => 'nullable|array',
            'social_activities.*.organization_name' => 'required_with:social_activities.*|string|max:255',
            'social_activities.*.field' => 'nullable|string|max:100',
            'social_activities.*.period' => 'nullable|string|max:50',
            'social_activities.*.description' => 'nullable|string',
            
            // Achievements - Optional
            'achievements' => 'nullable|array',
            'achievements.*.achievement' => 'required_with:achievements.*|string|max:255',
            'achievements.*.year' => 'nullable|integer|min:1950|max:2030',
            'achievements.*.description' => 'nullable|string',
            
            // General Information
            'willing_to_travel' => 'nullable|boolean',
            'has_vehicle' => 'nullable|boolean',
            'vehicle_types' => 'nullable|string|max:100',
            'motivation' => 'required|string',
            'strengths' => 'required|string',
            'weaknesses' => 'required|string',
            'other_income' => 'nullable|string|max:255',
            'has_police_record' => 'nullable|boolean',
            'police_record_detail' => 'nullable|required_if:has_police_record,1|string|max:255',
            'has_serious_illness' => 'nullable|boolean',
            'illness_detail' => 'nullable|required_if:has_serious_illness,1|string|max:255',
            'has_tattoo_piercing' => 'nullable|boolean',
            'tattoo_piercing_detail' => 'nullable|required_if:has_tattoo_piercing,1|string|max:255',
            'has_other_business' => 'nullable|boolean',
            'other_business_detail' => 'nullable|required_if:has_other_business,1|string|max:255',
            'absence_days' => 'nullable|integer|min:0|max:365',
            // ✅ PERUBAHAN: Explicit date validation untuk start_work_date
            'start_work_date' => 'required|date|after:' . now()->format('Y-m-d'),
            'information_source' => 'required|string|max:255',
            
            // Document Uploads - Enhanced validation with custom rule
            'cv' => 'required|file|mimes:pdf|max:2048',
            'photo' => ['required', 'file', 'max:2048', function ($attribute, $value, $fail) {
                $this->validateImageFile($attribute, $value, $fail);
            }],
            'transcript' => 'required|file|mimes:pdf|max:2048',
            'certificates' => 'nullable|array',
            'certificates.*' => 'file|mimes:pdf|max:2048',
        ];
    }

    /**
     * Custom validation for image files
     */
    private function validateImageFile($attribute, $value, $fail)
    {
        if (!$value || !$value->isValid()) {
            $fail("File {$attribute} tidak valid atau rusak.");
            return;
        }

        // Get file info
        $originalName = $value->getClientOriginalName();
        $mimeType = $value->getMimeType();
        $extension = strtolower($value->getClientOriginalExtension());
        $realPath = $value->getRealPath();

        // Log file details for debugging
        Log::info("File validation for {$attribute}", [
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => $value->getSize(),
            'real_path' => $realPath
        ]);

        // Valid extensions
        $validExtensions = ['jpg', 'jpeg', 'png'];
        
        // Valid MIME types (including variations)
        $validMimeTypes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/pjpeg', // IE JPEG
            'image/x-png'  // Some browsers
        ];

        // Check extension
        if (!in_array($extension, $validExtensions)) {
            Log::warning("Invalid extension for {$attribute}", [
                'extension' => $extension,
                'valid_extensions' => $validExtensions
            ]);
            $fail("File {$attribute} harus berformat JPG atau PNG (ekstensi file: {$extension}).");
            return;
        }

        // Check MIME type
        if (!in_array($mimeType, $validMimeTypes)) {
            Log::warning("Invalid MIME type for {$attribute}", [
                'mime_type' => $mimeType,
                'valid_mime_types' => $validMimeTypes
            ]);
            $fail("File {$attribute} harus berformat JPG atau PNG (tipe file terdeteksi: {$mimeType}).");
            return;
        }

        // Additional check: Try to verify if it's actually an image
        if (function_exists('getimagesize')) {
            $imageInfo = @getimagesize($realPath);
            if ($imageInfo === false) {
                Log::warning("File is not a valid image for {$attribute}", [
                    'file' => $originalName
                ]);
                $fail("File {$attribute} bukan gambar yang valid atau file rusak.");
                return;
            }

            // Check image type from getimagesize
            $imageType = $imageInfo[2];
            $validImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
            
            if (!in_array($imageType, $validImageTypes)) {
                Log::warning("Invalid image type for {$attribute}", [
                    'image_type' => $imageType,
                    'valid_types' => $validImageTypes
                ]);
                $fail("File {$attribute} harus berupa gambar JPG atau PNG yang valid.");
                return;
            }
        }

        Log::info("File validation passed for {$attribute}");
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Basic required fields
            'position_applied.required' => 'Posisi yang dilamar harus dipilih.',
            'full_name.required' => 'Nama lengkap harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar dalam sistem.',
            'agreement.required' => 'Anda harus menyetujui pernyataan.',
            'agreement.accepted' => 'Anda harus menyetujui pernyataan.',
            
            // Personal data
            'expected_salary.required' => 'Gaji yang diharapkan harus diisi.',
            'expected_salary.numeric' => 'Gaji yang diharapkan harus berupa angka.',
            'phone_number.required' => 'Nomor telepon harus diisi.',
            'phone_alternative.required' => 'Telepon alternatif harus diisi.',
            'birth_place.required' => 'Tempat lahir harus diisi.',
            'birth_date.required' => 'Tanggal lahir harus diisi.',
            // ✅ PERUBAHAN: Error message yang lebih jelas dengan tanggal spesifik
            'birth_date.before' => 'Tanggal lahir harus sebelum tanggal ' . now()->format('d/m/Y') . '.',
            'gender.required' => 'Jenis kelamin harus dipilih.',
            'religion.required' => 'Agama harus diisi.',
            'marital_status.required' => 'Status pernikahan harus dipilih.',
            'ethnicity.required' => 'Suku bangsa harus diisi.',
            'current_address.required' => 'Alamat tempat tinggal saat ini harus diisi.',
            'current_address_status.required' => 'Status tempat tinggal harus dipilih.',
            'ktp_address.required' => 'Alamat sesuai KTP harus diisi.',
            'height_cm.required' => 'Tinggi badan harus diisi.',
            'weight_kg.required' => 'Berat badan harus diisi.',
            
            // Family members
            'family_members.required' => 'Data keluarga harus diisi minimal 1 anggota.',
            'family_members.min' => 'Data keluarga harus diisi minimal 1 anggota.',
            'family_members.*.relationship.required' => 'Hubungan keluarga harus dipilih.',
            'family_members.*.name.required' => 'Nama anggota keluarga harus diisi.',
            'family_members.*.age.required' => 'Usia anggota keluarga harus diisi.',
            'family_members.*.education.required' => 'Pendidikan anggota keluarga harus diisi.',
            'family_members.*.occupation.required' => 'Pekerjaan anggota keluarga harus diisi.',
            
            // Formal education
            'formal_education.required' => 'Pendidikan formal harus diisi minimal 1 pendidikan.',
            'formal_education.min' => 'Pendidikan formal harus diisi minimal 1 pendidikan.',
            'formal_education.*.education_level.required' => 'Jenjang pendidikan harus dipilih.',
            'formal_education.*.institution_name.required' => 'Nama institusi harus diisi.',
            'formal_education.*.major.required' => 'Jurusan harus diisi.',
            'formal_education.*.start_year.required' => 'Tahun mulai harus diisi.',
            'formal_education.*.end_year.required' => 'Tahun selesai harus diisi.',
            'formal_education.*.gpa.required' => 'IPK/Nilai harus diisi.',
            
            // Language skills
            'language_skills.required' => 'Kemampuan bahasa harus diisi minimal 1 bahasa.',
            'language_skills.min' => 'Kemampuan bahasa harus diisi minimal 1 bahasa.',
            'language_skills.*.language.required' => 'Bahasa harus dipilih.',
            'language_skills.*.speaking_level.required' => 'Kemampuan berbicara harus dipilih.',
            'language_skills.*.writing_level.required' => 'Kemampuan menulis harus dipilih.',
            
            // General information
            'motivation.required' => 'Motivasi bergabung harus diisi.',
            'strengths.required' => 'Kelebihan Anda harus diisi.',
            'weaknesses.required' => 'Kekurangan Anda harus diisi.',
            'start_work_date.required' => 'Tanggal mulai kerja harus diisi.',
            // ✅ PERUBAHAN: Error message yang lebih jelas dengan tanggal spesifik
            'start_work_date.after' => 'Tanggal mulai kerja harus setelah tanggal ' . now()->format('d/m/Y') . '.',
            'information_source.required' => 'Sumber informasi lowongan harus diisi.',
            
            // File uploads
            'cv.required' => 'CV/Resume harus diupload.',
            'cv.file' => 'CV/Resume harus berupa file.',
            'cv.mimes' => 'CV/Resume harus berformat PDF.',
            'cv.max' => 'Ukuran CV/Resume maksimal 2MB.',
            'photo.required' => 'Foto harus diupload.',
            'photo.file' => 'Foto harus berupa file.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
            'transcript.required' => 'Transkrip nilai harus diupload.',
            'transcript.file' => 'Transkrip nilai harus berupa file.',
            'transcript.mimes' => 'Transkrip nilai harus berformat PDF.',
            'transcript.max' => 'Ukuran transkrip nilai maksimal 2MB.',
            'certificates.*.file' => 'Sertifikat harus berupa file.',
            'certificates.*.mimes' => 'Sertifikat harus berformat PDF.',
            'certificates.*.max' => 'Ukuran sertifikat maksimal 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cv' => 'CV/Resume',
            'photo' => 'Foto',
            'transcript' => 'Transkrip Nilai',
            'certificates' => 'Sertifikat',
            'certificates.*' => 'Sertifikat',
        ];
    }
}