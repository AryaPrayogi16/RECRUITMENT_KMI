<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    use SoftDeletes;
    
    protected $table = 'sessions';
    
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity'
    ];

    public $timestamps = false;
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'user_id' => 'integer',
        'last_activity' => 'integer'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query, $minutes = 30)
    {
        $cutoff = now()->subMinutes($minutes)->timestamp;
        return $query->where('last_activity', '>=', $cutoff);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByIpAddress($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    // Accessors
    public function getLastActivityDateAttribute()
    {
        return \Carbon\Carbon::createFromTimestamp($this->last_activity);
    }

    public function getIsActiveAttribute()
    {
        $cutoff = now()->subMinutes(30)->timestamp;
        return $this->last_activity >= $cutoff;
    }

    public function getFormattedLastActivityAttribute()
    {
        return $this->last_activity_date->diffForHumans();
    }

    public function getSessionDataAttribute()
    {
        return unserialize(base64_decode($this->payload));
    }

    // Methods
    public function isExpired($minutes = 30)
    {
        $cutoff = now()->subMinutes($minutes)->timestamp;
        return $this->last_activity < $cutoff;
    }

    public function touch()
    {
        $this->update(['last_activity' => time()]);
    }
}
