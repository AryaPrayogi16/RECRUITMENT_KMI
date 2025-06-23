<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'question_text_en',
        'question_text_id',
        'weight_d',
        'weight_i',
        'weight_s',
        'weight_c',
        'primary_dimension',
        'primary_strength',
        'order_number',
        'is_core_16',
        'is_active'
    ];

    protected $casts = [
        'weight_d' => 'decimal:4',
        'weight_i' => 'decimal:4',
        'weight_s' => 'decimal:4',
        'weight_c' => 'decimal:4',
        'primary_strength' => 'decimal:4',
        'is_core_16' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function answers()
    {
        return $this->hasMany(DiscAnswer::class, 'question_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCore16($query)
    {
        return $query->where('is_core_16', true);
    }

    public function scopeByDimension($query, $dimension)
    {
        return $query->where('primary_dimension', $dimension);
    }

    // Accessors
    public function getDimensionWeightsAttribute()
    {
        return [
            'D' => $this->weight_d,
            'I' => $this->weight_i,
            'S' => $this->weight_s,
            'C' => $this->weight_c
        ];
    }

    public function getQuestionTextAttribute()
    {
        return $this->question_text_id ?: $this->question_text_en;
    }
}
