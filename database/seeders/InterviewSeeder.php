<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Interview;

class InterviewSeeder extends Seeder
{
    public function run(): void
    {
        $interviews = [
            [
                'candidate_id' => 3,
                'interviewer_id' => 5, // Dr. Michael Chen
                'interview_date' => now()->addDays(1)->format('Y-m-d'),
                'interview_time' => '14:00:00',
                'interview_type' => 'in-person',
                'location' => 'Meeting Room A, Lt. 5',
                'status' => 'scheduled',
                'notes' => 'Interview untuk posisi UI/UX Designer',
                'score' => null,
            ],
            [
                'candidate_id' => 4,
                'interviewer_id' => 6, // John Smith
                'interview_date' => now()->format('Y-m-d'),
                'interview_time' => '16:00:00',
                'interview_type' => 'video',
                'location' => 'Google Meet Link: meet.google.com/abc-defg-hij',
                'status' => 'scheduled',
                'notes' => 'Interview untuk posisi Marketing Manager',
                'score' => null,
            ],
            [
                'candidate_id' => 8,
                'interviewer_id' => 5, // Dr. Michael Chen
                'interview_date' => now()->format('Y-m-d'),
                'interview_time' => '10:00:00',
                'interview_type' => 'phone',
                'location' => 'Phone Interview',
                'status' => 'completed',
                'notes' => 'Interview selesai, kandidat menunjukkan kemampuan leadership yang baik',
                'score' => 8,
            ],
            [
                'candidate_id' => 14,
                'interviewer_id' => 7, // David Wilson
                'interview_date' => now()->addDays(2)->format('Y-m-d'),
                'interview_time' => '09:00:00',
                'interview_type' => 'in-person',
                'location' => 'Meeting Room B, Lt. 3',
                'status' => 'scheduled',
                'notes' => 'Interview untuk posisi Sales Executive',
                'score' => null,
            ],
            [
                'candidate_id' => 5,
                'interviewer_id' => 8, // Emma Rodriguez
                'interview_date' => now()->subDays(3)->format('Y-m-d'),
                'interview_time' => '11:00:00',
                'interview_type' => 'video',
                'location' => 'Zoom Meeting',
                'status' => 'completed',
                'notes' => 'Kandidat sangat qualified, pengalaman sales yang impressive',
                'score' => 9,
            ],
        ];

        foreach ($interviews as $interview) {
            Interview::create($interview);
        }
    }
}