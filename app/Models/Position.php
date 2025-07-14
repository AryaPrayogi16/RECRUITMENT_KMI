<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'position_name',
        'department',
        'description',
        'requirements',
        'salary_range_min',
        'salary_range_max',
        'is_active',
        'location',
        'employment_type',
        'posted_date',
        'closing_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'posted_date' => 'date',
        'closing_date' => 'date',
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
    ];

    // Constants sesuai database enum
    const TYPE_FULL_TIME = 'full-time';
    const TYPE_PART_TIME = 'part-time';
    const TYPE_CONTRACT = 'contract';
    const TYPE_INTERNSHIP = 'internship';

    // Relationships
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    // Accessors
    public function getSalaryRangeAttribute()
    {
        if ($this->salary_range_min && $this->salary_range_max) {
            return 'Rp ' . number_format($this->salary_range_min, 0, ',', '.') . 
                   ' - Rp ' . number_format($this->salary_range_max, 0, ',', '.');
        }
        return 'Negotiable';
    }

    public function getIsOpenAttribute()
    {
        return $this->is_active && 
               (!$this->closing_date || $this->closing_date->isFuture());
    }

    public function getApplicationCountAttribute()
    {
        return $this->candidates()->count();
    }

    public function getDaysUntilClosingAttribute()
    {
        if (!$this->closing_date) return null;
        
        return $this->closing_date->diffInDays(now(), false);
    }

    public function getEmploymentTypeLabelAttribute()
    {
        $labels = [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'internship' => 'Internship'
        ];

        return $labels[$this->employment_type] ?? $this->employment_type;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->active()
                     ->where(function($q) {
                         $q->whereNull('closing_date')
                           ->orWhere('closing_date', '>=', now());
                     });
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    // Static methods
    public static function getEmploymentTypes()
    {
        return [
            self::TYPE_FULL_TIME => 'Full Time',
            self::TYPE_PART_TIME => 'Part Time',
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_INTERNSHIP => 'Internship'
        ];
    }

    public static function getDepartments()
    {
        return self::select('department')
                   ->distinct()
                   ->orderBy('department')
                   ->pluck('department')
                   ->toArray();
    }

    public static function getLocations()
    {
        return self::select('location')
                   ->distinct()
                   ->whereNotNull('location')
                   ->orderBy('location')
                   ->pluck('location')
                   ->toArray();
    }

    // ✅ ENHANCED: Update & Delete Safety Methods
    
    /**
     * Check if position can be safely deleted
     */
    public function canBeDeleted()
    {
        // Check if there are any candidates still associated
        return $this->candidates()->count() === 0;
    }

    /**
     * Check if position can be safely updated
     */
    public function canBeUpdated()
    {
        // Position can always be updated, but some fields might need special handling
        return true;
    }

    /**
     * Get count of active applications for this position
     */
    public function getActiveApplicationsCount()
    {
        return $this->candidates()
                    ->whereIn('application_status', ['submitted', 'screening', 'interview', 'offered'])
                    ->count();
    }

    /**
     * Get count of all applications (including completed/rejected)
     */
    public function getTotalApplicationsCount()
    {
        return $this->candidates()->count();
    }

    /**
     * Safe delete with validation
     */
    public function safeDelete()
    {
        if (!$this->canBeDeleted()) {
            throw new \Exception(
                "Cannot delete position '{$this->position_name}'. " .
                "There are {$this->getTotalApplicationsCount()} candidates associated with this position. " .
                "Please transfer or remove candidates first."
            );
        }

        return $this->delete(); // Soft delete
    }

    /**
     * Transfer candidates to another position before deletion
     */
    public function transferCandidatesAndDelete($newPositionId, $reason = null)
    {
        try {
            \DB::beginTransaction();

            // Update all candidates to new position
            $transferCount = $this->candidates()->update([
                'position_id' => $newPositionId,
                'updated_at' => now()
            ]);

            // Log the transfer if ApplicationLog model exists
            if (class_exists(\App\Models\ApplicationLog::class)) {
                foreach ($this->candidates as $candidate) {
                    \App\Models\ApplicationLog::create([
                        'candidate_id' => $candidate->id,
                        'user_id' => auth()->id(),
                        'action_type' => 'data_update',
                        'action_description' => "Position transferred from '{$this->position_name}' to new position due to position deletion" . 
                                              ($reason ? ": {$reason}" : "")
                    ]);
                }
            }

            // Now safely delete the position
            $this->delete();

            \DB::commit();

            return [
                'success' => true,
                'transferred_count' => $transferCount,
                'message' => "Position deleted successfully. {$transferCount} candidates transferred to new position."
            ];

        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Safe update with change tracking
     */
    public function safeUpdate(array $data, $trackChanges = true)
    {
        try {
            $originalData = $this->getOriginal();
            
            // Update the model
            $updated = $this->update($data);

            // Track significant changes if requested
            if ($trackChanges && $updated) {
                $this->trackSignificantChanges($originalData, $data);
            }

            return $updated;

        } catch (\Exception $e) {
            \Log::error('Position update failed', [
                'position_id' => $this->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Track significant changes that might affect candidates
     */
    protected function trackSignificantChanges($originalData, $newData)
    {
        $significantFields = ['position_name', 'department', 'salary_range_min', 'salary_range_max', 'is_active'];
        $changes = [];

        foreach ($significantFields as $field) {
            if (isset($newData[$field]) && $originalData[$field] != $newData[$field]) {
                $changes[$field] = [
                    'old' => $originalData[$field],
                    'new' => $newData[$field]
                ];
            }
        }

        // Log changes if ApplicationLog exists and there are significant changes
        if (!empty($changes) && class_exists(\App\Models\ApplicationLog::class)) {
            $changeDescription = "Position updated: " . json_encode($changes);
            
            // Log for each candidate affected by this position
            foreach ($this->candidates as $candidate) {
                \App\Models\ApplicationLog::create([
                    'candidate_id' => $candidate->id,
                    'user_id' => auth()->id(),
                    'action_type' => 'data_update',
                    'action_description' => $changeDescription
                ]);
            }
        }
    }

    /**
     * Close position (set inactive) instead of deleting
     */
    public function closePosition($reason = null)
    {
        $this->update([
            'is_active' => false,
            'closing_date' => now()
        ]);

        // Log the closure
        if (class_exists(\App\Models\ApplicationLog::class) && $this->candidates()->exists()) {
            foreach ($this->candidates as $candidate) {
                \App\Models\ApplicationLog::create([
                    'candidate_id' => $candidate->id,
                    'user_id' => auth()->id(),
                    'action_type' => 'status_change',
                    'action_description' => "Position '{$this->position_name}' has been closed" . 
                                          ($reason ? ": {$reason}" : "")
                ]);
            }
        }

        return true;
    }

    /**
     * Get positions that candidates can be transferred to
     */
    public static function getTransferablePositions($excludeId = null)
    {
        return self::active()
                   ->when($excludeId, function($query, $excludeId) {
                       return $query->where('id', '!=', $excludeId);
                   })
                   ->orderBy('department')
                   ->orderBy('position_name')
                   ->get();
    }
}