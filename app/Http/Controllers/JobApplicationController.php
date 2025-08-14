<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Position,
    FamilyMember,
    FormalEducation,
    NonFormalEducation,
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
use App\Services\CodeGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Requests\JobApplicationRequest;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class JobApplicationController extends Controller
{
    /**
     * Show job application form
     */
    public function showForm()
    {
        $positions = Position::active()
                            ->orderBy('department')
                            ->orderBy('position_name')
                            ->get();
        
        return view('job-application.form', compact('positions'));
    }

    /**
     * Get active positions API
     */
    public function getPositions()
    {
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
        
        $validated = $request->validated();
        $uploadedFiles = [];
        
        try {
            DB::beginTransaction();
            
            Log::info('Starting job application submission', [
                'position_applied' => $validated['position_applied'],
                'email' => $validated['email']
            ]);
            
            // Validate position is still active
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
                $this->createFormalEducation($candidate, $validated['formal_education']);
                Log::info('Formal education created', ['candidate_id' => $candidate->id, 'count' => count($validated['formal_education'])]);
            }
            
            if (!empty($validated['non_formal_education'])) {
                $this->createNonFormalEducation($candidate, $validated['non_formal_education']);
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
            
            // 10. Handle File Uploads with Chrome/Safari optimization
            $uploadedFiles = $this->handleDocumentUploads($candidate, $request);
            Log::info('Document uploads processed', ['candidate_id' => $candidate->id, 'files_count' => count($uploadedFiles)]);
            
            // 11. Create Application Log
            ApplicationLog::create([
                'candidate_id' => $candidate->id,
                'user_id' => null,
                'action_type' => 'document_upload',
                'action_description' => 'Application submitted via online form'
            ]);
            Log::info('Application log created', ['candidate_id' => $candidate->id]);
            
            DB::commit();
            Log::info('Job application submitted successfully', ['candidate_code' => $candidate->candidate_code]);

            // 🆕 Clear OCR session data after successful submit
            session()->forget([
                'ocr_validated',
                'ocr_nik', 
                'ocr_timestamp'
            ]);

            Log::info('OCR session data cleared after successful submission', [
                'candidate_id' => $candidate->id
            ]);

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

    /**
     * 🔧 KTP OCR upload - ONLY FOR NIK EXTRACTION (NO FILE STORAGE)
     */
    public function uploadKtpOcr(Request $request)
    {
        try {
            $request->validate([
                'ktp_image' => 'required|file|mimes:jpg,jpeg,png|max:5120',
                'extracted_nik' => 'required|string|size:16|regex:/^[0-9]{16}$/'
            ]);

            $extractedNik = $request->input('extracted_nik');
            
            Log::info('Processing KTP OCR for NIK extraction only', [
                'extracted_nik' => $extractedNik,
                'session_id' => session()->getId()
            ]);
            
            // Validate NIK format
            if (!preg_match('/^[0-9]{16}$/', $extractedNik)) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIK harus 16 digit angka'
                ], 400);
            }

            // Check if NIK already exists
            $existingCandidate = Candidate::where('nik', $extractedNik)->first();
            if ($existingCandidate) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIK sudah terdaftar dalam sistem'
                ], 409);
            }

            // 🆕 UPDATED: Only store NIK in session (NO FILE STORAGE)
            session([
                'ocr_validated' => true,
                'ocr_nik' => $extractedNik,
                'ocr_timestamp' => time()
            ]);

            // Verify session data was saved
            $sessionVerification = [
                'ocr_validated' => session('ocr_validated'),
                'ocr_nik' => session('ocr_nik'),
                'session_saved' => session('ocr_validated') === true
            ];

            Log::info('✅ OCR NIK processed and stored in session only', [
                'nik' => $extractedNik,
                'session_id' => session()->getId(),
                'session_verification' => $sessionVerification
            ]);

            return response()->json([
                'success' => true,
                'message' => 'NIK berhasil diekstrak dari KTP dan field NIK telah dikunci',
                'data' => [
                    'nik' => $extractedNik
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error uploading KTP OCR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => session()->getId()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses KTP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear OCR session data
     */
    public function clearKtpTemp(Request $request)
    {
        try {
            // Clear session data
            session()->forget([
                'ocr_validated',
                'ocr_nik', 
                'ocr_timestamp'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data OCR NIK berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing OCR session data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data OCR'
            ], 500);
        }
    }

    // 🔧 CRITICAL FIX: handleDocumentUploads method untuk Chrome/Safari support
    private function handleDocumentUploads($candidate, $request)
    {
        $uploadedFiles = [];
        
        try {
            // Create candidate folder
            $candidateFolder = "documents/candidates/{$candidate->candidate_code}";
            
            if (!Storage::disk('public')->exists($candidateFolder)) {
                Storage::disk('public')->makeDirectory($candidateFolder);
                Log::info('Created candidate folder', [
                    'candidate_id' => $candidate->id,
                    'folder' => $candidateFolder,
                    'full_path' => Storage::disk('public')->path($candidateFolder)
                ]);
            }

            // 🆕 BROWSER DETECTION for logging
            $userAgent = $request->header('User-Agent', '');
            $isChrome = strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edge') === false;
            $isSafari = strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false;
            $browserInfo = $isChrome ? 'Chrome' : ($isSafari ? 'Safari' : 'Other');

            Log::info('=== DOCUMENT UPLOAD - BROWSER DETECTION ===', [
                'candidate_id' => $candidate->id,
                'browser' => $browserInfo,
                'user_agent' => $userAgent,
                'is_chrome' => $isChrome,
                'is_safari' => $isSafari
            ]);

            // Handle CV
            if ($request->hasFile('cv')) {
                $file = $request->file('cv');
                
                Log::info('=== CV UPLOAD ===', [
                    'browser' => $browserInfo,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extension' => $file->getClientOriginalExtension()
                ]);
                
                // Standard PDF validation
                if (!$this->validateFileForUpload($file, ['pdf'], ['application/pdf'], 2 * 1024 * 1024, $browserInfo)) {
                    throw new \Exception('CV: Format file harus PDF dan ukuran maksimal 2MB');
                }
                
                $filename = $this->generateSecureFilename('cv', $file->getClientOriginalExtension(), $candidate);
                $filePath = $candidateFolder . '/' . $filename;
                
                $stored = Storage::disk('public')->putFileAs($candidateFolder, $file, $filename);
                
                if ($stored) {
                    $uploadedFiles[] = $filePath;
                    
                    DocumentUpload::create([
                        'candidate_id' => $candidate->id,
                        'document_type' => 'cv',
                        'document_name' => 'CV',
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);

                    Log::info('✅ CV uploaded successfully', [
                        'browser' => $browserInfo,
                        'candidate_id' => $candidate->id, 
                        'file_path' => $filePath,
                        'original_name' => $file->getClientOriginalName()
                    ]);
                }
            }

            // 🔧 CRITICAL: Handle Photo with Chrome/Safari optimization
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                
                Log::info('=== PHOTO UPLOAD - CHROME/SAFARI OPTIMIZATION ===', [
                    'browser' => $browserInfo,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extension' => $file->getClientOriginalExtension(),
                    'is_valid' => $file->isValid(),
                    'path' => $file->path(),
                    'real_path' => $file->getRealPath()
                ]);
                
                // 🆕 CHROME/SAFARI OPTIMIZED: Photo validation
                if (!$this->validatePhotoForBrowsers($file, $browserInfo)) {
                    throw new \Exception('Foto: Format file harus JPG, JPEG, atau PNG dan ukuran maksimal 2MB');
                }
                
                $filename = $this->generateSecureFilename('photo', $file->getClientOriginalExtension(), $candidate);
                $filePath = $candidateFolder . '/' . $filename;
                
                $stored = Storage::disk('public')->putFileAs($candidateFolder, $file, $filename);
                
                if ($stored) {
                    $uploadedFiles[] = $filePath;
                    
                    DocumentUpload::create([
                        'candidate_id' => $candidate->id,
                        'document_type' => 'photo',
                        'document_name' => 'Photo',
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);

                    Log::info('✅ Photo uploaded successfully with browser optimization', [
                        'browser' => $browserInfo,
                        'candidate_id' => $candidate->id, 
                        'file_path' => $filePath,
                        'original_name' => $file->getClientOriginalName(),
                        'stored_size' => Storage::disk('public')->size($filePath)
                    ]);
                } else {
                    throw new \Exception('Gagal menyimpan foto ke storage');
                }
            }

            // Handle Transcript (same as before)
            if ($request->hasFile('transcript')) {
                $file = $request->file('transcript');
                
                Log::info('=== TRANSCRIPT UPLOAD ===', [
                    'browser' => $browserInfo,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType()
                ]);
                
                if (!$this->validateFileForUpload($file, ['pdf'], ['application/pdf'], 2 * 1024 * 1024, $browserInfo)) {
                    throw new \Exception('Transkrip: Format file harus PDF dan ukuran maksimal 2MB');
                }
                
                $filename = $this->generateSecureFilename('transcript', $file->getClientOriginalExtension(), $candidate);
                $filePath = $candidateFolder . '/' . $filename;
                
                $stored = Storage::disk('public')->putFileAs($candidateFolder, $file, $filename);
                
                if ($stored) {
                    $uploadedFiles[] = $filePath;
                    
                    DocumentUpload::create([
                        'candidate_id' => $candidate->id,
                        'document_type' => 'transcript',
                        'document_name' => 'Transcript',
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);

                    Log::info('✅ Transcript uploaded successfully', [
                        'browser' => $browserInfo,
                        'candidate_id' => $candidate->id, 
                        'file_path' => $filePath
                    ]);
                }
            }

            // Handle Certificates (multiple)
            if ($request->hasFile('certificates')) {
                $certificates = $request->file('certificates');
                
                $certificatesFolder = $candidateFolder . '/certificates';
                if (!Storage::disk('public')->exists($certificatesFolder)) {
                    Storage::disk('public')->makeDirectory($certificatesFolder);
                }
                
                foreach ($certificates as $index => $certificate) {
                    Log::info('=== CERTIFICATE UPLOAD ===', [
                        'browser' => $browserInfo,
                        'index' => $index + 1,
                        'file_name' => $certificate->getClientOriginalName(),
                        'mime_type' => $certificate->getMimeType()
                    ]);
                    
                    if (!$this->validateFileForUpload($certificate, ['pdf'], ['application/pdf'], 2 * 1024 * 1024, $browserInfo)) {
                        Log::warning('Skipping invalid certificate', ['index' => $index + 1]);
                        continue;
                    }
                    
                    $filename = $this->generateSecureFilename('certificate_' . ($index + 1), $certificate->getClientOriginalExtension(), $candidate);
                    $filePath = $certificatesFolder . '/' . $filename;
                    
                    $stored = Storage::disk('public')->putFileAs($certificatesFolder, $certificate, $filename);
                    
                    if ($stored) {
                        $uploadedFiles[] = $filePath;
                        
                        DocumentUpload::create([
                            'candidate_id' => $candidate->id,
                            'document_type' => 'certificates',
                            'document_name' => 'Certificate ' . ($index + 1),
                            'original_filename' => $certificate->getClientOriginalName(),
                            'file_path' => $filePath,
                            'file_size' => $certificate->getSize(),
                            'mime_type' => $certificate->getMimeType(),
                        ]);

                        Log::info('✅ Certificate uploaded successfully', [
                            'browser' => $browserInfo,
                            'candidate_id' => $candidate->id, 
                            'index' => $index + 1, 
                            'file_path' => $filePath
                        ]);
                    }
                }
            }

            Log::info('=== DOCUMENT UPLOADS COMPLETED ===', [
                'browser' => $browserInfo,
                'candidate_id' => $candidate->id,
                'total_files' => count($uploadedFiles),
                'uploaded_files' => $uploadedFiles
            ]);

            return $uploadedFiles;

        } catch (\Exception $e) {
            Log::error('❌ Error during file upload', [
                'browser' => $browserInfo ?? 'Unknown',
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage(),
                'uploaded_files' => $uploadedFiles
            ]);
            $this->cleanupUploadedFiles($uploadedFiles);
            throw $e;
        }
    }

    // 🆕 NEW: Chrome/Safari optimized photo validation
    private function validatePhotoForBrowsers($file, $browserInfo)
    {
        try {
            // Basic checks
            if (!$file || !$file->isValid()) {
                Log::error("Photo validation failed: Invalid file", ['browser' => $browserInfo]);
                return false;
            }

            // Size check
            $maxSize = 2 * 1024 * 1024; // 2MB
            if ($file->getSize() > $maxSize) {
                Log::error("Photo validation failed: File too large", [
                    'browser' => $browserInfo,
                    'size' => $file->getSize(),
                    'max_size' => $maxSize
                ]);
                return false;
            }

            if ($file->getSize() === 0) {
                Log::error("Photo validation failed: Empty file", ['browser' => $browserInfo]);
                return false;
            }

            // Extension check (PRIORITY)
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (!in_array($extension, $allowedExtensions)) {
                Log::error("Photo validation failed: Invalid extension", [
                    'browser' => $browserInfo,
                    'extension' => $extension,
                    'allowed' => $allowedExtensions
                ]);
                return false;
            }

            // 🔧 CRITICAL: Chrome/Safari MIME type handling
            $mimeType = $file->getMimeType();
            $isChrome = strpos($browserInfo, 'Chrome') !== false;
            $isSafari = strpos($browserInfo, 'Safari') !== false;

            if ($isChrome || $isSafari) {
                // 🆕 RELAXED: For Chrome/Safari, accept various MIME types
                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/jpg', 
                    'image/png',
                    'image/webp',
                    'image/pjpeg', // IE/Edge
                    'image/x-png', // Alternative PNG
                    'image/heic', // iPhone
                    'image/heif', // iPhone
                    'application/octet-stream', // Chrome fallback
                    'binary/octet-stream', // Safari fallback
                    '', // Empty MIME
                    null // Null MIME
                ];

                if ($mimeType && !in_array($mimeType, $allowedMimeTypes)) {
                    Log::warning("Chrome/Safari: Unexpected MIME type but accepting based on extension", [
                        'browser' => $browserInfo,
                        'mime_type' => $mimeType,
                        'extension' => $extension
                    ]);
                }

                Log::info("✅ Chrome/Safari photo validation passed", [
                    'browser' => $browserInfo,
                    'extension' => $extension,
                    'mime_type' => $mimeType,
                    'size' => $file->getSize()
                ]);

                return true;
            } else {
                // Standard validation for other browsers
                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/jpg',
                    'image/png'
                ];

                if (!in_array($mimeType, $allowedMimeTypes)) {
                    Log::error("Standard photo validation failed: Invalid MIME type", [
                        'browser' => $browserInfo,
                        'mime_type' => $mimeType,
                        'allowed' => $allowedMimeTypes
                    ]);
                    return false;
                }

                Log::info("✅ Standard photo validation passed", [
                    'browser' => $browserInfo,
                    'extension' => $extension,
                    'mime_type' => $mimeType
                ]);

                return true;
            }

        } catch (\Exception $e) {
            Log::error("Photo validation exception", [
                'browser' => $browserInfo,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // 🔧 IMPROVED: General file validation with browser support
    private function validateFileForUpload($file, $allowedExtensions, $allowedMimeTypes, $maxSize, $browserInfo)
    {
        try {
            if (!$file || !$file->isValid()) {
                return false;
            }

            if ($file->getSize() > $maxSize || $file->getSize() === 0) {
                return false;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                return false;
            }

            // For Chrome/Safari, be more relaxed with MIME types
            $isChrome = strpos($browserInfo, 'Chrome') !== false;
            $isSafari = strpos($browserInfo, 'Safari') !== false;

            if ($isChrome || $isSafari) {
                // More permissive MIME type checking
                $mimeType = $file->getMimeType();
                if ($mimeType && !in_array($mimeType, array_merge($allowedMimeTypes, ['application/octet-stream', 'binary/octet-stream', '']))) {
                    Log::warning("Chrome/Safari: Unexpected MIME type but accepting", [
                        'browser' => $browserInfo,
                        'mime_type' => $mimeType,
                        'extension' => $extension
                    ]);
                }
                return true;
            } else {
                // Standard MIME type checking
                return in_array($file->getMimeType(), $allowedMimeTypes);
            }

        } catch (\Exception $e) {
            Log::error("File validation exception", [
                'browser' => $browserInfo,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate secure filename
     */
    private function generateSecureFilename($type, $extension, $candidate)
    {
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        $candidateHash = substr(md5($candidate->candidate_code), 0, 8);
        
        return $type . '_' . $candidateHash . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    // Create formal education records
    private function createFormalEducation($candidate, $formalEducations)
    {
        foreach ($formalEducations as $index => $education) {
            if (empty($education['education_level']) && empty($education['institution_name'])) {
                continue;
            }

            try {
                FormalEducation::create([
                    'candidate_id' => $candidate->id,
                    'education_level' => $education['education_level'] ?? null,
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

    // Create non-formal education records
    private function createNonFormalEducation($candidate, $nonFormalEducations)
    {
        foreach ($nonFormalEducations as $index => $education) {
            if (empty($education['course_name'])) {
                continue;
            }

            try {
                NonFormalEducation::create([
                    'candidate_id' => $candidate->id,
                    'course_name' => $education['course_name'] ?? null,
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

    public function success()
    {
        $candidateCode = request()->get('candidate_code') ?: session('candidate_code');
        
        if (!$candidateCode) {
            return redirect()->route('job.application.form')
                ->with('error', 'Sesi tidak valid. Silakan isi form lamaran kembali.');
        }
        
        $candidate = Candidate::where('candidate_code', $candidateCode)->first();
        if (!$candidate) {
            return redirect()->route('job.application.form')
                ->with('error', 'Data kandidat tidak ditemukan.');
        }
        
        $kraeplinTest = KraeplinTestSession::where('candidate_id', $candidate->id)
            ->where('status', 'completed')
            ->first();
            
        $disc3dTest = Disc3DTestSession::where('candidate_id', $candidate->id)
            ->where('status', 'completed')
            ->first();
        
        Log::info('Success page accessed', [
            'candidate_code' => $candidateCode,
            'kraeplin_completed' => (bool) $kraeplinTest,
            'disc3d_completed' => (bool) $disc3dTest,
            'url' => request()->fullUrl()
        ]);
        
        if (!$kraeplinTest) {
            return redirect()->route('kraeplin.instructions', $candidateCode)
                ->with('warning', 'Anda perlu menyelesaikan Test Kraeplin terlebih dahulu.');
        }
        
        if (!$disc3dTest) {
            return redirect()->route('disc3d.instructions', $candidateCode)
                ->with('warning', 'Anda perlu menyelesaikan Test DISC 3D untuk melengkapi proses lamaran.');
        }
        
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
     * Get candidate test status
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
     * Determine next step for candidate
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
     * Get candidate summary for dashboard/HR
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

    // Create candidate with CodeGenerationService
    private function createCandidate($validated, $positionId)
    {
        try {
            // Priority NIK: OCR Session > Form input (as fallback)
            $nik = session('ocr_nik') ?: $validated['nik'] ?? null;
            
            if (!$nik || strlen($nik) !== 16) {
                throw new \Exception('NIK tidak valid atau tidak ditemukan dari OCR session');
            }

            // Generate candidate code using CodeGenerationService
            $candidateCode = CodeGenerationService::generateCandidateCode();

            $candidateData = [
                'candidate_code' => $candidateCode,
                'position_id' => $positionId,
                'position_applied' => $validated['position_applied'],
                'expected_salary' => $validated['expected_salary'] ?? null,
                'application_status' => 'submitted',
                'application_date' => now(),

                // Personal data directly in candidates table
                'nik' => $nik,
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

            Log::info('Creating candidate with generated code', [
                'candidate_code' => $candidateCode,
                'nik' => $nik,
                'ocr_validated' => session('ocr_validated', false)
            ]);

            return Candidate::create($candidateData);
        } catch (\Exception $e) {
            Log::error('Error creating candidate', [
                'error' => $e->getMessage(),
                'nik_source' => session('ocr_nik') ? 'OCR' : 'FORM'
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

    private function createLanguageSkills($candidate, $skills)
    {
        foreach ($skills as $index => $skill) {
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
     * Check if email already exists - AJAX endpoint
     */
    public function checkEmailExists(Request $request)
    {
        $email = $request->get('email');
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'exists' => false,
                'message' => 'Email tidak valid'
            ]);
        }
        
        $exists = \App\Models\Candidate::where('email', $email)->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Email sudah terdaftar dalam sistem' : 'Email tersedia'
        ]);
    }

    /**
     * Check if NIK already exists - AJAX endpoint
     */
    public function checkNikExists(Request $request)
    {
        $nik = $request->get('nik');
        
        if (!$nik || !preg_match('/^[0-9]{16}$/', $nik)) {
            return response()->json([
                'exists' => false,
                'message' => 'NIK harus 16 digit angka'
            ]);
        }
        
        $exists = \App\Models\Candidate::where('nik', $nik)->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'NIK sudah terdaftar dalam sistem' : 'NIK tersedia'
        ]);
    }
}