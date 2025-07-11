<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateAdditionalInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'candidate_additional_info';

    protected $fillable = [
        'candidate_id',
        // Computer Skills
        'hardware_skills',
        'software_skills',
        // Other Skills
        'other_skills',
        // General Information
        'willing_to_travel',
        'has_vehicle',
        'vehicle_types',
        'motivation',
        'strengths',
        'weaknesses',
        'other_income',
        'has_police_record',
        'police_record_detail',
        'has_serious_illness',
        'illness_detail',
        'has_tattoo_piercing',
        'tattoo_piercing_detail',
        'has_other_business',
        'other_business_detail',
        'absence_days',
        'start_work_date',
        'information_source',
        'agreement'
    ];

    protected $casts = [
        'willing_to_travel' => 'boolean',
        'has_vehicle' => 'boolean',
        'has_police_record' => 'boolean',
        'has_serious_illness' => 'boolean',
        'has_tattoo_piercing' => 'boolean',
        'has_other_business' => 'boolean',
        'agreement' => 'boolean',
        'absence_days' => 'integer',
        'start_work_date' => 'date'
    ];

    // Relationships
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeForCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }
    
    // Accessors
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getFileExtensionAttribute()
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    public function getDocumentTypeLabelAttribute()
    {
        $labels = [
            'cv' => 'CV/Resume',
            'photo' => 'Foto',
            'certificates' => 'Sertifikat',
            'transcript' => 'Transkrip Nilai'
        ];

        return $labels[$this->document_type] ?? $this->document_type;
    }

    public function getIsImageAttribute()
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/jpg', 'image/png']);
    }

    public function getIsPdfAttribute()
    {
        return $this->mime_type === 'application/pdf';
    }

    // Static methods
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_CV => 'CV/Resume',
            self::TYPE_PHOTO => 'Foto',
            self::TYPE_CERTIFICATES => 'Sertifikat',
            self::TYPE_TRANSCRIPT => 'Transkrip Nilai'
        ];
    }
}
