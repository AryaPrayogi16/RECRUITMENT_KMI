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
        // ✅ FIXED: Data sample kandidat dengan NIK 16 digit
        $candidates = [
            [
                'candidate_code' => 'CND202501001',
                'position_id' => 1, // Driver
                'position_applied' => 'Driver',
                'expected_salary' => 3500000.00,
                'application_status' => 'submitted',
                'application_date' => '2025-01-10',
                // ✅ FIXED: NIK 16 digit (bukan 17-18 digit)
                'nik' => '3515041995031501', // 16 digit exact
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
                    ['course_name' => 'Kursus Mengemudi Profesional', 'organizer' => 'LPK Mengemudi Jakarta', 'date' => '2020-01-15', 'description' => 'Kursus mengemudi kendaraan berat dan teknik defensive driving'],
                ],
                'language_skills' => [
                    ['language' => 'Indonesia', 'speaking_level' => 'Mahir', 'writing_level' => 'Mahir'],
                    ['language' => 'English', 'speaking_level' => 'Pemula', 'writing_level' => 'Pemula'],
                ],
                'computer_skills' => [
                    'hardware_skills' => 'Basic computer operation, GPS navigation',
                    'software_skills' => 'WhatsApp, Google Maps, Basic Android'
                ],
                'other_skills' => [
                    'other_skills' => 'Mekanik dasar, Perawatan kendaraan, Customer service'
                ],
                'achievements' => [
                    ['achievement' => 'Driver Terbaik Tahun 2023', 'year' => 2023, 'description' => 'Penghargaan driver terbaik dengan zero accident selama 2 tahun'],
                ],
                'work_experiences' => [
                    [
                        'company_name' => 'PT. Logistik Jaya',
                        'company_address' => 'Jl. HR Rasuna Said, Jakarta',
                        'company_field' => 'Logistics',
                        'position' => 'Driver',
                        'start_year' => 2018,
                        'end_year' => 2024,
                        'salary' => 3000000.00,
                        'reason_for_leaving' => 'Seeking better opportunity',
                        'supervisor_contact' => '081345678901'
                    ]
                ],
                'social_activities' => [
                    ['organization_name' => 'Paguyuban Driver Jakarta', 'field' => 'Community', 'period' => '2019-Present', 'description' => 'Anggota aktif paguyuban driver'],
                ],
                'general_information' => [
                    'willing_to_travel' => true,
                    'has_vehicle' => true,
                    'vehicle_types' => 'Motor, Mobil',
                    'motivation' => 'Ingin menjadi driver profesional yang dapat diandalkan',
                    'strengths' => 'Jujur, tepat waktu, hati-hati dalam mengemudi',
                    'weaknesses' => 'Kurang lancar bahasa Inggris',
                    'other_income' => 'Driver online part time',
                    'has_police_record' => false,
                    'police_record_detail' => null,
                    'has_serious_illness' => false,
                    'illness_detail' => null,
                    'has_tattoo_piercing' => false,
                    'tattoo_piercing_detail' => null,
                    'has_other_business' => false,
                    'other_business_detail' => null,
                    'absence_days' => 2,
                    'start_work_date' => '2025-02-01',
                    'information_source' => 'Website perusahaan',
                    'agreement' => true
                ]
            ],
            [
                'candidate_code' => 'CND202501002',
                'position_id' => 2, // IT Support
                'position_applied' => 'IT Support',
                'expected_salary' => 5500000.00,
                'application_status' => 'interview',
                'application_date' => '2025-01-08',
                // ✅ FIXED: NIK 16 digit exact
                'nik' => '3515041992082202',
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
                    ['education_level' => 'SMA/SMK', 'institution_name' => 'SMAN 3 Bandung', 'major' => 'IPA', 'start_year' => 2007, 'end_year' => 2010, 'gpa' => 9.0],
                    ['education_level' => 'S1', 'institution_name' => 'Institut Teknologi Bandung', 'major' => 'Teknik Informatika', 'start_year' => 2010, 'end_year' => 2014, 'gpa' => 3.65]
                ],
                'non_formal_education' => [
                    ['course_name' => 'Network Administration', 'organizer' => 'Cisco Academy', 'date' => '2019-03-10', 'description' => 'Sertifikasi CCNA dan network troubleshooting'],
                    ['course_name' => 'Cloud Computing AWS', 'organizer' => 'Amazon Web Services', 'date' => '2021-09-15', 'description' => 'AWS Cloud Practitioner certification']
                ],
                'language_skills' => [
                    ['language' => 'Indonesia', 'speaking_level' => 'Mahir', 'writing_level' => 'Mahir'],
                    ['language' => 'English', 'speaking_level' => 'Mahir', 'writing_level' => 'Mahir'],
                ],
                'computer_skills' => [
                    'hardware_skills' => 'PC Assembly, Laptop Repair, Network Installation, Server Maintenance',
                    'software_skills' => 'Windows Server, Linux, VMware, Active Directory, Network Configuration'
                ],
                'other_skills' => [
                    'other_skills' => 'IT Project Management, Help Desk Support, System Documentation'
                ],
                'achievements' => [
                    ['achievement' => 'Best IT Support 2023', 'year' => 2023, 'description' => 'IT Support terbaik dengan customer satisfaction 98%'],
                    ['achievement' => 'Cisco CCNA Certified', 'year' => 2019, 'description' => 'Sertifikasi Cisco Certified Network Associate']
                ],
                'work_experiences' => [
                    [
                        'company_name' => 'PT. Tech Solutions',
                        'company_address' => 'Jl. Asia Afrika, Bandung',
                        'company_field' => 'Information Technology',
                        'position' => 'IT Support Specialist',
                        'start_year' => 2015,
                        'end_year' => 2024,
                        'salary' => 5000000.00,
                        'reason_for_leaving' => 'Career advancement',
                        'supervisor_contact' => '082567890123'
                    ]
                ],
                'social_activities' => [
                    ['organization_name' => 'IT Women Community Bandung', 'field' => 'Technology', 'period' => '2020-Present', 'description' => 'Mentor untuk perempuan di bidang IT']
                ],
                'general_information' => [
                    'willing_to_travel' => true,
                    'has_vehicle' => true,
                    'vehicle_types' => 'Mobil',
                    'motivation' => 'Berkontribusi dalam pengembangan sistem IT yang reliable',
                    'strengths' => 'Problem solving, patient, excellent communication',
                    'weaknesses' => 'Terkadang terlalu perfeksionis',
                    'other_income' => null,
                    'has_police_record' => false,
                    'police_record_detail' => null,
                    'has_serious_illness' => false,
                    'illness_detail' => null,
                    'has_tattoo_piercing' => false,
                    'tattoo_piercing_detail' => null,
                    'has_other_business' => false,
                    'other_business_detail' => null,
                    'absence_days' => 1,
                    'start_work_date' => '2025-02-15',
                    'information_source' => 'LinkedIn',
                    'agreement' => true
                ]
            ],
            [
                'candidate_code' => 'CND202501003',
                'position_id' => 11, // Staff Accounting
                'position_applied' => 'Staff Accounting',
                'expected_salary' => 4500000.00,
                'application_status' => 'screening',
                'application_date' => '2025-01-12',
                // ✅ FIXED: NIK 16 digit exact
                'nik' => '3515041990120503',
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
                        'salary' => 3500000.00,
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
                    'start_work_date' => '2025-02-01',
                    'information_source' => 'Referensi teman',
                    'agreement' => true
                ]
            ]
        ];

        // ✅ Insert data untuk setiap kandidat
        foreach ($candidates as $candidateData) {
            try {
                DB::beginTransaction();

                // ✅ Validasi NIK length
                if (strlen($candidateData['nik']) !== 16) {
                    throw new \Exception("NIK harus 16 digit, got: " . strlen($candidateData['nik']) . " digits");
                }

                $candidateId = DB::table('candidates')->insertGetId([
                    'candidate_code' => $candidateData['candidate_code'],
                    'position_id' => $candidateData['position_id'],
                    'position_name_snapshot' => $candidateData['position_applied'],
                    'position_applied' => $candidateData['position_applied'],
                    'expected_salary' => $candidateData['expected_salary'],
                    'application_status' => $candidateData['application_status'],
                    'application_date' => $candidateData['application_date'],
                    'nik' => $candidateData['nik'], // ✅ FIXED: 16 digit NIK
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

                // ✅ UPDATED: Insert formal education using new table
                foreach ($candidateData['formal_education'] as $education) {
                    DB::table('formal_education')->insert([
                        'candidate_id' => $candidateId,
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

                // ✅ UPDATED: Insert non formal education using new table
                foreach ($candidateData['non_formal_education'] as $nonFormal) {
                    DB::table('non_formal_education')->insert([
                        'candidate_id' => $candidateId,
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

                // Insert candidate additional info
                DB::table('candidate_additional_info')->insert(array_merge(
                    $candidateData['computer_skills'],
                    $candidateData['other_skills'],
                    $candidateData['general_information'],
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

                // ✅ UPDATED: Insert sample document uploads with KTP included
                $documentTypes = ['cv', 'photo', 'ktp', 'certificates', 'transcript']; // Added KTP and transcript
                foreach ($documentTypes as $docType) {
                    DB::table('document_uploads')->insert([
                        'candidate_id' => $candidateId,
                        'document_type' => $docType,
                        'document_name' => $docType . '_' . $candidateData['candidate_code'],
                        'original_filename' => $docType . '_' . strtolower(str_replace(' ', '_', $candidateData['personal_data']['full_name'])) . ($docType == 'photo' ? '.jpg' : '.pdf'),
                        'file_path' => 'uploads/candidates/' . $candidateId . '/' . $docType . ($docType == 'photo' ? '.jpg' : '.pdf'),
                        'file_size' => rand(100000, 2000000),
                        'mime_type' => $docType == 'photo' ? 'image/jpeg' : 'application/pdf',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Insert sample application log
                DB::table('application_logs')->insert([
                    'candidate_id' => $candidateId,
                    'user_id' => 1, // Admin user
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
                        'interviewer_id' => 3, // Interviewer user
                        'status' => $candidateData['application_status'] == 'interview' ? 'scheduled' : 'completed',
                        'notes' => 'Initial interview assessment',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // ✅ NEW: Insert sample test sessions and results
                $this->insertTestSessions($candidateId, $candidateData);

                DB::commit();
                $this->command->info("✅ Candidate {$candidateData['candidate_code']} seeded successfully! (NIK: {$candidateData['nik']})");

            } catch (\Exception $e) {
                DB::rollback();
                $this->command->error("❌ Error seeding candidate {$candidateData['candidate_code']}: " . $e->getMessage());
            }
        }

        // Insert email templates
        $this->insertEmailTemplates();

        $this->command->info('✅ All candidates and email templates seeded successfully!');
    }

    /**
     * ✅ NEW: Insert test sessions and results for candidates
     */
    private function insertTestSessions($candidateId, $candidateData)
    {
        try {
            // Insert Kraeplin test session for first candidate (completed)
            if ($candidateData['candidate_code'] === 'CND202501001') {
                $kraeplinSessionId = DB::table('kraeplin_test_sessions')->insertGetId([
                    'candidate_id' => $candidateId,
                    'session_token' => 'KRAEPLIN_' . $candidateData['candidate_code'] . '_' . time(),
                    'status' => 'completed',
                    'started_at' => now()->subDays(2),
                    'completed_at' => now()->subDays(2)->addMinutes(30),
                    'expires_at' => now()->addDays(7),
                    'total_questions' => 100,
                    'answered_questions' => 100,
                    'answers' => json_encode(array_fill(0, 100, rand(1, 4))),
                    'correct_answers' => 75,
                    'score' => 75,
                    'notes' => 'Good performance with consistent accuracy',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Insert DISC 3D test session (completed)
                $discSessionId = DB::table('disc3d_test_sessions')->insertGetId([
                    'candidate_id' => $candidateId,
                    'session_token' => 'DISC3D_' . $candidateData['candidate_code'] . '_' . time(),
                    'status' => 'completed',
                    'started_at' => now()->subDays(1),
                    'completed_at' => now()->subDays(1)->addMinutes(20),
                    'expires_at' => now()->addDays(7),
                    'current_question' => 24,
                    'total_questions' => 24,
                    'sections_completed' => 24,
                    'progress' => 100,
                    'answers' => json_encode($this->generateSampleDiscAnswers()),
                    'notes' => 'Test completed successfully',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Insert DISC 3D result
                DB::table('disc3d_results')->insert([
                    'candidate_id' => $candidateId,
                    'test_session_id' => $discSessionId,
                    'd_score' => 18,
                    'i_score' => 22,
                    's_score' => 16,
                    'c_score' => 14,
                    'primary_type' => 'I',
                    'secondary_type' => 'D',
                    'personality_profile' => json_encode([
                        'description' => 'Influence-Dominance type',
                        'characteristics' => ['Outgoing', 'Enthusiastic', 'Results-oriented', 'People-focused'],
                        'strengths' => ['Great communicator', 'Natural leader', 'Motivates others'],
                        'challenges' => ['May be impatient', 'Can be overly optimistic']
                    ]),
                    'summary' => 'Strong people-oriented leader with excellent communication skills',
                    'strengths' => 'Natural ability to influence and motivate others, results-driven approach',
                    'challenges' => 'May need to work on patience and attention to detail',
                    'ideal_environment' => 'Dynamic team environment with opportunities for leadership and people interaction',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert Kraeplin test session for second candidate (completed)
            if ($candidateData['candidate_code'] === 'CND202501002') {
                $kraeplinSessionId = DB::table('kraeplin_test_sessions')->insertGetId([
                    'candidate_id' => $candidateId,
                    'session_token' => 'KRAEPLIN_' . $candidateData['candidate_code'] . '_' . time(),
                    'status' => 'completed',
                    'started_at' => now()->subDays(3),
                    'completed_at' => now()->subDays(3)->addMinutes(28),
                    'expires_at' => now()->addDays(7),
                    'total_questions' => 100,
                    'answered_questions' => 100,
                    'answers' => json_encode(array_fill(0, 100, rand(1, 4))),
                    'correct_answers' => 88,
                    'score' => 88,
                    'notes' => 'Excellent accuracy and speed',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Insert DISC 3D test session (in progress)
                DB::table('disc3d_test_sessions')->insert([
                    'candidate_id' => $candidateId,
                    'session_token' => 'DISC3D_' . $candidateData['candidate_code'] . '_' . time(),
                    'status' => 'in_progress',
                    'started_at' => now()->subHours(2),
                    'completed_at' => null,
                    'expires_at' => now()->addDays(7),
                    'current_question' => 15,
                    'total_questions' => 24,
                    'sections_completed' => 15,
                    'progress' => 62,
                    'answers' => json_encode(array_slice($this->generateSampleDiscAnswers(), 0, 15)),
                    'notes' => 'Test in progress',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insert Kraeplin test session for third candidate (not started)
            if ($candidateData['candidate_code'] === 'CND202501003') {
                DB::table('kraeplin_test_sessions')->insert([
                    'candidate_id' => $candidateId,
                    'session_token' => 'KRAEPLIN_' . $candidateData['candidate_code'] . '_' . time(),
                    'status' => 'not_started',
                    'started_at' => null,
                    'completed_at' => null,
                    'expires_at' => now()->addDays(7),
                    'total_questions' => 100,
                    'answered_questions' => 0,
                    'answers' => null,
                    'correct_answers' => null,
                    'score' => null,
                    'notes' => 'Awaiting test start',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

        } catch (\Exception $e) {
            $this->command->warn("Warning: Could not insert test sessions for candidate {$candidateId}: " . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Generate sample DISC answers
     */
    private function generateSampleDiscAnswers()
    {
        $answers = [];
        
        // Generate 24 DISC responses (4 choices per section, ranked 1-4)
        for ($i = 1; $i <= 24; $i++) {
            $section = [
                'section_id' => $i,
                'choices' => [
                    'D' => rand(1, 4),  // Dominance
                    'I' => rand(1, 4),  // Influence
                    'S' => rand(1, 4),  // Steadiness
                    'C' => rand(1, 4)   // Compliance
                ]
            ];
            
            // Ensure no duplicate rankings within a section
            $rankings = [1, 2, 3, 4];
            shuffle($rankings);
            $dimensions = ['D', 'I', 'S', 'C'];
            
            foreach ($dimensions as $index => $dimension) {
                $section['choices'][$dimension] = $rankings[$index];
            }
            
            $answers[] = $section;
        }
        
        return $answers;
    }

    /**
     * ✅ Insert email templates
     */
    private function insertEmailTemplates()
    {
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
    }
    }

    /**
     * ✅ NEW: Insert test sessions and results for candidates
     */
    