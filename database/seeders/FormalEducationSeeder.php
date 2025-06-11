<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormalEducation;

class FormalEducationSeeder extends Seeder
{
    public function run(): void
    {
        $educationData = [
            // Ahmad Rizki Pratama
            [
                'candidate_id' => 1,
                'education_level' => 'S1',
                'institution_name' => 'Universitas Indonesia',
                'major' => 'Teknik Informatika',
                'start_month' => 9,
                'start_year' => 2015,
                'end_month' => 7,
                'end_year' => 2019,
                'gpa' => 3.45,
            ],
            [
                'candidate_id' => 1,
                'education_level' => 'SMA/SMK',
                'institution_name' => 'SMAN 1 Jakarta',
                'major' => 'IPA',
                'start_month' => 7,
                'start_year' => 2012,
                'end_month' => 5,
                'end_year' => 2015,
                'gpa' => 8.75,
            ],
            
            // Siti Nurhaliza
            [
                'candidate_id' => 2,
                'education_level' => 'S1',
                'institution_name' => 'Institut Teknologi Bandung',
                'major' => 'Teknik Informatika',
                'start_month' => 8,
                'start_year' => 2013,
                'end_month' => 6,
                'end_year' => 2017,
                'gpa' => 3.67,
            ],
            [
                'candidate_id' => 2,
                'education_level' => 'SMA/SMK',
                'institution_name' => 'SMAN 3 Bandung',
                'major' => 'IPA',
                'start_month' => 7,
                'start_year' => 2010,
                'end_month' => 5,
                'end_year' => 2013,
                'gpa' => 8.95,
            ],
            
            // Budi Santoso
            [
                'candidate_id' => 3,
                'education_level' => 'S1',
                'institution_name' => 'Universitas Airlangga',
                'major' => 'Desain Komunikasi Visual',
                'start_month' => 9,
                'start_year' => 2010,
                'end_month' => 8,
                'end_year' => 2014,
                'gpa' => 3.23,
            ],
        ];

        foreach ($educationData as $education) {
            FormalEducation::create($education);
        }
    }
}