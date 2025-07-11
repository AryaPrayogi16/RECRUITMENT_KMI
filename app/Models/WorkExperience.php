<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkExperience extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'work_experiences';

    protected $fillable = [
        'candidate_id',
        'company_name',
        'company_address',
        'company_field',
        'position',
        'start_year',
        'end_year',
        'salary',
        'reason_for_leaving',
        'supervisor_contact',
    ];

    // Set defaults sesuai database
    protected $attributes = [
        'company_name' => null,
        'company_address' => null,
        'company_field' => null,
        'position' => null,
        'start_year' => null,
        'end_year' => null,
        'salary' => null,
        'reason_for_leaving' => null,
        'supervisor_contact' => null,
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'salary' => 'decimal:2'
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeForCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    public function scopeByCompany($query, $company)
    {
        return $query->where('company_name', 'like', '%' . $company . '%');
    }

    public function scopeByField($query, $field)
    {
        return $query->where('company_field', 'like', '%' . $field . '%');
    }

    public function scopeOrderByRecent($query)
    {
        return $query->orderBy('end_year', 'desc')
                     ->orderBy('start_year', 'desc');
    }

    public function scopeRecentExperience($query, $years = 5)
    {
        $cutoffYear = now()->year - $years;
        return $query->where('end_year', '>=', $cutoffYear);
    }

    // Accessors
    public function getDurationAttribute()
    {
        if (!$this->start_year || !$this->end_year) return null;
        
        $years = $this->end_year - $this->start_year;
        
        if ($years == 0) return '< 1 tahun';
        if ($years == 1) return '1 tahun';
        return $years . ' tahun';
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->start_year || !$this->end_year) return null;
        
        return $this->start_year . ' - ' . $this->end_year . ' (' . $this->duration . ')';
    }

    public function getFormattedSalaryAttribute()
    {
        if (!$this->salary) return 'Tidak disebutkan';
        
        return 'Rp ' . number_format($this->salary, 0, ',', '.');
    }

    public function getYearsOfExperienceAttribute()
    {
        if (!$this->start_year || !$this->end_year) return 0;
        
        return max(0, $this->end_year - $this->start_year);
    }

    public function getIsRecentAttribute()
    {
        if (!$this->end_year) return false;
        
        return $this->end_year >= (now()->year - 3);
    }

    public function getCompanyInfoAttribute()
    {
        $info = $this->company_name;
        
        if ($this->company_field) {
            $info .= ' (' . $this->company_field . ')';
        }
        
        return $info;
    }

    public function getPositionWithDurationAttribute()
    {
        return $this->position . ' - ' . $this->formatted_duration;
    }

    public function getSupervisorNameAttribute()
    {
        if (!$this->supervisor_contact) return null;
        
        // Extract name from "Name - Phone" format
        $parts = explode(' - ', $this->supervisor_contact);
        return trim($parts[0] ?? '');
    }

    public function getSupervisorPhoneAttribute()
    {
        if (!$this->supervisor_contact) return null;
        
        // Extract phone from "Name - Phone" format
        $parts = explode(' - ', $this->supervisor_contact);
        return trim($parts[1] ?? '');
    }

    // Experience level calculation
    public function getExperienceLevelAttribute()
    {
        $years = $this->years_of_experience;
        
        if ($years == 0) return 'Entry Level';
        if ($years <= 2) return 'Junior';
        if ($years <= 5) return 'Mid Level';
        if ($years <= 10) return 'Senior';
        return 'Expert';
    }

    // Validation helper
    public function getValidationRules()
    {
        return [
            'candidate_id' => 'required|exists:candidates,id',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_field' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'start_year' => 'nullable|integer|between:1950,2030',
            'end_year' => 'nullable|integer|between:1950,2030|gte:start_year',
            'salary' => 'nullable|numeric|min:0',
            'reason_for_leaving' => 'nullable|string|max:500',
            'supervisor_contact' => 'nullable|string|max:255'
        ];
    }

    // Static methods
    public static function getTotalExperienceForCandidate($candidateId)
    {
        return self::where('candidate_id', $candidateId)
                   ->get()
                   ->sum('years_of_experience');
    }

    public static function getRecentCompaniesForCandidate($candidateId, $limit = 3)
    {
        return self::where('candidate_id', $candidateId)
                   ->orderByRecent()
                   ->limit($limit)
                   ->pluck('company_name')
                   ->filter()
                   ->toArray();
    }
}