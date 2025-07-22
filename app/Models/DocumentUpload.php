<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentUpload extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'document_type',
        'document_name',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type'
    ];

    protected $casts = [
        'file_size' => 'integer'
    ];

    // Constants
    const TYPE_CV = 'cv';
    const TYPE_PHOTO = 'photo';
    const TYPE_CERTIFICATES = 'certificates';
    const TYPE_TRANSCRIPT = 'transcript';
    const TYPE_KTP = 'ktp'; // 🆕 New KTP document type

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
            'transcript' => 'Transkrip Nilai',
            'ktp' => 'KTP' // 🆕 New KTP label
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
            self::TYPE_TRANSCRIPT => 'Transkrip Nilai',
            self::TYPE_KTP => 'KTP' // 🆕 New KTP type
        ];
    }
}