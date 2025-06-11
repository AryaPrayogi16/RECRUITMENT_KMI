<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkExperience;

class WorkExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $workExperiences = [
            // Ahmad Rizki Pratama (Frontend Developer)
            [
                'candidate_id' => 1,
                'company_name' => 'PT Tech Solutions',
                'company_address' => 'Jl. Sudirman No. 45, Jakarta',
                'business_field' => 'Software Development',
                'position' => 'Junior Frontend Developer',
                'start_month' => 1,
                'start_year' => 2020,
                'end_month' => 12,
                'end_year' => 2023,
                'salary' => 8500000,
                'reason_for_leaving' => 'Mencari tantangan dan pengembangan karir yang lebih baik',
                'supervisor_name' => 'Budi Hartono',
                'supervisor_phone' => '08123456789',
                'sequence_order' => 1,
            ],
            [
                'candidate_id' => 1,
                'company_name' => 'CV Digital Creative',
                'company_address' => 'Jl. Gatot Subroto No. 89, Jakarta',
                'business_field' => 'Digital Agency',
                'position' => 'Web Developer Intern',
                'start_month' => 7,
                'start_year' => 2019,
                'end_month' => 12,
                'end_year' => 2019,
                'salary' => 3000000,
                'reason_for_leaving' => 'Program magang selesai',
                'supervisor_name' => 'Sari Indah',
                'supervisor_phone' => '08234567890',
                'sequence_order' => 2,
            ],
            
            // Siti Nurhaliza (Backend Developer)
            [
                'candidate_id' => 2,
                'company_name' => 'PT Sistem Informasi Nusantara',
                'company_address' => 'Jl. Asia Afrika No. 123, Bandung',
                'business_field' => 'Information Technology',
                'position' => 'Backend Developer',
                'start_month' => 3,
                'start_year' => 2019,
                'end_month' => 11,
                'end_year' => 2024,
                'salary' => 12000000,
                'reason_for_leaving' => 'Ingin bergabung dengan perusahaan yang lebih besar',
                'supervisor_name' => 'Ahmad Fauzi',
                'supervisor_phone' => '08345678901',
                'sequence_order' => 1,
            ],
            [
                'candidate_id' => 2,
                'company_name' => 'Startup TechBandung',
                'company_address' => 'Jl. Dago No. 67, Bandung',
                'business_field' => 'Financial Technology',
                'position' => 'Junior PHP Developer',
                'start_month' => 8,
                'start_year' => 2017,
                'end_month' => 2,
                'end_year' => 2019,
                'salary' => 7500000,
                'reason_for_leaving' => 'Promosi ke perusahaan yang lebih stabil',
                'supervisor_name' => 'Rizky Pratama',
                'supervisor_phone' => '08456789012',
                'sequence_order' => 2,
            ],
        ];

        foreach ($workExperiences as $experience) {
            WorkExperience::create($experience);
        }
    }
}