<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkExperience extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'company_name',
        'company_address',
        'business_field',
        'position',
        'start_month',
        'start_year',
        'end_month',
        'end_year',
        'salary',
        'reason_for_leaving',
        'supervisor_name',
        'supervisor_phone',
        'sequence_order'
    ];

    protected $casts = [
        'start_month' => 'integer',
        'start_year' => 'integer',
        'end_month' => 'integer',
        'end_year' => 'integer',
        'salary' => 'decimal:2',
        'sequence_order' => 'integer'
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order')->orderBy('start_year', 'desc');
    }

    public function scopeByCompany($query, $company)
    {
        return $query->where('company_name', 'like', '%' . $company . '%');
    }

    // Accessors
    public function getPeriodAttribute()
    {
        $start = $this->start_year . ($this->start_month ? '/' . $this->start_month : '');
        $end = $this->end_year . ($this->end_month ? '/' . $this->end_month : '');
        return $start . ' - ' . $end;
    }

    public function getDurationInMonthsAttribute()
    {
        if ($this->start_year && $this->end_year) {
            $startDate = \Carbon\Carbon::create($this->start_year, $this->start_month ?: 1);
            $endDate = \Carbon\Carbon::create($this->end_year, $this->end_month ?: 12);
            return $startDate->diffInMonths($endDate);
        }
        return 0;
    }
}