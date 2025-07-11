<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrivingLicense extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'driving_licenses';

    protected $fillable = [
        'candidate_id',
        'license_type',
    ];

    // Set defaults sesuai database
    protected $attributes = [
        'license_type' => null,
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('license_type', $type);
    }

    public function scopeForCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    // Accessors
    public function getLicenseTypeNameAttribute()
    {
        $types = [
            'A' => 'SIM A (Motor)',
            'B1' => 'SIM B1 (Mobil Pribadi)',
            'B2' => 'SIM B2 (Mobil Angkutan)',
            'C' => 'SIM C (Truk/Bus)'
        ];

        return $types[$this->license_type] ?? $this->license_type;
    }

    // Static methods
    public static function getAvailableTypes()
    {
        return ['A', 'B1', 'B2', 'C'];
    }

    public static function getTypesWithLabels()
    {
        return [
            'A' => 'SIM A (Motor)',
            'B1' => 'SIM B1 (Mobil Pribadi)',
            'B2' => 'SIM B2 (Mobil Angkutan)',
            'C' => 'SIM C (Truk/Bus)'
        ];
    }
}