<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_id',
        'question_id',
        'item_code',
        'response',
        'weighted_score_d',
        'weighted_score_i',
        'weighted_score_s',
        'weighted_score_c',
        'time_spent_seconds'
    ];

    protected $casts = [
        'response' => 'integer',
        'time_spent_seconds' => 'integer',
        'weighted_score_d' => 'decimal:4',
        'weighted_score_i' => 'decimal:4',
        'weighted_score_s' => 'decimal:4',
        'weighted_score_c' => 'decimal:4'
    ];

    // Default attributes to prevent null values
    protected $attributes = [
        'weighted_score_d' => 0,
        'weighted_score_i' => 0,
        'weighted_score_s' => 0,
        'weighted_score_c' => 0,
        'time_spent_seconds' => 0
    ];

    // Relationships
    public function testSession()
    {
        return $this->belongsTo(DiscTestSession::class, 'test_session_id');
    }

    public function question()
    {
        return $this->belongsTo(DiscQuestion::class, 'question_id');
    }

    // SINGLE weighted scores accessor - FIXED version
    public function getAllWeightedScoresAttribute()
    {
        return [
            'D' => (float) ($this->attributes['weighted_score_d'] ?? 0),
            'I' => (float) ($this->attributes['weighted_score_i'] ?? 0),
            'S' => (float) ($this->attributes['weighted_score_s'] ?? 0),
            'C' => (float) ($this->attributes['weighted_score_c'] ?? 0)
        ];
    }

    public function getResponseLabelAttribute()
    {
        return match($this->response) {
            1 => 'Sangat Tidak Setuju',
            2 => 'Tidak Setuju', 
            3 => 'Netral',
            4 => 'Setuju',
            5 => 'Sangat Setuju',
            default => 'Unknown'
        };
    }

    // Mutators to ensure proper value setting
    public function setWeightedScoreDAttribute($value)
    {
        $this->attributes['weighted_score_d'] = (float) ($value ?? 0);
    }

    public function setWeightedScoreIAttribute($value)
    {
        $this->attributes['weighted_score_i'] = (float) ($value ?? 0);
    }

    public function setWeightedScoreSAttribute($value)
    {
        $this->attributes['weighted_score_s'] = (float) ($value ?? 0);
    }

    public function setWeightedScoreCAttribute($value)
    {
        $this->attributes['weighted_score_c'] = (float) ($value ?? 0);
    }

    // Helper method for debugging (optional)
    public function getScoresSummary()
    {
        return [
            'total_weighted_d' => $this->attributes['weighted_score_d'] ?? 0,
            'total_weighted_i' => $this->attributes['weighted_score_i'] ?? 0,
            'total_weighted_s' => $this->attributes['weighted_score_s'] ?? 0,
            'total_weighted_c' => $this->attributes['weighted_score_c'] ?? 0,
            'response_value' => $this->response,
            'time_spent' => $this->time_spent_seconds
        ];
    }
}