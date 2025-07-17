<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Disc3DSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();

            Log::info('🔄 Starting DISC 3D data seeding...');

            $this->seedDisc3DSections();
            Log::info('✅ Sections seeded');

            $this->seedDisc3DSectionChoices();
            Log::info('✅ Section choices seeded');

            $this->seedDisc3DProfileInterpretations();
            Log::info('✅ Profile interpretations seeded');

            $this->seedDisc3DPatternCombinations();
            Log::info('✅ Pattern combinations seeded');

            $this->seedDisc3DConfig();
            Log::info('✅ Configuration seeded');

            $this->verifyDataIntegrity();
            Log::info('✅ Data integrity verified');

            DB::commit();

            $this->command->info('🎉 DISC 3D seeding completed successfully!');

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('❌ DISC 3D seeding failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->command->error('❌ DISC 3D seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Seed 24 DISC 3D sections
     */
    private function seedDisc3DSections(): void
    {
        $sections = [];
        
        for ($i = 1; $i <= 24; $i++) {
            $sections[] = [
                'section_number' => $i,
                'section_code' => sprintf('SEC%02d', $i),
                'section_title' => "Section {$i}",
                'is_active' => true,
                'order_number' => $i,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        DB::table('disc_3d_sections')->insert($sections);
    }

    /**
     * Seed DISC 3D section choices with detailed weights
     */
    private function seedDisc3DSectionChoices(): void
    {
        // Get actual section IDs from database
        $sections = DB::table('disc_3d_sections')->select('id', 'section_number')->get();
        $sectionIdMap = $sections->pluck('id', 'section_number')->toArray();

        $choices = [
            // SECTION 1
            ['section' => 1, 'dim' => 'D', 'text' => 'Toleran, Menghormati', 
             'weights' => ['D' => 0.4521, 'I' => -0.1234, 'S' => 0.6789, 'C' => 0.2345]],
            ['section' => 1, 'dim' => 'I', 'text' => 'Gampangan, Mudah setuju', 
             'weights' => ['D' => -0.3456, 'I' => 0.7890, 'S' => 0.1234, 'C' => -0.2345]],
            ['section' => 1, 'dim' => 'S', 'text' => 'Percaya, Mudah percaya pada orang', 
             'weights' => ['D' => -0.5678, 'I' => 0.2345, 'S' => 0.8901, 'C' => -0.1234]],
            ['section' => 1, 'dim' => 'C', 'text' => 'Petualang, Mengambil resiko', 
             'weights' => ['D' => 0.6789, 'I' => 0.3456, 'S' => -0.7890, 'C' => -0.4567]],
            
            // SECTION 2
            ['section' => 2, 'dim' => 'D', 'text' => 'Pusat Perhatian, Suka gaul', 
             'weights' => ['D' => 0.5432, 'I' => 0.8765, 'S' => -0.3210, 'C' => -0.6543]],
            ['section' => 2, 'dim' => 'I', 'text' => 'Optimistik, Visioner', 
             'weights' => ['D' => 0.1234, 'I' => 0.9012, 'S' => 0.2345, 'C' => -0.3456]],
            ['section' => 2, 'dim' => 'S', 'text' => 'Lembut suara, Pendiam', 
             'weights' => ['D' => -0.6789, 'I' => -0.5432, 'S' => 0.7654, 'C' => 0.4321]],
            ['section' => 2, 'dim' => 'C', 'text' => 'Pendamai, Membawa Harmoni', 
             'weights' => ['D' => -0.4567, 'I' => 0.1234, 'S' => 0.6789, 'C' => 0.5678]],
            
            // SECTION 3
            ['section' => 3, 'dim' => 'D', 'text' => 'Ingin membuat tujuan', 
             'weights' => ['D' => 0.8765, 'I' => -0.1234, 'S' => -0.6543, 'C' => 0.3456]],
            ['section' => 3, 'dim' => 'I', 'text' => 'Menyemangati orang', 
             'weights' => ['D' => 0.2345, 'I' => 0.8901, 'S' => 0.1234, 'C' => -0.4567]],
            ['section' => 3, 'dim' => 'S', 'text' => 'Bagian dari kelompok', 
             'weights' => ['D' => -0.5678, 'I' => 0.3456, 'S' => 0.8765, 'C' => 0.1234]],
            ['section' => 3, 'dim' => 'C', 'text' => 'Berusaha sempurna', 
             'weights' => ['D' => -0.2345, 'I' => -0.4567, 'S' => -0.1234, 'C' => 0.9123]],
            
            // SECTION 4
            ['section' => 4, 'dim' => 'D', 'text' => 'Siap beroposisi', 
             'weights' => ['D' => 0.9234, 'I' => -0.3456, 'S' => -0.7890, 'C' => -0.2345]],
            ['section' => 4, 'dim' => 'I', 'text' => 'Menceritakan sisi saya', 
             'weights' => ['D' => 0.1234, 'I' => 0.7654, 'S' => -0.2345, 'C' => -0.3456]],
            ['section' => 4, 'dim' => 'S', 'text' => 'Menyimpan perasaan saya', 
             'weights' => ['D' => -0.6789, 'I' => -0.4567, 'S' => 0.8901, 'C' => 0.5678]],
            ['section' => 4, 'dim' => 'C', 'text' => 'Menjadi frustrasi', 
             'weights' => ['D' => 0.3456, 'I' => -0.2345, 'S' => -0.5678, 'C' => 0.4567]],
            
            // SECTION 5
            ['section' => 5, 'dim' => 'D', 'text' => 'Gerak cepat, Tekun', 
             'weights' => ['D' => 0.8234, 'I' => 0.1234, 'S' => -0.6789, 'C' => 0.2345]],
            ['section' => 5, 'dim' => 'I', 'text' => 'Hidup, Suka bicara', 
             'weights' => ['D' => 0.2345, 'I' => 0.9345, 'S' => -0.3456, 'C' => -0.5678]],
            ['section' => 5, 'dim' => 'S', 'text' => 'Usaha menjaga keseimbangan', 
             'weights' => ['D' => -0.4567, 'I' => -0.1234, 'S' => 0.8456, 'C' => 0.3456]],
            ['section' => 5, 'dim' => 'C', 'text' => 'Usaha mengikuti aturan', 
             'weights' => ['D' => -0.3456, 'I' => -0.5678, 'S' => 0.2345, 'C' => 0.8567]],
            
            // SECTION 6
            ['section' => 6, 'dim' => 'D', 'text' => 'Kelola waktu secara efisien', 
             'weights' => ['D' => 0.7456, 'I' => -0.2345, 'S' => -0.3456, 'C' => 0.6789]],
            ['section' => 6, 'dim' => 'I', 'text' => 'Masalah sosial itu penting', 
             'weights' => ['D' => -0.1234, 'I' => 0.8234, 'S' => 0.3456, 'C' => -0.2345]],
            ['section' => 6, 'dim' => 'S', 'text' => 'Suka selesaikan apa yang saya mulai', 
             'weights' => ['D' => 0.2345, 'I' => -0.3456, 'S' => 0.8678, 'C' => 0.4567]],
            ['section' => 6, 'dim' => 'C', 'text' => 'Sering terburu-buru, Merasa tertekan', 
             'weights' => ['D' => 0.5678, 'I' => 0.2345, 'S' => -0.6789, 'C' => -0.3456]],
            
            // SECTION 7
            ['section' => 7, 'dim' => 'D', 'text' => 'Tidak takut bertempur', 
             'weights' => ['D' => 0.9456, 'I' => -0.1234, 'S' => -0.8901, 'C' => -0.3456]],
            ['section' => 7, 'dim' => 'I', 'text' => 'Cenderung janji berlebihan', 
             'weights' => ['D' => 0.2345, 'I' => 0.6789, 'S' => -0.4567, 'C' => -0.5678]],
            ['section' => 7, 'dim' => 'S', 'text' => 'Tolak perubahan mendadak', 
             'weights' => ['D' => -0.6789, 'I' => -0.3456, 'S' => 0.8789, 'C' => 0.4567]],
            ['section' => 7, 'dim' => 'C', 'text' => 'Tarik diri di tengah tekanan', 
             'weights' => ['D' => -0.4567, 'I' => -0.5678, 'S' => 0.3456, 'C' => 0.6890]],
            
            // SECTION 8
            ['section' => 8, 'dim' => 'D', 'text' => 'Delegator yang baik', 
             'weights' => ['D' => 0.8345, 'I' => 0.2345, 'S' => -0.5678, 'C' => 0.1234]],
            ['section' => 8, 'dim' => 'I', 'text' => 'Penyemangat yang baik', 
             'weights' => ['D' => 0.3456, 'I' => 0.8901, 'S' => 0.2345, 'C' => -0.3456]],
            ['section' => 8, 'dim' => 'S', 'text' => 'Pendengar yang baik', 
             'weights' => ['D' => -0.5678, 'I' => 0.1234, 'S' => 0.9234, 'C' => 0.2345]],
            ['section' => 8, 'dim' => 'C', 'text' => 'Penganalisa yang baik', 
             'weights' => ['D' => -0.2345, 'I' => -0.4567, 'S' => -0.1234, 'C' => 0.9345]],
            
            // SECTION 9
            ['section' => 9, 'dim' => 'D', 'text' => 'Hasil adalah penting', 
             'weights' => ['D' => 0.9567, 'I' => -0.2345, 'S' => -0.6789, 'C' => 0.1234]],
            ['section' => 9, 'dim' => 'I', 'text' => 'Dibuat menyenangkan', 
             'weights' => ['D' => -0.1234, 'I' => 0.8678, 'S' => 0.1234, 'C' => -0.4567]],
            ['section' => 9, 'dim' => 'S', 'text' => 'Mari kerjakan bersama', 
             'weights' => ['D' => -0.4567, 'I' => 0.3456, 'S' => 0.8890, 'C' => -0.1234]],
            ['section' => 9, 'dim' => 'C', 'text' => 'Lakukan dengan benar, Akurasi penting', 
             'weights' => ['D' => -0.3456, 'I' => -0.5678, 'S' => -0.2345, 'C' => 0.9456]],
            
            // SECTION 10
            ['section' => 10, 'dim' => 'D', 'text' => 'Akan mengusahakan yang kuinginkan', 
             'weights' => ['D' => 0.8789, 'I' => 0.1234, 'S' => -0.7890, 'C' => -0.2345]],
            ['section' => 10, 'dim' => 'I', 'text' => 'Akan membeli sesuai dorongan hati', 
             'weights' => ['D' => 0.2345, 'I' => 0.7890, 'S' => -0.4567, 'C' => -0.6789]],
            ['section' => 10, 'dim' => 'S', 'text' => 'Akan menunggu, Tanpa tekanan', 
             'weights' => ['D' => -0.6789, 'I' => -0.2345, 'S' => 0.9012, 'C' => 0.3456]],
            ['section' => 10, 'dim' => 'C', 'text' => 'Akan berjalan terus tanpa kontrol diri', 
             'weights' => ['D' => 0.5678, 'I' => 0.4567, 'S' => -0.5678, 'C' => -0.7890]],
            
            // SECTION 11
            ['section' => 11, 'dim' => 'D', 'text' => 'Aktif mengubah sesuatu', 
             'weights' => ['D' => 0.8890, 'I' => 0.2345, 'S' => -0.7890, 'C' => -0.1234]],
            ['section' => 11, 'dim' => 'I', 'text' => 'Unik, Bosan rutinitas', 
             'weights' => ['D' => 0.3456, 'I' => 0.8123, 'S' => -0.4567, 'C' => -0.5678]],
            ['section' => 11, 'dim' => 'S', 'text' => 'Ramah, Mudah bergabung', 
             'weights' => ['D' => -0.4567, 'I' => 0.5678, 'S' => 0.8345, 'C' => -0.2345]],
            ['section' => 11, 'dim' => 'C', 'text' => 'Ingin hal-hal yang pasti', 
             'weights' => ['D' => -0.3456, 'I' => -0.4567, 'S' => 0.2345, 'C' => 0.8901]],
            
            // SECTION 12
            ['section' => 12, 'dim' => 'D', 'text' => 'Menuntut, Kasar', 
             'weights' => ['D' => 0.9123, 'I' => -0.3456, 'S' => -0.8901, 'C' => -0.4567]],
            ['section' => 12, 'dim' => 'I', 'text' => 'Perubahan pada menit terakhir', 
             'weights' => ['D' => 0.4567, 'I' => 0.6789, 'S' => -0.5678, 'C' => -0.7890]],
            ['section' => 12, 'dim' => 'S', 'text' => 'Non-konfrontasi, Menyerah', 
             'weights' => ['D' => -0.8901, 'I' => -0.1234, 'S' => 0.8234, 'C' => 0.2345]],
            ['section' => 12, 'dim' => 'C', 'text' => 'Dipenuhi hal detail', 
             'weights' => ['D' => -0.2345, 'I' => -0.5678, 'S' => -0.1234, 'C' => 0.9234]],
            
            // SECTION 13
            ['section' => 13, 'dim' => 'D', 'text' => 'Ingin kemajuan', 
             'weights' => ['D' => 0.8456, 'I' => 0.1234, 'S' => -0.6789, 'C' => 0.2345]],
            ['section' => 13, 'dim' => 'I', 'text' => 'Terbuka memperlihatkan perasaan', 
             'weights' => ['D' => -0.1234, 'I' => 0.8567, 'S' => 0.2345, 'C' => -0.4567]],
            ['section' => 13, 'dim' => 'S', 'text' => 'Rendah hati, Sederhana', 
             'weights' => ['D' => -0.5678, 'I' => -0.2345, 'S' => 0.8678, 'C' => 0.3456]],
            ['section' => 13, 'dim' => 'C', 'text' => 'Puas dengan segalanya', 
             'weights' => ['D' => -0.3456, 'I' => 0.1234, 'S' => 0.5678, 'C' => 0.6789]],
            
            // SECTION 14
            ['section' => 14, 'dim' => 'D', 'text' => 'Tak gentar, Berani', 
             'weights' => ['D' => 0.9345, 'I' => 0.1234, 'S' => -0.8901, 'C' => -0.3456]],
            ['section' => 14, 'dim' => 'I', 'text' => 'Bahagia, Tanpa beban', 
             'weights' => ['D' => -0.1234, 'I' => 0.8789, 'S' => 0.1234, 'C' => -0.4567]],
            ['section' => 14, 'dim' => 'S', 'text' => 'Menyenangkan, Baik hati', 
             'weights' => ['D' => -0.4567, 'I' => 0.5678, 'S' => 0.7890, 'C' => -0.2345]],
            ['section' => 14, 'dim' => 'C', 'text' => 'Tenang, Pendiam', 
             'weights' => ['D' => -0.5678, 'I' => -0.6789, 'S' => 0.4567, 'C' => 0.8123]],
            
            // SECTION 15
            ['section' => 15, 'dim' => 'D', 'text' => 'Menerima ganjaran atas tujuan yg dicapai', 
             'weights' => ['D' => 0.8901, 'I' => 0.2345, 'S' => -0.5678, 'C' => 0.3456]],
            ['section' => 15, 'dim' => 'I', 'text' => 'Bepergian demi petualangan baru', 
             'weights' => ['D' => 0.5678, 'I' => 0.8234, 'S' => -0.6789, 'C' => -0.4567]],
            ['section' => 15, 'dim' => 'S', 'text' => 'Menggunakan waktu berkualitas dgn teman', 
             'weights' => ['D' => -0.3456, 'I' => 0.6789, 'S' => 0.9012, 'C' => -0.1234]],
            ['section' => 15, 'dim' => 'C', 'text' => 'Rencanakan masa depan, Bersiap', 
             'weights' => ['D' => 0.1234, 'I' => -0.3456, 'S' => 0.2345, 'C' => 0.8890]],
            
            // SECTION 16
            ['section' => 16, 'dim' => 'D', 'text' => 'Aturan perlu dipertanyakan', 
             'weights' => ['D' => 0.8567, 'I' => 0.3456, 'S' => -0.7890, 'C' => -0.6789]],
            ['section' => 16, 'dim' => 'I', 'text' => 'Aturan membuat bosan', 
             'weights' => ['D' => 0.4567, 'I' => 0.7345, 'S' => -0.5678, 'C' => -0.8901]],
            ['section' => 16, 'dim' => 'S', 'text' => 'Aturan membuat aman', 
             'weights' => ['D' => -0.6789, 'I' => -0.2345, 'S' => 0.8456, 'C' => 0.5678]],
            ['section' => 16, 'dim' => 'C', 'text' => 'Aturan membuat adil', 
             'weights' => ['D' => -0.3456, 'I' => -0.4567, 'S' => 0.3456, 'C' => 0.9123]],
            
            // SECTION 17
            ['section' => 17, 'dim' => 'D', 'text' => 'Prestasi, Ganjaran', 
             'weights' => ['D' => 0.9234, 'I' => 0.2345, 'S' => -0.6789, 'C' => 0.1234]],
            ['section' => 17, 'dim' => 'I', 'text' => 'Sosial, Perkumpulan kelompok', 
             'weights' => ['D' => -0.1234, 'I' => 0.8890, 'S' => 0.5678, 'C' => -0.3456]],
            ['section' => 17, 'dim' => 'S', 'text' => 'Keselamatan, keamanan', 
             'weights' => ['D' => -0.5678, 'I' => -0.1234, 'S' => 0.9345, 'C' => 0.4567]],
            ['section' => 17, 'dim' => 'C', 'text' => 'Pendidikan, Kebudayaan', 
             'weights' => ['D' => -0.2345, 'I' => 0.1234, 'S' => 0.3456, 'C' => 0.8567]],
            
            // SECTION 18
            ['section' => 18, 'dim' => 'D', 'text' => 'Memimpin, Pendekatan langsung', 
             'weights' => ['D' => 0.9567, 'I' => 0.1234, 'S' => -0.8901, 'C' => -0.2345]],
            ['section' => 18, 'dim' => 'I', 'text' => 'Suka bergaul, Antusias', 
             'weights' => ['D' => 0.1234, 'I' => 0.9012, 'S' => -0.2345, 'C' => -0.5678]],
            ['section' => 18, 'dim' => 'S', 'text' => 'Dapat diramal, Konsisten', 
             'weights' => ['D' => -0.6789, 'I' => -0.3456, 'S' => 0.8789, 'C' => 0.4567]],
            ['section' => 18, 'dim' => 'C', 'text' => 'Waspada, Hati-hati', 
             'weights' => ['D' => -0.4567, 'I' => -0.5678, 'S' => 0.2345, 'C' => 0.8901]],
            
            // SECTION 19
            ['section' => 19, 'dim' => 'D', 'text' => 'Tidak mudah dikalahkan', 
             'weights' => ['D' => 0.9678, 'I' => -0.1234, 'S' => -0.7890, 'C' => -0.3456]],
            ['section' => 19, 'dim' => 'I', 'text' => 'Mudah terangsang, Riang', 
             'weights' => ['D' => 0.2345, 'I' => 0.8678, 'S' => -0.3456, 'C' => -0.5678]],
            ['section' => 19, 'dim' => 'S', 'text' => 'Kerjakan sesuai perintah, Ikut pimpinan', 
             'weights' => ['D' => -0.7890, 'I' => -0.2345, 'S' => 0.8234, 'C' => 0.3456]],
            ['section' => 19, 'dim' => 'C', 'text' => 'Ingin segalanya teratur, Rapi', 
             'weights' => ['D' => -0.3456, 'I' => -0.4567, 'S' => 0.1234, 'C' => 0.8890]],
            
            // SECTION 20
            ['section' => 20, 'dim' => 'D', 'text' => 'Saya akan pimpin mereka', 
             'weights' => ['D' => 0.9789, 'I' => 0.1234, 'S' => -0.8901, 'C' => -0.2345]],
            ['section' => 20, 'dim' => 'I', 'text' => 'Saya akan meyakinkan mereka', 
             'weights' => ['D' => 0.3456, 'I' => 0.8901, 'S' => -0.2345, 'C' => -0.4567]],
            ['section' => 20, 'dim' => 'S', 'text' => 'Saya akan melaksanakan', 
             'weights' => ['D' => -0.6789, 'I' => -0.1234, 'S' => 0.8456, 'C' => 0.3456]],
            ['section' => 20, 'dim' => 'C', 'text' => 'Saya dapatkan fakta', 
             'weights' => ['D' => -0.2345, 'I' => -0.4567, 'S' => -0.1234, 'C' => 0.9234]],
            
            // SECTION 21
            ['section' => 21, 'dim' => 'D', 'text' => 'Kompetitif, Suka tantangan', 
             'weights' => ['D' => 0.9456, 'I' => 0.1234, 'S' => -0.7890, 'C' => -0.2345]],
            ['section' => 21, 'dim' => 'I', 'text' => 'Optimis, Positif', 
             'weights' => ['D' => -0.1234, 'I' => 0.8789, 'S' => 0.2345, 'C' => -0.3456]],
            ['section' => 21, 'dim' => 'S', 'text' => 'Memikirkan orang dahulu', 
             'weights' => ['D' => -0.5678, 'I' => 0.3456, 'S' => 0.9123, 'C' => -0.1234]],
            ['section' => 21, 'dim' => 'C', 'text' => 'Pemikir logis, Sistematik', 
             'weights' => ['D' => -0.3456, 'I' => -0.4567, 'S' => -0.2345, 'C' => 0.9345]],
            
            // SECTION 22
            ['section' => 22, 'dim' => 'D', 'text' => 'Berani, Tak gentar', 
             'weights' => ['D' => 0.9567, 'I' => 0.2345, 'S' => -0.8901, 'C' => -0.3456]],
            ['section' => 22, 'dim' => 'I', 'text' => 'Tertawa lepas, Hidup', 
             'weights' => ['D' => 0.1234, 'I' => 0.8890, 'S' => -0.2345, 'C' => -0.5678]],
            ['section' => 22, 'dim' => 'S', 'text' => 'Menyenangkan orang, Mudah setuju', 
             'weights' => ['D' => -0.6789, 'I' => 0.4567, 'S' => 0.8234, 'C' => -0.2345]],
            ['section' => 22, 'dim' => 'C', 'text' => 'Tenang, Pendiam', 
             'weights' => ['D' => -0.5678, 'I' => -0.6789, 'S' => 0.3456, 'C' => 0.8456]],
            
            // SECTION 23
            ['section' => 23, 'dim' => 'D', 'text' => 'Ingin otoritas lebih', 
             'weights' => ['D' => 0.9234, 'I' => -0.1234, 'S' => -0.7890, 'C' => -0.2345]],
            ['section' => 23, 'dim' => 'I', 'text' => 'Ingin kesempatan baru', 
             'weights' => ['D' => 0.4567, 'I' => 0.8345, 'S' => -0.5678, 'C' => -0.3456]],
            ['section' => 23, 'dim' => 'S', 'text' => 'Menghindari konflik', 
             'weights' => ['D' => -0.7890, 'I' => -0.1234, 'S' => 0.8567, 'C' => 0.2345]],
            ['section' => 23, 'dim' => 'C', 'text' => 'Ingin petunjuk yang jelas', 
             'weights' => ['D' => -0.4567, 'I' => -0.3456, 'S' => 0.3456, 'C' => 0.8789]],
            
            // SECTION 24
            ['section' => 24, 'dim' => 'D', 'text' => 'Garis dasar, Orientasi hasil', 
             'weights' => ['D' => 0.9345, 'I' => -0.1234, 'S' => -0.6789, 'C' => 0.2345]],
            ['section' => 24, 'dim' => 'I', 'text' => 'Kreatif, Unik', 
             'weights' => ['D' => 0.2345, 'I' => 0.8456, 'S' => -0.4567, 'C' => -0.5678]],
            ['section' => 24, 'dim' => 'S', 'text' => 'Dapat diandalkan, Dapat dipercaya', 
             'weights' => ['D' => -0.5678, 'I' => -0.1234, 'S' => 0.9234, 'C' => 0.3456]],
            ['section' => 24, 'dim' => 'C', 'text' => 'Jalankan standar yang tinggi, Akurat', 
             'weights' => ['D' => -0.3456, 'I' => -0.4567, 'S' => -0.1234, 'C' => 0.9567]]
        ];
        
        $insertData = [];
        foreach ($choices as $choice) {
            $sectionNumber = $choice['section'];
            $actualSectionId = $sectionIdMap[$sectionNumber];

            // Determine primary dimension (highest absolute weight)
            $weights = $choice['weights'];
            $absWeights = array_map('abs', $weights);
            $primaryDim = array_keys($absWeights, max($absWeights))[0];

            $insertData[] = [
                'section_id' => $actualSectionId,
                'section_code' => sprintf('SEC%02d', $sectionNumber),
                'section_number' => $sectionNumber,
                'choice_dimension' => $choice['dim'],
                'choice_code' => sprintf('SEC%02d_%s', $sectionNumber, $choice['dim']),
                'choice_text' => $choice['text'],
                'choice_text_en' => null,
                'weight_d' => $weights['D'],
                'weight_i' => $weights['I'],
                'weight_s' => $weights['S'],
                'weight_c' => $weights['C'],
                'primary_dimension' => $primaryDim,
                'primary_strength' => abs($weights[$primaryDim]),
                'keywords' => json_encode($this->getKeywords($choice['dim'], $choice['text'])),
                'keywords_en' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insert in chunks
        $chunks = array_chunk($insertData, 50);
        foreach ($chunks as $chunk) {
            DB::table('disc_3d_section_choices')->insert($chunk);
        }
    }

    /**
     * Seed DISC 3D profile interpretations
     */
    private function seedDisc3DProfileInterpretations(): void
    {
        $interpretations = [];
        $dimensions = ['D', 'I', 'S', 'C'];
        
        foreach ($dimensions as $dim) {
            // MOST graph interpretations (1-7)
            for ($level = 1; $level <= 7; $level++) {
                $interpretations[] = [
                    'dimension' => $dim,
                    'graph_type' => 'MOST',
                    'segment_level' => $level,
                    'title' => $this->getInterpretationTitle($dim, $level),
                    'title_en' => $this->getInterpretationTitleEn($dim, $level),
                    'description' => $this->getInterpretationDesc($dim, 'MOST', $level),
                    'description_en' => null,
                    'characteristics' => json_encode($this->getCharacteristics($dim, $level)),
                    'characteristics_en' => null,
                    'behavioral_indicators' => json_encode($this->getBehavioralIndicators($dim, $level)),
                    'work_style' => json_encode($this->getWorkStyle($dim, $level)),
                    'communication_style' => json_encode($this->getCommunicationStyle($dim, $level)),
                    'stress_behavior' => json_encode($this->getStressBehavior($dim, $level)),
                    'motivators' => json_encode($this->getMotivators($dim, $level)),
                    'fears' => json_encode($this->getFears($dim, $level)),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            // LEAST graph interpretations (1-7)
            for ($level = 1; $level <= 7; $level++) {
                $interpretations[] = [
                    'dimension' => $dim,
                    'graph_type' => 'LEAST',
                    'segment_level' => $level,
                    'title' => $this->getInterpretationTitle($dim, $level),
                    'title_en' => $this->getInterpretationTitleEn($dim, $level),
                    'description' => $this->getInterpretationDesc($dim, 'LEAST', $level),
                    'description_en' => null,
                    'characteristics' => json_encode($this->getCharacteristics($dim, $level)),
                    'characteristics_en' => null,
                    'behavioral_indicators' => json_encode($this->getBehavioralIndicators($dim, $level)),
                    'work_style' => json_encode($this->getWorkStyle($dim, $level)),
                    'communication_style' => json_encode($this->getCommunicationStyle($dim, $level)),
                    'stress_behavior' => json_encode($this->getStressBehavior($dim, $level)),
                    'motivators' => json_encode($this->getMotivators($dim, $level)),
                    'fears' => json_encode($this->getFears($dim, $level)),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            // CHANGE graph interpretations (-4 to +4)
            for ($level = -4; $level <= 4; $level++) {
                $interpretations[] = [
                    'dimension' => $dim,
                    'graph_type' => 'CHANGE',
                    'segment_level' => $level,
                    'title' => $this->getChangeInterpretationTitle($dim, $level),
                    'title_en' => $this->getChangeInterpretationTitleEn($dim, $level),
                    'description' => $this->getInterpretationDesc($dim, 'CHANGE', $level),
                    'description_en' => null,
                    'characteristics' => json_encode($this->getChangeCharacteristics($dim, $level)),
                    'characteristics_en' => null,
                    'behavioral_indicators' => json_encode($this->getChangeBehavioralIndicators($dim, $level)),
                    'work_style' => json_encode($this->getChangeWorkStyle($dim, $level)),
                    'communication_style' => null,
                    'stress_behavior' => json_encode($this->getChangeStressBehavior($dim, $level)),
                    'motivators' => null,
                    'fears' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        // Insert in chunks
        $chunks = array_chunk($interpretations, 50);
        foreach ($chunks as $chunk) {
            DB::table('disc_3d_profile_interpretations')->insert($chunk);
        }
    }

    /**
     * Seed DISC 3D pattern combinations
     */
    private function seedDisc3DPatternCombinations(): void
    {
        $patterns = [
            ['code' => 'DI', 'name' => 'Penggerak', 'name_en' => 'Driver', 
             'desc' => 'Kombinasi dominan dan pengaruh menciptakan pemimpin yang karismatik'],
            ['code' => 'DC', 'name' => 'Penentu', 'name_en' => 'Decider',
             'desc' => 'Kombinasi dominan dan ketelitian menghasilkan pengambil keputusan yang tepat'],
            ['code' => 'DS', 'name' => 'Pelaksana', 'name_en' => 'Doer',
             'desc' => 'Kombinasi dominan dan kestabilan menciptakan pelaksana yang konsisten'],
            ['code' => 'ID', 'name' => 'Inspirator', 'name_en' => 'Inspirer',
             'desc' => 'Kombinasi pengaruh dan dominan menghasilkan motivator yang kuat'],
            ['code' => 'IS', 'name' => 'Pendukung', 'name_en' => 'Supporter',
             'desc' => 'Kombinasi pengaruh dan kestabilan menciptakan team player yang baik'],
            ['code' => 'IC', 'name' => 'Persuader', 'name_en' => 'Persuader',
             'desc' => 'Kombinasi pengaruh dan ketelitian menghasilkan komunikator yang efektif'],
            ['code' => 'SD', 'name' => 'Stabilizer', 'name_en' => 'Stabilizer',
             'desc' => 'Kombinasi kestabilan dan dominan menciptakan pemimpin yang sabar'],
            ['code' => 'SI', 'name' => 'Kolaborator', 'name_en' => 'Collaborator',
             'desc' => 'Kombinasi kestabilan dan pengaruh menghasilkan mediator yang baik'],
            ['code' => 'SC', 'name' => 'Koordinator', 'name_en' => 'Coordinator',
             'desc' => 'Kombinasi kestabilan dan ketelitian menciptakan organizer yang handal'],
            ['code' => 'CD', 'name' => 'Analis', 'name_en' => 'Analyst',
             'desc' => 'Kombinasi ketelitian dan dominan menghasilkan problem solver yang efektif'],
            ['code' => 'CI', 'name' => 'Penilai', 'name_en' => 'Assessor',
             'desc' => 'Kombinasi ketelitian dan pengaruh menciptakan evaluator yang objektif'],
            ['code' => 'CS', 'name' => 'Perfeksionis', 'name_en' => 'Perfectionist',
             'desc' => 'Kombinasi ketelitian dan kestabilan menghasilkan quality controller yang teliti']
        ];
        
        $insertData = [];
        foreach ($patterns as $pattern) {
            $insertData[] = [
                'pattern_code' => $pattern['code'],
                'pattern_name' => $pattern['name'],
                'pattern_name_en' => $pattern['name_en'],
                'description' => $pattern['desc'],
                'description_en' => null,
                'strengths' => json_encode($this->getPatternStrengths($pattern['code'])),
                'weaknesses' => json_encode($this->getPatternWeaknesses($pattern['code'])),
                'ideal_environment' => json_encode($this->getIdealEnvironment($pattern['code'])),
                'communication_tips' => json_encode($this->getCommunicationTips($pattern['code'])),
                'career_matches' => json_encode($this->getCareerMatches($pattern['code'])),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        DB::table('disc_3d_pattern_combinations')->insert($insertData);
    }

    /**
     * Seed DISC 3D configuration
     */
    private function seedDisc3DConfig(): void
    {
        $configs = [
            [
                'config_key' => 'scoring_method',
                'config_value' => json_encode([
                    'most_calculation' => 'sum_of_most_choice_weights',
                    'least_calculation' => 'sum_of_least_choice_weights_inverted',
                    'change_calculation' => 'most_score_minus_least_score',
                    'normalization' => 'convert_to_1_7_scale'
                ]),
                'description' => 'Scoring methodology for DISC 3D test'
            ],
            [
                'config_key' => 'segment_conversion',
                'config_value' => json_encode([
                    'method' => 'percentile_based',
                    'segments' => [
                        1 => ['min' => 0, 'max' => 14.28],
                        2 => ['min' => 14.29, 'max' => 28.57],
                        3 => ['min' => 28.58, 'max' => 42.86],
                        4 => ['min' => 42.87, 'max' => 57.14],
                        5 => ['min' => 57.15, 'max' => 71.43],
                        6 => ['min' => 71.44, 'max' => 85.71],
                        7 => ['min' => 85.72, 'max' => 100]
                    ]
                ]),
                'description' => 'Conversion from raw scores to 1-7 segments'
            ],
            [
                'config_key' => 'change_segment_conversion',
                'config_value' => json_encode([
                    'method' => 'difference_based',
                    'segments' => [
                        -4 => ['min' => -100, 'max' => -75],
                        -3 => ['min' => -74, 'max' => -50],
                        -2 => ['min' => -49, 'max' => -25],
                        -1 => ['min' => -24, 'max' => -1],
                        0 => ['min' => 0, 'max' => 0],
                        1 => ['min' => 1, 'max' => 24],
                        2 => ['min' => 25, 'max' => 49],
                        3 => ['min' => 50, 'max' => 74],
                        4 => ['min' => 75, 'max' => 100]
                    ]
                ]),
                'description' => 'Conversion for change graph segments'
            ],
            [
                'config_key' => 'validity_checks',
                'config_value' => json_encode([
                    'consistency_threshold' => 70,
                    'minimum_time_per_section' => 3,
                    'maximum_time_per_section' => 300,
                    'pattern_consistency_check' => true,
                    'response_distribution_check' => true
                ]),
                'description' => 'Validity checking parameters'
            ],
            [
                'config_key' => 'graph_labels',
                'config_value' => json_encode([
                    'MOST' => [
                        'id' => 'MOST (Topeng/Publik)',
                        'en' => 'MOST (Mask/Public)',
                        'description_id' => 'Bagaimana Anda berperilaku di depan umum',
                        'description_en' => 'How you behave in public'
                    ],
                    'LEAST' => [
                        'id' => 'LEAST (Inti/Pribadi)',
                        'en' => 'LEAST (Core/Private)',
                        'description_id' => 'Kepribadian alami Anda',
                        'description_en' => 'Your natural personality'
                    ],
                    'CHANGE' => [
                        'id' => 'CHANGE (Cermin/Adaptasi)',
                        'en' => 'CHANGE (Mirror/Adaptation)',
                        'description_id' => 'Tekanan dan adaptasi yang dialami',
                        'description_en' => 'Pressure and adaptation experienced'
                    ]
                ]),
                'description' => 'Graph labels and descriptions'
            ],
            [
                'config_key' => 'test_settings',
                'config_value' => json_encode([
                    'time_limit_minutes' => 60,
                    'sections_per_page' => 1,
                    'allow_navigation' => true,
                    'auto_save_interval' => 30,
                    'show_progress' => true,
                    'require_all_sections' => true
                ]),
                'description' => 'Default test settings and behavior'
            ],
            [
                'config_key' => 'analytics_settings',
                'config_value' => json_encode([
                    'track_mouse_movements' => true,
                    'track_focus_events' => true,
                    'track_timing_details' => true,
                    'suspicious_pattern_detection' => true,
                    'quality_score_calculation' => true
                ]),
                'description' => 'Analytics and tracking configuration'
            ]
        ];
        
        $timestamp = now();
        foreach ($configs as &$config) {
            $config['created_at'] = $timestamp;
            $config['updated_at'] = $timestamp;
        }
        
        DB::table('disc_3d_config')->insert($configs);
    }

    /**
     * Verify data integrity after seeding
     */
    private function verifyDataIntegrity(): void
    {
        // Verify sections count
        $sectionsCount = DB::table('disc_3d_sections')->count();
        if ($sectionsCount !== 24) {
            throw new \Exception("Expected 24 sections, got {$sectionsCount}");
        }
        
        // Verify choices count
        $choicesCount = DB::table('disc_3d_section_choices')->count();
        if ($choicesCount !== 96) {
            throw new \Exception("Expected 96 choices, got {$choicesCount}");
        }
        
        // Verify each section has 4 choices
        $sectionsWithWrongChoiceCount = DB::table('disc_3d_sections')
            ->leftJoin('disc_3d_section_choices', 'disc_3d_sections.id', '=', 'disc_3d_section_choices.section_id')
            ->select('disc_3d_sections.id', 'disc_3d_sections.section_number', 
                     DB::raw('COUNT(disc_3d_section_choices.id) as choice_count'))
            ->groupBy('disc_3d_sections.id', 'disc_3d_sections.section_number')
            ->having('choice_count', '!=', 4)
            ->get();
            
        if ($sectionsWithWrongChoiceCount->count() > 0) {
            $problematicSections = $sectionsWithWrongChoiceCount->pluck('section_number')->toArray();
            throw new \Exception("Sections with wrong choice count: " . implode(', ', $problematicSections));
        }
        
        // FIXED: Verify all DISC dimensions are present per section with better checking
        $sectionsWithMissingDimensions = [];
        
        // Check each section individually
        for ($sectionNum = 1; $sectionNum <= 24; $sectionNum++) {
            $dimensionsInSection = DB::table('disc_3d_section_choices')
                ->where('section_number', $sectionNum)
                ->distinct()
                ->pluck('choice_dimension')
                ->sort()
                ->values()
                ->toArray();
            
            $expectedDimensions = ['C', 'D', 'I', 'S'];
            
            if ($dimensionsInSection !== $expectedDimensions) {
                $sectionsWithMissingDimensions[] = $sectionNum;
                Log::warning("Section {$sectionNum} has dimensions: " . implode(',', $dimensionsInSection) . " but expected: " . implode(',', $expectedDimensions));
            }
        }
        
        if (!empty($sectionsWithMissingDimensions)) {
            throw new \Exception("Sections missing required dimensions: " . implode(', ', $sectionsWithMissingDimensions));
        }
        
        Log::info('✅ DISC 3D data integrity verification passed', [
            'sections' => $sectionsCount,
            'choices' => $choicesCount,
            'verification' => 'SUCCESS'
        ]);
    }

    // ===== HELPER METHODS FOR GENERATING DATA =====

    /**
     * Get keywords based on dimension and text
     */
    private function getKeywords($dimension, $text): array
    {
        // Extract keywords from text
        $keywords = [];
        $parts = explode(',', $text);
        foreach ($parts as $part) {
            $keywords[] = trim($part);
        }
        
        // Add dimension-specific keywords
        $dimKeywords = [
            'D' => ['tegas', 'langsung', 'kompetitif', 'hasil'],
            'I' => ['sosial', 'antusias', 'optimis', 'komunikatif'],
            'S' => ['sabar', 'stabil', 'kooperatif', 'mendukung'],
            'C' => ['teliti', 'analitis', 'sistematis', 'akurat']
        ];
        
        if (isset($dimKeywords[$dimension])) {
            $keywords = array_merge($keywords, array_slice($dimKeywords[$dimension], 0, 2));
        }
        
        return array_unique($keywords);
    }

    // Helper methods for generating interpretation data
    private function getInterpretationTitle($dim, $level): string
    {
        $levels = ['Sangat Rendah', 'Rendah', 'Agak Rendah', 'Sedang', 'Agak Tinggi', 'Tinggi', 'Sangat Tinggi'];
        $dims = ['D' => 'Dominan', 'I' => 'Pengaruh', 'S' => 'Kestabilan', 'C' => 'Ketelitian'];
        return $dims[$dim] . ' ' . $levels[$level - 1];
    }

    private function getInterpretationTitleEn($dim, $level): string
    {
        $levels = ['Very Low', 'Low', 'Below Average', 'Average', 'Above Average', 'High', 'Very High'];
        $dims = ['D' => 'Dominance', 'I' => 'Influence', 'S' => 'Steadiness', 'C' => 'Conscientiousness'];
        return $levels[$level - 1] . ' ' . $dims[$dim];
    }

    private function getInterpretationDesc($dim, $graph, $level): string
    {
        return "Deskripsi untuk dimensi {$dim} pada grafik {$graph} level {$level}. Ini menunjukkan karakteristik dan perilaku yang terkait dengan level ini.";
    }

    private function getCharacteristics($dim, $level): array
    {
        // Sample characteristics based on dimension and level
        $characteristics = [
            'D' => [
                1 => ['Sangat kooperatif', 'Menghindari konflik', 'Pasif dalam pengambilan keputusan'],
                7 => ['Sangat tegas', 'Kompetitif tinggi', 'Berorientasi hasil yang kuat']
            ],
            'I' => [
                1 => ['Pendiam', 'Introver', 'Formal dalam komunikasi'],
                7 => ['Sangat ekspresif', 'Ekstrovert', 'Antusias tinggi dalam berinteraksi']
            ],
            'S' => [
                1 => ['Suka perubahan', 'Tidak sabar', 'Dinamis dan fleksibel'],
                7 => ['Sangat sabar', 'Konsisten', 'Stabil dan dapat diandalkan']
            ],
            'C' => [
                1 => ['Fleksibel', 'Spontan', 'Generalis yang adaptif'],
                7 => ['Sangat teliti', 'Perfeksionis', 'Detail oriented dan sistematis']
            ]
        ];
        
        // Return interpolated values based on level
        $lowChars = $characteristics[$dim][1] ?? [];
        $highChars = $characteristics[$dim][7] ?? [];
        
        if ($level <= 3) {
            return $lowChars;
        } elseif ($level >= 5) {
            return $highChars;
        } else {
            return ['Seimbang dalam pendekatan', 'Moderat dalam ekspresi', 'Adaptif terhadap situasi'];
        }
    }

    private function getBehavioralIndicators($dim, $level): array
    {
        $indicators = [
            'D' => [
                1 => ['Menunggu instruksi', 'Menghindari konfrontasi', 'Mencari konsensus'],
                7 => ['Mengambil kendali', 'Menghadapi tantangan', 'Membuat keputusan cepat']
            ],
            'I' => [
                1 => ['Komunikasi tertulis', 'Mendengarkan lebih banyak', 'Interaksi terbatas'],
                7 => ['Komunikasi verbal aktif', 'Mempengaruhi orang lain', 'Networking yang luas']
            ],
            'S' => [
                1 => ['Menyukai variasi', 'Multitasking', 'Perubahan cepat'],
                7 => ['Rutinitas konsisten', 'Fokus satu tugas', 'Stabilitas jangka panjang']
            ],
            'C' => [
                1 => ['Keputusan cepat', 'Pendekatan umum', 'Fleksibilitas tinggi'],
                7 => ['Analisis mendalam', 'Perhatian detail', 'Standar tinggi']
            ]
        ];
        
        return $indicators[$dim][$level <= 3 ? 1 : 7] ?? ["Indikator untuk {$dim}-{$level}"];
    }

    private function getWorkStyle($dim, $level): array
    {
        return [
            "Gaya kerja karakteristik untuk dimensi {$dim} level {$level}",
            "Preferensi lingkungan kerja yang sesuai",
            "Pendekatan dalam menyelesaikan tugas"
        ];
    }

    private function getCommunicationStyle($dim, $level): array
    {
        return [
            "Gaya komunikasi untuk dimensi {$dim} level {$level}",
            "Preferensi dalam berinteraksi",
            "Cara menyampaikan informasi"
        ];
    }

    private function getStressBehavior($dim, $level): array
    {
        return [
            "Perilaku saat stres untuk dimensi {$dim} level {$level}",
            "Reaksi terhadap tekanan",
            "Cara mengatasi stress"
        ];
    }

    private function getMotivators($dim, $level): array
    {
        return [
            "Motivator utama untuk dimensi {$dim} level {$level}",
            "Faktor pendorong kinerja",
            "Sumber energi dan semangat"
        ];
    }

    private function getFears($dim, $level): array
    {
        return [
            "Ketakutan atau kekhawatiran untuk dimensi {$dim} level {$level}",
            "Situasi yang dihindari",
            "Sumber kecemasan potensial"
        ];
    }

    private function getChangeInterpretationTitle($dim, $level): string
    {
        if ($level == 0) return "Tidak Ada Perubahan {$dim}";
        $direction = $level > 0 ? "Peningkatan" : "Penurunan";
        $intensity = abs($level) > 2 ? "Besar" : "Kecil";
        $dims = ['D' => 'Dominan', 'I' => 'Pengaruh', 'S' => 'Kestabilan', 'C' => 'Ketelitian'];
        return "{$direction} {$intensity} {$dims[$dim]}";
    }

    private function getChangeInterpretationTitleEn($dim, $level): string
    {
        if ($level == 0) return "No Change in {$dim}";
        $direction = $level > 0 ? "Increased" : "Decreased";
        $intensity = abs($level) > 2 ? "Major" : "Minor";
        $dims = ['D' => 'Dominance', 'I' => 'Influence', 'S' => 'Steadiness', 'C' => 'Conscientiousness'];
        return "{$intensity} {$direction} {$dims[$dim]}";
    }

    private function getChangeCharacteristics($dim, $level): array
    {
        if ($level == 0) {
            return ["Konsisten antara perilaku publik dan pribadi untuk dimensi {$dim}"];
        }
        
        $direction = $level > 0 ? "meningkatkan" : "menurunkan";
        return [
            "Cenderung {$direction} ekspresi dimensi {$dim} di lingkungan publik",
            "Adaptasi perilaku sesuai tuntutan situasi",
            "Perbedaan antara kepribadian alami dan yang ditampilkan"
        ];
    }

    private function getChangeBehavioralIndicators($dim, $level): array
    {
        return [
            "Indikator perubahan perilaku untuk dimensi {$dim} level {$level}",
            "Tanda-tanda adaptasi situational"
        ];
    }

    private function getChangeWorkStyle($dim, $level): array
    {
        return [
            "Dampak perubahan pada gaya kerja untuk dimensi {$dim}",
            "Adaptasi terhadap tuntutan lingkungan kerja"
        ];
    }

    private function getChangeStressBehavior($dim, $level): array
    {
        if ($level == 0) return ["Tidak ada tekanan signifikan pada dimensi {$dim}"];
        $stress = abs($level) > 2 ? "Tekanan tinggi" : "Tekanan ringan";
        return [
            "{$stress} untuk mempertahankan atau mengubah perilaku {$dim}",
            "Potensi kelelahan dari adaptasi berkelanjutan"
        ];
    }

    private function getPatternStrengths($pattern): array
    {
        $strengths = [
            'DI' => ['Kepemimpinan karismatik', 'Persuasif dan memotivasi', 'Berorientasi hasil dan people'],
            'DC' => ['Analitis dan tegas', 'Pengambilan keputusan yang tepat', 'Fokus pada kualitas dan hasil'],
            'DS' => ['Konsisten dan reliable', 'Kepemimpinan yang stabil', 'Task oriented yang bertanggung jawab'],
            'ID' => ['Inspiratif dan energik', 'Memotivasi tim', 'Inovatif dalam pendekatan'],
            'IS' => ['Supportif dan kolaboratif', 'Team player yang baik', 'Harmonis dalam hubungan'],
            'IC' => ['Komunikatif dan teliti', 'Balanced approach', 'Detail oriented dalam relasi'],
            'SD' => ['Stabilitas dengan arah', 'Kepemimpinan yang sabar', 'Konsisten dalam eksekusi'],
            'SI' => ['Kolaboratif dan mendukung', 'Mediator yang baik', 'Harmonis dan adaptif'],
            'SC' => ['Koordinasi yang baik', 'Systematic dan terorganisir', 'Quality control yang handal'],
            'CD' => ['Analitis dengan action', 'Problem solving yang efektif', 'Research-based decisions'],
            'CI' => ['Evaluasi objektif', 'Komunikasi yang terstruktur', 'Analysis dengan empati'],
            'CS' => ['Perfeksionis yang stabil', 'Quality assurance', 'Detail oriented yang konsisten'],
            'default' => ['Adaptif terhadap situasi', 'Seimbang dalam pendekatan', 'Fleksibel dalam gaya']
        ];
        
        return $strengths[$pattern] ?? $strengths['default'];
    }

    private function getPatternWeaknesses($pattern): array
    {
        $weaknesses = [
            'DI' => ['Kurang sabar dengan detail', 'Bisa terkesan agresif', 'Kurang fokus pada proses'],
            'DC' => ['Terlalu kaku dalam pendekatan', 'Kurang fleksibel dengan perubahan', 'Perfeksionis yang berlebihan'],
            'DS' => ['Kurang ekspresif secara sosial', 'Terlalu task focused', 'Rigid dalam metode'],
            'ID' => ['Impulsif dalam keputusan', 'Kurang konsisten follow-up', 'Over-optimistic'],
            'IS' => ['Kurang tegas dalam konflik', 'Menghindari keputusan sulit', 'People pleaser yang berlebihan'],
            'IC' => ['Overthinking dalam action', 'Indecisive saat deadline', 'Analysis paralysis'],
            'SD' => ['Lambat dalam adaptasi', 'Resisten terhadap perubahan cepat', 'Kurang spontanitas'],
            'SI' => ['Menghindari konflik necessary', 'Kurang assertive', 'Terlalu compromise'],
            'SC' => ['Terlalu rigid dengan standar', 'Lambat dalam innovation', 'Perfeksionis yang menghambat'],
            'CD' => ['Over-analysis tanpa action', 'Kurang people skills', 'Terlalu critical'],
            'CI' => ['Terlalu analytical untuk spontanitas', 'Indecisive dengan time pressure', 'Over-thinking relasi'],
            'CS' => ['Terlalu perfeksionis', 'Resisten terhadap change', 'Lambat dalam delivery'],
            'default' => ['Perlu pengembangan focused', 'Area improvement yang spesifik']
        ];
        
        return $weaknesses[$pattern] ?? $weaknesses['default'];
    }

    private function getIdealEnvironment($pattern): array
    {
        $environments = [
            'DI' => ['Lingkungan dinamis dan challenging', 'Tim yang responsif dan energik', 'Budaya results-oriented'],
            'DC' => ['Struktur yang jelas dengan autonomy', 'Standar kualitas yang tinggi', 'Environment yang organized'],
            'DS' => ['Rutinitas yang predictable', 'Tim yang stable dan loyal', 'Clear processes dan procedures'],
            'ID' => ['Lingkungan kreatif dan social', 'Tim yang collaborative', 'Budaya innovation dan openness'],
            'IS' => ['Atmosfer yang harmonis', 'Team-based environment', 'Budaya supportive dan inclusive'],
            'IC' => ['Balance antara social dan task', 'Quality-focused culture', 'Collaborative yet structured'],
            'default' => ['Lingkungan yang balanced', 'Fleksibilitas dalam approach', 'Adaptif terhadap kebutuhan']
        ];
        
        return $environments[$pattern] ?? $environments['default'];
    }

    private function getCommunicationTips($pattern): array
    {
        $tips = [
            'DI' => ['Be direct dan confident', 'Focus pada results dan impact', 'Present ideas dengan enthusiasm'],
            'DC' => ['Provide data dan facts', 'Be structured dalam presentation', 'Focus pada quality dan accuracy'],
            'DS' => ['Be consistent dan reliable', 'Provide clear expectations', 'Allow time untuk processing'],
            'ID' => ['Be enthusiastic dan engaging', 'Allow brainstorming dan creativity', 'Keep energy high'],
            'IS' => ['Be warm dan supportive', 'Build relationship terlebih dahulu', 'Avoid confrontational approach'],
            'IC' => ['Balance facts dengan personal connection', 'Be thorough yet engaging', 'Allow discussion dan input'],
            'default' => ['Adapt style sesuai situasi', 'Balance approach', 'Listen actively']
        ];
        
        return $tips[$pattern] ?? $tips['default'];
    }

    private function getCareerMatches($pattern): array
    {
        $careers = [
            'DI' => ['CEO/Executive Leadership', 'Sales Director', 'Entrepreneur', 'Business Development Manager'],
            'DC' => ['Project Manager', 'Quality Control Manager', 'Operations Director', 'Strategic Planner'],
            'DS' => ['Operations Manager', 'Production Supervisor', 'Department Head', 'Process Manager'],
            'ID' => ['Marketing Manager', 'PR Manager', 'Training Manager', 'Creative Director'],
            'IS' => ['HR Manager', 'Customer Service Manager', 'Team Coordinator', 'Community Relations'],
            'IC' => ['Business Analyst', 'Management Consultant', 'Research Manager', 'Training Specialist'],
            'SD' => ['Operations Director', 'Administrative Manager', 'Compliance Officer', 'System Manager'],
            'SI' => ['HR Specialist', 'Customer Relations', 'Office Manager', 'Support Services'],
            'SC' => ['Quality Assurance', 'Administrative Coordinator', 'Compliance Specialist', 'Documentation Manager'],
            'CD' => ['Data Analyst', 'Research Director', 'Technical Manager', 'Strategy Consultant'],
            'CI' => ['Business Consultant', 'Training Coordinator', 'Market Research', 'Academic Roles'],
            'CS' => ['Quality Control', 'Documentation Specialist', 'Audit Manager', 'Standards Coordinator'],
            'default' => ['Multiple career paths', 'Adaptable roles', 'Various management positions']
        ];
        
        return $careers[$pattern] ?? $careers['default'];
    }
}