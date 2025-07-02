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
         return $this->hasMany(SocialActivity::class)->whereNull('deleted_at');
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

    // ========== DISC 3D TEST RELATIONSHIPS - UPDATED ==========
    public function disc3DTestSessions()
    {
        return $this->hasMany(Disc3DTestSession::class);
    }

    public function disc3DTestResults()
    {
        return $this->hasMany(Disc3DResult::class);
    }

    public function disc3DResponses()
    {
        return $this->hasMany(Disc3DResponse::class);
    }

    public function disc3DAnalytics()
    {
        return $this->hasMany(Disc3DTestAnalytics::class);
    }

    public function latestDisc3DTest()
    {
        return $this->hasOne(Disc3DTestSession::class)->latest();
    }

    public function completedDisc3DTest()
    {
        return $this->hasOne(Disc3DTestSession::class)->where('status', 'completed');
    }

    public function disc3DTestResult()
    {
        return $this->hasOne(Disc3DResult::class)->latest();
    }

    // ========== DISC TEST RELATIONSHIPS - DISC 3D ONLY ==========
    public function discTestSessions()
    {
        return $this->disc3DTestSessions();
    }

    public function discTestResults()
    {
        return $this->disc3DTestResults();
    }

    public function latestDiscTest()
    {
        return $this->latestDisc3DTest();
    }

    public function completedDiscTest()
    {
        return $this->completedDisc3DTest();
    }

    public function discTestResult()
    {
        return $this->disc3DTestResult();
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

    // ========== DISC 3D TEST SCOPES - NEW ==========
    public function scopeWithDisc3DTest($query)
    {
        return $query->whereHas('disc3DTestSessions', function($q) {
            $q->where('status', 'completed');
        });
    }

    public function scopeWithoutDisc3DTest($query)
    {
        return $query->whereDoesntHave('disc3DTestSessions', function($q) {
            $q->where('status', 'completed');
        });
    }

    public function scopeDisc3DInProgress($query)
    {
        return $query->whereHas('disc3DTestSessions', function($q) {
            $q->where('status', 'in_progress');
        });
    }

    // ========== DISC TEST SCOPES - DISC 3D ONLY ==========
    public function scopeWithDiscTest($query)
    {
        return $this->scopeWithDisc3DTest($query);
    }

    public function scopeWithoutDiscTest($query)
    {
        return $this->scopeWithoutDisc3DTest($query);
    }

    public function scopeDiscInProgress($query)
    {
        return $this->scopeDisc3DInProgress($query);
    }

    public function scopeWithAllTests($query)
    {
        return $query->whereHas('kraeplinTestSessions', function($q) {
            $q->where('status', self::TEST_COMPLETED);
        })->whereHas('disc3DTestSessions', function($q) {
            $q->where('status', 'completed');
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

    // ========== DISC 3D TEST ACCESSORS - NEW ==========
    public function getDisc3DStatusAttribute()
    {
        $session = $this->disc3DTestSessions()->latest()->first();
        
        if (!$session) {
            return 'not_started';
        }

        return $session->status;
    }

    public function getDisc3DStatusLabelAttribute()
    {
        $statusLabels = [
            'not_started' => 'Belum Dimulai',
            'in_progress' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            'timeout' => 'Timeout',
            'interrupted' => 'Terputus'
        ];
        
        return $statusLabels[$this->disc_3d_status] ?? 'Tidak Diketahui';
    }

    public function getDisc3DStatusBadgeClassAttribute()
    {
        return match($this->disc_3d_status) {
            'not_started' => 'status-pending',
            'in_progress' => 'status-submitted',
            'completed' => 'status-accepted',
            'timeout' => 'status-rejected',
            'interrupted' => 'status-rejected',
            default => 'status-pending'
        };
    }

    public function getDisc3DPrimaryTypeAttribute()
    {
        $result = $this->disc3DTestResult;
        return $result ? $result->primary_type : null;
    }

    public function getDisc3DSecondaryTypeAttribute()
    {
        $result = $this->disc3DTestResult;
        return $result ? $result->secondary_type : null;
    }

    public function getDisc3DPersonalityProfileAttribute()
    {
        $result = $this->disc3DTestResult;
        return $result ? $result->personality_profile : null;
    }

    public function getDisc3DPrimaryPercentageAttribute()
    {
        $result = $this->disc3DTestResult;
        return $result ? $result->primary_percentage : null;
    }

    public function getDisc3DSummaryAttribute()
    {
        $result = $this->disc3DTestResult;
        return $result ? $result->brief_summary : null;
    }

    public function getDisc3DStressLevelAttribute()
    {
        $result = $this->disc3DTestResult;
        return $result ? $result->stress_level : null;
    }

    public function getDisc3DAdaptationPatternAttribute()
    {
        $result = $this->disc3DTestResult;
        return $result ? $result->adaptation_pattern : null;
    }

    // ========== DISC TEST ACCESSORS - DISC 3D ONLY ==========
    public function getDiscStatusAttribute()
    {
        return $this->getDisc3DStatusAttribute();
    }

    public function getDiscStatusLabelAttribute()
    {
        return $this->getDisc3DStatusLabelAttribute();
    }

    public function getDiscStatusBadgeClassAttribute()
    {
        return $this->getDisc3DStatusBadgeClassAttribute();
    }

    public function getDiscPrimaryTypeAttribute()
    {
        return $this->getDisc3DPrimaryTypeAttribute();
    }

    public function getDiscSecondaryTypeAttribute()
    {
        return $this->getDisc3DSecondaryTypeAttribute();
    }

    public function getDiscPersonalityProfileAttribute()
    {
        return $this->getDisc3DPersonalityProfileAttribute();
    }

    public function getDiscPrimaryPercentageAttribute()
    {
        return $this->getDisc3DPrimaryPercentageAttribute();
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

    // ========== DISC 3D TEST HELPER METHODS - NEW ==========
    public function hasCompletedDisc3DTest()
    {
        return $this->disc3DTestSessions()
            ->where('status', 'completed')
            ->exists();
    }

    public function hasStartedDisc3DTest()
    {
        return $this->disc3DTestSessions()
            ->whereIn('status', ['in_progress', 'completed'])
            ->exists();
    }

    public function canStartDisc3DTest()
    {
        return $this->hasCompletedKraeplinTest() && !$this->hasCompletedDisc3DTest();
    }

    public function getDisc3DTestProgress()
    {
        $session = $this->latestDisc3DTest;
        
        if (!$session) {
            return 0;
        }

        if ($session->status === 'completed') {
            return 100;
        }

        return $session->progress ?? 0;
    }

    // ========== DISC TEST HELPER METHODS - DISC 3D ONLY ==========
    public function hasCompletedDiscTest()
    {
        return $this->hasCompletedDisc3DTest();
    }

    public function hasStartedDiscTest()
    {
        return $this->hasStartedDisc3DTest();
    }

    public function canStartDiscTest()
    {
        return $this->canStartDisc3DTest();
    }

    public function getDiscTestProgress()
    {
        return $this->getDisc3DTestProgress();
    }

    // ========== UNIFIED TEST METHODS ==========
    public function hasCompletedAllTests()
    {
        return $this->hasCompletedKraeplinTest() && $this->hasCompletedDisc3DTest();
    }

    public function canStartKraeplinTest()
    {
        return $this->application_status === self::STATUS_SUBMITTED && !$this->hasCompletedKraeplinTest();
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

    // ========== COMPREHENSIVE TEST SUMMARY - DISC 3D ONLY ==========
    public function getTestSummary()
    {
        $kraeplinSession = $this->latestKraeplinTest;
        $kraeplinResult = $this->kraeplinTestResult;
        $discSession = $this->latestDisc3DTest;
        $discResult = $this->disc3DTestResult;
        
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
                'status' => $this->disc_3d_status,
                'status_label' => $this->disc_3d_status_label,
                'progress' => $this->getDisc3DTestProgress(),
                'started_at' => $discSession?->started_at,
                'completed_at' => $discSession?->completed_at,
                'duration' => $discSession?->formatted_duration,
                'primary_type' => $discResult?->primary_type,
                'secondary_type' => $discResult?->secondary_type,
                'personality_profile' => $discResult?->personality_profile,
                'primary_percentage' => $discResult?->primary_percentage,
                'summary' => $this->disc_3d_summary,
                'stress_level' => $this->disc_3d_stress_level,
                'adaptation_pattern' => $this->disc_3d_adaptation_pattern
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
        
        if (!$this->hasCompletedDisc3DTest()) {
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
        
        if ($this->hasCompletedDisc3DTest()) {
            $completed++;
        }
        
        return round(($completed / $total) * 100, 2);
    }

    public function getApplicationCompletionStatus()
    {
        $steps = [
            'form_submitted' => $this->application_status !== self::STATUS_DRAFT,
            'kraeplin_completed' => $this->hasCompletedKraeplinTest(),
            'disc_completed' => $this->hasCompletedDisc3DTest(),
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

    // ========== DISC 3D SPECIFIC METHODS ==========
    
    /**
     * Get comprehensive DISC 3D analysis
     */
    public function getDisc3DAnalysis()
    {
        $result = $this->disc3DTestResult;
        $analytics = $this->disc3DAnalytics()->latest()->first();
        
        if (!$result) {
            return null;
        }

        return [
            'basic_info' => [
                'primary_type' => $result->primary_type,
                'secondary_type' => $result->secondary_type,
                'personality_profile' => $result->personality_profile,
                'summary' => $result->summary
            ],
            'three_graphs' => [
                'most' => $result->most_scores,
                'least' => $result->least_scores,
                'change' => $result->change_scores
            ],
            'interpretations' => [
                'public_self' => $result->public_self_summary,
                'private_self' => $result->private_self_summary,
                'adaptation' => $result->adaptation_summary,
                'overall' => $result->overall_profile
            ],
            'analytics' => [
                'engagement_level' => $analytics?->engagement_level,
                'quality_level' => $analytics?->quality_level,
                'completion_rate' => $analytics?->completion_rate,
                'total_time' => $analytics?->formatted_total_time,
                'suspicious_patterns' => $analytics?->suspicious_patterns
            ],
            'validity' => [
                'is_valid' => $result->is_valid,
                'consistency_score' => $result->consistency_score,
                'validity_flags' => $result->validity_flags
            ]
        ];
    }

    /**
     * Get DISC 3D responses breakdown
     */
    public function getDisc3DResponsesBreakdown()
    {
        return $this->disc3DResponses()
            ->with(['section', 'mostChoice', 'leastChoice'])
            ->orderBy('section_number')
            ->get()
            ->map(function($response) {
                return [
                    'section' => $response->section_number,
                    'most_choice' => $response->most_choice,
                    'least_choice' => $response->least_choice,
                    'most_text' => $response->mostChoice?->localized_text,
                    'least_text' => $response->leastChoice?->localized_text,
                    'net_scores' => $response->net_scores,
                    'time_spent' => $response->formatted_time
                ];
            });
    }

    /**
     * Check if candidate has valid DISC 3D result
     */
    public function hasValidDisc3DResult()
    {
        $result = $this->disc3DTestResult;
        return $result && $result->is_valid;
    }

    /**
     * Get DISC 3D recommended actions based on results
     */
    public function getDisc3DRecommendations()
    {
        $result = $this->disc3DTestResult;
        
        if (!$result) {
            return null;
        }

        $recommendations = [];

        // Based on primary type
        switch ($result->primary_type) {
            case 'D':
                $recommendations['role_fit'] = ['Leadership positions', 'Decision-making roles', 'Goal-oriented tasks'];
                $recommendations['management_tips'] = ['Give autonomy', 'Set clear objectives', 'Avoid micromanagement'];
                break;
            case 'I':
                $recommendations['role_fit'] = ['People-facing roles', 'Communication positions', 'Team collaboration'];
                $recommendations['management_tips'] = ['Provide social interaction', 'Recognize achievements publicly', 'Allow creative expression'];
                break;
            case 'S':
                $recommendations['role_fit'] = ['Support roles', 'Steady environments', 'Team member positions'];
                $recommendations['management_tips'] = ['Provide stability', 'Give advance notice of changes', 'Appreciate loyalty'];
                break;
            case 'C':
                $recommendations['role_fit'] = ['Detail-oriented tasks', 'Quality control', 'Analysis positions'];
                $recommendations['management_tips'] = ['Provide clear procedures', 'Allow time for thoroughness', 'Value accuracy'];
                break;
        }

        // Based on stress level
        if ($result->stress_level === 'High') {
            $recommendations['stress_management'] = [
                'Monitor workload carefully',
                'Provide stress management resources',
                'Consider role adjustments'
            ];
        }

        // Based on adaptation pattern
        if ($result->hasHighAdaptation()) {
            $recommendations['adaptation_support'] = [
                'Acknowledge adaptation efforts',
                'Provide authentic environment',
                'Regular check-ins on satisfaction'
            ];
        }

        return $recommendations;
    }
}