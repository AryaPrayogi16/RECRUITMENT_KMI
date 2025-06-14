<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    // Relationships
    public function candidates()
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
               $this->closing_date && 
               $this->closing_date->isFuture();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->active()
                     ->where('closing_date', '>=', now());
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }
}