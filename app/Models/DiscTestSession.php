<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscTestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'test_code',
        'test_type',
        'status',
        'started_at',
        'completed_at',
        'total_duration_seconds',
        'language'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    const TYPE_CORE_16 = 'core_16';
    const TYPE_FULL_50 = 'full_50';

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function answers()
    {
        return $this->hasMany(DiscAnswer::class, 'test_session_id');
    }

    public function result()
    {
        return $this->hasOne(DiscTestResult::class, 'test_session_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_NOT_STARTED => 'Belum Dimulai',
            self::STATUS_IN_PROGRESS => 'Sedang Berlangsung',
            self::STATUS_COMPLETED => 'Selesai',
            default => 'Tidak Diketahui'
        };
    }

    public function getTestTypeLabelAttribute()
    {
        return match($this->test_type) {
            self::TYPE_CORE_16 => 'DISC Test Singkat (16 Soal)',
            self::TYPE_FULL_50 => 'DISC Test Lengkap (50+ Soal)',
            default => 'Unknown Test Type'
        };
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->total_duration_seconds) {
            return 'N/A';
        }

        $minutes = floor($this->total_duration_seconds / 60);
        $seconds = $this->total_duration_seconds % 60;
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getProgressAttribute()
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return 100;
        }

        if ($this->status === self::STATUS_NOT_STARTED) {
            return 0;
        }

        // For in-progress, calculate based on answers
        $totalQuestions = $this->getTotalQuestions();
        $answeredQuestions = $this->answers()->count();
        
        return $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0;
    }

    public function getTotalQuestions()
    {
        return DiscQuestion::active()
            ->when($this->test_type === self::TYPE_CORE_16, function($query) {
                return $query->core16();
            })
            ->count();
    }
}
