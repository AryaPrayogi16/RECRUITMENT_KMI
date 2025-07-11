<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data sample kandidat
        $candidates = [
            [
                'candidate_code' => 'CND001',
                'position_id' => 1, // Pastikan position_id sudah ada di tabel positions
                'position_applied' => 'Software Developer',
                'expected_salary' => 8000000.00,
                'application_status' => 'submitted',
                'application_date' => '2025-06-10',
                'personal_data' => [
                    'full_name' => 'Ahmad Rizki Pratama',
                    'email' => 'ahmad.rizki@email.com',
                    'phone_number' => '081234567890',
                    'phone_alternative' => '087654321098',
                    'birth_place' => 'Jakarta',
                    'birth_date' => '1995-03-15',
                    'gender' => 'Laki-laki',
                    'religion' => 'Islam',
                    'marital_status' => 'Lajang',
                    'ethnicity' => 'Jawa',
                    'current_address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                    'current_address_status' => 'Kontrak',
                    'ktp_address' => 'Jl. Merdeka No. 456, Jakarta Timur',
                    'height_cm' => 175,
                    'weight_kg' => 70,
                    'vaccination_status' => 'Booster'
                ],
                'driving_licenses' => ['A', 'B1'],
                'family_members' => [
                    ['relationship' => 'Ayah', 'name' => 'Budi Pratama', 'age' => 55, 'education' => 'S1', 'occupation' => 'Manager'],
                    ['relationship' => 'Ibu', 'name' => 'Siti Nurhasanah', 'age' => 50, 'education' => 'SMA', 'occupation' => 'Ibu Rumah Tangga'],
                    ['relationship' => 'Saudara', 'name' => 'Indira Pratama', 'age' => 25, 'education' => 'S1', 'occupation' => 'Guru']
                ],
                'formal_education' => [
                    ['education_level' => 'SMA/SMK', 'institution_name' => 'SMAN 1 Jakarta', 'major' => 'IPA', 'start_year' => 2010, 'end_year' => 2013, 'gpa' => 8.5],
                    ['education_level' => 'S1', 'institution_name' => 'Universitas Indonesia', 'major' => 'Teknik Informatika', 'start_year' => 2013, 'end_year' => 2017, 'gpa' => 3.45]
                ],
                'non_formal_education' => [
                    ['course_name' => 'Full Stack Web Development', 'organizer' => 'Dicoding Academy', 'date' => '2020-01-15', 'description' => 'Kursus pengembangan web full stack menggunakan JavaScript, React, dan Node.js'],
                    ['course_name' => 'AWS Cloud Practitioner', 'organizer' => 'Amazon Web Services', 'date' => '2021-06-20', 'description' => 'Sertifikasi dasar cloud computing AWS']
                ],
                'language_skills' => [
                    ['language' => 'Indonesia', 'speaking_level' => 'Mahir', 'writing_level' => 'Mahir'],
                    ['language' => 'English', 'speaking_level' => 'Menengah', 'writing_level' => 'Menengah'],
                    ['language' => 'Mandarin', 'speaking_level' => 'Pemula', 'writing_level' => 'Pemula']
                ],
                'computer_skills' => [
                    'hardware_skills' => 'PC Assembly, Laptop Repair, Network Installation',
                    'software_skills' => 'PHP, JavaScript, Python, React, Laravel, MySQL, Git'
                ],
                'other_skills' => [
                    'other_skills' => 'Public Speaking, Project Management, Digital Marketing'
                ],
                'achievements' => [
                    ['achievement' => 'Juara 1 Programming Contest', 'year' => 2016, 'description' => 'Juara 1 dalam kompetisi programming tingkat universitas'],
                    ['achievement' => 'Best Employee of the Month', 'year' => 2022, 'description' => 'Karyawan terbaik bulan Mei 2022 di perusahaan sebelumnya']
                ],
                'work_experiences' => [
                    [
                        'company_name' => 'PT. Tech Solutions',
                        'company_address' => 'Jl. HR Rasuna Said, Jakarta',
                        'company_field' => 'Information Technology',
                        'position' => 'Junior Developer',
                        'start_year' => 2018,
                        'end_year' => 2020,
                        'salary' => 5000000.00,
                        'reason_for_leaving' => 'Career advancement',
                        'supervisor_contact' => '081345678901'
                    ],
                    [
                        'company_name' => 'PT. Digital Innovation',
                        'company_address' => 'Jl. Gatot Subroto, Jakarta',
                        'company_field' => 'Software Development',
                        'position' => 'Frontend Developer',
                        'start_year' => 2020,
                        'end_year' => 2024,
                        'salary' => 7500000.00,
                        'reason_for_leaving' => 'Seeking new challenges',
                        'supervisor_contact' => '081456789012'
                    ]
                ],
                'social_activities' => [
                    ['organization_name' => 'Komunitas Programmer Jakarta', 'field' => 'Technology', 'period' => '2019-Present', 'description' => 'Volunteer sebagai mentor untuk pemula'],
                    ['organization_name' => 'PMI Jakarta', 'field' => 'Social', 'period' => '2018-2020', 'description' => 'Relawan donor darah']
                ],
                'general_information' => [
                    'willing_to_travel' => true,
                    'has_vehicle' => true,
                    'vehicle_types' => 'Motor, Mobil',
                    'motivation' => 'Ingin berkembang di bidang teknologi dan berkontribusi pada proyek-proyek inovatif',
                    'strengths' => 'Problem solving, quick learner, team player',
                    'weaknesses' => 'Terkadang terlalu perfeksionis',
                    'other_income' => 'Freelance web development',
                    'has_police_record' => false,
                    'police_record_detail' => null,
                    'has_serious_illness' => false,
                    'illness_detail' => null,
                    'has_tattoo_piercing' => false,
                    'tattoo_piercing_detail' => null,
                    'has_other_business' => true,
                    'other_business_detail' => 'Online course creator',
                    'absence_days' => 2,
                    'start_work_date' => '2025-07-01',
                    'information_source' => 'Website perusahaan',
                    'agreement' => true
                ]
            ],
            [
                'candidate_code' => 'CND002',
                'position_id' => 2,
                'position_applied' => 'Marketing Manager',
                'expected_salary' => 12000000.00,
                'application_status' => 'interview',
                'application_date' => '2025-06-08',
                'personal_data' => [
                    'full_name' => 'Sarah Putri Melinda',
                    'email' => 'sarah.putri@email.com',
                    'phone_number' => '082345678901',
                    'phone_alternative' => '078765432109',
                    'birth_place' => 'Bandung',
                    'birth_date' => '1992-08-22',
                    'gender' => 'Perempuan',
                    'religion' => 'Kristen',
                    'marital_status' => 'Menikah',
                    'ethnicity' => 'Sunda',
                    'current_address' => 'Jl. Dago No. 789, Bandung',
                    'current_address_status' => 'Milik Sendiri',
                    'ktp_address' => 'Jl. Dago No. 789, Bandung',
                    'height_cm' => 165,
                    'weight_kg' => 55,
                    'vaccination_status' => 'Vaksin 3'
                ],
                'driving_licenses' => ['B1'],
                'family_members' => [
                    ['relationship' => 'Pasangan', 'name' => 'David Christian', 'age' => 35, 'education' => 'S2', 'occupation' => 'Doctor'],
                    ['relationship' => 'Anak', 'name' => 'Michelle Sarah', 'age' => 5, 'education' => 'TK', 'occupation' => 'Pelajar']
                ],
                'formal_education' => [
                    ['education_level' => 'SMA/SMK', 'institution_name' => 'SMAN 3 Bandung', 'major' => 'IPS', 'start_year' => 2007, 'end_year' => 2010, 'gpa' => 9.0],
                    ['education_level' => 'S1', 'institution_name' => 'Universitas Padjadjaran', 'major' => 'Manajemen Pemasaran', 'start_year' => 2010, 'end_year' => 2014, 'gpa' => 3.65]
                ],
                'non_formal_education' => [
                    ['course_name' => 'Digital Marketing Strategy', 'organizer' => 'Google Digital Marketing', 'date' => '2019-03-10', 'description' => 'Strategi pemasaran digital dan analytics'],
                    ['course_name' => 'Leadership Development', 'organizer' => 'Dale Carnegie', 'date' => '2021-09-15', 'description' => 'Program pengembangan kepemimpinan']
                ],
                'language_skills' => [
                    ['language' => 'Indonesia', 'speaking_level' => 'Mahir', 'writing_level' => 'Mahir'],
                    ['language' => 'English', 'speaking_level' => 'Mahir', 'writing_level' => 'Mahir'],
                    ['language' => 'French', 'speaking_level' => 'Menengah', 'writing_level' => 'Pemula']
                ],
                'computer_skills' => [
                    'hardware_skills' => 'Basic PC Troubleshooting',
                    'software_skills' => 'Microsoft Office, Adobe Creative Suite, Google Analytics, Salesforce, HubSpot'
                ],
                'other_skills' => [
                    'other_skills' => 'Content Creation, Social Media Management, Brand Strategy, Event Planning'
                ],
                'achievements' => [
                    ['achievement' => 'Best Marketing Campaign 2023', 'year' => 2023, 'description' => 'Kampanye marketing terbaik tahun 2023 yang meningkatkan penjualan 150%'],
                    ['achievement' => 'Magna Cum Laude Graduate', 'year' => 2014, 'description' => 'Lulus dengan predikat Magna Cum Laude']
                ],
                'work_experiences' => [
                    [
                        'company_name' => 'PT. Brand Solutions',
                        'company_address' => 'Jl. Asia Afrika, Bandung',
                        'company_field' => 'Marketing & Advertising',
                        'position' => 'Marketing Executive',
                        'start_year' => 2015,
                        'end_year' => 2018,
                        'salary' => 6000000.00,
                        'reason_for_leaving' => 'Promotion opportunity',
                        'supervisor_contact' => '082567890123'
                    ],
                    [
                        'company_name' => 'PT. Creative Media',
                        'company_address' => 'Jl. Pasteur, Bandung',
                        'company_field' => 'Digital Marketing',
                        'position' => 'Senior Marketing Specialist',
                        'start_year' => 2018,
                        'end_year' => 2025,
                        'salary' => 10000000.00,
                        'reason_for_leaving' => 'Career growth',
                        'supervisor_contact' => '082678901234'
                    ]
                ],
                'social_activities' => [
                    ['organization_name' => 'Women in Business Bandung', 'field' => 'Professional', 'period' => '2020-Present', 'description' => 'Mentor untuk wanita pengusaha muda']
                ],
                'general_information' => [
                    'willing_to_travel' => true,
                    'has_vehicle' => true,
                    'vehicle_types' => 'Mobil',
                    'motivation' => 'Berkontribusi dalam membangun brand yang strong dan sustainable',
                    'strengths' => 'Creative thinking, data-driven decision making, excellent communication',
                    'weaknesses' => 'Kadang terlalu detail oriented',
                    'other_income' => null,
                    'has_police_record' => false,
                    'police_record_detail' => null,
                    'has_serious_illness' => false,
                    'illness_detail' => null,
                    'has_tattoo_piercing' => true,
                    'tattoo_piercing_detail' => 'Small tattoo on wrist',
                    'has_other_business' => false,
                    'other_business_detail' => null,
                    'absence_days' => 1,
                    'start_work_date' => '2025-08-01',
                    'information_source' => 'LinkedIn',
                    'agreement' => true
                ]
            ],
            [
                'candidate_code' => 'CND003',
                'position_id' => 3,
                'position_applied' => 'Accounting Staff',
                'expected_salary' => 5500000.00,
                'application_status' => 'screening',
                'application_date' => '2025-06-12',
                'personal_data' => [
                    'full_name' => 'Budi Santoso',
                    'email' => 'budi.santoso@email.com',
                    'phone_number' => '083456789012',
                    'phone_alternative' => null,
                    'birth_place' => 'Surabaya',
                    'birth_date' => '1990-12-05',
                    'gender' => 'Laki-laki',
                    'religion' => 'Islam',
                    'marital_status' => 'Menikah',
                    'ethnicity' => 'Jawa',
                    'current_address' => 'Jl. Pemuda No. 321, Surabaya',
                    'current_address_status' => 'Orang Tua',
                    'ktp_address' => 'Jl. Pemuda No. 321, Surabaya',
                    'height_cm' => 170,
                    'weight_kg' => 75,
                    'vaccination_status' => 'Vaksin 2'
                ],
                'driving_licenses' => ['A', 'B2'],
                'family_members' => [
                    ['relationship' => 'Pasangan', 'name' => 'Rina Sari', 'age' => 28, 'education' => 'S1', 'occupation' => 'Teacher'],
                    ['relationship' => 'Anak', 'name' => 'Arif Santoso', 'age' => 3, 'education' => 'Belum Sekolah', 'occupation' => 'Anak']
                ],
                'formal_education' => [
                    ['education_level' => 'SMA/SMK', 'institution_name' => 'SMK Negeri 1 Surabaya', 'major' => 'Akuntansi', 'start_year' => 2006, 'end_year' => 2009, 'gpa' => 8.2],
                    ['education_level' => 'Diploma', 'institution_name' => 'Politeknik Negeri Surabaya', 'major' => 'Akuntansi', 'start_year' => 2009, 'end_year' => 2012, 'gpa' => 3.4]
                ],
                'non_formal_education' => [
                    ['course_name' => 'Taxation Workshop', 'organizer' => 'IKPI Surabaya', 'date' => '2020-05-20', 'description' => 'Workshop perpajakan untuk UMKM']
                ],
                'language_skills' => [
                    ['language' => 'Indonesia', 'speaking_level' => 'Mahir', 'writing_level' => 'Mahir'],
                    ['language' => 'English', 'speaking_level' => 'Pemula', 'writing_level' => 'Pemula']
                ],
                'computer_skills' => [
                    'hardware_skills' => 'Basic PC Operation',
                    'software_skills' => 'Microsoft Excel, MYOB, Zahir Accounting, SAP'
                ],
                'other_skills' => [
                    'other_skills' => 'Financial Analysis, Tax Preparation, Audit Support'
                ],
                'achievements' => [
                    ['achievement' => 'Best Student Award', 'year' => 2012, 'description' => 'Mahasiswa terbaik jurusan Akuntansi']
                ],
                'work_experiences' => [
                    [
                        'company_name' => 'PT. Jaya Mandiri',
                        'company_address' => 'Jl. Tunjungan, Surabaya',
                        'company_field' => 'Manufacturing',
                        'position' => 'Junior Accountant',
                        'start_year' => 2013,
                        'end_year' => 2020,
                        'salary' => 4500000.00,
                        'reason_for_leaving' => 'Company downsizing',
                        'supervisor_contact' => '083789012345'
                    ]
                ],
                'social_activities' => [
                    ['organization_name' => 'Karang Taruna RT 05', 'field' => 'Community', 'period' => '2015-2018', 'description' => 'Bendahara karang taruna']
                ],
                'general_information' => [
                    'willing_to_travel' => false,
                    'has_vehicle' => true,
                    'vehicle_types' => 'Motor',
                    'motivation' => 'Ingin berkembang di bidang akuntansi dan keuangan',
                    'strengths' => 'Detail-oriented, honest, reliable',
                    'weaknesses' => 'Kurang percaya diri dalam public speaking',
                    'other_income' => 'Jasa pembukuan UMKM',
                    'has_police_record' => false,
                    'police_record_detail' => null,
                    'has_serious_illness' => false,
                    'illness_detail' => null,
                    'has_tattoo_piercing' => false,
                    'tattoo_piercing_detail' => null,
                    'has_other_business' => true,
                    'other_business_detail' => 'Jasa konsultasi pajak',
                    'absence_days' => 0,
                    'start_work_date' => '2025-07-15',
                    'information_source' => 'Referensi teman',
                    'agreement' => true
                ]
            ]
        ];

        // Insert data untuk setiap kandidat
        foreach ($candidates as $candidateData) {
            // Insert candidate dengan personal data (merged table structure)
            $candidateId = DB::table('candidates')->insertGetId([
                'candidate_code' => $candidateData['candidate_code'],
                'position_id' => $candidateData['position_id'],
                'position_applied' => $candidateData['position_applied'],
                'expected_salary' => $candidateData['expected_salary'],
                'application_status' => $candidateData['application_status'],
                'application_date' => $candidateData['application_date'],
                // Personal data merged into candidates table
                'nik' => $candidateData['nik'],
                'full_name' => $candidateData['personal_data']['full_name'],
                'email' => $candidateData['personal_data']['email'],
                'phone_number' => $candidateData['personal_data']['phone_number'],
                'phone_alternative' => $candidateData['personal_data']['phone_alternative'],
                'birth_place' => $candidateData['personal_data']['birth_place'],
                'birth_date' => $candidateData['personal_data']['birth_date'],
                'gender' => $candidateData['personal_data']['gender'],
                'religion' => $candidateData['personal_data']['religion'],
                'marital_status' => $candidateData['personal_data']['marital_status'],
                'ethnicity' => $candidateData['personal_data']['ethnicity'],
                'current_address' => $candidateData['personal_data']['current_address'],
                'current_address_status' => $candidateData['personal_data']['current_address_status'],
                'ktp_address' => $candidateData['personal_data']['ktp_address'],
                'height_cm' => $candidateData['personal_data']['height_cm'],
                'weight_kg' => $candidateData['personal_data']['weight_kg'],
                'vaccination_status' => $candidateData['personal_data']['vaccination_status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert driving licenses
            foreach ($candidateData['driving_licenses'] as $license) {
                DB::table('driving_licenses')->insert([
                    'candidate_id' => $candidateId,
                    'license_type' => $license,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert family members
            foreach ($candidateData['family_members'] as $family) {
                DB::table('family_members')->insert(array_merge(
                    $family,
                    [
                        'candidate_id' => $candidateId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ));
            }

            // Insert formal education
            foreach ($candidateData['formal_education'] as $education) {
                DB::table('education')->insert([
                    'candidate_id' => $candidateId,
                    'education_type' => 'formal',
                    'education_level' => $education['education_level'],
                    'institution_name' => $education['institution_name'],
                    'major' => $education['major'],
                    'start_year' => $education['start_year'],
                    'end_year' => $education['end_year'],
                    'gpa' => $education['gpa'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert non formal education
            foreach ($candidateData['non_formal_education'] as $nonFormal) {
                DB::table('education')->insert([
                    'candidate_id' => $candidateId,
                    'education_type' => 'non_formal',
                    'course_name' => $nonFormal['course_name'],
                    'organizer' => $nonFormal['organizer'],
                    'date' => $nonFormal['date'],
                    'description' => $nonFormal['description'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert language skills
            foreach ($candidateData['language_skills'] as $language) {
                DB::table('language_skills')->insert(array_merge(
                    $language,
                    [
                        'candidate_id' => $candidateId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ));
            }

            // Insert candidate additional info (merged table)
            DB::table('candidate_additional_info')->insert(array_merge(
                $candidateData['additional_info'],
                [
                    'candidate_id' => $candidateId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ));

            // Insert achievements as activities
            foreach ($candidateData['achievements'] as $achievement) {
                DB::table('activities')->insert([
                    'candidate_id' => $candidateId,
                    'activity_type' => 'achievement',
                    'title' => $achievement['achievement'],
                    'field_or_year' => $achievement['year'],
                    'description' => $achievement['description'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert work experiences
            foreach ($candidateData['work_experiences'] as $work) {
                DB::table('work_experiences')->insert(array_merge(
                    $work,
                    [
                        'candidate_id' => $candidateId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ));
            }

            // Insert social activities as activities
            foreach ($candidateData['social_activities'] as $social) {
                DB::table('activities')->insert([
                    'candidate_id' => $candidateId,
                    'activity_type' => 'social_activity',
                    'title' => $social['organization_name'],
                    'field_or_year' => $social['field'],
                    'period' => $social['period'],
                    'description' => $social['description'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert sample document uploads
            $documentTypes = ['cv', 'photo', 'certificates'];
            foreach ($documentTypes as $docType) {
                DB::table('document_uploads')->insert([
                    'candidate_id' => $candidateId,
                    'document_type' => $docType,
                    'document_name' => $docType . '_' . $candidateData['candidate_code'],
                    'original_filename' => $docType . '_' . strtolower(str_replace(' ', '_', $candidateData['personal_data']['full_name'])) . '.pdf',
                    'file_path' => 'uploads/candidates/' . $candidateId . '/' . $docType . '.pdf',
                    'file_size' => rand(100000, 2000000),
                    'mime_type' => $docType == 'photo' ? 'image/jpeg' : 'application/pdf',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert sample application log
            DB::table('application_logs')->insert([
                'candidate_id' => $candidateId,
                'user_id' => 1, // Assuming admin user exists
                'action_type' => 'status_change',
                'action_description' => 'Application status changed to ' . $candidateData['application_status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert sample interview if status is interview or beyond
            if (in_array($candidateData['application_status'], ['interview', 'offered', 'accepted'])) {
                DB::table('interviews')->insert([
                    'candidate_id' => $candidateId,
                    'interview_date' => Carbon::parse($candidateData['application_date'])->addDays(7),
                    'interview_time' => '10:00:00',
                    'location' => 'Meeting Room A',
                    'interviewer_id' => 1, // Assuming interviewer user exists
                    'status' => $candidateData['application_status'] == 'interview' ? 'scheduled' : 'completed',
                    'notes' => 'Initial interview assessment',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Insert email templates
        $emailTemplates = [
            [
                'template_name' => 'Application Received',
                'subject' => 'Terima kasih atas aplikasi Anda',
                'body' => 'Terima kasih telah melamar di perusahaan kami. Aplikasi Anda sedang dalam proses review.',
                'template_type' => 'application_received',
                'is_active' => true
            ],
            [
                'template_name' => 'Interview Invitation',
                'subject' => 'Undangan Interview - {{position}}',
                'body' => 'Selamat! Anda diundang untuk interview pada {{date}} di {{location}}.',
                'template_type' => 'interview_invitation',
                'is_active' => true
            ],
            [
                'template_name' => 'Job Offer',
                'subject' => 'Selamat! Anda Diterima',
                'body' => 'Selamat! Anda diterima untuk posisi {{position}}. Silakan konfirmasi penerimaan Anda.',
                'template_type' => 'acceptance',
                'is_active' => true
            ],
            [
                'template_name' => 'Application Rejection',
                'subject' => 'Update Status Aplikasi',
                'body' => 'Terima kasih atas minat Anda. Saat ini kami memutuskan untuk melanjutkan dengan kandidat lain.',
                'template_type' => 'rejection',
                'is_active' => true
            ]
        ];

        foreach ($emailTemplates as $template) {
            DB::table('email_templates')->insert(array_merge(
                $template,
                [
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ));
        }

        $this->command->info('Candidate seeder completed successfully!');
    }
}