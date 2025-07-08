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
<<<<<<< HEAD
    
=======
         URL::forceRootUrl(config('app.url'));
>>>>>>> 025c2862bf332aae40a1272ed2615a1b04c212e7
    }
}
