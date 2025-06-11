<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralInformation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'willing_to_travel',
        'has_vehicle',
        'vehicle_types',
        'motivation',
        'weaknesses',
        'strengths',
        'other_income_sources',
        'criminal_record',
        'medical_history',
        'has_tattoo_piercing',
        'other_company_ownership',
        'annual_sick_days',
        'start_work_date',
        'information_source'
    ];

    protected $casts = [
        'willing_to_travel' => 'boolean',
        'has_vehicle' => 'boolean',
        'has_tattoo_piercing' => 'boolean',
        'annual_sick_days' => 'integer',
        'start_work_date' => 'date'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}