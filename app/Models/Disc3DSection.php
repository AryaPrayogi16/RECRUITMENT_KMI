<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disc3DSection extends Model
{
    use HasFactory, SoftDeletes;

    // ✅ CRITICAL: Explicitly define table name
    protected $table = 'disc_3d_sections';

    protected $fillable = [
        'section_number',
        'section_code',
        'section_title',
        'is_active',
        'order_number'
    ];

    protected $casts = [
        'section_number' => 'integer',
        'order_number' => 'integer',
        'is_active' => 'boolean'
    ];

    // ===== RELATIONSHIPS =====

    /**
     * ✅ FIXED: Get all choices for this section
     */
    public function choices()
    {
        return $this->hasMany(Disc3DSectionChoice::class, 'section_id');
    }

    /**
     * Get active choices only
     */
    public function activeChoices()
    {
        return $this->hasMany(Disc3DSectionChoice::class, 'section_id')
                    ->where('is_active', true)
                    ->orderBy('choice_dimension');
    }

    /**
     * Get responses for this section
     */
    public function responses()
    {
        return $this->hasMany(Disc3DResponse::class, 'section_id');
    }

    // ===== SCOPES =====

    /**
     * Scope for active sections
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by section number
     */
    public function scopeOrderedByNumber($query)
    {
        return $query->orderBy('section_number');
    }

    /**
     * Scope for ordering by order_number
     */
    public function scopeOrderedBySequence($query)
    {
        return $query->orderBy('order_number');
    }

    /**
     * Scope by section number
     */
    public function scopeBySectionNumber($query, $sectionNumber)
    {
        return $query->where('section_number', $sectionNumber);
    }

    // ===== HELPER METHODS =====

    /**
     * Check if section has all required choices (D, I, S, C)
     */
    public function hasCompleteChoices(): bool
    {
        $choices = $this->activeChoices;
        $dimensions = $choices->pluck('choice_dimension')->unique()->sort()->values()->toArray();
        
        return $dimensions === ['C', 'D', 'I', 'S'];
    }

    /**
     * Get choices grouped by dimension
     */
    public function getChoicesByDimension(): array
    {
        $choices = $this->activeChoices;
        
        return [
            'D' => $choices->where('choice_dimension', 'D')->first(),
            'I' => $choices->where('choice_dimension', 'I')->first(),
            'S' => $choices->where('choice_dimension', 'S')->first(),
            'C' => $choices->where('choice_dimension', 'C')->first()
        ];
    }

    /**
     * Get randomized choices (for test presentation)
     */
    public function getRandomizedChoices()
    {
        return $this->activeChoices->shuffle();
    }

    /**
     * Validate section for test usage
     */
    public function isValidForTest(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->hasCompleteChoices()) {
            return false;
        }

        // Check if all choices have valid weights
        $choices = $this->activeChoices;
        foreach ($choices as $choice) {
            if ($choice->weight_d === null || $choice->weight_i === null || 
                $choice->weight_s === null || $choice->weight_c === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get section summary for logging
     */
    public function getSummary(): array
    {
        return [
            'section_id' => $this->id,
            'section_number' => $this->section_number,
            'section_code' => $this->section_code,
            'section_title' => $this->section_title,
            'is_active' => $this->is_active,
            'order_number' => $this->order_number,
            'choices_count' => $this->choices()->count(),
            'active_choices_count' => $this->activeChoices()->count(),
            'has_complete_choices' => $this->hasCompleteChoices(),
            'is_valid_for_test' => $this->isValidForTest()
        ];
    }

    // ===== ACCESSORS =====

    /**
     * Get formatted section title
     */
    public function getFormattedTitleAttribute(): string
    {
        return $this->section_title ?: "Section {$this->section_number}";
    }

    /**
     * Get section display name
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->section_code} - {$this->formatted_title}";
    }

    // ===== BOOT METHODS =====

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($section) {
            // Auto-generate section_code if not provided
            if (empty($section->section_code) && $section->section_number) {
                $section->section_code = 'SEC' . str_pad($section->section_number, 2, '0', STR_PAD_LEFT);
            }
            
            // Auto-set order_number if not provided
            if (empty($section->order_number)) {
                $section->order_number = $section->section_number;
            }
        });
        
        static::created(function ($section) {
            \Log::info('DISC section created', [
                'section_id' => $section->id,
                'section_number' => $section->section_number,
                'section_code' => $section->section_code
            ]);
        });
        
        static::deleting(function ($section) {
            // Soft delete related choices
            $section->choices()->delete();
            
            \Log::info('DISC section deleted', [
                'section_id' => $section->id,
                'section_number' => $section->section_number
            ]);
        });
    }

    // ===== STATIC METHODS =====

    /**
     * Get all active sections ordered for test
     */
    public static function getTestSections()
    {
        return static::active()
                    ->with(['choices' => function($query) {
                        $query->where('is_active', true)
                              ->orderBy('choice_dimension');
                    }])
                    ->orderBy('order_number')
                    ->get();
    }

    /**
     * Validate all sections for test readiness
     */
    public static function validateAllSectionsForTest(): array
    {
        $sections = static::getTestSections();
        $errors = [];
        
        if ($sections->count() < 24) {
            $errors[] = "Only {$sections->count()} sections found, need 24 for complete test";
        }
        
        foreach ($sections as $section) {
            if (!$section->isValidForTest()) {
                $errors[] = "Section {$section->section_number} ({$section->section_code}) is not valid for test";
            }
        }
        
        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'sections_count' => $sections->count(),
            'valid_sections_count' => $sections->filter(fn($s) => $s->isValidForTest())->count()
        ];
    }

    /**
     * Get section by number
     */
    public static function getBySectionNumber(int $sectionNumber)
    {
        return static::where('section_number', $sectionNumber)
                    ->with(['choices' => function($query) {
                        $query->where('is_active', true)
                              ->orderBy('choice_dimension');
                    }])
                    ->first();
    }
}