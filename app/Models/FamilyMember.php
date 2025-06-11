<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'relationship',
        'name',
        'age',
        'education',
        'occupation',
        'sequence_number'
    ];

    protected $casts = [
        'age' => 'integer',
        'sequence_number' => 'integer'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByRelationship($query, $relationship)
    {
        return $query->where('relationship', $relationship);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_number');
    }
}
