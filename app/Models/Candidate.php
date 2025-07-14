<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    // Relationships
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function education(): HasMany
    {
        return $this->hasMany(Education::class);
    }

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

    public function formalEducation(): HasMany
    {
        return $this->education()->where('education_type', 'formal');
    }

    public function nonFormalEducation(): HasMany
    {
        return $this->education()->where('education_type', 'non_formal');
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

    // Check completeness
    public function hasCompleteMinimalRecords()
    {
        return $this->familyMembers()->exists() &&
               $this->education()->exists() &&
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

        // Education
        if ($this->education()->exists()) $completed++;

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

    // Boot method for auto-generating candidate code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($candidate) {
            if (empty($candidate->candidate_code)) {
                $candidate->candidate_code = self::generateCandidateCode();
            }
        });
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
     * ✅ FIXED: KRAEPLIN TEST HELPER METHODS
     */
    public function hasCompletedKraeplinTest()
    {
        return $this->kraeplinTestSessions()
            ->where('status', KraeplinTestSession::STATUS_COMPLETED)
            ->exists();
    }

    // ✅ FIXED: Ubah menjadi method bukan property  
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
}