<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KraeplinQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_number',
        'row_number',
        'value'
    ];

    protected $casts = [
        'column_number' => 'integer',
        'row_number' => 'integer',
        'value' => 'integer'
    ];

    public function scopeByColumn($query, $columnNumber)
    {
        return $query->where('column_number', $columnNumber);
    }

    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('column_number')->orderBy('row_number');
    }
}

