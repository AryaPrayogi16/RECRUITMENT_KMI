<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscProfileDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'dimension',
        'name_en',
        'name_id',
        'description_en',
        'description_id',
        'traits_en',
        'traits_id',
        'strengths',
        'weaknesses',
        'work_style'
    ];

    protected $casts = [
        'traits_en' => 'array',
        'traits_id' => 'array',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'work_style' => 'array'
    ];

    // Scopes
    public function scopeByDimension($query, $dimension)
    {
        return $query->where('dimension', $dimension);
    }

    // Accessors
    public function getNameAttribute()
    {
        return $this->name_id ?: $this->name_en;
    }

    public function getDescriptionAttribute()
    {
        return $this->description_id ?: $this->description_en;
    }

    public function getTraitsAttribute()
    {
        return $this->traits_id ?: $this->traits_en;
    }
}