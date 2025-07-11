<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
class DrivingLicense extends Model
{
    use HasFactory, SoftDeletes; 

    protected $fillable = [
        'candidate_id',
        'license_type',
    
    ];

    protected $casts = [
        'expiry_date' => 'date'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getFormattedExpiryDateAttribute()
    {
        return $this->expiry_date ? $this->expiry_date->format('d M Y') : null;
    }
}