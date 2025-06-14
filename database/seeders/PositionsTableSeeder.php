<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PositionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'position_name' => 'Staff Produksi',
                'department' => 'Produksi',
                'description' => 'Bertanggung jawab untuk kegiatan produksi furniture kayu',
                'requirements' => 'Min. SMA/SMK, pengalaman di bidang furniture lebih disukai',
                'salary_range_min' => 3500000,
                'salary_range_max' => 5000000,
                'is_active' => true,
                'location' => 'Jepara',
                'employment_type' => 'full-time',
                'posted_date' => Carbon::now()->subDays(7),
                'closing_date' => Carbon::now()->addDays(30),
            ],
            [
                'position_name' => 'Quality Control',
                'department' => 'Produksi',
                'description' => 'Memastikan kualitas produk sesuai standar perusahaan',
                'requirements' => 'Min. D3 Teknik, teliti, detail oriented',
                'salary_range_min' => 4000000,
                'salary_range_max' => 6000000,
                'is_active' => true,
                'location' => 'Jepara',
                'employment_type' => 'full-time',
                'posted_date' => Carbon::now()->subDays(5),
                'closing_date' => Carbon::now()->addDays(25),
            ],
            [
                'position_name' => 'Sales Executive',
                'department' => 'Sales & Marketing',
                'description' => 'Memasarkan produk furniture kepada customer B2B',
                'requirements' => 'Min. D3, pengalaman sales min 2 tahun, memiliki kendaraan sendiri',
                'salary_range_min' => 4500000,
                'salary_range_max' => 8000000,
                'is_active' => true,
                'location' => 'Jakarta',
                'employment_type' => 'full-time',
                'posted_date' => Carbon::now()->subDays(3),
                'closing_date' => Carbon::now()->addDays(45),
            ],
            [
                'position_name' => 'Designer Furniture',
                'department' => 'Design',
                'description' => 'Membuat desain furniture sesuai trend pasar',
                'requirements' => 'Min. S1 Desain/Arsitektur, menguasai AutoCAD, 3D Max',
                'salary_range_min' => 5000000,
                'salary_range_max' => 10000000,
                'is_active' => true,
                'location' => 'Jepara',
                'employment_type' => 'full-time',
                'posted_date' => Carbon::now()->subDays(10),
                'closing_date' => Carbon::now()->addDays(60),
            ],
            [
                'position_name' => 'Admin Produksi',
                'department' => 'Produksi',
                'description' => 'Mengelola administrasi dan dokumentasi produksi',
                'requirements' => 'Min. D3, menguasai MS Office, teliti',
                'salary_range_min' => 3500000,
                'salary_range_max' => 4500000,
                'is_active' => true,
                'location' => 'Jepara',
                'employment_type' => 'full-time',
                'posted_date' => Carbon::now()->subDays(2),
                'closing_date' => Carbon::now()->addDays(30),
            ],
        ];

        foreach ($positions as $position) {
            DB::table('positions')->insert(array_merge($position, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));
        }
    }
}