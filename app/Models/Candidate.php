<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_code',
        'position_id',
        'position_applied',
        'expected_salary',
        'application_status',
        'application_date'
    ];

    protected $casts = [
        'expected_salary' => 'decimal:2',
        'application_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Constants for application status
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_SCREENING = 'screening';
    const STATUS_INTERVIEW = 'interview';
    const STATUS_OFFERED = 'offered';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    // Constants for test status (matching test sessions)
    const TEST_NOT_STARTED = 'not_started';
    const TEST_IN_PROGRESS = 'in_progress';
    const TEST_COMPLETED = 'completed';

    public static function getStatuses()
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

    public static function getTestStatuses()
    {
        return [
            self::TEST_NOT_STARTED => 'Belum Dimulai',
            self::TEST_IN_PROGRESS => 'Sedang Berlangsung',
            self::TEST_COMPLETED => 'Selesai'
        ];
    }

    // ========== BASIC RELATIONSHIPS ==========
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
    
    public function personalData()
    {
        return $this->hasOne(PersonalData::class);
    }

    public function drivingLicenses()
    {
        return $this->hasMany(DrivingLicense::class);
    }

    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function formalEducation()
    {
        return $this->hasMany(FormalEducation::class);
    }

    public function nonFormalEducation()
    {
        return $this->hasMany(NonFormalEducation::class);
    }

    public function languageSkills()
    {
        return $this->hasMany(LanguageSkill::class);
    }

    public function computerSkills()
    {
        return $this->hasOne(ComputerSkill::class);
    }
    
    public function otherSkills()
    {
        return $this->hasOne(OtherSkill::class);
    }

    public function socialActivities()
    {
        return $this->hasMany(SocialActivity::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function workExperiences()
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function generalInformation()
    {
        return $this->hasOne(GeneralInformation::class);
    }

    public function applicationLogs()
    {
        return $this->hasMany(ApplicationLog::class);
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    public function documentUploads()
    {
        return $this->hasMany(DocumentUpload::class);
    }

    // ========== KRAEPLIN TEST RELATIONSHIPS ==========
    public function kraeplinTestSessions()
    {
        return $this->hasMany(KraeplinTestSession::class);
    }

    public function kraeplinTestResults()
    {
        return $this->hasMany(KraeplinTestResult::class);
    }

    public function latestKraeplinTest()
    {
        return $this->hasOne(KraeplinTestSession::class)->latest();
    }

    public function completedKraeplinTest()
    {
        return $this->hasOne(KraeplinTestSession::class)->where('status', self::TEST_COMPLETED);
    }

    public function kraeplinTestResult()
    {
        return $this->hasOne(KraeplinTestResult::class)->latest();
    }

    // ========== DISC TEST RELATIONSHIPS - NEW ==========
    public function discTestSessions()
    {
        return $this->hasMany(DiscTestSession::class);
    }

    public function discTestResults()
    {
        return $this->hasMany(DiscTestResult::class);
    }

    public function latestDiscTest()
    {
        return $this->hasOne(DiscTestSession::class)->latest();
    }

    public function completedDiscTest()
    {
        return $this->hasOne(DiscTestSession::class)->where('status', self::TEST_COMPLETED);
    }

    public function discTestResult()
    {
        return $this->hasOne(DiscTestResult::class)->latest();
    }

    // ========== BASIC SCOPES ==========
    public function scopeByStatus($query, $status)
    {
        return $query->where('application_status', $status);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('application_status', self::STATUS_SUBMITTED);
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position_applied', $position);
    }

    // ========== KRAEPLIN TEST SCOPES ==========
    public function scopeWithKraeplinTest($query)
    {
        return $query->whereHas('kraeplinTestSessions', function($q) {
            $q->where('status', self::TEST_COMPLETED);
        });
    }

    public function scopeWithoutKraeplinTest($query)
    {
        return $query->whereDoesntHave('kraeplinTestSessions', function($q) {
            $q->where('status', self::TEST_COMPLETED);
        });
    }


    public function scopeKraeplinInProgress($query)
    {
        return $query->whereHas('kraeplinTestSessions', function($q) {
            $q->where('status', self::TEST_IN_PROGRESS);
        });
    }

    // ========== DISC TEST SCOPES - NEW ==========
    public function scopeWithDiscTest($query)
    {
        return $query->whereHas('discTestSessions', function($q) {
            $q->where('status', self::TEST_COMPLETED);
        });
    }

    public function scopeWithoutDiscTest($query)
    {
        return $query->whereDoesntHave('discTestSessions', function($q) {
            $q->where('status', self::TEST_COMPLETED);
        });
    }

    public function scopeDiscInProgress($query)
    {
        return $query->whereHas('discTestSessions', function($q) {
            $q->where('status', self::TEST_IN_PROGRESS);
        });
    }

    public function scopeWithAllTests($query)
    {
        return $query->whereHas('kraeplinTestSessions', function($q) {
            $q->where('status', self::TEST_COMPLETED);
        })->whereHas('discTestSessions', function($q) {
            $q->where('status', self::TEST_COMPLETED);
        });
    }

    // ========== BASIC ACCESSORS ==========
    public function getFullNameAttribute()
    {
        return $this->personalData?->full_name ?? 'N/A';
    }

    public function getEmailAttribute()
    {
        return $this->personalData?->email ?? 'N/A';
    }

    public function getPhoneAttribute()
    {
        return $this->personalData?->phone_number ?? 'N/A';
    }
    
    public function getStatusBadgeClassAttribute()
    {
        return match($this->application_status) {
            'draft' => 'status-draft',
            'submitted' => 'status-submitted',
            'screening' => 'status-screening',
            'interview' => 'status-interview',
            'offered' => 'status-offered',
            'accepted' => 'status-accepted',
            'rejected' => 'status-rejected',
            default => 'status-pending'
        };
    }

    public function getFormattedExpectedSalaryAttribute()
    {
        return $this->expected_salary ? 'Rp ' . number_format($this->expected_salary, 0, ',', '.') : 'N/A';
    }

    // ========== KRAEPLIN TEST ACCESSORS ==========
    public function getKraeplinStatusAttribute()
    {
        $session = $this->kraeplinTestSessions()->latest()->first();
        
        if (!$session) {
            return self::TEST_NOT_STARTED;
        }

        return $session->status;
    }

    public function getKraeplinStatusLabelAttribute()
    {
        $statuses = self::getTestStatuses();
        return $statuses[$this->kraeplin_status] ?? 'Tidak Diketahui';
    }

    public function getKraeplinStatusBadgeClassAttribute()
    {
        return match($this->kraeplin_status) {
            self::TEST_NOT_STARTED => 'status-pending',
            self::TEST_IN_PROGRESS => 'status-submitted',
            self::TEST_COMPLETED => 'status-accepted',
            default => 'status-pending'
        };
    }

    public function getKraeplinScoreAttribute()
    {
        $result = $this->kraeplinTestResult;
        return $result ? $result->overall_score : null;
    }

    public function getKraeplinPerformanceCategoryAttribute()
    {
        $result = $this->kraeplinTestResult;
        return $result ? $result->performance_category : null;
    }

    // ========== DISC TEST ACCESSORS - NEW ==========
    public function getDiscStatusAttribute()
    {
        $session = $this->discTestSessions()->latest()->first();
        
        if (!$session) {
            return self::TEST_NOT_STARTED;
        }

        return $session->status;
    }

    public function getDiscStatusLabelAttribute()
    {
        $statuses = self::getTestStatuses();
        return $statuses[$this->disc_status] ?? 'Tidak Diketahui';
    }

    public function getDiscStatusBadgeClassAttribute()
    {
        return match($this->disc_status) {
            self::TEST_NOT_STARTED => 'status-pending',
            self::TEST_IN_PROGRESS => 'status-submitted',
            self::TEST_COMPLETED => 'status-accepted',
            default => 'status-pending'
        };
    }

    public function getDiscPrimaryTypeAttribute()
    {
        $result = $this->discTestResult;
        return $result ? $result->primary_type : null;
    }

    public function getDiscSecondaryTypeAttribute()
    {
        $result = $this->discTestResult;
        return $result ? $result->secondary_type : null;
    }

    public function getDiscPersonalityProfileAttribute()
    {
        $result = $this->discTestResult;
        return $result ? $result->personality_profile : null;
    }

    public function getDiscPrimaryPercentageAttribute()
    {
        $result = $this->discTestResult;
        return $result ? $result->primary_percentage : null;
    }

    // ========== TEST HELPER METHODS ==========
    public function hasCompletedKraeplinTest()
    {
        return $this->kraeplinTestSessions()
            ->where('status', self::TEST_COMPLETED)
            ->exists();
    }

    public function hasStartedKraeplinTest()
    {
        return $this->kraeplinTestSessions()
            ->whereIn('status', [self::TEST_IN_PROGRESS, self::TEST_COMPLETED])
            ->exists();
    }

    public function hasCompletedDiscTest()
    {
        return $this->discTestSessions()
            ->where('status', self::TEST_COMPLETED)
            ->exists();
    }

    public function hasStartedDiscTest()
    {
        return $this->discTestSessions()
            ->whereIn('status', [self::TEST_IN_PROGRESS, self::TEST_COMPLETED])
            ->exists();
    }

    public function hasCompletedAllTests()
    {
        return $this->hasCompletedKraeplinTest() && $this->hasCompletedDiscTest();
    }

    public function canStartKraeplinTest()
    {
        return $this->application_status === self::STATUS_SUBMITTED && !$this->hasCompletedKraeplinTest();
    }

    public function canStartDiscTest()
    {
        return $this->hasCompletedKraeplinTest() && !$this->hasCompletedDiscTest();
    }

    public function isTestingRequired()
    {
        return $this->application_status === self::STATUS_SUBMITTED && !$this->hasCompletedAllTests();
    }

    // ========== TEST PROGRESS METHODS ==========
    public function getKraeplinTestProgress()
    {
        $session = $this->latestKraeplinTest;
        
        if (!$session) {
            return 0;
        }

        if ($session->status === self::TEST_COMPLETED) {
            return 100;
        }

        return $session->progress ?? 0;
    }

    public function getDiscTestProgress()
    {
        $session = $this->latestDiscTest;
        
        if (!$session) {
            return 0;
        }

        if ($session->status === self::TEST_COMPLETED) {
            return 100;
        }

        return $session->progress ?? 0;
    }

    // ========== COMPREHENSIVE TEST SUMMARY ==========
    public function getTestSummary()
    {
        $kraeplinSession = $this->latestKraeplinTest;
        $kraeplinResult = $this->kraeplinTestResult;
        $discSession = $this->latestDiscTest;
        $discResult = $this->discTestResult;
        
        return [
            'kraeplin' => [
                'status' => $this->kraeplin_status,
                'status_label' => $this->kraeplin_status_label,
                'progress' => $this->getKraeplinTestProgress(),
                'started_at' => $kraeplinSession?->started_at,
                'completed_at' => $kraeplinSession?->completed_at,
                'duration' => $kraeplinSession?->formatted_duration,
                'score' => $kraeplinResult?->overall_score,
                'accuracy' => $kraeplinResult?->accuracy_percentage,
                'performance_category' => $kraeplinResult?->performance_category
            ],
            'disc' => [
                'status' => $this->disc_status,
                'status_label' => $this->disc_status_label,
                'progress' => $this->getDiscTestProgress(),
                'started_at' => $discSession?->started_at,
                'completed_at' => $discSession?->completed_at,
                'duration' => $discSession?->formatted_duration,
                'primary_type' => $discResult?->primary_type,
                'secondary_type' => $discResult?->secondary_type,
                'personality_profile' => $discResult?->personality_profile,
                'primary_percentage' => $discResult?->primary_percentage
            ],
            'overall' => [
                'all_completed' => $this->hasCompletedAllTests(),
                'next_step' => $this->getNextTestStep(),
                'completion_percentage' => $this->getOverallTestCompletion()
            ]
        ];
    }

    public function getNextTestStep()
    {
        if (!$this->hasCompletedKraeplinTest()) {
            return 'kraeplin';
        }
        
        if (!$this->hasCompletedDiscTest()) {
            return 'disc';
        }
        
        return 'completed';
    }

    public function getOverallTestCompletion()
    {
        $completed = 0;
        $total = 2; // Kraeplin + DISC
        
        if ($this->hasCompletedKraeplinTest()) {
            $completed++;
        }
        
        if ($this->hasCompletedDiscTest()) {
            $completed++;
        }
        
        return round(($completed / $total) * 100, 2);
    }

    public function getApplicationCompletionStatus()
    {
        $steps = [
            'form_submitted' => $this->application_status !== self::STATUS_DRAFT,
            'kraeplin_completed' => $this->hasCompletedKraeplinTest(),
            'disc_completed' => $this->hasCompletedDiscTest(),
            'documents_uploaded' => $this->documentUploads()->count() > 0,
        ];

        $completed = array_filter($steps);
        $total = count($steps);
        $completedCount = count($completed);

        return [
            'steps' => $steps,
            'completed' => $completedCount,
            'total' => $total,
            'percentage' => round(($completedCount / $total) * 100, 2)
        ];
    }
}