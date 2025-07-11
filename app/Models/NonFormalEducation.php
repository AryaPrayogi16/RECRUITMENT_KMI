<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonFormalEducation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'non_formal_education';

    protected $fillable = [
        'candidate_id',
        'course_name',
        'organizer',
        'date',
        'description'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}