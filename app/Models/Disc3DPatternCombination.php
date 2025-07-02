<?php

// ==== 1. DISC 3D SECTION MODEL ====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


// ==== 8. DISC 3D PATTERN COMBINATION MODEL ====
class Disc3DPatternCombination extends Model
{
    use HasFactory;

    protected $table = 'disc_3d_pattern_combinations';

    protected $fillable = [
        'pattern_code',
        'pattern_name',
        'pattern_name_en',
        'description',
        'description_en',
        'strengths',
        'weaknesses',
        'ideal_environment',
        'communication_tips',
        'career_matches'
    ];

    protected $casts = [
        'strengths' => 'array',
        'weaknesses' => 'array',
        'ideal_environment' => 'array',
        'communication_tips' => 'array',
        'career_matches' => 'array'
    ];

    // Scopes
    public function scopeByPattern($query, $patternCode)
    {
        return $query->where('pattern_code', $patternCode);
    }

    // Accessors
    public function getLocalizedNameAttribute()
    {
        return app()->getLocale() === 'id' ? $this->pattern_name : ($this->pattern_name_en ?? $this->pattern_name);
    }

    public function getLocalizedDescriptionAttribute()
    {
        return app()->getLocale() === 'id' ? $this->description : ($this->description_en ?? $this->description);
    }

    // Static methods
    public static function getByPattern($patternCode)
    {
        return self::where('pattern_code', $patternCode)->first();
    }

    public static function getAllPatterns()
    {
        return self::all()->keyBy('pattern_code');
    }
}
