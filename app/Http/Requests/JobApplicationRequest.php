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
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // 🆕 UPDATED: Simplified NIK validation focusing on actual OCR data
        $ocrNik = session('ocr_nik');
        $formNik = $this->input('nik');
        $ocrValidated = session('ocr_validated', false);
        
        // 🔍 ENHANCED DEBUGGING: Log all relevant session information
        \Log::info('=== NIK VALIDATION PREPARATION (Simplified) ===', [
            'ocr_nik' => $ocrNik,
            'form_nik' => $formNik,
            'ocr_validated' => $ocrValidated,
            'session_id' => session()->getId(),
            'has_ocr_ktp_path' => session()->has('ocr_ktp_path'),
            'ocr_ktp_path' => session('ocr_ktp_path'),
            'ocr_timestamp' => session('ocr_timestamp'),
        ]);

        // 🆕 SIMPLIFIED: Use OCR NIK if available, otherwise allow form input as fallback
        $finalNik = null;
        if ($ocrNik && strlen(trim($ocrNik)) === 16) {
            $finalNik = $ocrNik;
            \Log::info('✅ Using NIK from OCR session', ['nik' => $finalNik]);
        } elseif ($formNik && strlen(trim($formNik)) === 16) {
            $finalNik = $formNik;
            \Log::info('⚠️ Using NIK from form input (fallback)', ['nik' => $finalNik]);
        } else {
            \Log::error('❌ No valid NIK found', [
                'ocr_nik_length' => $ocrNik ? strlen(trim($ocrNik)) : 0,
                'form_nik_length' => $formNik ? strlen(trim($formNik)) : 0
            ]);
        }

        if ($finalNik) {
            $this->merge(['nik' => $finalNik]);
            \Log::info('✅ Final NIK merged into request', ['final_nik' => $finalNik]);
        } else {
            \Log::error('❌ No NIK to merge into request - form will fail validation');
        }
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
            'email' => 'required|email|unique:candidates,email',
            
            // 🆕 UPDATED: Simplified NIK validation - less restrictive but still secure
            'nik' => [
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]{16}$/',
                function ($attribute, $value, $fail) {
                    \Log::info('=== NIK CUSTOM VALIDATION (Simplified) ===', [
                        'attribute' => $attribute,
                        'provided_value' => $value,
                        'value_length' => strlen($value ?? ''),
                        'is_numeric' => is_numeric($value ?? ''),
                    ]);

                    // Enhanced NIK format validation
                    if (!$this->validateNIKFormat($value)) {
                        \Log::warning('❌ NIK format validation failed', ['nik' => $value]);
                        $fail('Format NIK tidak valid.');
                        return;
                    }

                    // Check if NIK already exists in database
                    $existingCandidate = \App\Models\Candidate::where('nik', $value)
                                                           ->whereNull('deleted_at')
                                                           ->first();
                    if ($existingCandidate) {
                        \Log::warning('❌ NIK already exists in database', [
                            'nik' => $value,
                            'existing_candidate_id' => $existingCandidate->id,
                            'existing_created_at' => $existingCandidate->created_at
                        ]);
                        $fail("NIK sudah terdaftar dalam sistem pada tanggal " . 
                              $existingCandidate->created_at->format('d/m/Y') . 
                              " untuk posisi " . $existingCandidate->position_applied . ".");
                        return;
                    }

                    // 🆕 SIMPLIFIED SECURITY: Only require OCR validation if OCR session exists
                    $ocrValidated = session('ocr_validated', false);
                    $ocrNik = session('ocr_nik');
                    $hasOcrData = session()->has('ocr_ktp_path') || session()->has('ocr_timestamp');
                    
                    \Log::info('=== NIK SECURITY VALIDATION (Simplified) ===', [
                        'provided_nik' => $value,
                        'ocr_nik' => $ocrNik,
                        'ocr_validated' => $ocrValidated,
                        'has_ocr_data' => $hasOcrData,
                        'nik_match_ocr' => $ocrNik === $value,
                    ]);

                    // If OCR was performed and NIK doesn't match, require matching
                    if ($ocrValidated && $ocrNik && $ocrNik !== $value) {
                        \Log::warning('❌ NIK mismatch with validated OCR result', [
                            'provided' => $value,
                            'ocr_result' => $ocrNik
                        ]);
                        $fail('NIK tidak sesuai dengan hasil scan KTP. NIK dari scan: ' . $ocrNik);
                        return;
                    }

                    // 🆕 RELAXED: Allow form submission without OCR if no OCR data exists
                    // This provides a fallback for cases where OCR fails or isn't available
                    if (!$ocrValidated && !$hasOcrData && !$ocrNik) {
                        \Log::info('⚠️ No OCR data found - allowing manual NIK entry', [
                            'nik' => $value,
                            'note' => 'This is a fallback for when OCR is not available'
                        ]);
                        return; // Allow the NIK to pass validation
                    }

                    \Log::info('✅ NIK validation passed', [
                        'nik' => $value,
                        'validation_method' => $ocrValidated ? 'ocr_validated' : 'manual_fallback'
                    ]);
                },
            ],
            'agreement' => 'required|accepted',
            
            // Personal Data
            'expected_salary' => 'required|numeric|min:0',
            'phone_number' => 'required|string|max:20',
            'phone_alternative' => 'required|string|max:20',
            'birth_place' => 'required|string|max:100',
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
            'formal_education.*.end_year' => [
                'required',
                'integer',
                'min:1950',
                'max:2030',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $startYear = request("formal_education.{$index}.start_year");
                    if ($startYear && $value < $startYear) {
                        $fail('Tahun selesai harus sama atau setelah tahun mulai.');
                    }
                },
            ],

            'formal_education.*.gpa' => [    'required','numeric', function ($attribute, $value, $fail) {
        if (!($value >= 1 && $value <= 4 || $value >= 1 && $value <= 100)) {
            $fail('Nilai harus antara 1.00–4.00 atau 1–100.');
                        }
                    },
                ],
            
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
            'work_experiences.*.end_year' => [
                'nullable',
                'integer',
                'min:1950',
                'max:2030',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $startYear = request("work_experiences.{$index}.start_year");
                    if ($startYear && $value && $value < $startYear) {
                        $fail('Tahun selesai kerja harus sama atau setelah tahun mulai.');
                    }
                },
            ],
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
            'start_work_date' => 'required|date|after:' . now()->format('Y-m-d'),
            'information_source' => 'required|string|max:255',
            
            // Document Uploads
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
     * 🆕 Enhanced NIK format validation
     */
    private function validateNIKFormat($nik)
    {
        \Log::info('Validating NIK format', ['nik' => $nik]);

        // Basic format check
        if (!preg_match('/^[0-9]{16}$/', $nik)) {
            \Log::warning('NIK format check failed: not 16 digits', ['nik' => $nik]);
            return false;
        }
        
        // Check if all digits are the same (invalid pattern)
        if (preg_match('/^(\d)\1{15}$/', $nik)) {
            \Log::warning('NIK format check failed: all same digits', ['nik' => $nik]);
            return false;
        }
        
        // Check if starts with 00 (usually invalid)
        if (substr($nik, 0, 2) === '00') {
            \Log::warning('NIK format check failed: starts with 00', ['nik' => $nik]);
            return false;
        }
        
        // Basic province code validation (first 2 digits should be 11-94)
        $provinceCode = (int)substr($nik, 0, 2);
        if ($provinceCode < 11 || $provinceCode > 94) {
            \Log::warning('NIK format check failed: invalid province code', [
                'nik' => $nik,
                'province_code' => $provinceCode
            ]);
            return false;
        }
        
        // Basic date validation (digits 7-12 represent DDMMYY)
        $day = (int)substr($nik, 6, 2);
        $month = (int)substr($nik, 8, 2);
        $year = (int)substr($nik, 10, 2);
        
        // Adjust day for female (subtract 40)
        if ($day > 40) {
            $day -= 40;
        }
        
        // Basic date range validation
        if ($day < 1 || $day > 31 || $month < 1 || $month > 12) {
            \Log::warning('NIK format check failed: invalid date', [
                'nik' => $nik,
                'day' => $day,
                'month' => $month,
                'year' => $year
            ]);
            return false;
        }
        
        \Log::info('✅ NIK format validation passed', ['nik' => $nik]);
        return true;
    }

    /**
     * Custom validation for image files - enhanced
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

        // Check file size
        if ($value->getSize() > 2 * 1024 * 1024) {
            $fail("File {$attribute} maksimal 2MB.");
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

            // 🆕 UPDATED: Improved NIK validation messages
            'nik.required' => 'NIK harus diisi. Gunakan fitur scan KTP untuk pengisian otomatis.',
            'nik.size' => 'NIK harus terdiri dari 16 digit angka.',
            'nik.regex' => 'NIK harus berupa 16 digit angka tanpa spasi atau karakter lain.',
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
            'nik' => 'NIK'
        ];
    }
}