<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'template_name' => 'Application Received',
                'subject' => 'Terima kasih atas lamaran Anda - {{position_name}}',
                'body' => 'Halo {{candidate_name}},

Terima kasih telah melamar posisi {{position_name}} di perusahaan kami.

Kami telah menerima lamaran Anda dan akan segera meninjau aplikasi tersebut. Tim HR kami akan menghubungi Anda dalam 3-5 hari kerja jika profil Anda sesuai dengan persyaratan yang kami cari.

Hormat kami,
Tim HR',
                'template_type' => 'application_received',
                'is_active' => true,
            ],
            [
                'template_name' => 'Interview Invitation',
                'subject' => 'Undangan Interview - {{position_name}}',
                'body' => 'Halo {{candidate_name}},

Selamat! Kami tertarik dengan profil Anda dan ingin mengundang Anda untuk interview posisi {{position_name}}.

Detail Interview:
- Tanggal: {{interview_date}}
- Waktu: {{interview_time}}
- Lokasi: {{interview_location}}
- Interviewer: {{interviewer_name}}

Mohon konfirmasi kehadiran Anda.

Hormat kami,
Tim HR',
                'template_type' => 'interview_invitation',
                'is_active' => true,
            ],
            [
                'template_name' => 'Job Offer',
                'subject' => 'Selamat! Penawaran Pekerjaan - {{position_name}}',
                'body' => 'Halo {{candidate_name}},

Selamat! Kami dengan senang hati menawarkan posisi {{position_name}} kepada Anda.

Detail Penawaran:
- Posisi: {{position_name}}
- Gaji: {{salary_offer}}
- Tanggal Mulai: {{start_date}}

Silakan hubungi kami untuk membahas detail lebih lanjut.

Hormat kami,
Tim HR',
                'template_type' => 'acceptance',
                'is_active' => true,
            ],
            [
                'template_name' => 'Application Rejection',
                'subject' => 'Update Status Lamaran - {{position_name}}',
                'body' => 'Halo {{candidate_name}},

Terima kasih atas minat Anda untuk bergabung dengan perusahaan kami untuk posisi {{position_name}}.

Setelah melalui proses seleksi yang ketat, kami memutuskan untuk melanjutkan dengan kandidat lain yang lebih sesuai dengan kebutuhan saat ini.

Kami menghargai waktu dan usaha yang Anda berikan. Semoga sukses untuk pencarian kerja selanjutnya.

Hormat kami,
Tim HR',
                'template_type' => 'rejection',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }
}
