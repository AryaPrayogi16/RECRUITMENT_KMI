<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherSkill extends Model
{
    use SoftDeletes;

    protected $table = 'other_skills';

    protected $fillable = [
        'candidate_id',
        'other_skills'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}