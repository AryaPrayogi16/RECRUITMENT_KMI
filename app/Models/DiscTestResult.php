<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_id',
        'candidate_id',
        'd_raw_score',
        'i_raw_score',
        's_raw_score',
        'c_raw_score',
        'd_max_score',
        'i_max_score',
        's_max_score',
        'c_max_score',
        'd_percentage',
        'i_percentage',
        's_percentage',
        'c_percentage',
        'primary_type',
        'primary_percentage',
        'secondary_type',
        'secondary_percentage',
        'd_segment',
        'i_segment',
        's_segment',
        'c_segment',
        'graph_data',
        'profile_summary',
        'full_profile'
    ];

    protected $casts = [
        'd_raw_score' => 'decimal:3',
        'i_raw_score' => 'decimal:3',
        's_raw_score' => 'decimal:3',
        'c_raw_score' => 'decimal:3',
        'd_max_score' => 'decimal:3',
        'i_max_score' => 'decimal:3',
        's_max_score' => 'decimal:3',
        'c_max_score' => 'decimal:3',
        'd_percentage' => 'decimal:2',
        'i_percentage' => 'decimal:2',
        's_percentage' => 'decimal:2',
        'c_percentage' => 'decimal:2',
        'primary_percentage' => 'decimal:2',
        'secondary_percentage' => 'decimal:2',
        'graph_data' => 'array',
        'full_profile' => 'array'
    ];

    // Relationships
    public function testSession()
    {
        return $this->belongsTo(DiscTestSession::class, 'test_session_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Accessors
    public function getRawScoresAttribute()
    {
        return [
            'D' => $this->d_raw_score,
            'I' => $this->i_raw_score,
            'S' => $this->s_raw_score,
            'C' => $this->c_raw_score
        ];
    }

    public function getPercentagesAttribute()
    {
        return [
            'D' => $this->d_percentage,
            'I' => $this->i_percentage,
            'S' => $this->s_percentage,
            'C' => $this->c_percentage
        ];
    }

    public function getSegmentsAttribute()
    {
        return [
            'D' => $this->d_segment,
            'I' => $this->i_segment,
            'S' => $this->s_segment,
            'C' => $this->c_segment
        ];
    }

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

    public function getPersonalityProfileAttribute()
    {
        return "{$this->primary_type}{$this->secondary_type}";
    }

    public function getFormattedPercentagesAttribute()
    {
        return [
            'D' => number_format($this->d_percentage, 1) . '%',
            'I' => number_format($this->i_percentage, 1) . '%',
            'S' => number_format($this->s_percentage, 1) . '%',
            'C' => number_format($this->c_percentage, 1) . '%'
        ];
    }

    public function getDominantTraitsAttribute()
    {
        $traits = [];
        
        if ($this->d_percentage > 60) $traits[] = 'Dominan';
        if ($this->i_percentage > 60) $traits[] = 'Komunikatif';
        if ($this->s_percentage > 60) $traits[] = 'Stabil';
        if ($this->c_percentage > 60) $traits[] = 'Teliti';
        
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
}