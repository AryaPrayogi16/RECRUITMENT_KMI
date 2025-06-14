<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormalEducation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'formal_education';

    protected $fillable = [
        'candidate_id',
        'education_level',
        'institution_name',
        'major',
        'start_year',
        'end_year',
        'gpa'
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'gpa' => 'decimal:2'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}