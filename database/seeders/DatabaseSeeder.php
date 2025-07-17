<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * INTERNAL SYSTEM ONLY:
     * - Admin/HR creates all user accounts
     * - No public registration
     * - No email verification needed
     */
    public function run(): void
    {
        // ✅ Create internal users - auto-active (no verification needed)
        
        // Super Admin
        \App\Models\User::create([
            'username' => 'admin',
            'email' => 'admin@pawindo.com',
            // ❌ REMOVED: email_verified_at (not needed for internal system)
            'password' => Hash::make('admin123'),
            'full_name' => 'Administrator',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // HR Manager
        \App\Models\User::create([
            'username' => 'hr_manager',
            'email' => 'hr@pawindo.com',
            'password' => Hash::make('hr123'),
            'full_name' => 'HR Manager',
            'role' => 'hr',
            'is_active' => true,
        ]);

        // Interviewer
        \App\Models\User::create([
            'username' => 'interviewer',
            'email' => 'interviewer@pawindo.com',
            'password' => Hash::make('int123'),
            'full_name' => 'Interviewer',
            'role' => 'interviewer',
            'is_active' => true,
        ]);

        // ✅ Additional HR Staff (optional)
        \App\Models\User::create([
            'username' => 'hr_staff',
            'email' => 'hrstaff@pawindo.com',
            'password' => Hash::make('hrstaff123'),
            'full_name' => 'HR Staff',
            'role' => 'hr',
            'is_active' => true,
        ]);

        // ✅ Seed master data
        $this->call([
            PositionsTableSeeder::class,
            CandidateSeeder::class,
        ]);

        $this->command->info('✅ Internal system database seeded successfully!');
        $this->command->info('📋 Users created:');
        $this->command->info('   - admin@pawindo.com (admin123)');
        $this->command->info('   - hr@pawindo.com (hr123)');
        $this->command->info('   - interviewer@pawindo.com (int123)');
        $this->command->info('   - hrstaff@pawindo.com (hrstaff123)');
    }
}