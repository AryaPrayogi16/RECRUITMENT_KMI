<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Seeder;
use Illuminate\Validation\Rules\Can;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        \App\Models\User::create([
            'username' => 'admin',
            'email' => 'admin@pawindo.com',
            'password' => bcrypt('admin123'),
            'full_name' => 'Administrator',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create HR user
        \App\Models\User::create([
            'username' => 'hr_manager',
            'email' => 'hr@pawindo.com',
            'password' => bcrypt('hr123'),
            'full_name' => 'HR Manager',
            'role' => 'hr',
            'is_active' => true,
        ]);

        // Create Interviewer user
        \App\Models\User::create([
            'username' => 'interviewer',
            'email' => 'interviewer@pawindo.com',
            'password' => bcrypt('int123'),
            'full_name' => 'Interviewer',
            'role' => 'interviewer',
            'is_active' => true,
        ]);

        // Seed positions and candidates
        $this->call([
            PositionsTableSeeder::class
            // CandidateSeeder::class,
        ]);
    }
}