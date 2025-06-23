<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KraeplinTestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'test_code',
        'status',
        'started_at',
        'completed_at',
        'total_duration_seconds'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Status constants
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function answers()
    {
        return $this->hasMany(KraeplinAnswer::class, 'test_session_id');
    }

    public function testResult()
    {
        return $this->hasOne(KraeplinTestResult::class, 'test_session_id');
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

    public function scopeNotStarted($query)
    {
        return $query->where('status', self::STATUS_NOT_STARTED);
    }

    // Accessors
    public function getDurationInMinutesAttribute()
    {
        return $this->total_duration_seconds ? round($this->total_duration_seconds / 60, 2) : null;
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

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_NOT_STARTED => 'Belum Dimulai',
            self::STATUS_IN_PROGRESS => 'Sedang Berlangsung',
            self::STATUS_COMPLETED => 'Selesai'
        ];

        return $labels[$this->status] ?? 'Tidak Diketahui';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_NOT_STARTED => 'status-pending',
            self::STATUS_IN_PROGRESS => 'status-submitted',
            self::STATUS_COMPLETED => 'status-accepted',
            default => 'status-pending'
        };
    }

    // Helper methods
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isNotStarted()
    {
        return $this->status === self::STATUS_NOT_STARTED;
    }

    public function getProgress()
    {
        if ($this->isCompleted()) {
            return 100;
        }

        if ($this->isNotStarted()) {
            return 0;
        }

        // For in progress, we can calculate based on answers
        $totalAnswers = $this->answers()->count();
        $maxPossibleAnswers = 32 * 26; // 32 columns * 26 questions per column
        
        return round(($totalAnswers / $maxPossibleAnswers) * 100, 2);
    }

    public function getTotalAnswersCount()
    {
        return $this->answers()->count();
    }

    public function getCorrectAnswersCount()
    {
        return $this->answers()->where('is_correct', true)->count();
    }

    public function getAccuracyPercentage()
    {
        $total = $this->getTotalAnswersCount();
        if ($total === 0) {
            return 0;
        }

        $correct = $this->getCorrectAnswersCount();
        return round(($correct / $total) * 100, 2);
    }
}