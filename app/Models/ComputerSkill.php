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
        'hardware_skills',
        'software_skills'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
    
    // Accessors
    public function getHardwareSkillsArrayAttribute()
    {
        return $this->hardware_skills ? explode(',', $this->hardware_skills) : [];
    }
    
    public function getSoftwareSkillsArrayAttribute()
    {
        return $this->software_skills ? explode(',', $this->software_skills) : [];
    }
}