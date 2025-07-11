<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cache extends Model
{
    protected $table = 'cache';
    
    protected $fillable = [
        'key',
        'value',
        'expiration'
    ];

    public $timestamps = false;
    
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'expiration' => 'integer'
    ];

    // Check if cache is expired
    public function isExpired()
    {
        return $this->expiration < time();
    }

    // Get value if not expired
    public function getValue()
    {
        if ($this->isExpired()) {
            return null;
        }
        
        return $this->value;
    }
}
