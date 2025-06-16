<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialActivity extends Model
{
    use SoftDeletes;

    protected $table = 'social_activities';

    protected $fillable = [
        'candidate_id',
        'organization_name',
        'field',
        'period',
        'description'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}