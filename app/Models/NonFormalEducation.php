<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class NonFormalEducation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'non_formal_education';

    protected $fillable = [
        'candidate_id',
        'course_name',
        'organizer',
        'date',
        'description'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByOrganizer($query, $organizer)
    {
        return $query->where('organizer', 'like', '%' . $organizer . '%');
    }

    public function scopeByCourse($query, $courseName)
    {
        return $query->where('course_name', 'like', '%' . $courseName . '%');
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeRecent($query, $months = 12)
    {
        return $query->where('date', '>=', Carbon::now()->subMonths($months));
    }

    public function scopeOrderByDate($query, $direction = 'desc')
    {
        return $query->orderBy('date', $direction);
    }

    public function scopeByCertificationType($query, $type)
    {
        $certificationTypes = [
            'technical' => ['programming', 'coding', 'development', 'network', 'database', 'system', 'IT', 'computer'],
            'management' => ['leadership', 'management', 'project', 'supervisor'],
            'language' => ['english', 'language', 'bahasa', 'conversation', 'speaking', 'writing'],
            'professional' => ['certification', 'license', 'professional', 'workshop', 'seminar'],
            'skill' => ['skill', 'training', 'course', 'workshop']
        ];

        if (isset($certificationTypes[$type])) {
            $keywords = $certificationTypes[$type];
            return $query->where(function($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('course_name', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                }
            });
        }

        return $query;
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('d M Y') : null;
    }

    public function getEducationTitleAttribute()
    {
        return $this->course_name . ' - ' . $this->organizer;
    }

    public function getYearAttribute()
    {
        return $this->date ? $this->date->year : null;
    }

    public function getMonthYearAttribute()
    {
        return $this->date ? $this->date->format('M Y') : null;
    }

    public function getIsRecentAttribute()
    {
        if (!$this->date) return false;
        return $this->date->isAfter(Carbon::now()->subYear());
    }

    public function getAgeInMonthsAttribute()
    {
        if (!$this->date) return null;
        return Carbon::now()->diffInMonths($this->date);
    }

    public function getAgeInYearsAttribute()
    {
        if (!$this->date) return null;
        return Carbon::now()->diffInYears($this->date);
    }

    public function getCertificationTypeAttribute()
    {
        $courseName = strtolower($this->course_name);
        $description = strtolower($this->description ?? '');
        $content = $courseName . ' ' . $description;

        if (preg_match('/\b(programming|coding|development|network|database|system|IT|computer|software|hardware)\b/i', $content)) {
            return 'Technical';
        }

        if (preg_match('/\b(leadership|management|project|supervisor|team|lead)\b/i', $content)) {
            return 'Management';
        }

        if (preg_match('/\b(english|language|bahasa|conversation|speaking|writing|communication)\b/i', $content)) {
            return 'Language';
        }

        if (preg_match('/\b(certification|license|professional|workshop|seminar|conference)\b/i', $content)) {
            return 'Professional';
        }

        return 'General';
    }

    // Validation helper
    public function getValidationRules()
    {
        return [
            'candidate_id' => 'required|exists:candidates,id',
            'course_name' => 'required|string|max:255',
            'organizer' => 'required|string|max:255',
            'date' => 'nullable|date|before_or_equal:today',
            'description' => 'nullable|string|max:1000'
        ];
    }

    // Helper methods
    public function isValidCertification()
    {
        // Check if it's a valid certification (has date and proper organizer)
        return $this->date && 
               $this->organizer && 
               strlen($this->organizer) > 2;
    }

    public function isTechnicalCertification()
    {
        return $this->getCertificationTypeAttribute() === 'Technical';
    }

    public function isProfessionalCertification()
    {
        return $this->getCertificationTypeAttribute() === 'Professional';
    }

    public function getRelevanceScore($jobRequirements = [])
    {
        if (empty($jobRequirements)) return 0;

        $score = 0;
        $courseName = strtolower($this->course_name);
        $description = strtolower($this->description ?? '');
        $content = $courseName . ' ' . $description;

        foreach ($jobRequirements as $requirement) {
            if (str_contains($content, strtolower($requirement))) {
                $score += 1;
            }
        }

        // Bonus for recent certifications
        if ($this->getIsRecentAttribute()) {
            $score *= 1.2;
        }

        return $score;
    }

    // Static methods
    public static function getCertificationTypes()
    {
        return ['Technical', 'Management', 'Language', 'Professional', 'General'];
    }

    public static function getRecentCertificationsForCandidate($candidateId, $months = 24)
    {
        return self::where('candidate_id', $candidateId)
                  ->recent($months)
                  ->orderByDate()
                  ->get();
    }

    public static function getTechnicalCertificationsForCandidate($candidateId)
    {
        return self::where('candidate_id', $candidateId)
                  ->get()
                  ->filter(function($cert) {
                      return $cert->isTechnicalCertification();
                  });
    }

    public static function getCertificationCountByType($candidateId)
    {
        $certifications = self::where('candidate_id', $candidateId)->get();
        
        $counts = [];
        foreach (self::getCertificationTypes() as $type) {
            $counts[$type] = $certifications->filter(function($cert) use ($type) {
                return $cert->getCertificationTypeAttribute() === $type;
            })->count();
        }

        return $counts;
    }

    public static function getMostRelevantCertifications($candidateId, $jobRequirements = [], $limit = 5)
    {
        return self::where('candidate_id', $candidateId)
                  ->get()
                  ->map(function($cert) use ($jobRequirements) {
                      $cert->relevance_score = $cert->getRelevanceScore($jobRequirements);
                      return $cert;
                  })
                  ->sortByDesc('relevance_score')
                  ->take($limit);
    }
}