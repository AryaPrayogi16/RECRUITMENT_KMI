<?php

// ==== 1. DISC 3D SECTION MODEL ====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


// ==== 6. DISC 3D CONFIGURATION MODEL ====
class Disc3DConfig extends Model
{
    use HasFactory;

   protected $table = 'disc_3d_config';

    protected $fillable = [
        'config_key',
        'config_value',
        'description'
    ];

    protected $casts = [
        'config_value' => 'array'
    ];

    // Static methods (inspired by old DiscTestConfig)
    public static function getSegmentThresholds()
    {
        $config = self::where('config_key', 'segment_conversion')->first();
        return $config ? $config->config_value['segments'] ?? [] : [];
    }

    public static function getValidityChecks()
    {
        $config = self::where('config_key', 'validity_checks')->first();
        return $config ? $config->config_value : [];
    }

    public static function getTestSettings()
    {
        $config = self::where('config_key', 'test_settings')->first();
        return $config ? $config->config_value : [];
    }

    public static function getScoringMethod()
    {
        $config = self::where('config_key', 'scoring_method')->first();
        return $config ? $config->config_value : [];
    }

    public static function getGraphLabels()
    {
        $config = self::where('config_key', 'graph_labels')->first();
        return $config ? $config->config_value : [];
    }

    // Scopes
    public function scopeByKey($query, $key)
    {
        return $query->where('config_key', $key);
    }

    // Methods
    public function getValue($default = null)
    {
        return $this->config_value ?? $default;
    }

    public static function setValue($key, $value, $description = null)
    {
        return self::updateOrCreate(
            ['config_key' => $key],
            [
                'config_value' => $value,
                'description' => $description
            ]
        );
    }
}
