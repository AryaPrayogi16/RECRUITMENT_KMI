<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'candidate_id',
        'title',
        'message',
        'type',
        'read_at',
        'data'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'json'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Methods
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    // Static methods
    public static function createForUser($userId, $title, $message, $type = 'info', $candidateId = null, $data = null)
    {
        return self::create([
            'user_id' => $userId,
            'candidate_id' => $candidateId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data
        ]);
    }
}
