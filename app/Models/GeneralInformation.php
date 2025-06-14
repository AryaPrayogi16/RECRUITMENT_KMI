<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralInformation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'general_information';

    protected $fillable = [
        'candidate_id',
        'willing_to_travel',
        'has_vehicle',
        'vehicle_types',
        'motivation',
        'strengths',
        'weaknesses',
        'other_income',
        'has_police_record',
        'police_record_detail',
        'has_serious_illness',
        'illness_detail',
        'has_tattoo_piercing',
        'tattoo_piercing_detail',
        'has_other_business',
        'other_business_detail',
        'absence_days',
        'start_work_date',
        'information_source',
        'agreement'
    ];

    protected $casts = [
        'willing_to_travel' => 'boolean',
        'has_vehicle' => 'boolean',
        'has_police_record' => 'boolean',
        'has_serious_illness' => 'boolean',
        'has_tattoo_piercing' => 'boolean',
        'has_other_business' => 'boolean',
        'agreement' => 'boolean',
        'absence_days' => 'integer',
        'start_work_date' => 'date'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}