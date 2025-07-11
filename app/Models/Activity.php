<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'activities';

    protected $fillable = [
        'candidate_id',
        'activity_type',
        'title',
        'field_or_year',
        'period',
        'description'
    ];

    // Set explicit null defaults sesuai database
    protected $attributes = [
        'title' => null,
        'field_or_year' => null,
        'period' => null,
        'description' => null,
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeAchievements($query)
    {
        return $query->where('activity_type', 'achievement');
    }

    public function scopeSocialActivities($query)
    {
        return $query->where('activity_type', 'social_activity');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeOrderByYear($query)
    {
        return $query->orderBy('field_or_year', 'desc');
    }

    public function scopeForCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    // Accessors
    public function getIsAchievementAttribute()
    {
        return $this->activity_type === 'achievement';
    }

    public function getIsSocialActivityAttribute()
    {
        return $this->activity_type === 'social_activity';
    }

    public function getFormattedTitleAttribute()
    {
        if ($this->is_achievement) {
            return $this->title . ($this->field_or_year ? ' (' . $this->field_or_year . ')' : '');
        }
        
        return $this->title . ($this->field_or_year ? ' - ' . $this->field_or_year : '');
    }

    public function getActivityLabelAttribute()
    {
        return $this->activity_type === 'achievement' ? 'Prestasi' : 'Kegiatan Sosial';
    }

    public function getTimeInfoAttribute()
    {
        if ($this->is_achievement) {
            return $this->field_or_year ? 'Tahun ' . $this->field_or_year : null;
        }
        
        return $this->period ?: ($this->field_or_year ? 'Bidang: ' . $this->field_or_year : null);
    }

    public function getActivityTypeBadgeAttribute()
    {
        $badges = [
            'achievement' => 'bg-yellow-100 text-yellow-800',
            'social_activity' => 'bg-green-100 text-green-800'
        ];

        return $badges[$this->activity_type] ?? 'bg-gray-100 text-gray-800';
    }

    // Static methods
    public static function getActivityTypes()
    {
        return [
            'achievement' => 'Prestasi',
            'social_activity' => 'Kegiatan Sosial'
        ];
    }

    // Validation helper
    public function getValidationRules()
    {
        $rules = [
            'candidate_id' => 'required|exists:candidates,id',
            'activity_type' => 'required|in:achievement,social_activity',
            'title' => 'nullable|string|max:255'
        ];

        if ($this->activity_type === 'achievement') {
            $rules['field_or_year'] = 'nullable|integer|between:1950,2030';
        } else {
            $rules['field_or_year'] = 'nullable|string|max:255';
            $rules['period'] = 'nullable|string|max:255';
        }

        $rules['description'] = 'nullable|string';

        return $rules;
    }
}