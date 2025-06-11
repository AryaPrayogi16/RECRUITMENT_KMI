<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = [
            [
                'candidate_code' => 'CND001',
                'position_applied' => 'Frontend Developer',
                'expected_salary' => 12000000,
                'application_status' => 'pending',
            ],
            [
                'candidate_code' => 'CND002',
                'position_applied' => 'Backend Developer',
                'expected_salary' => 15000000,
                'application_status' => 'reviewing',
            ],
            [
                'candidate_code' => 'CND003',
                'position_applied' => 'UI/UX Designer',
                'expected_salary' => 10000000,
                'application_status' => 'interview',
            ],
            [
                'candidate_code' => 'CND004',
                'position_applied' => 'Marketing Manager',
                'expected_salary' => 18000000,
                'application_status' => 'interview',
            ],
            [
                'candidate_code' => 'CND005',
                'position_applied' => 'Sales Executive',
                'expected_salary' => 8000000,
                'application_status' => 'accepted',
            ],
            [
                'candidate_code' => 'CND006',
                'position_applied' => 'HR Specialist',
                'expected_salary' => 11000000,
                'application_status' => 'reviewing',
            ],
            [
                'candidate_code' => 'CND007',
                'position_applied' => 'Accountant',
                'expected_salary' => 9000000,
                'application_status' => 'pending',
            ],
            [
                'candidate_code' => 'CND008',
                'position_applied' => 'Project Manager',
                'expected_salary' => 20000000,
                'application_status' => 'interview',
            ],
            [
                'candidate_code' => 'CND009',
                'position_applied' => 'Data Analyst',
                'expected_salary' => 13000000,
                'application_status' => 'reviewing',
            ],
            [
                'candidate_code' => 'CND010',
                'position_applied' => 'Content Writer',
                'expected_salary' => 7500000,
                'application_status' => 'pending',
            ],
            [
                'candidate_code' => 'CND011',
                'position_applied' => 'Frontend Developer',
                'expected_salary' => 11000000,
                'application_status' => 'rejected',
            ],
            [
                'candidate_code' => 'CND012',
                'position_applied' => 'Backend Developer',
                'expected_salary' => 16000000,
                'application_status' => 'pending',
            ],
            [
                'candidate_code' => 'CND013',
                'position_applied' => 'UI/UX Designer',
                'expected_salary' => 9500000,
                'application_status' => 'reviewing',
            ],
            [
                'candidate_code' => 'CND014',
                'position_applied' => 'Sales Executive',
                'expected_salary' => 7000000,
                'application_status' => 'interview',
            ],
            [
                'candidate_code' => 'CND015',
                'position_applied' => 'Data Analyst',
                'expected_salary' => 14000000,
                'application_status' => 'accepted',
            ],
        ];

        foreach ($candidates as $candidate) {
            Candidate::create($candidate);
        }
    }
}
