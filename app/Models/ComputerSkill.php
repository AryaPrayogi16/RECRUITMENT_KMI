<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputerSkill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'skill_type',
        'skill_description',
        'proficiency_level'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('skill_type', $type);
    }

    public function scopeHardware($query)
    {
        return $query->where('skill_type', 'Hardware');
    }

    public function scopeSoftware($query)
    {
        return $query->where('skill_type', 'Software');
    }
}