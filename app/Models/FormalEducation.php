<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormalEducation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'formal_education';

    protected $fillable = [
        'candidate_id',
        'education_level',
        'institution_name',
        'major',
        'start_year',
        'end_year',
        'gpa'
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'gpa' => 'decimal:2'
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByLevel($query, $level)
    {
        return $query->where('education_level', $level);
    }

    public function scopeOrderByLevel($query)
    {
        $levelOrder = ['S3', 'S2', 'S1', 'Diploma', 'SMA/SMK'];
        
        return $query->orderByRaw("FIELD(education_level, '" . implode("','", $levelOrder) . "')");
    }

    public function scopeOrderByYear($query, $direction = 'desc')
    {
        return $query->orderBy('end_year', $direction);
    }

    public function scopeByInstitution($query, $institution)
    {
        return $query->where('institution_name', 'like', '%' . $institution . '%');
    }

    public function scopeByMajor($query, $major)
    {
        return $query->where('major', 'like', '%' . $major . '%');
    }

    public function scopeWithHighGPA($query, $minGpa = 3.0)
    {
        return $query->where('gpa', '>=', $minGpa);
    }

    // Accessors
    public function getFormattedDurationAttribute()
    {
        return $this->start_year . ' - ' . $this->end_year;
    }

    public function getFormattedGpaAttribute()
    {
        return $this->gpa ? number_format($this->gpa, 2) : null;
    }

    public function getEducationTitleAttribute()
    {
        return $this->education_level . ' ' . $this->major . ' - ' . $this->institution_name;
    }

    public function getDurationInYearsAttribute()
    {
        return $this->end_year - $this->start_year;
    }

    public function getIsHigherEducationAttribute()
    {
        return in_array($this->education_level, ['S1', 'S2', 'S3']);
    }

    public function getIsVocationalAttribute()
    {
        return in_array($this->education_level, ['SMA/SMK', 'Diploma']);
    }

    public function getGpaScaleAttribute()
    {
        // Determine GPA scale based on education level
        if (in_array($this->education_level, ['S1', 'S2', 'S3', 'Diploma'])) {
            return '4.0'; // University scale
        }
        
        return '10.0'; // High school scale
    }

    public function getGpaPercentageAttribute()
    {
        if (!$this->gpa) return null;
        
        $scale = $this->getGpaScaleAttribute();
        
        if ($scale === '4.0') {
            return ($this->gpa / 4.0) * 100;
        }
        
        return ($this->gpa / 10.0) * 100;
    }

    // Validation helper
    public function getValidationRules()
    {
        return [
            'candidate_id' => 'required|exists:candidates,id',
            'education_level' => 'required|in:SMA/SMK,Diploma,S1,S2,S3',
            'institution_name' => 'required|string|max:255',
            'major' => 'required|string|max:255',
            'start_year' => 'required|integer|between:1950,2030',
            'end_year' => 'required|integer|between:1950,2030|gte:start_year',
            'gpa' => 'nullable|numeric|between:0,' . ($this->getGpaScaleAttribute() === '4.0' ? '4' : '10')
        ];
    }

    // Helper methods
    public function isCompleted()
    {
        return $this->end_year <= now()->year;
    }

    public function isOngoing()
    {
        return $this->end_year > now()->year;
    }

    public function getEducationPriority()
    {
        $priorities = [
            'S3' => 1,
            'S2' => 2, 
            'S1' => 3,
            'Diploma' => 4,
            'SMA/SMK' => 5
        ];
        
        return $priorities[$this->education_level] ?? 999;
    }

    // Static methods
    public static function getEducationLevels()
    {
        return ['SMA/SMK', 'Diploma', 'S1', 'S2', 'S3'];
    }

    public static function getHighestEducationForCandidate($candidateId)
    {
        return self::where('candidate_id', $candidateId)
                  ->orderByRaw("FIELD(education_level, 'S3', 'S2', 'S1', 'Diploma', 'SMA/SMK')")
                  ->first();
    }

    public static function getLatestEducationForCandidate($candidateId)
    {
        return self::where('candidate_id', $candidateId)
                  ->orderBy('end_year', 'desc')
                  ->first();
    }
}