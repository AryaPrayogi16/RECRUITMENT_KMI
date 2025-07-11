<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'is_active' => 'boolean',
        'last_login_at' => 'datetime'
    ];

    // Constants sesuai database enum
    const ROLE_ADMIN = 'admin';
    const ROLE_HR = 'hr';
    const ROLE_INTERVIEWER = 'interviewer';

    // Relationships
    public function applicationLogs(): HasMany
    {
        return $this->hasMany(ApplicationLog::class);
    }

    public function interviews(): HasMany
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

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    // Accessors
    public function getRoleBadgeAttribute()
    {
        $badges = [
            'admin' => 'bg-purple-100 text-purple-800',
            'hr' => 'bg-blue-100 text-blue-800',
            'interviewer' => 'bg-green-100 text-green-800'
        ];

        return $badges[$this->role] ?? 'bg-gray-100 text-gray-800';
    }

    public function getRoleLabelAttribute()
    {
        $labels = [
            'admin' => 'Administrator',
            'hr' => 'HR Staff',
            'interviewer' => 'Interviewer'
        ];

        return $labels[$this->role] ?? $this->role;
    }

    public function getFormattedLastLoginAttribute()
    {
        return $this->last_login_at ? $this->last_login_at->diffForHumans() : 'Belum pernah login';
    }

    public function getIsActiveStatusAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    // Methods
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isHr(): bool
    {
        return $this->role === self::ROLE_HR;
    }

    public function isInterviewer(): bool
    {
        return $this->role === self::ROLE_INTERVIEWER;
    }

    public function canInterview(): bool
    {
        return in_array($this->role, [self::ROLE_HR, self::ROLE_INTERVIEWER]);
    }

    public function canManageUsers(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function canAccessReports(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_HR]);
    }

    // Update last login
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    // Static methods
    public static function getAvailableRoles()
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_HR => 'HR Staff',
            self::ROLE_INTERVIEWER => 'Interviewer'
        ];
    }
}
