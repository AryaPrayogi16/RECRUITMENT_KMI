<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_code',
        'position_id',
        'position_applied',
        'expected_salary',
        'application_status',
        'application_date',
        // Personal Data - sesuai database (stored directly in candidates table)
        'nik',
        'full_name',
        'email',
        'phone_number',
        'phone_alternative',
        'birth_place',
        'birth_date',
        'gender',
        'religion',
        'marital_status',
        'ethnicity',
        'current_address',
        'current_address_status',
        'ktp_address',
        'height_cm',
        'weight_kg',
        'vaccination_status'
    ];

    protected $casts = [
        'expected_salary' => 'decimal:2',
        'application_date' => 'date',
        'birth_date' => 'date',
        'height_cm' => 'integer',
        'weight_kg' => 'integer'
    ];

    // Constants sesuai database enum
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_SCREENING = 'screening';
    const STATUS_INTERVIEW = 'interview';
    const STATUS_OFFERED = 'offered';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    const GENDER_MALE = 'Laki-laki';
    const GENDER_FEMALE = 'Perempuan';

    const MARITAL_SINGLE = 'Lajang';
    const MARITAL_MARRIED = 'Menikah';
    const MARITAL_WIDOW = 'Janda';
    const MARITAL_WIDOWER = 'Duda';

    const ADDRESS_OWN = 'Milik Sendiri';
    const ADDRESS_PARENTS = 'Orang Tua';
    const ADDRESS_CONTRACT = 'Kontrak';
    const ADDRESS_RENT = 'Sewa';

    const VACCINE_1 = 'Vaksin 1';
    const VACCINE_2 = 'Vaksin 2';
    const VACCINE_3 = 'Vaksin 3';
    const VACCINE_BOOSTER = 'Booster';

    // Boot method untuk auto-generating candidate code dan cleanup file
    protected static function boot()
    {
        parent::boot();

        // Auto-generate candidate code saat create
        static::creating(function ($candidate) {
            if (empty($candidate->candidate_code)) {
                $candidate->candidate_code = self::generateCandidateCode();
            }
        });

        // Cleanup file storage saat force delete
        static::forceDeleting(function ($candidate) {
            try {
                // Load document uploads sebelum model di-delete
                $candidate->load('documentUploads');
                
                // Hapus semua file documents
                foreach ($candidate->documentUploads as $document) {
                    if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                        Storage::disk('public')->delete($document->file_path);
                        Log::info('Document file deleted during forceDelete', [
                            'file_path' => $document->file_path,
                            'document_id' => $document->id,
                            'candidate_id' => $candidate->id
                        ]);
                    }
                }
                
                // Hapus folder kandidat
                if ($candidate->candidate_code) {
                    $folderPath = "documents/{$candidate->candidate_code}";
                    
                    // Hapus menggunakan Storage facade
                    if (Storage::disk('public')->exists($folderPath)) {
                        Storage::disk('public')->deleteDirectory($folderPath);
                        Log::info('Candidate folder deleted during forceDelete', [
                            'folder_path' => $folderPath,
                            'candidate_code' => $candidate->candidate_code
                        ]);
                    }
                    
                    // Juga hapus dari file system langsung sebagai backup
                    $fullPath = storage_path("app/public/{$folderPath}");
                    if (File::exists($fullPath)) {
                        File::deleteDirectory($fullPath);
                        Log::info('Candidate folder deleted from file system during forceDelete', [
                            'full_path' => $fullPath,
                            'candidate_code' => $candidate->candidate_code
                        ]);
                    }
                }
                
            } catch (\Exception $e) {
                Log::error('Error during file cleanup in forceDeleting event', [
                    'candidate_id' => $candidate->id,
                    'candidate_code' => $candidate->candidate_code,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    // Relationships
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * ✅ UPDATED: Education relationships for split tables
     */
    public function formalEducation(): HasMany
    {
        return $this->hasMany(FormalEducation::class)->orderByRaw("FIELD(education_level, 'S3', 'S2', 'S1', 'Diploma', 'SMA/SMK')");
    }

    public function nonFormalEducation(): HasMany
    {
        return $this->hasMany(NonFormalEducation::class)->orderBy('date', 'desc');
    }

    public function highestEducation(): HasOne
    {
        return $this->hasOne(FormalEducation::class)->orderByRaw("FIELD(education_level, 'S3', 'S2', 'S1', 'Diploma', 'SMA/SMK')");
    }

    public function latestEducation(): HasOne
    {
        return $this->hasOne(FormalEducation::class)->orderBy('end_year', 'desc');
    }

    /**
     * ❌ REMOVED: Legacy education relationship (causing the error)
     * This method has been removed because Education model no longer exists.
     * Use formalEducation() and nonFormalEducation() instead.
     */

    public function languageSkills(): HasMany
    {
        return $this->hasMany(LanguageSkill::class);
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function drivingLicenses(): HasMany
    {
        return $this->hasMany(DrivingLicense::class);
    }

    public function additionalInfo(): HasOne
    {
        return $this->hasOne(CandidateAdditionalInfo::class);
    }

    public function documentUploads(): HasMany
    {
        return $this->hasMany(DocumentUpload::class);
    }

    public function applicationLogs(): HasMany
    {
        return $this->hasMany(ApplicationLog::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    /**
     * DISC 3D Test Relationships
     */
    public function disc3DTestSessions(): HasMany
    {
        return $this->hasMany(Disc3DTestSession::class);
    }

    public function disc3DResponses(): HasMany
    {
        return $this->hasMany(Disc3DResponse::class);
    }

    public function disc3DResult(): HasOne
    {
        return $this->hasOne(Disc3DResult::class);
    }

    // Specific helper methods
    public function latestDisc3DTest(): HasOne
    {
        return $this->hasOne(Disc3DTestSession::class)->latest('completed_at');
    }

    public function disc3DTestResult(): HasOne
    {
        return $this->hasOne(Disc3DResult::class)->latest('test_completed_at');
    }

    // Specific relationship methods
    public function achievements(): HasMany
    {
        return $this->activities()->where('activity_type', 'achievement');
    }

    public function socialActivities(): HasMany
    {
        return $this->activities()->where('activity_type', 'social_activity');
    }

    // Document relationships
    public function cvDocuments(): HasMany
    {
        return $this->documentUploads()->where('document_type', 'cv');
    }

    public function photoDocuments(): HasMany
    {
        return $this->documentUploads()->where('document_type', 'photo');
    }

    public function certificateDocuments(): HasMany
    {
        return $this->documentUploads()->where('document_type', 'certificates');
    }

    public function transcriptDocuments(): HasMany
    {
        return $this->documentUploads()->where('document_type', 'transcript');
    }

    public function ktpDocuments(): HasMany
    {
        return $this->documentUploads()->where('document_type', 'ktp');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('application_status', [
            self::STATUS_SUBMITTED, 
            self::STATUS_SCREENING, 
            self::STATUS_INTERVIEW, 
            self::STATUS_OFFERED
        ]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('application_status', $status);
    }

    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByMaritalStatus($query, $status)
    {
        return $query->where('marital_status', $status);
    }

    public function scopeRecentApplications($query, $days = 30)
    {
        return $query->where('application_date', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFormattedExpectedSalaryAttribute()
    {
        if (!$this->expected_salary) return 'Tidak disebutkan';
        return 'Rp ' . number_format($this->expected_salary, 0, ',', '.');
    }

    public function getAgeAttribute()
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getFormattedBirthDateAttribute()
    {
        return $this->birth_date ? $this->birth_date->format('d F Y') : null;
    }

    public function getBirthPlaceAndDateAttribute()
    {
        $place = $this->birth_place ?: '-';
        $date = $this->formatted_birth_date ?: '-';
        return $place . ', ' . $date;
    }

    public function getStatusBadgeAttribute()
    {
        $statusMap = [
            'draft' => 'bg-gray-100 text-gray-800',
            'submitted' => 'bg-blue-100 text-blue-800',
            'screening' => 'bg-yellow-100 text-yellow-800',
            'interview' => 'bg-purple-100 text-purple-800',
            'offered' => 'bg-green-100 text-green-800',
            'accepted' => 'bg-green-200 text-green-900',
            'rejected' => 'bg-red-100 text-red-800'
        ];

        return $statusMap[$this->application_status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'screening' => 'Screening',
            'interview' => 'Interview',
            'offered' => 'Offered',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected'
        ];

        return $labels[$this->application_status] ?? $this->application_status;
    }

    public function getGenderLabelAttribute()
    {
        return $this->gender ?: '-';
    }

    public function getMaritalStatusLabelAttribute()
    {
        return $this->marital_status ?: '-';
    }

    public function getFormattedHeightWeightAttribute()
    {
        $height = $this->height_cm ? $this->height_cm . ' cm' : '-';
        $weight = $this->weight_kg ? $this->weight_kg . ' kg' : '-';
        return $height . ' / ' . $weight;
    }

    public function getCurrentAddressStatusLabelAttribute()
    {
        return $this->current_address_status ?: '-';
    }

    public function getVaccinationStatusLabelAttribute()
    {
        return $this->vaccination_status ?: '-';
    }

    /**
     * ✅ UPDATED: Education-related accessors for split tables
     */
    public function getAllEducationAttribute()
    {
        $formal = $this->formalEducation->map(function($edu) {
            return [
                'type' => 'formal',
                'title' => $edu->education_title,
                'institution' => $edu->institution_name,
                'period' => $edu->formatted_duration,
                'details' => $edu->major . ($edu->gpa ? ' (GPA: ' . $edu->formatted_gpa . ')' : ''),
                'sort_date' => $edu->end_year . '-12-31'
            ];
        });

        $nonFormal = $this->nonFormalEducation->map(function($edu) {
            return [
                'type' => 'non_formal',
                'title' => $edu->course_name,
                'institution' => $edu->organizer,
                'period' => $edu->formatted_date,
                'details' => $edu->description,
                'sort_date' => $edu->date ? $edu->date->format('Y-m-d') : '1970-01-01'
            ];
        });

        return $formal->concat($nonFormal)->sortByDesc('sort_date')->values();
    }

    public function getEducationSummaryAttribute()
    {
        $highest = $this->highestEducation;
        $certCount = $this->nonFormalEducation->count();
        
        if (!$highest) {
            return $certCount > 0 ? $certCount . ' certifications' : 'No education data';
        }
        
        $summary = $highest->education_level . ' ' . $highest->major;
        
        if ($certCount > 0) {
            $summary .= ' + ' . $certCount . ' certification' . ($certCount > 1 ? 's' : '');
        }
        
        return $summary;
    }

    public function getRecentCertificationsAttribute()
    {
        return $this->nonFormalEducation->filter(function($cert) {
            return $cert->is_recent;
        });
    }

    // Check completeness
    public function hasCompleteMinimalRecords()
    {
        return $this->familyMembers()->exists() &&
               ($this->formalEducation()->exists() || $this->nonFormalEducation()->exists()) &&
               $this->languageSkills()->exists() &&
               $this->workExperiences()->exists() &&
               $this->activities()->exists() &&
               $this->drivingLicenses()->exists() &&
               $this->additionalInfo()->exists();
    }

    public function getCompletionPercentageAttribute()
    {
        $total = 8; // Total sections
        $completed = 0;

        // Basic info (always completed if candidate exists)
        $completed++;

        // Family members
        if ($this->familyMembers()->exists()) $completed++;

        // Education (check both tables)
        if ($this->formalEducation()->exists() || $this->nonFormalEducation()->exists()) $completed++;

        // Language skills
        if ($this->languageSkills()->exists()) $completed++;

        // Work experience
        if ($this->workExperiences()->exists()) $completed++;

        // Activities
        if ($this->activities()->exists()) $completed++;

        // Driving licenses
        if ($this->drivingLicenses()->exists()) $completed++;

        // Additional info
        if ($this->additionalInfo()->exists()) $completed++;

        return round(($completed / $total) * 100);
    }

    /**
     * ✅ UPDATED: Education helper methods for split tables
     */
    public function hasEducationLevel($level)
    {
        return $this->formalEducation()->where('education_level', $level)->exists();
    }

    public function hasTechnicalCertifications()
    {
        return $this->nonFormalEducation->contains(function($cert) {
            return $cert->isTechnicalCertification();
        });
    }

    public function hasHigherEducation()
    {
        return $this->formalEducation()->whereIn('education_level', ['S1', 'S2', 'S3'])->exists();
    }

    public function getHighestEducationLevelAttribute()
    {
        $highest = $this->highestEducation;
        return $highest ? $highest->education_level : null;
    }

    // Mutators
    public function setNikAttribute($value)
    {
        $this->attributes['nik'] = preg_replace('/[^0-9]/', '', $value);
    }

    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = preg_replace('/[^0-9+]/', '', $value);
    }

    public function setPhoneAlternativeAttribute($value)
    {
        $this->attributes['phone_alternative'] = preg_replace('/[^0-9+]/', '', $value);
    }

    public static function generateCandidateCode()
    {
        $prefix = 'CND';
        $year = date('Y');
        $month = date('m');
        
        $lastCandidate = self::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->orderBy('id', 'desc')
                            ->first();
        
        $sequence = $lastCandidate ? (int)substr($lastCandidate->candidate_code, -4) + 1 : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Static methods for options
    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_SCREENING => 'Screening',
            self::STATUS_INTERVIEW => 'Interview',
            self::STATUS_OFFERED => 'Offered',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected'
        ];
    }

    public static function getGenderOptions()
    {
        return [
            self::GENDER_MALE => 'Laki-laki',
            self::GENDER_FEMALE => 'Perempuan'
        ];
    }

    public static function getMaritalStatusOptions()
    {
        return [
            self::MARITAL_SINGLE => 'Lajang',
            self::MARITAL_MARRIED => 'Menikah',
            self::MARITAL_WIDOW => 'Janda',
            self::MARITAL_WIDOWER => 'Duda'
        ];
    }

    public static function getAddressStatusOptions()
    {
        return [
            self::ADDRESS_OWN => 'Milik Sendiri',
            self::ADDRESS_PARENTS => 'Orang Tua',
            self::ADDRESS_CONTRACT => 'Kontrak',
            self::ADDRESS_RENT => 'Sewa'
        ];
    }

    public static function getVaccinationStatusOptions()
    {
        return [
            self::VACCINE_1 => 'Vaksin 1',
            self::VACCINE_2 => 'Vaksin 2',
            self::VACCINE_3 => 'Vaksin 3',
            self::VACCINE_BOOSTER => 'Booster'
        ];
    }

    /**
     * DISC 3D Test Helper Methods
     */
    public function hasCompletedDisc3DTest()
    {
        return $this->disc3DTestSessions()
            ->where('status', Disc3DTestSession::STATUS_COMPLETED)
            ->exists();
    }

    public function canStartDisc3DTest()
    {
        // Bisa mulai DISC jika belum ada test yang completed atau in progress
        return !$this->disc3DTestSessions()
            ->whereIn('status', [
                Disc3DTestSession::STATUS_COMPLETED,
                Disc3DTestSession::STATUS_IN_PROGRESS
            ])->exists();
    }

    public function getDisc3DProgressAttribute()
    {
        $latestSession = $this->latestDisc3DTest;
        
        if (!$latestSession) {
            return 0;
        }
        
        return $latestSession->progress ?? 0;
    }

    public function getDisc3DStatusAttribute()
    {
        $latestSession = $this->latestDisc3DTest;
        
        if (!$latestSession) {
            return 'not_started';
        }
        
        return $latestSession->status;
    }

    /**
     * KRAEPLIN TEST RELATIONSHIPS
     */
    public function kraeplinTestSessions(): HasMany
    {
        return $this->hasMany(KraeplinTestSession::class);
    }

    public function kraeplinTestResult(): HasOne
    {
        return $this->hasOne(KraeplinTestResult::class);
    }

    public function latestKraeplinTest(): HasOne
    {
        return $this->hasOne(KraeplinTestSession::class)->latest('completed_at');
    }

    /**
     * KRAEPLIN TEST HELPER METHODS
     */
    public function hasCompletedKraeplinTest()
    {
        return $this->kraeplinTestSessions()
            ->where('status', KraeplinTestSession::STATUS_COMPLETED)
            ->exists();
    }

    public function canStartKraeplinTest()
    {
        // Bisa mulai KRAEPLIN jika belum ada test yang completed atau in progress
        return !$this->kraeplinTestSessions()
            ->whereIn('status', [
                KraeplinTestSession::STATUS_COMPLETED,
                KraeplinTestSession::STATUS_IN_PROGRESS
            ])->exists();
    }

    public function getKraeplinProgressAttribute()
    {
        $latestSession = $this->latestKraeplinTest;
        
        if (!$latestSession) {
            return 0;
        }
        
        return $latestSession->progress ?? 0;
    }

    public function getKraeplinStatusAttribute()
    {
        $latestSession = $this->latestKraeplinTest;
        
        if (!$latestSession) {
            return 'not_started';
        }
        
        return $latestSession->status;
    }

    /**
     * HELPER METHODS UNTUK FILE CLEANUP
     */
    public function cleanupFiles()
    {
        try {
            // Hapus semua file documents
            foreach ($this->documentUploads as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
            }
            
            // Hapus folder kandidat
            if ($this->candidate_code) {
                $folderPath = "documents/{$this->candidate_code}";
                
                if (Storage::disk('public')->exists($folderPath)) {
                    Storage::disk('public')->deleteDirectory($folderPath);
                }
                
                // Backup cleanup dengan File facade
                $fullPath = storage_path("app/public/{$folderPath}");
                if (File::exists($fullPath)) {
                    File::deleteDirectory($fullPath);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error cleaning up candidate files', [
                'candidate_id' => $this->id,
                'candidate_code' => $this->candidate_code,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get total file size untuk kandidat ini
     */
    public function getTotalFileSizeAttribute()
    {
        $totalSize = 0;
        
        if ($this->candidate_code) {
            $folderPath = storage_path("app/public/documents/{$this->candidate_code}");
            
            if (File::exists($folderPath)) {
                $files = File::allFiles($folderPath);
                foreach ($files as $file) {
                    $totalSize += $file->getSize();
                }
            }
        }
        
        return $totalSize;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->total_file_size;
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}