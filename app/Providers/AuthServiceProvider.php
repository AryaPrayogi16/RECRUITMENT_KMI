<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Admin gates
        Gate::define('admin-access', function ($user) {
            return $user->role === 'admin';
        });
        
        // HR gates
        Gate::define('hr-access', function ($user) {
            return in_array($user->role, ['admin', 'hr']);
        });
        
        // Interviewer gates
        Gate::define('interviewer-access', function ($user) {
            return in_array($user->role, ['admin', 'hr', 'interviewer']);
        });
        
        // Super admin gate
        Gate::define('super-admin', function ($user) {
            return $user->role === 'admin';
        });
    }
}