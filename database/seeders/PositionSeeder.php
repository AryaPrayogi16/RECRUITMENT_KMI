<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            [
                'position_name' => 'Frontend Developer',
                'department' => 'IT',
                'description' => 'Mengembangkan antarmuka pengguna yang menarik dan responsif menggunakan teknologi modern seperti React, Vue, atau Angular.',
                'requirements' => 'Minimal S1 Teknik Informatika, pengalaman 2+ tahun dengan JavaScript, HTML, CSS, framework modern',
                'salary_range_min' => 8000000,
                'salary_range_max' => 15000000,
                'is_active' => true,
            ],
            [
                'position_name' => 'Backend Developer',
                'department' => 'IT',
                'description' => 'Mengembangkan sistem backend yang robust dan scalable menggunakan PHP Laravel, Node.js, atau Python.',
                'requirements' => 'Minimal S1 Teknik Informatika, pengalaman 3+ tahun dengan PHP/Laravel, database design, API development',
                'salary_range_min' => 10000000,
                'salary_range_max' => 18000000,
                'is_active' => true,
            ],
            [
                'position_name' => 'UI/UX Designer',
                'department' => 'Design',
                'description' => 'Merancang pengalaman pengguna yang intuitif dan desain antarmuka yang menarik untuk produk digital.',
                'requirements' => 'Minimal S1 Desain Grafis/DKV, portfolio yang kuat, mahir Figma/Adobe XD, pemahaman user research',
                'salary_range_min' => 7000000,
                'salary_range_max' => 12000000,
                'is_active' => true,
            ],
            [
                'position_name' => 'Marketing Manager',
                'department' => 'Marketing',
                'description' => 'Mengembangkan dan mengeksekusi strategi pemasaran untuk meningkatkan brand awareness dan penjualan.',
                'requirements' => 'Minimal S1 Marketing/Bisnis, pengalaman 4+ tahun di digital marketing, leadership skills, data analysis',
                'salary_range_min' => 12000000,
                'salary_range_max' => 20000000,
                'is_active' => true,
            ],
            [
                'position_name' => 'Sales Executive',
                'department' => 'Sales',
                'description' => 'Melakukan penjualan produk/jasa perusahaan dan membangun hubungan baik dengan client.',
                'requirements' => 'Minimal S1 semua jurusan, pengalaman sales 2+ tahun, komunikasi excellent, target oriented',
                'salary_range_min' => 6000000,
                'salary_range_max' => 12000000,
                'is_active' => true,
            ],
            [
                'position_name' => 'HR Specialist',
                'department' => 'HR',
                'description' => 'Mengelola proses recruitment, employee relations, dan pengembangan SDM perusahaan.',
                'requirements' => 'Minimal S1 Psikologi/HR, pengalaman HR 2+ tahun, memahami labor law, sertifikasi HR advantage',
                'salary_range_min' => 7000000,
                'salary_range_max' => 13000000,
                'is_active' => true,
            ],
            [
                'position_name' => 'Accountant',
                'department' => 'Finance',
                'description' => 'Mengelola pembukuan, laporan keuangan, dan memastikan compliance terhadap regulasi akuntansi.',
                'requirements' => 'Minimal S1 Akuntansi, pengalaman 2+ tahun, mahir software akuntansi, detail oriented',
                'salary_range_min' => 6500000,
                'salary_range_max' => 11000000,
                'is_active' => true,
            ],
            [
                'position_name' => 'Project Manager',
                'department' => 'Operations',
                'description' => 'Memimpin dan mengelola proyek dari tahap planning hingga delivery dengan timeline dan budget yang tepat.',
                'requirements' => 'Minimal S1 semua jurusan, pengalaman project management 3+ tahun, PMP certification preferred',
                'salary_range_min' => 13000000,
                'salary_range_max' => 22000000,
                'is_active' => true,
            ],
            [
                'position_name' => 'Data Analyst',
                'department' => 'IT',
                'description' => 'Menganalisis data bisnis untuk memberikan insights dan rekomendasi strategic kepada management.',
                'requirements' => 'Minimal S1 Statistik/Matematika/IT, pengalaman data analysis 2+ tahun, mahir SQL, Python/R, Tableau',
                'salary_range_min' => 9000000,
                'salary_range_max' => 16000000,
                'is_active' => true,
            ],
            [
                'position_name' => 'Content Writer',
                'department' => 'Marketing',
                'description' => 'Membuat konten menarik untuk berbagai platform digital dan material marketing perusahaan.',
                'requirements' => 'Minimal S1 Komunikasi/Sastra, portfolio writing yang kuat, SEO knowledge, creative thinking',
                'salary_range_min' => 5500000,
                'salary_range_max' => 9000000,
                'is_active' => true,
            ],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
