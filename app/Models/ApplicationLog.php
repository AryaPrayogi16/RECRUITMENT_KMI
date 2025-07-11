<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'user_id',
        'action_type',
        'action_description'
    ];

    // Constants sesuai database enum
    const ACTION_STATUS_CHANGE = 'status_change';
    const ACTION_DOCUMENT_UPLOAD = 'document_upload';
    const ACTION_DATA_UPDATE = 'data_update';
    const ACTION_EXPORT = 'export';

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByActionType($query, $type)
    {
        return $query->where('action_type', $type);
    }

    public function scopeForCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getActionTypeLabelAttribute()
    {
        $labels = [
            'status_change' => 'Perubahan Status',
            'document_upload' => 'Upload Dokumen',
            'data_update' => 'Update Data',
            'export' => 'Export Data'
        ];

        return $labels[$this->action_type] ?? $this->action_type;
    }

    public function getActionTypeBadgeAttribute()
    {
        $badges = [
            'status_change' => 'bg-blue-100 text-blue-800',
            'document_upload' => 'bg-green-100 text-green-800',
            'data_update' => 'bg-yellow-100 text-yellow-800',
            'export' => 'bg-purple-100 text-purple-800'
        ];

        return $badges[$this->action_type] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    // Static methods
    public static function logAction($candidateId, $userId, $actionType, $description)
    {
        return self::create([
            'candidate_id' => $candidateId,
            'user_id' => $userId,
            'action_type' => $actionType,
            'action_description' => $description
        ]);
    }

    public static function getActionTypes()
    {
        return [
            self::ACTION_STATUS_CHANGE => 'Perubahan Status',
            self::ACTION_DOCUMENT_UPLOAD => 'Upload Dokumen',
            self::ACTION_DATA_UPDATE => 'Update Data',
            self::ACTION_EXPORT => 'Export Data'
        ];
    }

    // Accessors
    public function getHardwareSkillsArrayAttribute()
    {
        return $this->hardware_skills ? array_map('trim', explode(',', $this->hardware_skills)) : [];
    }

    public function getSoftwareSkillsArrayAttribute()
    {
        return $this->software_skills ? array_map('trim', explode(',', $this->software_skills)) : [];
    }

    public function getOtherSkillsArrayAttribute()
    {
        return $this->other_skills ? array_map('trim', explode(',', $this->other_skills)) : [];
    }

    public function getVehicleTypesArrayAttribute()
    {
        return $this->vehicle_types ? array_map('trim', explode(',', $this->vehicle_types)) : [];
    }

    public function getFormattedStartWorkDateAttribute()
    {
        return $this->start_work_date ? $this->start_work_date->format('d F Y') : null;
    }

    public function getTravelWillingnessAttribute()
    {
        return $this->willing_to_travel ? 'Ya' : 'Tidak';
    }

    public function getVehicleOwnershipAttribute()
    {
        return $this->has_vehicle ? 'Ya' : 'Tidak';
    }

    public function getPoliceRecordStatusAttribute()
    {
        return $this->has_police_record ? 'Ya' : 'Tidak';
    }

    public function getIllnessStatusAttribute()
    {
        return $this->has_serious_illness ? 'Ya' : 'Tidak';
    }

    public function getTattooStatusAttribute()
    {
        return $this->has_tattoo_piercing ? 'Ya' : 'Tidak';
    }

    public function getBusinessStatusAttribute()
    {
        return $this->has_other_business ? 'Ya' : 'Tidak';
    }

    public function getAgreementStatusAttribute()
    {
        return $this->agreement ? 'Setuju' : 'Tidak Setuju';
    }

    // Helper methods for displaying skills
    public function getAllSkillsAttribute()
    {
        $skills = [];
        
        if ($this->hardware_skills_array) {
            $skills['Hardware'] = $this->hardware_skills_array;
        }
        
        if ($this->software_skills_array) {
            $skills['Software'] = $this->software_skills_array;
        }
        
        if ($this->other_skills_array) {
            $skills['Lainnya'] = $this->other_skills_array;
        }
        
        return $skills;
    }

    public function getHealthIssuesAttribute()
    {
        $issues = [];
        
        if ($this->has_police_record) {
            $issues[] = 'Catatan Kepolisian' . ($this->police_record_detail ? ': ' . $this->police_record_detail : '');
        }
        
        if ($this->has_serious_illness) {
            $issues[] = 'Riwayat Sakit Serius' . ($this->illness_detail ? ': ' . $this->illness_detail : '');
        }
        
        if ($this->has_tattoo_piercing) {
            $issues[] = 'Tato/Tindik' . ($this->tattoo_piercing_detail ? ': ' . $this->tattoo_piercing_detail : '');
        }
        
        return $issues;
    }

    // Mutators
    public function setHardwareSkillsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['hardware_skills'] = implode(', ', array_filter($value));
        } else {
            $this->attributes['hardware_skills'] = $value;
        }
    }

    public function setSoftwareSkillsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['software_skills'] = implode(', ', array_filter($value));
        } else {
            $this->attributes['software_skills'] = $value;
        }
    }

    public function setOtherSkillsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['other_skills'] = implode(', ', array_filter($value));
        } else {
            $this->attributes['other_skills'] = $value;
        }
    }

    public function setVehicleTypesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['vehicle_types'] = implode(', ', array_filter($value));
        } else {
            $this->attributes['vehicle_types'] = $value;
        }
    }
}