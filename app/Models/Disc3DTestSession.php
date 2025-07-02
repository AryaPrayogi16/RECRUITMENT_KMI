<?php

// ==== 1. DISC 3D TEST SESSION MODEL - FIXED ====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disc3DTestSession extends Model
{
    use HasFactory;

    // ✅ FIXED: Explicitly define table name to match migration
    protected $table = 'disc_3d_test_sessions';

    protected $fillable = [
        'candidate_id',
        'test_code',
        'status',
        'started_at',
        'completed_at',
        'last_activity_at',
        'total_duration_seconds',
        'sections_completed',
        'progress',
        'language',
        'time_limit_minutes',
        'auto_save',
        'user_agent',
        'ip_address',
        'session_token',
        'metadata',
        'device_info'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'progress' => 'decimal:2',
        'auto_save' => 'boolean',
        'metadata' => 'array',
        'device_info' => 'array'
    ];

    // Constants
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_TIMEOUT = 'timeout';
    const STATUS_INTERRUPTED = 'interrupted';

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function responses()
    {
        return $this->hasMany(Disc3DResponse::class, 'test_session_id');
    }

    public function result()
    {
        return $this->hasOne(Disc3DResult::class, 'test_session_id');
    }

    // Rest of the methods remain the same
    public function analytics()
    {
        return $this->hasOne(Disc3DTestAnalytics::class, 'test_session_id');
    }

    public function sectionAnalytics()
    {
        return $this->hasMany(Disc3DSectionAnalytics::class, 'test_session_id');
    }

    // Scopes (enhanced from old model)
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_NOT_STARTED, self::STATUS_IN_PROGRESS]);
    }

    public function scopeByCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    // Accessors (enhanced from old model)
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_NOT_STARTED => 'Belum Dimulai',
            self::STATUS_IN_PROGRESS => 'Sedang Berlangsung',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_TIMEOUT => 'Timeout',
            self::STATUS_INTERRUPTED => 'Terputus',
            default => 'Tidak Diketahui'
        };
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->total_duration_seconds) return 'N/A';
        
        $minutes = floor($this->total_duration_seconds / 60);
        $seconds = $this->total_duration_seconds % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getProgressPercentageAttribute()
    {
        return round(($this->sections_completed / 24) * 100, 2);
    }

    public function getCompletionStatusAttribute()
    {
        return [
            'total_sections' => 24,
            'completed_sections' => $this->sections_completed,
            'remaining_sections' => 24 - $this->sections_completed,
            'progress_percentage' => $this->progress_percentage,
            'is_completed' => $this->status === self::STATUS_COMPLETED
        ];
    }

    public function getTimeRemainingAttribute()
    {
        if (!$this->started_at || !$this->time_limit_minutes) {
            return null;
        }

        $elapsedMinutes = $this->started_at->diffInMinutes(now());
        $remainingMinutes = max(0, $this->time_limit_minutes - $elapsedMinutes);
        
        return [
            'total_minutes' => $this->time_limit_minutes,
            'elapsed_minutes' => $elapsedMinutes,
            'remaining_minutes' => $remainingMinutes,
            'is_expired' => $remainingMinutes <= 0
        ];
    }

    // Methods
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canContinue()
    {
        return in_array($this->status, [self::STATUS_NOT_STARTED, self::STATUS_IN_PROGRESS]);
    }

    public function getTotalSections()
    {
        return 24; // Fixed for DISC 3D
    }

    public function getCompletedSectionsCount()
    {
        return $this->responses()->count();
    }

    public function updateProgress()
    {
        $completed = $this->getCompletedSectionsCount();
        $total = $this->getTotalSections();
        
        $this->sections_completed = $completed;
        $this->progress = round(($completed / $total) * 100, 2);
        $this->save();

        return $this->progress;
    }

    public function recordActivity()
    {
        $this->last_activity_at = now();
        $this->save();
    }
}