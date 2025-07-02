<?php

// ==== 1. DISC 3D SECTION MODEL ====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


// ==== 9. DISC 3D TEST ANALYTICS MODEL ====
class Disc3DTestAnalytics extends Model
{
    use HasFactory;

    protected $table = 'disc_3d_test_analytics';

    protected $fillable = [
        'candidate_id',
        'test_session_id',
        'total_sections',
        'completed_sections',
        'completion_rate',
        'total_time_seconds',
        'average_time_per_section',
        'fastest_section_time',
        'slowest_section_time',
        'revisions_made',
        'section_timing',
        'response_patterns',
        'response_variance',
        'engagement_score',
        'device_analytics',
        'page_reloads',
        'focus_lost_count',
        'idle_time_seconds',
        'response_quality_score',
        'suspicious_patterns',
        'quality_flags'
    ];

    protected $casts = [
        'completion_rate' => 'decimal:2',
        'response_variance' => 'decimal:2',
        'engagement_score' => 'decimal:2',
        'response_quality_score' => 'decimal:2',
        'suspicious_patterns' => 'boolean',
        'section_timing' => 'array',
        'response_patterns' => 'array',
        'device_analytics' => 'array',
        'quality_flags' => 'array'
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

    // Accessors
    public function getFormattedTotalTimeAttribute()
    {
        if (!$this->total_time_seconds) return 'N/A';
        
        $minutes = floor($this->total_time_seconds / 60);
        $seconds = $this->total_time_seconds % 60;
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getEngagementLevelAttribute()
    {
        return match(true) {
            $this->engagement_score >= 80 => 'High',
            $this->engagement_score >= 60 => 'Moderate',
            default => 'Low'
        };
    }

    public function getQualityLevelAttribute()
    {
        return match(true) {
            $this->response_quality_score >= 80 => 'High',
            $this->response_quality_score >= 60 => 'Moderate',
            default => 'Low'
        };
    }

    // Methods
    public function calculateEngagementScore()
    {
        $factors = [
            'completion_rate' => $this->completion_rate * 0.4,
            'time_consistency' => max(0, 100 - ($this->response_variance ?? 0)) * 0.3,
            'focus_stability' => max(0, 100 - ($this->focus_lost_count * 10)) * 0.3
        ];

        $this->engagement_score = array_sum($factors);
        return $this->engagement_score;
    }

    public function flagSuspiciousPatterns()
    {
        $flags = [];

        // Too fast completion
        if ($this->average_time_per_section < 5) {
            $flags[] = 'too_fast_completion';
        }

        // Too many revisions
        if ($this->revisions_made > 50) {
            $flags[] = 'excessive_revisions';
        }

        // High variance in response time
        if (($this->response_variance ?? 0) > 80) {
            $flags[] = 'inconsistent_timing';
        }

        // Too many focus losses
        if ($this->focus_lost_count > 20) {
            $flags[] = 'poor_focus';
        }

        $this->quality_flags = $flags;
        $this->suspicious_patterns = !empty($flags);
        
        return $flags;
    }
}