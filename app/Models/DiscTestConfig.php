<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscTestConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'config_key',
        'config_value',
        'description'
    ];

    protected $casts = [
        'config_value' => 'array'
    ];

    // Static methods for common configurations
    public static function getSegmentThresholds()
    {
        $config = self::where('config_key', 'segment_thresholds')->first();
        return $config ? $config->config_value : [];
    }

    public static function getTestVersions()
    {
        $config = self::where('config_key', 'test_versions')->first();
        return $config ? $config->config_value : [];
    }

    // Scopes
    public function scopeByKey($query, $key)
    {
        return $query->where('config_key', $key);
    }
}