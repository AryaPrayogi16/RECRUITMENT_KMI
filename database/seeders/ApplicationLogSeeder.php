<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApplicationLog;

class ApplicationLogSeeder extends Seeder
{
    public function run(): void
    {
        $logs = [
            [
                'candidate_id' => 1,
                'user_id' => 2, // Sarah Johnson (HR)
                'action' => 'created',
                'notes' => 'Kandidat baru mendaftar untuk posisi Frontend Developer',
            ],
            [
                'candidate_id' => 2,
                'user_id' => 2,
                'action' => 'status_changed',
                'notes' => 'Status diubah dari pending ke reviewing',
            ],
            [
                'candidate_id' => 3,
                'user_id' => 3, // Lisa Wong (HR)
                'action' => 'interview_scheduled',
                'notes' => 'Interview dijadwalkan dengan Dr. Michael Chen',
            ],
            [
                'candidate_id' => 5,
                'user_id' => 2,
                'action' => 'status_changed',
                'notes' => 'Status diubah ke accepted setelah interview positif',
            ],
            [
                'candidate_id' => 11,
                'user_id' => 4, // Maya Sari (HR)
                'action' => 'status_changed',
                'notes' => 'Status diubah ke rejected - tidak sesuai requirement',
            ],
        ];

        foreach ($logs as $log) {
            ApplicationLog::create($log);
        }
    }
}