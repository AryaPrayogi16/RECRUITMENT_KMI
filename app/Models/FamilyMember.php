<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    // Set explicit null defaults - sesuai database
    protected $attributes = [
        'relationship' => null,
        'name' => null,
        'age' => null,
        'education' => null,
        'occupation' => null,
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByRelationship($query, $relationship)
    {
        return $query->where('relationship', $relationship);
    }

    public function scopeForCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    public function scopeOrderByRelationship($query)
    {
        $relationshipOrder = ['Pasangan', 'Ayah', 'Ibu', 'Anak', 'Saudara'];
        
        return $query->orderByRaw("FIELD(relationship, '" . implode("','", $relationshipOrder) . "')");
    }

    // Accessors
    public function getRelationshipLabelAttribute()
    {
        $labels = [
            'Pasangan' => 'Pasangan',
            'Anak' => 'Anak',
            'Ayah' => 'Ayah',
            'Ibu' => 'Ibu',
            'Saudara' => 'Saudara'
        ];

        return $labels[$this->relationship] ?? $this->relationship;
    }

    public function getAgeGroupAttribute()
    {
        if (!$this->age) return null;

        if ($this->age < 18) return 'Anak-anak';
        if ($this->age < 40) return 'Dewasa Muda';
        if ($this->age < 60) return 'Dewasa';
        return 'Lansia';
    }

    public function getFormattedInfoAttribute()
    {
        return $this->name . ' (' . $this->relationship . ', ' . $this->age . ' tahun)';
    }

    // Static methods
    public static function getAvailableRelationships()
    {
        return ['Pasangan', 'Anak', 'Ayah', 'Ibu', 'Saudara'];
    }

    public static function getRelationshipOptions()
    {
        return [
            'Pasangan' => 'Pasangan',
            'Anak' => 'Anak',
            'Ayah' => 'Ayah',
            'Ibu' => 'Ibu',
            'Saudara' => 'Saudara'
        ];
    }

    // Validation helper
    public function getValidationRules()
    {
        return [
            'candidate_id' => 'required|exists:candidates,id',
            'relationship' => 'nullable|in:Pasangan,Anak,Ayah,Ibu,Saudara',
            'name' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:0|max:120',
            'education' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255'
        ];
    }
}