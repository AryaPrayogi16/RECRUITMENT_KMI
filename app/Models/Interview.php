<?php

namespace App\Models;
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'interview_date',
        'interview_time',
        'location',
        'interviewer_id',
        'status',
        'notes'
    ];

    protected $casts = [
        'interview_date' => 'date',
        'interview_time' => 'datetime:H:i'
    ];

    // Constants sesuai database enum
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
    
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeByInterviewer($query, $interviewerId)
    {
        return $query->where('interviewer_id', $interviewerId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('interview_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('interview_date', '>=', today())
                     ->where('status', self::STATUS_SCHEDULED);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'scheduled' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800'
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFormattedDateTimeAttribute()
    {
        return $this->interview_date->format('d F Y') . ' - ' . $this->interview_time->format('H:i');
    }

    // Static methods
    public static function getStatusOptions()
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled'
        ];
    }
}