<?php

// ==== 1. DISC 3D SECTION MODEL ====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


// ==== 5. DISC 3D RESULT MODEL ====
class Disc3DResult extends Model
{
    use HasFactory;

    // ✅ FIXED: Explicitly define table name to match migration
    protected $table = 'disc_3d_results';

    protected $fillable = [
        'test_session_id',
        'candidate_id',
        'test_code',
        'test_completed_at',
        'test_duration_seconds',
        
        // MOST scores
        'most_d_raw', 'most_i_raw', 'most_s_raw', 'most_c_raw',
        'most_d_percentage', 'most_i_percentage', 'most_s_percentage', 'most_c_percentage',
        'most_d_segment', 'most_i_segment', 'most_s_segment', 'most_c_segment',
        
        // LEAST scores  
        'least_d_raw', 'least_i_raw', 'least_s_raw', 'least_c_raw',
        'least_d_percentage', 'least_i_percentage', 'least_s_percentage', 'least_c_percentage',
        'least_d_segment', 'least_i_segment', 'least_s_segment', 'least_c_segment',
        
        // CHANGE scores
        'change_d_raw', 'change_i_raw', 'change_s_raw', 'change_c_raw',
        'change_d_segment', 'change_i_segment', 'change_s_segment', 'change_c_segment',
        
        // Patterns
        'most_primary_type', 'most_secondary_type',
        'least_primary_type', 'least_secondary_type',
        'most_pattern', 'least_pattern', 'adaptation_pattern',
        
        // Simplified accessors
        'primary_type', 'secondary_type', 'personality_profile', 'primary_percentage', 'summary',
        
        // JSON data
        'graph_most_data', 'graph_least_data', 'graph_change_data',
        'most_score_breakdown', 'least_score_breakdown',
        
        // Interpretations
        'public_self_summary', 'private_self_summary', 'adaptation_summary', 'overall_profile',
        
        // Analysis
        'section_responses', 'stress_indicators', 'behavioral_insights', 'consistency_analysis',
        
        // Validity
        'consistency_score', 'is_valid', 'validity_flags',
        
        // Performance
        'response_consistency', 'average_response_time', 'timing_analysis'
    ];

    protected $casts = [
        'test_completed_at' => 'datetime',
        
        // Most scores casting
        'most_d_raw' => 'decimal:4', 'most_i_raw' => 'decimal:4', 'most_s_raw' => 'decimal:4', 'most_c_raw' => 'decimal:4',
        'most_d_percentage' => 'decimal:2', 'most_i_percentage' => 'decimal:2', 'most_s_percentage' => 'decimal:2', 'most_c_percentage' => 'decimal:2',
        
        // Least scores casting
        'least_d_raw' => 'decimal:4', 'least_i_raw' => 'decimal:4', 'least_s_raw' => 'decimal:4', 'least_c_raw' => 'decimal:4',
        'least_d_percentage' => 'decimal:2', 'least_i_percentage' => 'decimal:2', 'least_s_percentage' => 'decimal:2', 'least_c_percentage' => 'decimal:2',
        
        // Change scores casting
        'change_d_raw' => 'decimal:4', 'change_i_raw' => 'decimal:4', 'change_s_raw' => 'decimal:4', 'change_c_raw' => 'decimal:4',
        
        'primary_percentage' => 'decimal:2',
        'consistency_score' => 'decimal:2',
        'response_consistency' => 'decimal:2',
        'is_valid' => 'boolean',
        
        // JSON fields
        'graph_most_data' => 'array', 'graph_least_data' => 'array', 'graph_change_data' => 'array',
        'most_score_breakdown' => 'array', 'least_score_breakdown' => 'array',
        'section_responses' => 'array', 'stress_indicators' => 'array', 'behavioral_insights' => 'array',
        'consistency_analysis' => 'array', 'validity_flags' => 'array', 'timing_analysis' => 'array'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function testSession()
    {
        return $this->belongsTo(Disc3DTestSession::class, 'test_session_id');
    }

    // Accessors (enhanced from old DiscTestResult model)
    public function getMostScoresAttribute()
    {
        return [
            'raw' => [
                'D' => $this->most_d_raw, 'I' => $this->most_i_raw,
                'S' => $this->most_s_raw, 'C' => $this->most_c_raw
            ],
            'percentage' => [
                'D' => $this->most_d_percentage, 'I' => $this->most_i_percentage,
                'S' => $this->most_s_percentage, 'C' => $this->most_c_percentage
            ],
            'segment' => [
                'D' => $this->most_d_segment, 'I' => $this->most_i_segment,
                'S' => $this->most_s_segment, 'C' => $this->most_c_segment
            ]
        ];
    }

    public function getLeastScoresAttribute()
    {
        return [
            'raw' => [
                'D' => $this->least_d_raw, 'I' => $this->least_i_raw,
                'S' => $this->least_s_raw, 'C' => $this->least_c_raw
            ],
            'percentage' => [
                'D' => $this->least_d_percentage, 'I' => $this->least_i_percentage,
                'S' => $this->least_s_percentage, 'C' => $this->least_c_percentage
            ],
            'segment' => [
                'D' => $this->least_d_segment, 'I' => $this->least_i_segment,
                'S' => $this->least_s_segment, 'C' => $this->least_c_segment
            ]
        ];
    }

    public function getChangeScoresAttribute()
    {
        return [
            'raw' => [
                'D' => $this->change_d_raw, 'I' => $this->change_i_raw,
                'S' => $this->change_s_raw, 'C' => $this->change_c_raw
            ],
            'segment' => [
                'D' => $this->change_d_segment, 'I' => $this->change_i_segment,
                'S' => $this->change_s_segment, 'C' => $this->change_c_segment
            ]
        ];
    }

    public function getAllDimensionScoresAttribute()
    {
        return [
            'most' => $this->most_scores,
            'least' => $this->least_scores,
            'change' => $this->change_scores
        ];
    }

    // Enhanced from old model
    public function getPrimaryTypeLabelAttribute()
    {
        return match($this->primary_type) {
            'D' => 'Dominance (Dominan)',
            'I' => 'Influence (Pengaruh)',
            'S' => 'Steadiness (Kestabilan)',
            'C' => 'Conscientiousness (Ketelitian)',
            default => 'Unknown'
        };
    }

    public function getSecondaryTypeLabelAttribute()
    {
        return match($this->secondary_type) {
            'D' => 'Dominance (Dominan)',
            'I' => 'Influence (Pengaruh)',
            'S' => 'Steadiness (Kestabilan)',
            'C' => 'Conscientiousness (Ketelitian)',
            default => 'Unknown'
        };
    }

    public function getPersonalityTypeCodeAttribute()
    {
        return $this->primary_type . ($this->secondary_type ?? '');
    }

    public function getFormattedPercentagesAttribute()
    {
        return [
            'most' => [
                'D' => number_format($this->most_d_percentage, 1) . '%',
                'I' => number_format($this->most_i_percentage, 1) . '%',
                'S' => number_format($this->most_s_percentage, 1) . '%',
                'C' => number_format($this->most_c_percentage, 1) . '%'
            ],
            'least' => [
                'D' => number_format($this->least_d_percentage, 1) . '%',
                'I' => number_format($this->least_i_percentage, 1) . '%',
                'S' => number_format($this->least_s_percentage, 1) . '%',
                'C' => number_format($this->least_c_percentage, 1) . '%'
            ]
        ];
    }

    public function getDominantTraitsAttribute()
    {
        $traits = [];
        
        if ($this->most_d_percentage > 60) $traits[] = 'Dominan';
        if ($this->most_i_percentage > 60) $traits[] = 'Komunikatif';
        if ($this->most_s_percentage > 60) $traits[] = 'Stabil';
        if ($this->most_c_percentage > 60) $traits[] = 'Teliti';
        
        return $traits;
    }

    public function getRecommendedRolesAttribute()
    {
        $roles = [];
        
        if ($this->primary_type === 'D') {
            $roles = ['Leader', 'Manager', 'Decision Maker', 'Entrepreneur'];
        } elseif ($this->primary_type === 'I') {
            $roles = ['Sales', 'Marketing', 'Public Relations', 'Team Builder'];
        } elseif ($this->primary_type === 'S') {
            $roles = ['Support', 'Team Player', 'Customer Service', 'Coordinator'];
        } elseif ($this->primary_type === 'C') {
            $roles = ['Analyst', 'Quality Control', 'Researcher', 'Specialist'];
        }
        
        return $roles;
    }

    public function getBriefSummaryAttribute()
    {
        if ($this->summary) {
            return $this->summary;
        }

        $primary = $this->primary_type_label;
        $percentage = $this->primary_percentage;
        
        return "{$primary} ({$percentage}%) - {$this->personality_profile}";
    }

    public function getStressLevelAttribute()
    {
        $stressIndicators = array_filter([
            abs($this->change_d_segment ?? 0) > 2,
            abs($this->change_i_segment ?? 0) > 2,
            abs($this->change_s_segment ?? 0) > 2,
            abs($this->change_c_segment ?? 0) > 2
        ]);

        $stressCount = count($stressIndicators);

        return match(true) {
            $stressCount === 0 => 'Low',
            $stressCount <= 2 => 'Moderate',
            default => 'High'
        };
    }

    // Methods
    public function generateSummary()
    {
        $primary = $this->primary_type_label;
        $secondary = $this->secondary_type ? " dengan kecenderungan {$this->secondary_type_label}" : '';
        
        return "Profil kepribadian {$primary}{$secondary}. " .
               "Kekuatan utama pada {$this->primary_percentage}% dengan tingkat stress {$this->stress_level}.";
    }

    public function getGraphData($graphType = 'most')
    {
        return match($graphType) {
            'most' => $this->graph_most_data,
            'least' => $this->graph_least_data,
            'change' => $this->graph_change_data,
            default => null
        };
    }

    public function hasHighAdaptation()
    {
        $changeValues = array_map('abs', [
            $this->change_d_segment ?? 0,
            $this->change_i_segment ?? 0,
            $this->change_s_segment ?? 0,
            $this->change_c_segment ?? 0
        ]);

        return max($changeValues) >= 3;
    }
}