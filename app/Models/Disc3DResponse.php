<?php

// ==== DISC 3D RESPONSE MODEL - DEBUG VERSION ====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Disc3DResponse extends Model
{
    use HasFactory;

    protected $table = 'disc_3d_responses';

    protected $fillable = [
        'test_session_id',
        'candidate_id',
        'section_id',
        'section_code',
        'section_number',
        'most_choice_id',
        'least_choice_id',
        'most_choice',
        'least_choice',
        'most_score_d', 'most_score_i', 'most_score_s', 'most_score_c',
        'least_score_d', 'least_score_i', 'least_score_s', 'least_score_c',
        'net_score_d', 'net_score_i', 'net_score_s', 'net_score_c',
        'time_spent_seconds',
        'response_order',
        'answered_at',
        'revision_count'
    ];

    protected $casts = [
        'most_score_d' => 'decimal:4',
        'most_score_i' => 'decimal:4',
        'most_score_s' => 'decimal:4',
        'most_score_c' => 'decimal:4',
        'least_score_d' => 'decimal:4',
        'least_score_i' => 'decimal:4',
        'least_score_s' => 'decimal:4',
        'least_score_c' => 'decimal:4',
        'net_score_d' => 'decimal:4',
        'net_score_i' => 'decimal:4',
        'net_score_s' => 'decimal:4',
        'net_score_c' => 'decimal:4',
        'answered_at' => 'datetime'
    ];

    // ✅ DEBUG: Override __get to provide detailed logging
    public function __get($key)
    {
        // Log access attempts to score fields
        if (str_contains($key, '_score_')) {
            Log::info("Accessing score field: {$key}", [
                'model_id' => $this->id ?? 'unknown',
                'attributes' => array_keys($this->attributes),
                'has_attribute' => array_key_exists($key, $this->attributes),
                'attribute_value' => $this->attributes[$key] ?? 'NOT_SET'
            ]);
        }

        // Check if attribute exists
        if (array_key_exists($key, $this->attributes)) {
            return parent::__get($key);
        }

        // Provide fallback for score fields
        if (preg_match('/^(most|least|net)_score_[disc]$/', $key)) {
            Log::warning("Score field not found, providing fallback", [
                'field' => $key,
                'model_id' => $this->id ?? 'unknown'
            ]);
            return 0.0;
        }

        return parent::__get($key);
    }

    // ✅ DEBUG: Override getAttribute for more control
    public function getAttribute($key)
    {
        // Special handling for score attributes
        if (preg_match('/^(most|least|net)_score_[disc]$/', $key)) {
            $value = parent::getAttribute($key);
            if (is_null($value)) {
                Log::warning("Score attribute is null, returning 0", [
                    'attribute' => $key,
                    'model_id' => $this->id ?? 'unknown'
                ]);
                return 0.0;
            }
            return $value;
        }

        return parent::getAttribute($key);
    }

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function testSession()
    {
        return $this->belongsTo(Disc3DTestSession::class, 'test_session_id');
    }

    public function section()
    {
        return $this->belongsTo(Disc3DSection::class, 'section_id');
    }

    public function mostChoice()
    {
        return $this->belongsTo(Disc3DSectionChoice::class, 'most_choice_id');
    }

    public function leastChoice()
    {
        return $this->belongsTo(Disc3DSectionChoice::class, 'least_choice_id');
    }

    // ✅ SAFE: Accessors with null checks
    public function getMostScoresAttribute()
    {
        return [
            'D' => $this->getAttribute('most_score_d') ?? 0,
            'I' => $this->getAttribute('most_score_i') ?? 0,
            'S' => $this->getAttribute('most_score_s') ?? 0,
            'C' => $this->getAttribute('most_score_c') ?? 0
        ];
    }

    public function getLeastScoresAttribute()
    {
        return [
            'D' => $this->getAttribute('least_score_d') ?? 0,
            'I' => $this->getAttribute('least_score_i') ?? 0,
            'S' => $this->getAttribute('least_score_s') ?? 0,
            'C' => $this->getAttribute('least_score_c') ?? 0
        ];
    }

    public function getNetScoresAttribute()
    {
        return [
            'D' => $this->getAttribute('net_score_d') ?? 0,
            'I' => $this->getAttribute('net_score_i') ?? 0,
            'S' => $this->getAttribute('net_score_s') ?? 0,
            'C' => $this->getAttribute('net_score_c') ?? 0
        ];
    }

    public function getFormattedTimeAttribute()
    {
        if (!$this->time_spent_seconds) return 'N/A';
        
        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getChoiceLabelsAttribute()
    {
        return [
            'most' => $this->most_choice,
            'least' => $this->least_choice,
            'most_text' => $this->mostChoice?->localized_text,
            'least_text' => $this->leastChoice?->localized_text
        ];
    }

    // ✅ SAFE: Methods with error handling
    public function calculateScores()
    {
        try {
            $mostWeights = $this->mostChoice?->dimension_weights ?? ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];
            $leastWeights = $this->leastChoice?->dimension_weights ?? ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];

            // Most scores (positive contribution)
            $this->most_score_d = $mostWeights['D'] ?? 0;
            $this->most_score_i = $mostWeights['I'] ?? 0;
            $this->most_score_s = $mostWeights['S'] ?? 0;
            $this->most_score_c = $mostWeights['C'] ?? 0;

            // Least scores (negative contribution)
            $this->least_score_d = -($leastWeights['D'] ?? 0);
            $this->least_score_i = -($leastWeights['I'] ?? 0);
            $this->least_score_s = -($leastWeights['S'] ?? 0);
            $this->least_score_c = -($leastWeights['C'] ?? 0);

            // Net scores
            $this->net_score_d = $this->most_score_d + $this->least_score_d;
            $this->net_score_i = $this->most_score_i + $this->least_score_i;
            $this->net_score_s = $this->most_score_s + $this->least_score_s;
            $this->net_score_c = $this->most_score_c + $this->least_score_c;

            return $this;
            
        } catch (\Exception $e) {
            Log::error('Error calculating scores', [
                'model_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            // Set default scores on error
            $this->most_score_d = $this->most_score_i = $this->most_score_s = $this->most_score_c = 0;
            $this->least_score_d = $this->least_score_i = $this->least_score_s = $this->least_score_c = 0;
            $this->net_score_d = $this->net_score_i = $this->net_score_s = $this->net_score_c = 0;
            
            return $this;
        }
    }

    public function recordRevision()
    {
        $this->revision_count = ($this->revision_count ?? 0) + 1;
        $this->save();
    }

    public function isValidResponse()
    {
        return $this->most_choice !== $this->least_choice 
            && !is_null($this->most_choice_id) 
            && !is_null($this->least_choice_id);
    }

    // ✅ DEBUG: Method to check model state
    public function debugModelState(): array
    {
        return [
            'id' => $this->id,
            'attributes' => $this->attributes,
            'score_fields' => [
                'most_score_d' => $this->getAttribute('most_score_d'),
                'most_score_i' => $this->getAttribute('most_score_i'),
                'most_score_s' => $this->getAttribute('most_score_s'),
                'most_score_c' => $this->getAttribute('most_score_c'),
                'least_score_d' => $this->getAttribute('least_score_d'),
                'least_score_i' => $this->getAttribute('least_score_i'),
                'least_score_s' => $this->getAttribute('least_score_s'),
                'least_score_c' => $this->getAttribute('least_score_c'),
            ],
            'has_scores' => [
                'most_score_d' => isset($this->attributes['most_score_d']),
                'most_score_i' => isset($this->attributes['most_score_i']),
                'most_score_s' => isset($this->attributes['most_score_s']),
                'most_score_c' => isset($this->attributes['most_score_c']),
            ]
        ];
    }
}