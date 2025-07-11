<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CacheLock extends Model
{
    protected $table = 'cache_locks';
    
    protected $fillable = [
        'key',
        'owner',
        'expiration'
    ];

    public $timestamps = false;
    
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'expiration' => 'integer'
    ];

    // Check if lock is expired
    public function isExpired()
    {
        return $this->expiration < time();
    }

    // Check if owned by specific owner
    public function isOwnedBy($owner)
    {
        return $this->owner === $owner;
    }
}