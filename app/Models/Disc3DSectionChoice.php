<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disc3DSectionChoice extends Model
{
    use HasFactory, SoftDeletes;

    // ✅ CRITICAL: Explicitly define table name to match migration
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

    // ===== RELATIONSHIPS =====

    /**
     * Get the section this choice belongs to
     */
    public function section()
    {
        return $this->belongsTo(Disc3DSection::class, 'section_id');
    }

    /**
     * Get responses where this choice was selected as MOST
     */
    public function mostResponses()
    {
        return $this->hasMany(Disc3DResponse::class, 'most_choice_id');
    }

    /**
     * Get responses where this choice was selected as LEAST
     */
    public function leastResponses()
    {
        return $this->hasMany(Disc3DResponse::class, 'least_choice_id');
    }

    // ===== SCOPES =====

    /**
     * Scope for active choices
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by dimension
     */
    public function scopeByDimension($query, $dimension)
    {
        return $query->where('choice_dimension', $dimension);
    }

    /**
     * Scope by section number
     */
    public function scopeBySectionNumber($query, $sectionNumber)
    {
        return $query->where('section_number', $sectionNumber);
    }

    /**
     * Scope by section
     */
    public function scopeBySection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    // ===== ACCESSORS =====

    /**
     * Get dimension weights as array
     */
    public function getDimensionWeightsAttribute(): array
    {
        return [
            'D' => $this->weight_d ?? 0,
            'I' => $this->weight_i ?? 0,
            'S' => $this->weight_s ?? 0,
            'C' => $this->weight_c ?? 0
        ];
    }

    /**
     * Get localized choice text
     */
    public function getLocalizedTextAttribute(): string
    {
        return app()->getLocale() === 'id' 
            ? $this->choice_text
            : ($this->choice_text_en ?? $this->choice_text);
    }

    /**
     * Get localized keywords
     */
    public function getLocalizedKeywordsAttribute(): array
    {
        return app()->getLocale() === 'id' 
            ? ($this->keywords ?? [])
            : ($this->keywords_en ?? $this->keywords ?? []);
    }

    // ===== METHODS =====

    /**
     * Calculate weighted score for response type
     */
    public function calculateWeightedScore(string $responseType = 'most'): array
    {
        $multiplier = $responseType === 'most' ? 1 : -1;
        
        return [
            'D' => ($this->weight_d ?? 0) * $multiplier,
            'I' => ($this->weight_i ?? 0) * $multiplier,
            'S' => ($this->weight_s ?? 0) * $multiplier,
            'C' => ($this->weight_c ?? 0) * $multiplier
        ];
    }

    /**
     * Get dominance strength of this choice
     */
    public function getDominanceStrength(): float
    {
        $weights = $this->dimension_weights;
        return max(array_map('abs', array_values($weights)));
    }

    /**
     * Check if choice has valid weights
     */
    public function hasValidWeights(): bool
    {
        return !is_null($this->weight_d) && 
               !is_null($this->weight_i) && 
               !is_null($this->weight_s) && 
               !is_null($this->weight_c);
    }

    /**
     * Get primary dimension based on highest weight
     */
    public function getPrimaryDimension(): string
    {
        $weights = $this->dimension_weights;
        $maxWeight = max($weights);
        
        foreach ($weights as $dimension => $weight) {
            if ($weight == $maxWeight) {
                return $dimension;
            }
        }
        
        return $this->choice_dimension; // fallback
    }

    /**
     * Get choice summary for logging
     */
    public function getSummary(): array
    {
        return [
            'choice_id' => $this->id,
            'choice_code' => $this->choice_code,
            'choice_dimension' => $this->choice_dimension,
            'section_id' => $this->section_id,
            'section_number' => $this->section_number,
            'choice_text' => substr($this->choice_text, 0, 50) . '...',
            'weights' => $this->dimension_weights,
            'primary_dimension' => $this->getPrimaryDimension(),
            'dominance_strength' => $this->getDominanceStrength(),
            'has_valid_weights' => $this->hasValidWeights(),
            'is_active' => $this->is_active
        ];
    }

    // ===== BOOT METHODS =====

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($choice) {
            // Auto-generate choice_code if not provided
            if (empty($choice->choice_code) && $choice->section_code && $choice->choice_dimension) {
                $choice->choice_code = $choice->section_code . '_' . $choice->choice_dimension;
            }
            
            // Auto-set primary_dimension based on highest weight
            if (empty($choice->primary_dimension) && $choice->hasValidWeights()) {
                $choice->primary_dimension = $choice->getPrimaryDimension();
            }
        });
        
        static::created(function ($choice) {
            \Log::info('DISC choice created', [
                'choice_id' => $choice->id,
                'choice_code' => $choice->choice_code,
                'dimension' => $choice->choice_dimension,
                'section_id' => $choice->section_id
            ]);
        });
    }

    // ===== STATIC METHODS =====

    /**
     * Get choices by section and dimension
     */
    public static function getBySectionAndDimension(int $sectionId, string $dimension)
    {
        return static::where('section_id', $sectionId)
                    ->where('choice_dimension', $dimension)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Get all active choices for a section
     */
    public static function getActiveChoicesForSection(int $sectionId)
    {
        return static::where('section_id', $sectionId)
                    ->where('is_active', true)
                    ->orderBy('choice_dimension')
                    ->get();
    }

    /**
     * Validate choice data
     */
    public static function validateChoiceData(array $data): array
    {
        $errors = [];
        
        // Required fields
        $required = ['section_id', 'choice_dimension', 'choice_text'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field {$field} is required";
            }
        }
        
        // Valid dimension
        if (!empty($data['choice_dimension']) && !in_array($data['choice_dimension'], ['D', 'I', 'S', 'C'])) {
            $errors[] = "Invalid choice_dimension: {$data['choice_dimension']}";
        }
        
        // Weight validation
        $weightFields = ['weight_d', 'weight_i', 'weight_s', 'weight_c'];
        foreach ($weightFields as $field) {
            if (isset($data[$field]) && (!is_numeric($data[$field]) || $data[$field] < 0)) {
                $errors[] = "Invalid {$field}: must be a non-negative number";
            }
        }
        
        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }
}