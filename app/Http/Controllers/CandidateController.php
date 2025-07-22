<?php

namespace App\Http\Controllers;

use App\Models\{
    Candidate, 
    Position, 
    ApplicationLog,
    FamilyMember,
    FormalEducation,        // ✅ UPDATED: Use separate formal education model
    NonFormalEducation,     // ✅ UPDATED: Use separate non-formal education model
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

class CandidateController extends Controller
{
    /**
     * ✅ UPDATED: Show candidate details with correct education relationships
     */
    public function show($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models instead of unified education
            'formalEducation' => function($query) {
                $query->orderBy('education_level')->orderBy('end_year', 'desc');
            },
            'nonFormalEducation' => function($query) {
                $query->orderBy('date', 'desc');
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
     * ✅ UPDATED: Edit form with correct education relationships
     */
    public function edit($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
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
     * ✅ UPDATED: Update candidate data with new education structure
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
            
            // ✅ UPDATED: Handle formal education with separate model
            if ($request->has('formal_education')) {
                $candidate->formalEducation()->delete();
                
                foreach ($request->formal_education as $education) {
                    if (!empty($education['education_level'])) {
                        FormalEducation::create([
                            'candidate_id' => $candidate->id,
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

            // ✅ UPDATED: Handle non-formal education with separate model
            if ($request->has('non_formal_education')) {
                $candidate->nonFormalEducation()->delete();
                
                foreach ($request->non_formal_education as $education) {
                    if (!empty($education['course_name'])) {
                        NonFormalEducation::create([
                            'candidate_id' => $candidate->id,
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
     * ✅ UPDATED: Preview with correct education relationships
     */
    public function preview($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
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
     * ✅ UPDATED: Generate PDF preview with correct education relationships
     */
    public function previewPdf($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
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
     * ✅ UPDATED: Generate HTML preview with correct education relationships
     */
    public function previewHtml($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
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
     * ✅ UPDATED: Export single candidate to PDF with correct education relationships
     */
    public function exportSingle($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
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
     * ✅ UPDATED: Export to Word format with correct education relationships
     */
    public function exportWord($id)
    {
        Gate::authorize('hr-access');
        
        $candidate = Candidate::with([
            'familyMembers',
            // ✅ UPDATED: Use separate education models
            'formalEducation',
            'nonFormalEducation',
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
            DB::beginTransaction();
            
            $candidate = Candidate::onlyTrashed()
                ->with(['documentUploads'])
                ->findOrFail($id);
            
            $candidateName = $candidate->full_name ?? 'Unknown';
            $candidateCode = $candidate->candidate_code;
            
            // 1. Hapus semua file documents dari storage
            $this->deleteDocumentFiles($candidate);
            
            // 2. Hapus folder kandidat jika ada
            $this->deleteCandidateFolder($candidateCode);
            
            // 3. Force delete dari database (ini akan otomatis hapus relasi karena foreign key cascade)
            $candidate->forceDelete();
            
            DB::commit();
            
            Log::info('Candidate permanently deleted with file cleanup', [
                'candidate_id' => $id,
                'candidate_code' => $candidateCode,
                'candidate_name' => $candidateName,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Kandidat {$candidateName} berhasil dihapus permanen beserta semua filenya"
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
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

    /**
     * Bulk force delete candidates dengan cleanup file storage
     */
    public function bulkForceDelete(Request $request)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:candidates,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $candidateIds = $request->ids;
            $candidates = Candidate::onlyTrashed()
                ->with(['documentUploads'])
                ->whereIn('id', $candidateIds)
                ->get();
            
            $deletedCount = 0;
            
            foreach ($candidates as $candidate) {
                // 1. Hapus semua file documents dari storage
                $this->deleteDocumentFiles($candidate);
                
                // 2. Hapus folder kandidat jika ada
                $this->deleteCandidateFolder($candidate->candidate_code);
                
                // 3. Force delete dari database
                $candidate->forceDelete();
                
                $deletedCount++;
                
                Log::info('Candidate bulk permanently deleted with file cleanup', [
                    'candidate_id' => $candidate->id,
                    'candidate_code' => $candidate->candidate_code,
                    'candidate_name' => $candidate->full_name,
                    'user_id' => Auth::id()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} kandidat berhasil dihapus permanen beserta semua filenya"
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error bulk force deleting candidates', [
                'candidate_ids' => $request->ids,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kandidat secara permanen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus semua file documents dari storage berdasarkan DocumentUpload records
     */
    private function deleteDocumentFiles($candidate)
    {
        try {
            foreach ($candidate->documentUploads as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                    Log::info('Document file deleted', [
                        'file_path' => $document->file_path,
                        'document_id' => $document->id,
                        'candidate_id' => $candidate->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error deleting document files', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Hapus folder kandidat dari storage
     */
    private function deleteCandidateFolder($candidateCode)
    {
        try {
            if (!$candidateCode) {
                return;
            }
            
            $folderPath = "documents/{$candidateCode}";
            
            // Hapus menggunakan Storage facade
            if (Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->deleteDirectory($folderPath);
                Log::info('Candidate folder deleted from storage', [
                    'folder_path' => $folderPath,
                    'candidate_code' => $candidateCode
                ]);
            }
            
            // Juga hapus dari file system langsung sebagai backup
            $fullPath = storage_path("app/public/{$folderPath}");
            if (File::exists($fullPath)) {
                File::deleteDirectory($fullPath);
                Log::info('Candidate folder deleted from file system', [
                    'full_path' => $fullPath,
                    'candidate_code' => $candidateCode
                ]);
            }
            
        } catch (\Exception $e) {
            Log::warning('Error deleting candidate folder', [
                'candidate_code' => $candidateCode,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cleanup orphaned folders - utility method untuk membersihkan folder yatim
     */
    public function cleanupOrphanedFolders()
    {
        Gate::authorize('hr-access');
        
        try {
            $documentsPath = storage_path('app/public/documents');
            
            if (!File::exists($documentsPath)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Folder documents tidak ditemukan'
                ]);
            }
            
            $folders = File::directories($documentsPath);
            $deletedFolders = [];
            
            foreach ($folders as $folder) {
                $folderName = basename($folder);
                
                // Cek apakah kandidat dengan kode ini masih ada
                $candidateExists = Candidate::withTrashed()
                    ->where('candidate_code', $folderName)
                    ->exists();
                
                if (!$candidateExists) {
                    // Folder yatim, hapus
                    File::deleteDirectory($folder);
                    $deletedFolders[] = $folderName;
                    
                    Log::info('Orphaned folder deleted', [
                        'folder_name' => $folderName,
                        'folder_path' => $folder,
                        'user_id' => Auth::id()
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => count($deletedFolders) > 0 
                    ? 'Berhasil menghapus ' . count($deletedFolders) . ' folder yatim: ' . implode(', ', $deletedFolders)
                    : 'Tidak ada folder yatim yang ditemukan'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error cleaning up orphaned folders', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan folder yatim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get storage usage statistics
     */
    public function getStorageStats()
    {
        Gate::authorize('hr-access');
        
        try {
            $documentsPath = storage_path('app/public/documents');
            $totalSize = 0;
            $totalFiles = 0;
            $totalFolders = 0;
            $orphanedFolders = 0;
            
            if (File::exists($documentsPath)) {
                $folders = File::directories($documentsPath);
                $totalFolders = count($folders);
                
                foreach ($folders as $folder) {
                    $folderName = basename($folder);
                    
                    // Cek apakah kandidat dengan kode ini masih ada
                    $candidateExists = Candidate::withTrashed()
                        ->where('candidate_code', $folderName)
                        ->exists();
                    
                    if (!$candidateExists) {
                        $orphanedFolders++;
                    }
                    
                    // Hitung ukuran folder
                    $files = File::allFiles($folder);
                    foreach ($files as $file) {
                        $totalSize += $file->getSize();
                        $totalFiles++;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_size' => $this->formatBytes($totalSize),
                    'total_size_bytes' => $totalSize,
                    'total_files' => $totalFiles,
                    'total_folders' => $totalFolders,
                    'orphaned_folders' => $orphanedFolders,
                    'active_candidates' => Candidate::count(),
                    'trashed_candidates' => Candidate::onlyTrashed()->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting storage stats', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik storage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format bytes ke human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}