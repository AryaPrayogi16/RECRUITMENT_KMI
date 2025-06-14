<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkExperience extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'company_name',
        'company_address',
        'company_field',
        'position',
        'start_year',
        'end_year',
        'salary',
        'reason_for_leaving',
        'supervisor_contact'
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'salary' => 'decimal:2'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}