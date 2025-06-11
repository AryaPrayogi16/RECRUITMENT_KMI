<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Users
        User::create([
            'username' => 'admin',
            'email' => 'admin@company.com',
            'password' => Hash::make('admin123'),
            'full_name' => 'Super Administrator',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // HR Users
        User::create([
            'username' => 'hr1',
            'email' => 'sarah@company.com',
            'password' => Hash::make('hr1234'),
            'full_name' => 'Sarah Johnson',
            'role' => 'hr',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'hr2',
            'email' => 'lisa@company.com',
            'password' => Hash::make('hr1234'),
            'full_name' => 'Lisa Wong',
            'role' => 'hr',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'hr3',
            'email' => 'maya@company.com',
            'password' => Hash::make('hr1234'),
            'full_name' => 'Maya Sari',
            'role' => 'hr',
            'is_active' => true,
        ]);

        // Interviewer Users
        User::create([
            'username' => 'interviewer1',
            'email' => 'michael@company.com',
            'password' => Hash::make('int1234'),
            'full_name' => 'Dr. Michael Chen',
            'role' => 'interviewer',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'interviewer2',
            'email' => 'john@company.com',
            'password' => Hash::make('int1234'),
            'full_name' => 'John Smith',
            'role' => 'interviewer',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'interviewer3',
            'email' => 'david@company.com',
            'password' => Hash::make('int1234'),
            'full_name' => 'David Wilson',
            'role' => 'interviewer',
            'is_active' => true,
        ]);

        User::create([
            'username' => 'interviewer4',
            'email' => 'emma@company.com',
            'password' => Hash::make('int1234'),
            'full_name' => 'Emma Rodriguez',
            'role' => 'interviewer',
            'is_active' => true,
        ]);
    }
}
