<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal_data';

    protected $fillable = [
        'candidate_id',
        'nik',                    // ✅ ADDED
        'full_name',
        'email',
        'phone_number',
        'phone_alternative',
        'birth_place',
        'birth_date',
        'gender',
        'religion',
        'marital_status',
        'ethnicity',
        'current_address',
        'current_address_status',
        'ktp_address',
        'height_cm',
        'weight_kg',
        'vaccination_status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'height_cm' => 'integer',
        'weight_kg' => 'integer',
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Accessors
    public function getAgeAttribute()
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    public function getFullAddressAttribute()
    {
        return $this->current_address ?: $this->ktp_address;
    }

    // ✅ NEW: NIK Validation Methods
    public function getNikFormattedAttribute()
    {
        if (!$this->nik) return null;
        
        // Format: XXXX-XXXX-XXXX-XXXX
        return substr($this->nik, 0, 4) . '-' . 
               substr($this->nik, 4, 4) . '-' . 
               substr($this->nik, 8, 4) . '-' . 
               substr($this->nik, 12, 4);
    }

    public function isValidNik()
    {
        if (!$this->nik) return false;
        
        // NIK must be exactly 16 digits
        return preg_match('/^\d{16}$/', $this->nik);
    }

    // Scopes
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByMaritalStatus($query, $status)
    {
        return $query->where('marital_status', $status);
    }

    public function scopeByNik($query, $nik)
    {
        return $query->where('nik', $nik);
    }
}