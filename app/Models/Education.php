<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'education_type',
        // For formal education
        'education_level',
        'institution_name',
        'major',
        'start_year',
        'end_year',
        'gpa',
        // For non-formal education
        'course_name',
        'organizer',
        'date',
        'description'
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'gpa' => 'decimal:2',
        'date' => 'date'
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeFormal($query)
    {
        return $query->where('education_type', 'formal');
    }

    public function scopeNonFormal($query)
    {
        return $query->where('education_type', 'non_formal');
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('education_level', $level);
    }

    public function scopeOrderByLevel($query)
    {
        $levelOrder = ['S3', 'S2', 'S1', 'Diploma', 'SMA/SMK'];
        
        return $query->orderByRaw("FIELD(education_level, '" . implode("','", $levelOrder) . "')");
    }

    // Accessors
    public function getFormattedDurationAttribute()
    {
        if ($this->education_type === 'formal' && $this->start_year && $this->end_year) {
            return $this->start_year . ' - ' . $this->end_year;
        }
        
        return $this->date ? $this->date->format('Y') : null;
    }

    public function getFormattedGpaAttribute()
    {
        return $this->gpa ? number_format($this->gpa, 2) : null;
    }

    public function getEducationTitleAttribute()
    {
        if ($this->education_type === 'formal') {
            return $this->education_level . ' ' . $this->major . ' - ' . $this->institution_name;
        }
        
        return $this->course_name . ' - ' . $this->organizer;
    }

    public function getIsFormalAttribute()
    {
        return $this->education_type === 'formal';
    }

    public function getIsNonFormalAttribute()
    {
        return $this->education_type === 'non_formal';
    }

    // Validation helper
    public function getValidationRules()
    {
        $rules = [
            'candidate_id' => 'required|exists:candidates,id',
            'education_type' => 'required|in:formal,non_formal'
        ];

        if ($this->education_type === 'formal') {
            $rules = array_merge($rules, [
                'education_level' => 'required|in:SMA/SMK,Diploma,S1,S2,S3',
                'institution_name' => 'required|string|max:255',
                'major' => 'required|string|max:255',
                'start_year' => 'required|integer|between:1950,2030',
                'end_year' => 'required|integer|between:1950,2030|gte:start_year',
                'gpa' => 'nullable|numeric|between:0,4'
            ]);
        } else {
            $rules = array_merge($rules, [
                'course_name' => 'required|string|max:255',
                'organizer' => 'required|string|max:255',
                'date' => 'nullable|date',
                'description' => 'nullable|string'
            ]);
        }

        return $rules;
    }
}
