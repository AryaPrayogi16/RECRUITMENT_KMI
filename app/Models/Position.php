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

    // ✅ SIMPLIFIED: Status Logic - Hanya 2 Status
    
    /**
     * Get the status of the position
     * - AKTIF: is_active = true AND (no closing_date OR closing_date > today)
     * - TUTUP: is_active = false OR closing_date <= today
     */
    public function getDetailedStatusAttribute()
    {
        // Jika is_active = false, maka status = TUTUP
        if (!$this->is_active) {
            return 'tutup';
        }
        
        // Jika ada closing_date dan sudah lewat, maka status = TUTUP
        if ($this->closing_date && $this->closing_date->isPast()) {
            return 'tutup';
        }
        
        // Selain itu, status = AKTIF
        return 'aktif';
    }

    /**
     * Get human readable status
     */
    public function getStatusLabelAttribute()
    {
        return $this->detailed_status === 'aktif' ? 'Aktif' : 'Tutup';
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->detailed_status === 'aktif' ? 'status-active' : 'status-closed';
    }

    /**
     * Can this position accept new applications?
     */
    public function canAcceptApplications()
    {
        return $this->detailed_status === 'aktif';
    }

    /**
     * Check if position is currently open (accepting applications)
     */
    public function getIsOpenAttribute()
    {
        return $this->canAcceptApplications();
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
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->whereNull('closing_date')
                           ->orWhere('closing_date', '>=', now());
                     });
    }

    public function scopeClosed($query)
    {
        return $query->where(function($q) {
            $q->where('is_active', false)
              ->orWhere(function($subQuery) {
                  $subQuery->where('is_active', true)
                           ->whereNotNull('closing_date')
                           ->where('closing_date', '<', now());
              });
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

    // Enhanced: Update & Delete Safety Methods
    
    /**
     * Check if position can be safely deleted
     */
    public function canBeDeleted()
    {
        return $this->candidates()->count() === 0;
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

            $newPosition = Position::findOrFail($newPositionId);
            $candidates = $this->candidates()->get();
            
            // Update all candidates to new position
            $transferCount = $this->candidates()->update([
                'position_id' => $newPositionId,
                'position_applied' => $newPosition->position_name, // Update applied position name
                'updated_at' => now()
            ]);

            // Log the transfer for each candidate
            foreach ($candidates as $candidate) {
                if (class_exists(\App\Models\ApplicationLog::class)) {
                    \App\Models\ApplicationLog::create([
                        'candidate_id' => $candidate->id,
                        'user_id' => auth()->id(),
                        'action_type' => 'data_update',
                        'action_description' => "Candidate transferred from position '{$this->position_name}' to '{$newPosition->position_name}'" . 
                                              ($reason ? " - Reason: {$reason}" : "") .
                                              " due to position deletion."
                    ]);
                }
            }

            // Now safely delete the position
            $positionName = $this->position_name;
            $this->delete();

            \DB::commit();

            return [
                'success' => true,
                'transferred_count' => $transferCount,
                'message' => "Position '{$positionName}' deleted successfully. {$transferCount} candidates transferred to '{$newPosition->position_name}'.",
                'new_position' => $newPosition
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
        $significantFields = ['position_name', 'department', 'salary_range_min', 'salary_range_max', 'is_active', 'closing_date'];
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
     * ✅ SIMPLIFIED: Close position (multiple ways to close)
     */
    public function closePosition($reason = null, $setClosingDate = false)
    {
        $updateData = ['is_active' => false];
        
        // Optionally set closing_date to now if requested
        if ($setClosingDate) {
            $updateData['closing_date'] = now();
        }
        
        $this->update($updateData);

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
     * ✅ SIMPLIFIED: Open/Activate position
     */
    public function openPosition($reason = null, $extendClosingDate = null)
    {
        $updateData = ['is_active' => true];
        
        // Extend closing date if provided
        if ($extendClosingDate) {
            $updateData['closing_date'] = $extendClosingDate;
        } elseif ($this->closing_date && $this->closing_date->isPast()) {
            // If closing_date is in the past, clear it when reopening
            $updateData['closing_date'] = null;
        }
        
        $this->update($updateData);

        // Log the opening
        if (class_exists(\App\Models\ApplicationLog::class) && $this->candidates()->exists()) {
            foreach ($this->candidates as $candidate) {
                \App\Models\ApplicationLog::create([
                    'candidate_id' => $candidate->id,
                    'user_id' => auth()->id(),
                    'action_type' => 'status_change',
                    'action_description' => "Position '{$this->position_name}' has been reopened" . 
                                          ($reason ? ": {$reason}" : "")
                ]);
            }
        }

        return true;
    }

    /**
     * Toggle position status (open/close)
     */
    public function toggleStatus($reason = null)
    {
        if ($this->detailed_status === 'aktif') {
            return $this->closePosition($reason);
        } else {
            return $this->openPosition($reason);
        }
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

    /**
     * Auto-close positions that have passed their closing date
     * This can be run via a scheduled job
     */
    public static function autoCloseExpiredPositions()
    {
        $expiredPositions = self::where('is_active', true)
                               ->whereNotNull('closing_date')
                               ->where('closing_date', '<', now())
                               ->get();

        foreach ($expiredPositions as $position) {
            $position->closePosition('Auto-closed: Closing date has passed');
        }

        return $expiredPositions->count();
    }
}