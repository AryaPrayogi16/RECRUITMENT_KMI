<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'role',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean'
    ];

    // Constants
    const ROLE_ADMIN = 'admin';
    const ROLE_HR = 'hr';
    const ROLE_INTERVIEWER = 'interviewer';

    // Relationships
    public function applicationLogs()
    {
        return $this->hasMany(ApplicationLog::class);
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'interviewer_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeHrStaff($query)
    {
        return $query->where('role', self::ROLE_HR);
    }

    public function scopeInterviewers($query)
    {
        return $query->whereIn('role', [self::ROLE_HR, self::ROLE_INTERVIEWER]);
    }

    // Methods
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isHr()
    {
        return $this->role === self::ROLE_HR;
    }

    public function isInterviewer()
    {
        return $this->role === self::ROLE_INTERVIEWER;
    }
}