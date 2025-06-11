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
        'position_applied',
        'expected_salary',
        'application_status'
    ];

    protected $casts = [
        'expected_salary' => 'decimal:2'
    ];

    // Constants for application status
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWING = 'reviewing';
    const STATUS_INTERVIEW = 'interview';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_REVIEWING => 'Under Review',
            self::STATUS_INTERVIEW => 'Interview',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected'
        ];
    }

    // Relationships
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
        return $this->hasMany(ComputerSkill::class);
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class);
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

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_applied', 'position_name');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('application_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('application_status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('application_status', [self::STATUS_REJECTED]);
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position_applied', $position);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->personalData?->full_name ?? '';
    }

    public function getEmailAttribute()
    {
        return $this->personalData?->email ?? '';
    }

    public function getPhoneAttribute()
    {
        return $this->personalData?->phone_number ?? '';
    }
}
