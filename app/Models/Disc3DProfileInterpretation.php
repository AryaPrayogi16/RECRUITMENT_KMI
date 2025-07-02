<?php

// ==== 1. DISC 3D SECTION MODEL ====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// ==== 7. DISC 3D PROFILE INTERPRETATION MODEL ====
class Disc3DProfileInterpretation extends Model
{
    use HasFactory;

    // ✅ FIXED: Explicitly define table name
    protected $table = 'disc_3d_profile_interpretations';

    protected $fillable = [
        'dimension',
        'graph_type',
        'segment_level',
        'title',
        'title_en',
        'description',
        'description_en',
        'characteristics',
        'characteristics_en',
        'behavioral_indicators',
        'work_style',
        'communication_style',
        'stress_behavior',
        'motivators',
        'fears'
    ];

    protected $casts = [
        'characteristics' => 'array',
        'characteristics_en' => 'array',
        'behavioral_indicators' => 'array',
        'work_style' => 'array',
        'communication_style' => 'array',
        'stress_behavior' => 'array',
        'motivators' => 'array',
        'fears' => 'array'
    ];

    // Scopes
    public function scopeByDimension($query, $dimension)
    {
        return $query->where('dimension', $dimension);
    }

    public function scopeByGraphType($query, $graphType)
    {
        return $query->where('graph_type', $graphType);
    }

    public function scopeBySegment($query, $segmentLevel)
    {
        return $query->where('segment_level', $segmentLevel);
    }

    // Accessors
    public function getLocalizedTitleAttribute()
    {
        return app()->getLocale() === 'id' ? $this->title : ($this->title_en ?? $this->title);
    }

    public function getLocalizedDescriptionAttribute()
    {
        return app()->getLocale() === 'id' ? $this->description : ($this->description_en ?? $this->description);
    }

    public function getLocalizedCharacteristicsAttribute()
    {
        return app()->getLocale() === 'id' ? $this->characteristics : ($this->characteristics_en ?? $this->characteristics);
    }

    // Static methods
    public static function getInterpretation($dimension, $graphType, $segmentLevel)
    {
        return self::where('dimension', $dimension)
            ->where('graph_type', $graphType)
            ->where('segment_level', $segmentLevel)
            ->first();
    }
}
