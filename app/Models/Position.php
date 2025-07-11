<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'position_name',
        'department',
        'description',
        'requirements',
        'salary_range_min',
        'salary_range_max',
        'is_active',
        'location',
        'employment_type',
        'posted_date',
        'closing_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'posted_date' => 'date',
        'closing_date' => 'date',
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
    ];

    // Constants sesuai database enum
    const TYPE_FULL_TIME = 'full-time';
    const TYPE_PART_TIME = 'part-time';
    const TYPE_CONTRACT = 'contract';
    const TYPE_INTERNSHIP = 'internship';

    // Relationships
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    // Accessors
    public function getSalaryRangeAttribute()
    {
        if ($this->salary_range_min && $this->salary_range_max) {
            return 'Rp ' . number_format($this->salary_range_min, 0, ',', '.') . 
                   ' - Rp ' . number_format($this->salary_range_max, 0, ',', '.');
        }
        return 'Negotiable';
    }

    public function getIsOpenAttribute()
    {
        return $this->is_active && 
               (!$this->closing_date || $this->closing_date->isFuture());
    }

    public function getApplicationCountAttribute()
    {
        return $this->candidates()->count();
    }

    public function getDaysUntilClosingAttribute()
    {
        if (!$this->closing_date) return null;
        
        return $this->closing_date->diffInDays(now(), false);
    }

    public function getEmploymentTypeLabelAttribute()
    {
        $labels = [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'internship' => 'Internship'
        ];

        return $labels[$this->employment_type] ?? $this->employment_type;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->active()
                     ->where(function($q) {
                         $q->whereNull('closing_date')
                           ->orWhere('closing_date', '>=', now());
                     });
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    // Static methods
    public static function getEmploymentTypes()
    {
        return [
            self::TYPE_FULL_TIME => 'Full Time',
            self::TYPE_PART_TIME => 'Part Time',
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_INTERNSHIP => 'Internship'
        ];
    }

    public static function getDepartments()
    {
        return self::select('department')
                   ->distinct()
                   ->orderBy('department')
                   ->pluck('department')
                   ->toArray();
    }

    public static function getLocations()
    {
        return self::select('location')
                   ->distinct()
                   ->whereNotNull('location')
                   ->orderBy('location')
                   ->pluck('location')
                   ->toArray();
    }
}