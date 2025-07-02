<?php

// ==== 1. DISC 3D SECTION MODEL ====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


// ==== 10. DISC 3D SECTION ANALYTICS MODEL ====
class Disc3DSectionAnalytics extends Model
{
    use HasFactory;

  // ✅ FIXED: Explicitly define table name
    protected $table = 'disc_3d_test_analytics';

    protected $fillable = [
        'candidate_id',
        'test_session_id',
        'section_id',
        'section_number',
        'time_to_first_response',
        'time_to_completion',
        'hesitation_time',
        'most_choice_changes',
        'least_choice_changes',
        'choice_sequence',
        'mouse_movements',
        'rushed_response',
        'delayed_response',
        'confidence_score'
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'rushed_response' => 'boolean',
        'delayed_response' => 'boolean',
        'choice_sequence' => 'array'
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

    public function section()
    {
        return $this->belongsTo(Disc3DSection::class, 'section_id');
    }

    // Accessors
    public function getResponseQualityAttribute()
    {
        return match(true) {
            $this->rushed_response => 'Poor',
            $this->delayed_response => 'Poor',
            $this->confidence_score >= 80 => 'High',
            $this->confidence_score >= 60 => 'Moderate',
            default => 'Low'
        };
    }

    public function getTotalChangesAttribute()
    {
        return $this->most_choice_changes + $this->least_choice_changes;
    }

    // Methods
    public function calculateConfidenceScore()
    {
        $factors = [];

        // Time factor (optimal range: 10-60 seconds)
        $timeScore = match(true) {
            $this->time_to_completion < 5 => 20,
            $this->time_to_completion > 120 => 40,
            default => 100
        };
        $factors['time'] = $timeScore * 0.4;

        // Changes factor (fewer changes = higher confidence)
        $changesScore = max(0, 100 - ($this->total_changes * 20));
        $factors['changes'] = $changesScore * 0.3;

        // Hesitation factor
        $hesitationScore = $this->hesitation_time > 30 ? 60 : 100;
        $factors['hesitation'] = $hesitationScore * 0.3;

        $this->confidence_score = array_sum($factors);
        return $this->confidence_score;
    }
}