<?php

// ==== 1. DISC 3D SECTION MODEL ====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disc3DSectionChoice extends Model
{
    use HasFactory;

    // ✅ FIXED: Explicitly define table name to match migration
    protected $table = 'disc_3d_section_choices';


    protected $fillable = [
        'section_id',
        'section_code',
        'section_number',
        'choice_dimension',
        'choice_code',
        'choice_text',
        'choice_text_en',
        'weight_d',
        'weight_i',
        'weight_s',
        'weight_c',
        'primary_dimension',
        'primary_strength',
        'keywords',
        'keywords_en',
        'is_active'
    ];

    protected $casts = [
        'weight_d' => 'decimal:4',
        'weight_i' => 'decimal:4',
        'weight_s' => 'decimal:4',
        'weight_c' => 'decimal:4',
        'primary_strength' => 'decimal:4',
        'keywords' => 'array',
        'keywords_en' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function section()
    {
        return $this->belongsTo(Disc3DSection::class, 'section_id');
    }

    public function mostResponses()
    {
        return $this->hasMany(Disc3DResponse::class, 'most_choice_id');
    }

    public function leastResponses()
    {
        return $this->hasMany(Disc3DResponse::class, 'least_choice_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDimension($query, $dimension)
    {
        return $query->where('choice_dimension', $dimension);
    }

    public function scopeBySection($query, $sectionNumber)
    {
        return $query->where('section_number', $sectionNumber);
    }

    // Accessors (inspired by old DiscQuestion model)
    public function getDimensionWeightsAttribute()
    {
        return [
            'D' => $this->weight_d,
            'I' => $this->weight_i,
            'S' => $this->weight_s,
            'C' => $this->weight_c
        ];
    }

    public function getChoiceTextAttribute()
    {
        return $this->choice_text_id ?? $this->choice_text_en ?? $this->attributes['choice_text'];
    }

    public function getLocalizedTextAttribute()
    {
        return app()->getLocale() === 'id' 
            ? $this->attributes['choice_text']
            : ($this->choice_text_en ?? $this->attributes['choice_text']);
    }

    public function getLocalizedKeywordsAttribute()
    {
        return app()->getLocale() === 'id' 
            ? $this->keywords
            : ($this->keywords_en ?? $this->keywords);
    }

    // Methods
    public function calculateWeightedScore($responseType = 'most')
    {
        $multiplier = $responseType === 'most' ? 1 : -1;
        
        return [
            'D' => $this->weight_d * $multiplier,
            'I' => $this->weight_i * $multiplier,
            'S' => $this->weight_s * $multiplier,
            'C' => $this->weight_c * $multiplier
        ];
    }

    public function getDominanceStrength()
    {
        $weights = $this->dimension_weights;
        return max(array_map('abs', $weights));
    }
}