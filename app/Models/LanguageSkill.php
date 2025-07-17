<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LanguageSkill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'language_skills';

    protected $fillable = [
        'candidate_id',
        'language',
        'speaking_level',
        'writing_level',
    ];

    // Set defaults sesuai database
    protected $attributes = [
        'language' => null,
        'speaking_level' => null,
        'writing_level' => null,
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopeForCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('speaking_level', $level)
                     ->orWhere('writing_level', $level);
    }

    public function scopeOrderByProficiency($query)
    {
        $levelOrder = ['Mahir', 'Menengah', 'Pemula'];
        
        return $query->orderByRaw("FIELD(speaking_level, '" . implode("','", $levelOrder) . "')")
                     ->orderByRaw("FIELD(writing_level, '" . implode("','", $levelOrder) . "')");
    }

    // Accessors
    public function getSpeakingLevelBadgeAttribute()
    {
        $badges = [
            'Pemula' => 'bg-red-100 text-red-800',
            'Menengah' => 'bg-yellow-100 text-yellow-800',
            'Mahir' => 'bg-green-100 text-green-800'
        ];

        return $badges[$this->speaking_level] ?? 'bg-gray-100 text-gray-800';
    }

    public function getWritingLevelBadgeAttribute()
    {
        $badges = [
            'Pemula' => 'bg-red-100 text-red-800',
            'Menengah' => 'bg-yellow-100 text-yellow-800',
            'Mahir' => 'bg-green-100 text-green-800'
        ];

        return $badges[$this->writing_level] ?? 'bg-gray-100 text-gray-800';
    }

    public function getOverallLevelAttribute()
    {
        $levels = ['Pemula' => 1, 'Menengah' => 2, 'Mahir' => 3];
        
        $speakingScore = $levels[$this->speaking_level] ?? 0;
        $writingScore = $levels[$this->writing_level] ?? 0;
        
        if ($speakingScore == 0 && $writingScore == 0) return null;
        
        $average = ($speakingScore + $writingScore) / 2;
        
        if ($average >= 2.5) return 'Mahir';
        if ($average >= 1.5) return 'Menengah';
        return 'Pemula';
    }

    public function getFormattedSkillAttribute()
    {
        return $this->language . ' (Bicara: ' . $this->speaking_level . ', Tulis: ' . $this->writing_level . ')';
    }

    public function getLanguageDisplayNameAttribute()
    {
        $languages = [
            'Bahasa Indonesia' => 'Indonesian',
            'Bahasa Inggris' => 'English',
            'Bahasa Mandarin' => 'Mandarin',
            'Lainnya' => 'Other Languages'
        ];

        return $languages[$this->language] ?? $this->language;
    }

    // Static methods
    public static function getAvailableLanguages()
    {
        return ['Bahasa Inggris', 'Bahasa Mandarin', 'Lainnya'];
    }

    public static function getAvailableLevels()
    {
        return ['Pemula', 'Menengah', 'Mahir'];
    }

    public static function getLanguageOptions()
    {
        return [
            'Bahasa Inggris' => 'Bahasa Inggris',
            'Bahasa Mandarin' => 'Bahasa Mandarin',
            'Lainnya' => 'Lainnya'
        ];
    }

    public static function getLevelOptions()
    {
        return [
            'Pemula' => 'Pemula',
            'Menengah' => 'Menengah',
            'Mahir' => 'Mahir'
        ];
    }

    // Validation helper
    public function getValidationRules()
    {
        return [
            'candidate_id' => 'required|exists:candidates,id',
            'language' => 'nullable|string|max:255',
            'speaking_level' => 'nullable|in:Pemula,Menengah,Mahir',
            'writing_level' => 'nullable|in:Pemula,Menengah,Mahir'
        ];
    }
}
