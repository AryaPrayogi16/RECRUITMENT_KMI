<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Position,
    FamilyMember,
    FormalEducation,      // ✅ UPDATED: Use FormalEducation model
    NonFormalEducation,   // ✅ UPDATED: Use NonFormalEducation model
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
use App\Services\CodeGenerationService; // ✅ NEW: Import CodeGenerationService
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
            
            // 1. Create Candidate dengan CodeGenerationService
            $candidate = $this->createCandidate($validated, $position->id);
            Log::info('Candidate created', ['candidate_id' => $candidate->id, 'candidate_code' => $candidate->candidate_code]);
            
            // 2. Create Family Members
            if (!empty($validated['family_members'])) {
                $this->createFamilyMembers($candidate, $validated['family_members']);
                Log::info('Family members created', ['candidate_id' => $candidate->id, 'count' => count($validated['family_members'])]);
            }
            
            // 3. ✅ UPDATED: Create Education Records using separate models
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
            
            // 10. ✅ FIXED: Handle File Uploads dengan storage yang benar
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

            // 🆕 Clear OCR session data setelah berhasil submit
            session()->forget([
                'ocr_validated',
                'ocr_nik', 
                'ocr_ktp_path',
                'ocr_ktp_original',
                'ocr_ktp_size',
                'ocr_ktp_mime',
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
     * 🔧 IMPROVED: Enhanced KTP OCR upload with better error handling
     */
    public function uploadKtpOcr(Request $request)
    {
        try {
            $request->validate([
                'ktp_image' => 'required|file|mimes:jpg,jpeg,png|max:5120',
                'extracted_nik' => 'required|string|size:16|regex:/^[0-9]{16}$/'
            ]);

            $file = $request->file('ktp_image');
            $extractedNik = $request->input('extracted_nik');
            
            Log::info('Processing KTP OCR upload', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
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

            // ✅ IMPROVED: Temporary storage menggunakan Storage facade yang benar
            $sessionId = session()->getId();
            $timestamp = time();
            $extension = $file->getClientOriginalExtension();
            $tempFilename = "ktp_ocr_{$sessionId}_{$timestamp}.{$extension}";
            
            // ✅ FIXED: Store di temp folder menggunakan Storage disk public
            $tempPath = $file->storeAs('temp/ktp_ocr', $tempFilename, 'public');
            
            // Verify file was stored
            if (!Storage::disk('public')->exists($tempPath)) {
                throw new \Exception('Failed to store temporary KTP file');
            }
            
            $storedFileSize = Storage::disk('public')->size($tempPath);
            
            Log::info('KTP file stored temporarily', [
                'temp_path' => $tempPath,
                'original_size' => $file->getSize(),
                'stored_size' => $storedFileSize,
                'temp_file_exists' => Storage::disk('public')->exists($tempPath),
                'storage_path' => Storage::disk('public')->path($tempPath)
            ]);

            // ✅ IMPROVED: Enhanced session storage with verification
            session([
                'ocr_validated' => true,
                'ocr_nik' => $extractedNik,
                'ocr_ktp_path' => $tempPath,
                'ocr_ktp_original' => $file->getClientOriginalName(),
                'ocr_ktp_size' => $storedFileSize,
                'ocr_ktp_mime' => $file->getMimeType(),
                'ocr_timestamp' => $timestamp
            ]);

            // Verify session data was saved
            $sessionVerification = [
                'ocr_validated' => session('ocr_validated'),
                'ocr_nik' => session('ocr_nik'),
                'ocr_ktp_path' => session('ocr_ktp_path'),
                'session_saved' => session('ocr_validated') === true
            ];

            Log::info('✅ OCR KTP processed successfully', [
                'nik' => $extractedNik,
                'temp_path' => $tempPath,
                'session_id' => $sessionId,
                'file_size' => $storedFileSize,
                'session_verification' => $sessionVerification
            ]);

            return response()->json([
                'success' => true,
                'message' => 'KTP berhasil diproses dan NIK berhasil diekstrak',
                'data' => [
                    'nik' => $extractedNik,
                    'filename' => $file->getClientOriginalName(),
                    'file_size' => $this->formatFileSize($storedFileSize),
                    'temp_path' => $tempPath
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
                'message' => 'Terjadi kesalahan saat memproses file KTP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear KTP temporary file from session
     */
    public function clearKtpTemp(Request $request)
    {
        try {
            $tempPath = session('ocr_ktp_path');
            
            if ($tempPath && Storage::disk('public')->exists($tempPath)) {
                Storage::disk('public')->delete($tempPath);
                Log::info('Temporary KTP file deleted', ['path' => $tempPath]);
            }

            // Clear session data
            session()->forget([
                'ocr_validated',
                'ocr_nik', 
                'ocr_ktp_path',
                'ocr_ktp_original',
                'ocr_ktp_size',
                'ocr_ktp_mime',
                'ocr_timestamp'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File KTP temporary berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing KTP temp file', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file temporary'
            ], 500);
        }
    }

    // ✅ COMPLETELY REWRITTEN: Enhanced document uploads dengan storage yang benar
    private function handleDocumentUploads($candidate, $request)
    {
        $uploadedFiles = [];
        
        try {
            // Pastikan folder kandidat ada di storage yang benar
            $candidateFolder = "documents/candidates/{$candidate->candidate_code}";
            
            // Buat folder jika belum ada
            if (!Storage::disk('public')->exists($candidateFolder)) {
                Storage::disk('public')->makeDirectory($candidateFolder);
                Log::info('Created candidate folder', [
                    'candidate_id' => $candidate->id,
                    'folder' => $candidateFolder,
                    'full_path' => Storage::disk('public')->path($candidateFolder)
                ]);
            }

            // Handle CV
            if ($request->hasFile('cv')) {
                $file = $request->file('cv');
                $filename = $this->generateSecureFilename('cv', $file->getClientOriginalExtension(), $candidate);
                $filePath = $candidateFolder . '/' . $filename;
                
                // ✅ STORE MENGGUNAKAN STORAGE DISK YANG BENAR
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

                    Log::info('CV uploaded successfully', [
                        'candidate_id' => $candidate->id, 
                        'file_path' => $filePath,
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize()
                    ]);
                }
            }

            // Handle Photo  
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
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

                    Log::info('Photo uploaded successfully', [
                        'candidate_id' => $candidate->id, 
                        'file_path' => $filePath
                    ]);
                }
            }

            // ✅ FIXED: Enhanced KTP handling dari OCR session
            $ktpProcessed = $this->handleKTPFromOCRSession($candidate, $uploadedFiles, $candidateFolder);
            if (!$ktpProcessed) {
                Log::warning('No KTP file processed from OCR session', [
                    'candidate_id' => $candidate->id,
                    'session_data' => [
                        'ocr_validated' => session('ocr_validated'),
                        'ocr_ktp_path' => session('ocr_ktp_path'),
                    ]
                ]);
            }

            // Handle Transcript
            if ($request->hasFile('transcript')) {
                $file = $request->file('transcript');
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

                    Log::info('Transcript uploaded successfully', [
                        'candidate_id' => $candidate->id, 
                        'file_path' => $filePath
                    ]);
                }
            }

            // Handle Certificates (multiple)
            if ($request->hasFile('certificates')) {
                $certificates = $request->file('certificates');
                
                // Buat subfolder untuk certificates
                $certificatesFolder = $candidateFolder . '/certificates';
                if (!Storage::disk('public')->exists($certificatesFolder)) {
                    Storage::disk('public')->makeDirectory($certificatesFolder);
                }
                
                foreach ($certificates as $index => $certificate) {
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

                        Log::info('Certificate uploaded successfully', [
                            'candidate_id' => $candidate->id, 
                            'index' => $index + 1, 
                            'file_path' => $filePath
                        ]);
                    }
                }
            }

            Log::info('All document uploads completed', [
                'candidate_id' => $candidate->id,
                'total_files' => count($uploadedFiles),
                'uploaded_files' => $uploadedFiles
            ]);

            return $uploadedFiles;

        } catch (\Exception $e) {
            Log::error('Error during file upload', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage(),
                'uploaded_files' => $uploadedFiles
            ]);
            $this->cleanupUploadedFiles($uploadedFiles);
            throw $e;
        }
    }

    /**
     * ✅ COMPLETELY REWRITTEN: Enhanced KTP handling dari OCR session
     */
    private function handleKTPFromOCRSession($candidate, &$uploadedFiles, $candidateFolder)
    {
        // Check if we have OCR session data
        if (!session('ocr_validated') || !session('ocr_ktp_path')) {
            Log::info('No OCR KTP session data found', [
                'candidate_id' => $candidate->id,
                'ocr_validated' => session('ocr_validated', 'not_set'),
                'ocr_ktp_path' => session('ocr_ktp_path', 'not_set')
            ]);
            return false;
        }

        $ktpTempPath = session('ocr_ktp_path');
        $ktpOriginalName = session('ocr_ktp_original');
        $ktpFileSize = session('ocr_ktp_size');
        $ktpMimeType = session('ocr_ktp_mime');
        
        Log::info('Processing KTP from OCR session', [
            'candidate_id' => $candidate->id,
            'temp_path' => $ktpTempPath,
            'original_name' => $ktpOriginalName,
            'file_size' => $ktpFileSize,
            'mime_type' => $ktpMimeType
        ]);

        try {
            // Check if temp file exists
            if (!Storage::disk('public')->exists($ktpTempPath)) {
                Log::error('KTP temp file not found', [
                    'candidate_id' => $candidate->id,
                    'temp_path' => $ktpTempPath,
                    'full_path' => Storage::disk('public')->path($ktpTempPath)
                ]);
                return false;
            }

            // Generate filename untuk KTP permanent
            $extension = pathinfo($ktpOriginalName, PATHINFO_EXTENSION) ?: 'jpg';
            $ktpFilename = $this->generateSecureFilename('ktp', $extension, $candidate);
            $ktpPermanentPath = $candidateFolder . '/' . $ktpFilename;
            
            Log::info('Moving KTP file to permanent location', [
                'candidate_id' => $candidate->id,
                'from' => $ktpTempPath,
                'to' => $ktpPermanentPath
            ]);

            // ✅ IMPROVED: Move file menggunakan Storage facade yang lebih reliable
            $fileContent = Storage::disk('public')->get($ktpTempPath);
            $moved = Storage::disk('public')->put($ktpPermanentPath, $fileContent);
            
            if ($moved) {
                // Delete temp file setelah berhasil copy
                Storage::disk('public')->delete($ktpTempPath);
                
                $uploadedFiles[] = $ktpPermanentPath;
                
                // ✅ FIXED: Get actual file size dari file yang sudah dipindah
                $actualFileSize = Storage::disk('public')->size($ktpPermanentPath);
                
                // Save to database
                DocumentUpload::create([
                    'candidate_id' => $candidate->id,
                    'document_type' => 'ktp',
                    'document_name' => 'KTP (OCR Scan)',
                    'original_filename' => $ktpOriginalName ?: 'ktp_scan.jpg',
                    'file_path' => $ktpPermanentPath,
                    'file_size' => $actualFileSize ?: $ktpFileSize,
                    'mime_type' => $ktpMimeType ?: 'image/jpeg',
                ]);
                
                Log::info('✅ KTP successfully moved and saved to database', [
                    'candidate_id' => $candidate->id,
                    'permanent_path' => $ktpPermanentPath,
                    'original_name' => $ktpOriginalName,
                    'file_size' => $actualFileSize,
                    'temp_file_deleted' => !Storage::disk('public')->exists($ktpTempPath)
                ]);
                
                return true;
                
            } else {
                Log::error('❌ Failed to move KTP from temp to permanent location', [
                    'candidate_id' => $candidate->id,
                    'temp_path' => $ktpTempPath,
                    'permanent_path' => $ktpPermanentPath,
                    'temp_exists' => Storage::disk('public')->exists($ktpTempPath)
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('Exception while processing KTP from OCR session', [
                'candidate_id' => $candidate->id,
                'temp_path' => $ktpTempPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * ✅ NEW: Generate secure filename untuk mencegah conflict
     */
    private function generateSecureFilename($type, $extension, $candidate)
    {
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        $candidateHash = substr(md5($candidate->candidate_code), 0, 8);
        
        return $type . '_' . $candidateHash . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    // ✅ NEW: Create formal education records
    private function createFormalEducation($candidate, $formalEducations)
    {
        foreach ($formalEducations as $index => $education) {
            // Skip if main fields are empty
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

    // ✅ NEW: Create non-formal education records
    private function createNonFormalEducation($candidate, $nonFormalEducations)
    {
        foreach ($nonFormalEducations as $index => $education) {
            // Skip if course name is empty
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

    // 🆕 NEW: Debug method to check KTP file status
    public function debugKtpStatus(Request $request)
    {
        $sessionId = session()->getId();
        
        $debugInfo = [
            'session_id' => $sessionId,
            'session_data' => [
                'ocr_validated' => session('ocr_validated'),
                'ocr_nik' => session('ocr_nik'),
                'ocr_ktp_path' => session('ocr_ktp_path'),
                'ocr_ktp_original' => session('ocr_ktp_original'),
                'ocr_ktp_size' => session('ocr_ktp_size'),
                'ocr_ktp_mime' => session('ocr_ktp_mime'),
                'ocr_timestamp' => session('ocr_timestamp')
            ],
            'file_system_check' => [],
            'recent_candidates' => [],
            'recent_document_uploads' => []
        ];
        
        // Check if temp file exists
        $tempPath = session('ocr_ktp_path');
        if ($tempPath) {
            $debugInfo['file_system_check'] = [
                'temp_path' => $tempPath,
                'file_exists' => Storage::disk('public')->exists($tempPath),
                'file_size' => Storage::disk('public')->exists($tempPath) ? Storage::disk('public')->size($tempPath) : null,
                'full_path' => Storage::disk('public')->path($tempPath),
                'temp_directory_contents' => Storage::disk('public')->files('temp/ktp_ocr')
            ];
        }
        
        // Check recent candidates
        $debugInfo['recent_candidates'] = Candidate::latest()
            ->take(5)
            ->select('id', 'candidate_code', 'nik', 'full_name', 'created_at')
            ->get()
            ->toArray();
        
        // Check recent document uploads
        $debugInfo['recent_document_uploads'] = DocumentUpload::where('document_type', 'ktp')
            ->latest()
            ->take(10)
            ->select('id', 'candidate_id', 'document_type', 'original_filename', 'file_path', 'file_size', 'created_at')
            ->with('candidate:id,candidate_code,nik,full_name')
            ->get()
            ->toArray();
        
        Log::info('🔍 KTP Debug Status', $debugInfo);
        
        return response()->json([
            'success' => true,
            'debug_info' => $debugInfo
        ]);
    }

    /**
     * 🆕 NEW: Clean temp files method  
     */
    public function cleanTempKtpFiles(Request $request)
    {
        try {
            $tempDir = 'temp/ktp_ocr';
            $cleaned = 0;
            
            if (Storage::disk('public')->exists($tempDir)) {
                $files = Storage::disk('public')->files($tempDir);
                
                foreach ($files as $file) {
                    $lastModified = Storage::disk('public')->lastModified($file);
                    $hoursOld = now()->diffInHours(Carbon::createFromTimestamp($lastModified));
                    
                    // Delete files older than 24 hours
                    if ($hoursOld > 24) {
                        Storage::disk('public')->delete($file);
                        $cleaned++;
                        Log::info('Cleaned old temp KTP file', [
                            'file' => $file,
                            'hours_old' => $hoursOld
                        ]);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Cleaned {$cleaned} old temp files",
                'cleaned_count' => $cleaned
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error cleaning temp KTP files', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error cleaning temp files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🆕 NEW: Verify KTP file integrity
     */
    public function verifyKtpIntegrity($candidateCode = null)
    {
        try {
            $query = Candidate::with(['documentUploads' => function($q) {
                $q->where('document_type', 'ktp');
            }]);
            
            if ($candidateCode) {
                $query->where('candidate_code', $candidateCode);
            } else {
                $query->latest()->take(10);
            }
            
            $candidates = $query->get();
            $results = [];
            
            foreach ($candidates as $candidate) {
                $ktpDocument = $candidate->documentUploads->where('document_type', 'ktp')->first();
                
                $result = [
                    'candidate_code' => $candidate->candidate_code,
                    'nik' => $candidate->nik,
                    'has_ktp_record' => !is_null($ktpDocument),
                    'ktp_file_exists' => false,
                    'file_path' => null,
                    'file_size' => null,
                    'database_size' => null,
                    'size_match' => false
                ];
                
                if ($ktpDocument) {
                    $result['file_path'] = $ktpDocument->file_path;
                    $result['database_size'] = $ktpDocument->file_size;
                    $result['ktp_file_exists'] = Storage::disk('public')->exists($ktpDocument->file_path);
                    
                    if ($result['ktp_file_exists']) {
                        $actualSize = Storage::disk('public')->size($ktpDocument->file_path);
                        $result['file_size'] = $actualSize;
                        $result['size_match'] = ($actualSize == $ktpDocument->file_size);
                    }
                }
                
                $results[] = $result;
            }
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'summary' => [
                    'total_checked' => count($results),
                    'with_ktp_record' => collect($results)->where('has_ktp_record', true)->count(),
                    'files_exist' => collect($results)->where('ktp_file_exists', true)->count(),
                    'size_matches' => collect($results)->where('size_match', true)->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error verifying KTP integrity', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error verifying KTP integrity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🆕 NEW: Force process pending KTP from session
     */
    public function forceProcessKtpFromSession(Request $request)
    {
        try {
            // Get the latest candidate or use provided candidate code
            $candidateCode = $request->get('candidate_code');
            
            if ($candidateCode) {
                $candidate = Candidate::where('candidate_code', $candidateCode)->first();
            } else {
                $candidate = Candidate::latest()->first();
            }
            
            if (!$candidate) {
                return response()->json([
                    'success' => false,
                    'message' => 'No candidate found'
                ], 404);
            }
            
            Log::info('🔧 Force processing KTP from session', [
                'candidate_id' => $candidate->id,
                'candidate_code' => $candidate->candidate_code
            ]);
            
            $uploadedFiles = [];
            $candidateFolder = "documents/candidates/{$candidate->candidate_code}";
            $processed = $this->handleKTPFromOCRSession($candidate, $uploadedFiles, $candidateFolder);
            
            return response()->json([
                'success' => $processed,
                'message' => $processed ? 'KTP processed successfully' : 'Failed to process KTP',
                'candidate_code' => $candidate->candidate_code,
                'uploaded_files' => $uploadedFiles
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error force processing KTP from session', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing KTP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to format file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    // ✅ KEEP ALL EXISTING METHODS UNCHANGED BELOW THIS POINT
    
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

    // ✅ UPDATED: Create candidate dengan CodeGenerationService
    private function createCandidate($validated, $positionId)
    {
        try {
            // Prioritas NIK: OCR Session > Form input (sebagai fallback)
            $nik = session('ocr_nik') ?: $validated['nik'] ?? null;
            
            if (!$nik || strlen($nik) !== 16) {
                throw new \Exception('NIK tidak valid atau tidak ditemukan dari OCR session');
            }

            // ✅ NEW: Generate candidate code menggunakan CodeGenerationService
            $candidateCode = CodeGenerationService::generateCandidateCode();

            $candidateData = [
                'candidate_code' => $candidateCode, // ✅ FIXED: Gunakan CodeGenerationService
                'position_id' => $positionId,
                'position_applied' => $validated['position_applied'],
                'expected_salary' => $validated['expected_salary'] ?? null,
                'application_status' => 'submitted',
                'application_date' => now(),

                // Data pribadi langsung di tabel candidates
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