<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Custom command untuk setup demo data
Artisan::command('recruitment:demo-setup', function () {
    $this->info('Setting up recruitment demo data...');
    
    // Fresh migration
    $this->call('migrate:fresh');
    
    // Run seeders
    $this->call('db:seed');
    
    $this->info('Demo data setup completed!');
    $this->info('Login with: admin@company.com / admin123');
})->purpose('Setup recruitment demo data');