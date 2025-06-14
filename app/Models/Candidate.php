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
    const STATUS_WITHDRAWN = 'withdrawn';

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_SCREENING => 'Screening',
            self::STATUS_INTERVIEW => 'Interview',
            self::STATUS_OFFERED => 'Offered',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_WITHDRAWN => 'Withdrawn'
        ];
    }

    // Relationships
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

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('application_status', $status);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('application_status', self::STATUS_SUBMITTED);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('application_status', [self::STATUS_REJECTED, self::STATUS_WITHDRAWN]);
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position_applied', $position);
    }

    // Accessors
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
            'withdrawn' => 'status-withdrawn',
            default => 'status-pending'
        };
    }
}