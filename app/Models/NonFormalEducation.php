<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonFormalEducation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'course_name',
        'organizer',
        'date_completed',
        'description'
    ];

    protected $casts = [
        'date_completed' => 'date'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('date_completed');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('date_completed', 'desc');
    }
}