<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Disc3DTestSession extends Model
{
    use HasFactory;

    protected $table = 'disc_3d_test_sessions';

    /**
     * ✅ UPDATED: Simplified fillable fields according to new migration
     */
    protected $fillable = [
        'candidate_id',
        'test_code',
        'status',
        'started_at',
        'completed_at',
        'total_duration_seconds',
        'created_at',
        'updated_at'
    ];

    /**
     * ✅ UPDATED: Simplified casts according to new migration
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_duration_seconds' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * ✅ UPDATED: Simplified status values
     */
    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    public const VALID_STATUSES = [
        self::STATUS_NOT_STARTED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get the candidate that owns this test session
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get all responses for this test session
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Disc3DResponse::class, 'test_session_id');
    }

    /**
     * Get the result for this test session
     */
    public function result(): HasOne
    {
        return $this->hasOne(Disc3DResult::class, 'test_session_id');
    }

    // ===== SCOPES =====

    /**
     * Scope for active (not completed) sessions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_NOT_STARTED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Scope for completed sessions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for in progress sessions
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope for sessions by candidate
     */
    public function scopeByCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    // ===== HELPER METHODS =====

    /**
     * ✅ UPDATED: Check if session is active (can be used for testing)
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_NOT_STARTED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * ✅ UPDATED: Check if session is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * ✅ UPDATED: Check if session is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * ✅ UPDATED: Check if session is not started
     */
    public function isNotStarted(): bool
    {
        return $this->status === self::STATUS_NOT_STARTED;
    }

    /**
     * ✅ UPDATED: Get progress percentage based on completed responses
     */
    public function getProgressPercentage(): float
    {
        $totalSections = 24;
        $completedResponses = $this->responses()->count();
        
        return ($completedResponses / $totalSections) * 100;
    }

    /**
     * ✅ UPDATED: Get remaining sections count
     */
    public function getRemainingsections(): int
    {
        $totalSections = 24;
        $completedResponses = $this->responses()->count();
        
        return max(0, $totalSections - $completedResponses);
    }

    /**
     * ✅ UPDATED: Get formatted duration
     */
    public function getFormattedDuration(): string
    {
        if (!$this->total_duration_seconds) {
            return 'N/A';
        }
        
        $minutes = floor($this->total_duration_seconds / 60);
        $seconds = $this->total_duration_seconds % 60;
        
        return sprintf('%d menit %d detik', $minutes, $seconds);
    }

    /**
     * ✅ UPDATED: Get test duration in minutes
     */
    public function getDurationInMinutes(): float
    {
        return $this->total_duration_seconds ? round($this->total_duration_seconds / 60, 1) : 0;
    }

    /**
     * ✅ UPDATED: Get status badge class for UI
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_NOT_STARTED => 'badge-secondary',
            self::STATUS_IN_PROGRESS => 'badge-warning',
            self::STATUS_COMPLETED => 'badge-success',
            default => 'badge-secondary'
        };
    }

    /**
     * ✅ UPDATED: Get localized status text
     */
    public function getStatusText(): string
    {
        return match($this->status) {
            self::STATUS_NOT_STARTED => 'Belum Dimulai',
            self::STATUS_IN_PROGRESS => 'Sedang Berlangsung',
            self::STATUS_COMPLETED => 'Selesai',
            default => 'Unknown'
        };
    }

    /**
     * ✅ UPDATED: Mark session as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * ✅ UPDATED: Mark session as completed
     */
    public function markAsCompleted(?int $totalDuration = null): void
    {
        $updateData = [
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'updated_at' => now()
        ];
        
        if ($totalDuration !== null) {
            $updateData['total_duration_seconds'] = $totalDuration;
        }
        
        $this->update($updateData);
    }

    /**
     * ✅ UPDATED: Check if session can be resumed
     */
    public function canBeResumed(): bool
    {
        return $this->isActive() && $this->responses()->count() < 24;
    }

    /**
     * ✅ UPDATED: Get next section number to answer
     */
    public function getNextSectionNumber(): int
    {
        $completedSections = $this->responses()
            ->pluck('section_number')
            ->toArray();
            
        for ($i = 1; $i <= 24; $i++) {
            if (!in_array($i, $completedSections)) {
                return $i;
            }
        }
        
        return 25; // All sections completed
    }

    /**
     * ✅ UPDATED: Get completed section numbers
     */
    public function getCompletedSectionNumbers(): array
    {
        return $this->responses()
            ->pluck('section_number')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * ✅ UPDATED: Validate session can accept new responses
     */
    public function validateForNewResponse(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        if ($this->responses()->count() >= 24) {
            return false;
        }
        
        return true;
    }

    /**
     * ✅ UPDATED: Get session summary for logging
     */
    public function getSummary(): array
    {
        return [
            'session_id' => $this->id,
            'candidate_id' => $this->candidate_id,
            'test_code' => $this->test_code,
            'status' => $this->status,
            'started_at' => $this->started_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'total_duration_seconds' => $this->total_duration_seconds,
            'responses_count' => $this->responses()->count(),
            'progress_percentage' => $this->getProgressPercentage(),
            'remaining_sections' => $this->getRemainingSections(),
            'can_be_resumed' => $this->canBeResumed(),
            'next_section' => $this->getNextSectionNumber()
        ];
    }

    // ===== BOOT METHODS =====

    /**
     * ✅ UPDATED: Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($session) {
            // Ensure valid status
            if (!in_array($session->status, self::VALID_STATUSES)) {
                $session->status = self::STATUS_NOT_STARTED;
            }
        });
        
        static::updating(function ($session) {
            // Validate status transitions
            if ($session->isDirty('status')) {
                $oldStatus = $session->getOriginal('status');
                $newStatus = $session->status;
                
                // Log status changes
                \Log::info('DISC session status change', [
                    'session_id' => $session->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'candidate_id' => $session->candidate_id
                ]);
            }
        });
    }

    // ===== ACCESSORS & MUTATORS =====

    /**
     * ✅ UPDATED: Get formatted created date
     */
    public function getCreatedDateAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d M Y H:i') : 'N/A';
    }

    /**
     * ✅ UPDATED: Get formatted completed date
     */
    public function getCompletedDateAttribute(): string
    {
        return $this->completed_at ? $this->completed_at->format('d M Y H:i') : 'N/A';
    }

    /**
     * ✅ UPDATED: Get formatted started date
     */
    public function getStartedDateAttribute(): string
    {
        return $this->started_at ? $this->started_at->format('d M Y H:i') : 'N/A';
    }
}