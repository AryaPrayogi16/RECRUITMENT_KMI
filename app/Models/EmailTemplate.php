<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_name',
        'subject',
        'body',
        'template_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Constants - sesuai dengan enum di database
    const TYPE_APPLICATION_RECEIVED = 'application_received';
    const TYPE_INTERVIEW_INVITATION = 'interview_invitation';
    const TYPE_ACCEPTANCE = 'acceptance';
    const TYPE_REJECTION = 'rejection';

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('template_type', $type);
    }

    // Methods
    public function parseTemplate($variables = [])
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($variables as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body
        ];
    }

    // Static method untuk mendapatkan semua template types
    public static function getTemplateTypes()
    {
        return [
            self::TYPE_APPLICATION_RECEIVED => 'Application Received',
            self::TYPE_INTERVIEW_INVITATION => 'Interview Invitation', 
            self::TYPE_ACCEPTANCE => 'Acceptance',
            self::TYPE_REJECTION => 'Rejection'
        ];
    }
}
