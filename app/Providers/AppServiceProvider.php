<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Services\DiscTestService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DiscTestService::class, function ($app) {
            return new DiscTestService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    
    }
}
