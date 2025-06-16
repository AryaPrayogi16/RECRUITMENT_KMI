<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'user_id',
        'action_type',        // ✅ Harus sesuai dengan controller
        'action_description'  // ✅ Harus sesuai dengan controller
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByAction($query, $action)
    {
        return $query->where('action_type', $action); // ✅ Gunakan action_type
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d M Y H:i');
    }

    public function getActionTypeFormattedAttribute()
    {
        return match($this->action_type) {
            'status_change' => 'Perubahan Status',
            'document_upload' => 'Upload Dokumen',
            'data_update' => 'Update Data',
            'interview_scheduled' => 'Penjadwalan Interview',
            'interview_completed' => 'Interview Selesai',
            'offer_sent' => 'Penawaran Dikirim',
            'application_received' => 'Lamaran Diterima',
            default => ucfirst(str_replace('_', ' ', $this->action_type))
        };
    }

    // Constants for action types
    const ACTION_STATUS_CHANGE = 'status_change';
    const ACTION_DOCUMENT_UPLOAD = 'document_upload';
    const ACTION_DATA_UPDATE = 'data_update';
    const ACTION_INTERVIEW_SCHEDULED = 'interview_scheduled';
    const ACTION_INTERVIEW_COMPLETED = 'interview_completed';
    const ACTION_OFFER_SENT = 'offer_sent';
    const ACTION_APPLICATION_RECEIVED = 'application_received';

    public static function getActionTypes()
    {
        return [
            self::ACTION_STATUS_CHANGE => 'Perubahan Status',
            self::ACTION_DOCUMENT_UPLOAD => 'Upload Dokumen',
            self::ACTION_DATA_UPDATE => 'Update Data',
            self::ACTION_INTERVIEW_SCHEDULED => 'Penjadwalan Interview',
            self::ACTION_INTERVIEW_COMPLETED => 'Interview Selesai',
            self::ACTION_OFFER_SENT => 'Penawaran Dikirim',
            self::ACTION_APPLICATION_RECEIVED => 'Lamaran Diterima',
        ];
    }

    // Helper methods
    public static function logAction($candidateId, $actionType, $description, $userId = null)
    {
        return self::create([
            'candidate_id' => $candidateId,
            'user_id' => $userId,
            'action_type' => $actionType,
            'action_description' => $description
        ]);
    }

    public static function logStatusChange($candidateId, $oldStatus, $newStatus, $userId = null)
    {
        return self::logAction(
            $candidateId,
            self::ACTION_STATUS_CHANGE,
            "Status berubah dari '{$oldStatus}' ke '{$newStatus}'",
            $userId
        );
    }

    public static function logApplicationReceived($candidateId, $positionName)
    {
        return self::logAction(
            $candidateId,
            self::ACTION_APPLICATION_RECEIVED,
            "Lamaran diterima untuk posisi: {$positionName}"
        );
    }
}