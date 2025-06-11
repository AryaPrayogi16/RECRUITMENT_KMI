<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PositionSeeder::class,
            CandidateSeeder::class,
            PersonalDataSeeder::class,
            WorkExperienceSeeder::class,
            FormalEducationSeeder::class,
            InterviewSeeder::class,
            ApplicationLogSeeder::class,
            EmailTemplateSeeder::class,
        ]);
    }
}