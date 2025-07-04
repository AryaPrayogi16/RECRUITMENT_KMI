<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonalData;

class PersonalDataSeeder extends Seeder
{
    public function run(): void
    {
        $personalData = [
            [
                'candidate_id' => 1,
                'full_name' => 'Ahmad Rizki Pratama',
                'birth_place' => 'Jakarta',
                'birth_date' => '1995-03-15',
                'age' => 29,
                'gender' => 'Laki-laki',
                'religion' => 'Islam',
                'ethnicity' => 'Jawa',
                'marital_status' => 'Lajang',
                'email' => 'ahmad.rizki@email.com',
                'current_address' => 'Jl. Sudirman No. 123, Jakarta Selatan',
                'ktp_address' => 'Jl. Sudirman No. 123, Jakarta Selatan',
                'phone_number' => '08123456789',
                'residence_status' => 'Kontrak',
                'height_cm' => 175,
                'weight_kg' => 70,
                'vaccination_status' => 'Vaksin 3',
            ],
            [
                'candidate_id' => 2,
                'full_name' => 'Siti Nurhaliza Putri',
                'birth_place' => 'Bandung',
                'birth_date' => '1993-07-22',
                'age' => 31,
                'gender' => 'Perempuan',
                'religion' => 'Islam',
                'ethnicity' => 'Sunda',
                'marital_status' => 'Menikah',
                'email' => 'siti.nurhaliza@email.com',
                'current_address' => 'Jl. Dago No. 456, Bandung',
                'ktp_address' => 'Jl. Dago No. 456, Bandung',
                'phone_number' => '08234567890',
                'residence_status' => 'Milik Sendiri',
                'height_cm' => 160,
                'weight_kg' => 55,
                'vaccination_status' => 'Vaksin 3',
            ],
            [
                'candidate_id' => 3,
                'full_name' => 'Budi Santoso',
                'birth_place' => 'Surabaya',
                'birth_date' => '1990-11-08',
                'age' => 34,
                'gender' => 'Laki-laki',
                'religion' => 'Kristen',
                'ethnicity' => 'Jawa',
                'marital_status' => 'Menikah',
                'email' => 'budi.santoso@email.com',
                'current_address' => 'Jl. Raya Gubeng No. 789, Surabaya',
                'ktp_address' => 'Jl. Raya Gubeng No. 789, Surabaya',
                'phone_number' => '08345678901',
                'residence_status' => 'Milik Sendiri',
                'height_cm' => 170,
                'weight_kg' => 75,
                'vaccination_status' => 'Vaksin 2',
            ],
            [
                'candidate_id' => 4,
                'full_name' => 'Maya Sari Dewi',
                'birth_place' => 'Yogyakarta',
                'birth_date' => '1992-05-12',
                'age' => 32,
                'gender' => 'Perempuan',
                'religion' => 'Islam',
                'ethnicity' => 'Jawa',
                'marital_status' => 'Lajang',
                'email' => 'maya.sari@email.com',
                'current_address' => 'Jl. Malioboro No. 321, Yogyakarta',
                'ktp_address' => 'Jl. Malioboro No. 321, Yogyakarta',
                'phone_number' => '08456789012',
                'residence_status' => 'Orang Tua',
                'height_cm' => 165,
                'weight_kg' => 58,
                'vaccination_status' => 'Vaksin 3',
            ],
            [
                'candidate_id' => 5,
                'full_name' => 'Andi Kurniawan',
                'birth_place' => 'Makassar',
                'birth_date' => '1994-09-18',
                'age' => 30,
                'gender' => 'Laki-laki',
                'religion' => 'Islam',
                'ethnicity' => 'Bugis',
                'marital_status' => 'Lajang',
                'email' => 'andi.kurniawan@email.com',
                'current_address' => 'Jl. AP Pettarani No. 654, Makassar',
                'ktp_address' => 'Jl. AP Pettarani No. 654, Makassar',
                'phone_number' => '08567890123',
                'residence_status' => 'Sewa',
                'height_cm' => 168,
                'weight_kg' => 65,
                'vaccination_status' => 'Vaksin 3',
            ],
            [
                'candidate_id' => 6,
                'full_name' => 'Rina Handayani',
                'birth_place' => 'Medan',
                'birth_date' => '1991-12-25',
                'age' => 33,
                'gender' => 'Perempuan',
                'religion' => 'Kristen',
                'ethnicity' => 'Batak',
                'marital_status' => 'Menikah',
                'email' => 'rina.handayani@email.com',
                'current_address' => 'Jl. Sisingamangaraja No. 987, Medan',
                'ktp_address' => 'Jl. Sisingamangaraja No. 987, Medan',
                'phone_number' => '08678901234',
                'residence_status' => 'Milik Sendiri',
                'height_cm' => 158,
                'weight_kg' => 52,
                'vaccination_status' => 'Vaksin 2',
            ],
            [
                'candidate_id' => 7,
                'full_name' => 'Dedi Kurniadi',
                'birth_place' => 'Palembang',
                'birth_date' => '1989-04-30',
                'age' => 35,
                'gender' => 'Laki-laki',
                'religion' => 'Islam',
                'ethnicity' => 'Melayu',
                'marital_status' => 'Menikah',
                'email' => 'dedi.kurniadi@email.com',
                'current_address' => 'Jl. Jenderal Ahmad Yani No. 147, Palembang',
                'ktp_address' => 'Jl. Jenderal Ahmad Yani No. 147, Palembang',
                'phone_number' => '08789012345',
                'residence_status' => 'Milik Sendiri',
                'height_cm' => 172,
                'weight_kg' => 78,
                'vaccination_status' => 'Vaksin 3',
            ],
            [
                'candidate_id' => 8,
                'full_name' => 'Indira Wulandari',
                'birth_place' => 'Denpasar',
                'birth_date' => '1988-08-14',
                'age' => 36,
                'gender' => 'Perempuan',
                'religion' => 'Hindu',
                'ethnicity' => 'Bali',
                'marital_status' => 'Menikah',
                'email' => 'indira.wulandari@email.com',
                'current_address' => 'Jl. Sunset Road No. 258, Denpasar',
                'ktp_address' => 'Jl. Sunset Road No. 258, Denpasar',
                'phone_number' => '08890123456',
                'residence_status' => 'Milik Sendiri',
                'height_cm' => 162,
                'weight_kg' => 57,
                'vaccination_status' => 'Vaksin 3',
            ],
            [
                'candidate_id' => 9,
                'full_name' => 'Reza Maulana',
                'birth_place' => 'Semarang',
                'birth_date' => '1996-01-20',
                'age' => 28,
                'gender' => 'Laki-laki',
                'religion' => 'Islam',
                'ethnicity' => 'Jawa',
                'marital_status' => 'Lajang',
                'email' => 'reza.maulana@email.com',
                'current_address' => 'Jl. Pandanaran No. 369, Semarang',
                'ktp_address' => 'Jl. Pandanaran No. 369, Semarang',
                'phone_number' => '08901234567',
                'residence_status' => 'Kontrak',
                'height_cm' => 178,
                'weight_kg' => 72,
                'vaccination_status' => 'Vaksin 3',
            ],
            [
                'candidate_id' => 10,
                'full_name' => 'Fitri Ramadhani',
                'birth_place' => 'Padang',
                'birth_date' => '1993-10-05',
                'age' => 31,
                'gender' => 'Perempuan',
                'religion' => 'Islam',
                'ethnicity' => 'Minang',
                'marital_status' => 'Lajang',
                'email' => 'fitri.ramadhani@email.com',
                'current_address' => 'Jl. Prof. Dr. Hamka No. 741, Padang',
                'ktp_address' => 'Jl. Prof. Dr. Hamka No. 741, Padang',
                'phone_number' => '08012345678',
                'residence_status' => 'Orang Tua',
                'height_cm' => 156,
                'weight_kg' => 50,
                'vaccination_status' => 'Vaksin 2',
            ],
        ];

        foreach ($personalData as $data) {
            PersonalData::create($data);
        }
    }
}