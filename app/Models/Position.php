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
        'is_active'
    ];

    protected $casts = [
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'position_applied', 'position_name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    // Accessors
    public function getSalaryRangeAttribute()
    {
        if ($this->salary_range_min && $this->salary_range_max) {
            return 'Rp ' . number_format($this->salary_range_min, 0, ',', '.') . ' - Rp ' . 
                   number_format($this->salary_range_max, 0, ',', '.');
        }
        return 'Negotiable';
    }

    public function getCandidateCountAttribute()
    {
        return $this->candidates()->count();
    }
}