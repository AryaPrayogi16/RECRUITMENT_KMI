<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Migration extends Model
{
    protected $table = 'migrations';
    
    protected $fillable = [
        'migration',
        'batch'
    ];

    public $timestamps = false;

    protected $casts = [
        'batch' => 'integer'
    ];

    // Scopes
    public function scopeByBatch($query, $batch)
    {
        return $query->where('batch', $batch);
    }

    public function scopeLatestBatch($query)
    {
        $latestBatch = self::max('batch');
        return $query->where('batch', $latestBatch);
    }

    // Static methods
    public static function getNextBatchNumber()
    {
        return (self::max('batch') ?? 0) + 1;
    }

    public static function getLatestBatch()
    {
        return self::max('batch') ?? 0;
    }
}
