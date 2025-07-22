<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Lamaran Kerja - PT Kayu Mebel Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .form-section:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .section-title {
            color: #1f2937;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }
        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn-secondary {
            background-color: #6b7280;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-add {
            background-color: #10b981;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-add:hover {
            background-color: #059669;
        }
        .btn-remove {
            background-color: #ef4444;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-remove:hover {
            background-color: #dc2626;
        }
        .dynamic-group {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            background-color: #f9fafb;
            position: relative;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .save-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            display: none;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .save-indicator.show {
            display: flex;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .required-star {
            color: #ef4444;
            font-weight: bold;
        }
        .company-logo {
            max-height: 80px;
            margin: 0 auto;
        }
        .file-input-container {
            position: relative;
            display: inline-block;
        }
        .file-list {
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
        }
        .file-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Header with Logo -->
        <div class="text-center mb-8">
            <x-company-logo class="company-logo mb-4" />
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Form Lamaran Kerja</h1>
            <p class="text-lg text-gray-600">PT Kayu Mebel Indonesia</p>
            <p class="text-sm text-gray-500 mt-2">Silakan lengkapi semua data dengan benar </p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h3 class="text-red-800 font-medium mb-2">Terdapat kesalahan pada form:</h3>
                <ul class="text-red-700 text-sm list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('job.application.submit') }}" enctype="multipart/form-data" id="applicationForm">
            @csrf

            <!-- 1. Informasi Posisi -->
            <div class="form-section" data-section="1">
                <h2 class="section-title">Informasi Posisi yang Dilamar</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="position_applied">Posisi yang Dilamar <span class="required-star">*</span></label>
                        <select name="position_applied" id="position_applied" class="form-input" required>
                            <option value="">Pilih Posisi</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->position_name }}" {{ old('position_applied') == $position->position_name ? 'selected' : '' }}>
                                    {{ $position->position_name }} - {{ $position->department }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="expected_salary">Gaji yang Diharapkan (Rp)</label>
                        <input type="number" name="expected_salary" id="expected_salary" class="form-input" 
                               value="{{ old('expected_salary') }}" placeholder="contoh: 5000000">
                    </div>
                </div>
            </div>

            <!-- 2. Data Pribadi -->
            <div class="form-section" data-section="2">
                <h2 class="section-title">Data Pribadi</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="full_name">Nama Lengkap <span class="required-star">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-input" 
                               value="{{ old('full_name') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email <span class="required-star">*</span></label>
                        <input type="email" name="email" id="email" class="form-input" 
                               value="{{ old('email') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="phone_number">Nomor Telepon</label>
                        <input type="text" name="phone_number" id="phone_number" class="form-input" 
                               value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone_alternative">Telepon Alternatif</label>
                        <input type="text" name="phone_alternative" id="phone_alternative" class="form-input" 
                               value="{{ old('phone_alternative') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="birth_place">Tempat Lahir</label>
                        <input type="text" name="birth_place" id="birth_place" class="form-input" 
                               value="{{ old('birth_place') }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="birth_date">Tanggal Lahir</label>
                        <input type="date" name="birth_date" id="birth_date" class="form-input" 
                               value="{{ old('birth_date') }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="gender">Jenis Kelamin</label>
                        <select name="gender" id="gender" class="form-input">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="religion">Agama</label>
                        <input type="text" name="religion" id="religion" class="form-input" 
                               value="{{ old('religion') }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="marital_status">Status Pernikahan</label>
                        <select name="marital_status" id="marital_status" class="form-input">
                            <option value="">Pilih Status</option>
                            <option value="Lajang" {{ old('marital_status') == 'Lajang' ? 'selected' : '' }}>Lajang</option>
                            <option value="Menikah" {{ old('marital_status') == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                            <option value="Janda" {{ old('marital_status') == 'Janda' ? 'selected' : '' }}>Janda</option>
                            <option value="Duda" {{ old('marital_status') == 'Duda' ? 'selected' : '' }}>Duda</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="ethnicity">Suku Bangsa</label>
                        <input type="text" name="ethnicity" id="ethnicity" class="form-input" 
                               value="{{ old('ethnicity') }}">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="form-group">
                        <label class="form-label" for="current_address">Alamat Tempat Tinggal Saat Ini</label>
                        <textarea name="current_address" id="current_address" class="form-input" rows="3">{{ old('current_address') }}</textarea>
                        <div class="mt-2">
                            <label class="form-label" for="current_address_status">Status Tempat Tinggal</label>
                            <select name="current_address_status" id="current_address_status" class="form-input">
                                <option value="">Pilih Status</option>
                                <option value="Milik Sendiri" {{ old('current_address_status') == 'Milik Sendiri' ? 'selected' : '' }}>Milik Sendiri</option>
                                <option value="Orang Tua" {{ old('current_address_status') == 'Orang Tua' ? 'selected' : '' }}>Orang Tua</option>
                                <option value="Kontrak" {{ old('current_address_status') == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                                <option value="Sewa" {{ old('current_address_status') == 'Sewa' ? 'selected' : '' }}>Sewa</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="ktp_address">Alamat Sesuai KTP</label>
                        <textarea name="ktp_address" id="ktp_address" class="form-input" rows="3">{{ old('ktp_address') }}</textarea>
                        
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="form-group">
                        <label class="form-label" for="height_cm">Tinggi Badan (cm)</label>
                        <input type="number" name="height_cm" id="height_cm" class="form-input" 
                               value="{{ old('height_cm') }}" min="100" max="250">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="weight_kg">Berat Badan (kg)</label>
                        <input type="number" name="weight_kg" id="weight_kg" class="form-input" 
                               value="{{ old('weight_kg') }}" min="30" max="200">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="vaccination_status">Status Vaksinasi</label>
                        <select name="vaccination_status" id="vaccination_status" class="form-input">
                            <option value="">Pilih Status</option>
                            <option value="Vaksin 1" {{ old('vaccination_status') == 'Vaksin 1' ? 'selected' : '' }}>Vaksin 1</option>
                            <option value="Vaksin 2" {{ old('vaccination_status') == 'Vaksin 2' ? 'selected' : '' }}>Vaksin 2</option>
                            <option value="Vaksin 3" {{ old('vaccination_status') == 'Vaksin 3' ? 'selected' : '' }}>Vaksin 3</option>
                            <option value="Booster" {{ old('vaccination_status') == 'Booster' ? 'selected' : '' }}>Booster</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 3. Data Keluarga -->
            <div class="form-section" data-section="3">
                <h2 class="section-title">Data Keluarga</h2>
                
                <!-- Susunan Keluarga (Unified) -->
                <div id="familyMembers">
                    <div class="dynamic-group" data-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Hubungan Keluarga</label>
                                <select name="family_members[0][relationship]" class="form-input">
                                    <option value="">Pilih Hubungan</option>
                                    <option value="Pasangan">Pasangan</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Ayah">Ayah</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Saudara">Saudara</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama</label>
                                <input type="text" name="family_members[0][name]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Usia</label>
                                <input type="number" name="family_members[0][age]" class="form-input" min="0" max="120">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pendidikan</label>
                                <input type="text" name="family_members[0][education]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pekerjaan</label>
                                <input type="text" name="family_members[0][occupation]" class="form-input">
                            </div>
                            <div class="form-group flex items-end">
                                <button type="button" class="btn-remove" onclick="removeFamilyMember(this)">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-add" onclick="addFamilyMember()">+ Tambah Anggota Keluarga</button>
            </div>

            <!-- 4. Pendidikan -->
            <div class="form-section" data-section="4">
                <h2 class="section-title">Latar Belakang Pendidikan</h2>
                
                <!-- Pendidikan Formal -->
                <h3 class="text-lg font-medium mb-4">Pendidikan Formal</h3>
                <div id="formalEducation">
                    <div class="dynamic-group" data-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Jenjang Pendidikan</label>
                                <select name="formal_education[0][education_level]" class="form-input">
                                    <option value="">Pilih Jenjang</option>
                                    <option value="SMA/SMK">SMA/SMK</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Institusi</label>
                                <input type="text" name="formal_education[0][institution_name]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jurusan</label>
                                <input type="text" name="formal_education[0][major]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tahun Mulai</label>
                                <input type="number" name="formal_education[0][start_year]" class="form-input" min="1950" max="2030">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tahun Selesai</label>
                                <input type="number" name="formal_education[0][end_year]" class="form-input" min="1950" max="2030">
                            </div>
                            <div class="form-group">
                                <label class="form-label">IPK/Nilai</label>
                                <input type="number" name="formal_education[0][gpa]" class="form-input" min="0" max="100">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn-remove" onclick="removeEducation(this)">Hapus Pendidikan</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-add" onclick="addEducation()">+ Tambah Pendidikan</button>
                
                <!-- Pendidikan Non Formal -->
                <h3 class="text-lg font-medium mb-4 mt-8">Pendidikan Non Formal (Kursus, Pelatihan, Seminar, dll)</h3>
                <div id="nonFormalEducation">
                    <div class="dynamic-group" data-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Nama Kursus/Pelatihan</label>
                                <input type="text" name="non_formal_education[0][course_name]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Penyelenggara</label>
                                <input type="text" name="non_formal_education[0][organizer]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="non_formal_education[0][date]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="non_formal_education[0][description]" class="form-input">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn-remove" onclick="removeNonFormalEducation(this)">Hapus</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-add" onclick="addNonFormalEducation()">+ Tambah Pelatihan</button>
            </div>

            <!-- 5. Kemampuan & Skills -->
            <div class="form-section" data-section="5">
                <h2 class="section-title">Kemampuan & Skills</h2>
                
                <!-- SIM -->
                <div class="form-group">
                    <label class="form-label">SIM yang Dimiliki</label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" name="driving_licenses[]" value="A" id="sim_a">
                            <label for="sim_a">SIM A</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="driving_licenses[]" value="B1" id="sim_b1">
                            <label for="sim_b1">SIM B1</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="driving_licenses[]" value="B2" id="sim_b2">
                            <label for="sim_b2">SIM B2</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="driving_licenses[]" value="C" id="sim_c">
                            <label for="sim_c">SIM C</label>
                        </div>
                    </div>
                </div>

                <!-- Language Skills -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium mb-4">Kemampuan Bahasa</h3>
                    <div id="languageSkills">
                        <div class="dynamic-group" data-index="0">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Bahasa</label>
                                    <select name="language_skills[0][language]" class="form-input">
                                        <option value="">Pilih Bahasa</option>
                                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                                        <option value="Bahasa Mandarin">Bahasa Mandarin</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Kemampuan Berbicara</label>
                                    <select name="language_skills[0][speaking_level]" class="form-input">
                                        <option value="">Pilih Level</option>
                                        <option value="Pemula">Pemula</option>
                                        <option value="Menengah">Menengah</option>
                                        <option value="Mahir">Mahir</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Kemampuan Menulis</label>
                                    <select name="language_skills[0][writing_level]" class="form-input">
                                        <option value="">Pilih Level</option>
                                        <option value="Pemula">Pemula</option>
                                        <option value="Menengah">Menengah</option>
                                        <option value="Mahir">Mahir</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="button" class="btn-remove" onclick="removeLanguageSkill(this)">Hapus Bahasa</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-add" onclick="addLanguageSkill()">+ Tambah Kemampuan Bahasa</button>
                </div>
                
                <!-- Computer Skills -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium mb-4">Kemampuan Komputer</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label" for="hardware_skills">Hardware (pisahkan dengan koma)</label>
                            <textarea name="hardware_skills" id="hardware_skills" class="form-input" rows="2" 
                                      placeholder="contoh: Instalasi PC, Troubleshooting, Network">{{ old('hardware_skills') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="software_skills">Software (pisahkan dengan koma)</label>
                            <textarea name="software_skills" id="software_skills" class="form-input" rows="2" 
                                      placeholder="contoh: MS Office, Adobe Photoshop, AutoCAD">{{ old('software_skills') }}</textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Other Skills -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium mb-4">Kemampuan Lainnya</h3>
                    <div class="form-group">
                        <label class="form-label" for="other_skills">Jelaskan kemampuan lain yang Anda miliki</label>
                        <textarea name="other_skills" id="other_skills" class="form-input" rows="3" 
                                  placeholder="contoh: Public Speaking, Leadership, Project Management, dll">{{ old('other_skills') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 6. Organisasi & Prestasi -->
            <div class="form-section" data-section="6">
                <h2 class="section-title">Latar Belakang Organisasi & Prestasi</h2>
                
                <!-- Aktivitas Sosial -->
                <h3 class="text-lg font-medium mb-4">Aktivitas Sosial/Organisasi</h3>
                <div id="socialActivities">
                    <div class="dynamic-group" data-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Nama Organisasi</label>
                                <input type="text" name="social_activities[0][organization_name]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Bidang</label>
                                <input type="text" name="social_activities[0][field]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Periode Kepesertaan</label>
                                <input type="text" name="social_activities[0][period]" class="form-input" 
                                       placeholder="contoh: 2020-2022">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="social_activities[0][description]" class="form-input">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn-remove" onclick="removeSocialActivity(this)">Hapus</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-add" onclick="addSocialActivity()">+ Tambah Aktivitas</button>
                
                <!-- Penghargaan -->
                <h3 class="text-lg font-medium mb-4 mt-8">Penghargaan/Prestasi</h3>
                <div id="achievements">
                    <div class="dynamic-group" data-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Prestasi</label>
                                <input type="text" name="achievements[0][achievement]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tahun</label>
                                <input type="number" name="achievements[0][year]" class="form-input" min="1950" max="2030">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="achievements[0][description]" class="form-input">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn-remove" onclick="removeAchievement(this)">Hapus</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-add" onclick="addAchievement()">+ Tambah Prestasi</button>
            </div>

            <!-- 7. Pengalaman Kerja -->
            <div class="form-section" data-section="7">
                <h2 class="section-title">Pengalaman Kerja</h2>
                <p class="text-sm text-gray-600 mb-4">Mohon isi dimulai dari pekerjaan terakhir</p>
                <div id="workExperiences">
                    <div class="dynamic-group" data-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Nama Perusahaan</label>
                                <input type="text" name="work_experiences[0][company_name]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Alamat Perusahaan</label>
                                <input type="text" name="work_experiences[0][company_address]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Bergerak di Bidang</label>
                                <input type="text" name="work_experiences[0][company_field]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Posisi/Jabatan</label>
                                <input type="text" name="work_experiences[0][position]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tahun Mulai</label>
                                <input type="number" name="work_experiences[0][start_year]" class="form-input" min="1950" max="2030">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tahun Selesai</label>
                                <input type="number" name="work_experiences[0][end_year]" class="form-input" min="1950" max="2030">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Gaji Terakhir</label>
                                <input type="number" name="work_experiences[0][salary]" class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Alasan Berhenti</label>
                                <input type="text" name="work_experiences[0][reason_for_leaving]" class="form-input">
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label">Nama & No Telp Atasan Langsung</label>
                                <input type="text" name="work_experiences[0][supervisor_contact]" class="form-input" 
                                       placeholder="contoh: Bpk. Ahmad - 081234567890">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn-remove" onclick="removeWorkExperience(this)">Hapus Pengalaman</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-add" onclick="addWorkExperience()">+ Tambah Pengalaman Kerja</button>
            </div>

            <!-- 8. Informasi Umum -->
            <div class="form-section" data-section="8">
                <h2 class="section-title">Informasi Umum</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="willing_to_travel" value="1" {{ old('willing_to_travel') ? 'checked' : '' }}>
                            Bersedia melakukan perjalanan dinas
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="has_vehicle" value="1" {{ old('has_vehicle') ? 'checked' : '' }}>
                            Memiliki kendaraan pribadi
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="vehicle_types">Jenis Kendaraan (jika ada)</label>
                    <input type="text" name="vehicle_types" id="vehicle_types" class="form-input" 
                           value="{{ old('vehicle_types') }}" placeholder="contoh: Motor, Mobil">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="motivation">Motivasi untuk bergabung dengan PT Kayu Mebel Indonesia</label>
                    <textarea name="motivation" id="motivation" class="form-input" rows="3" 
                              placeholder="Jelaskan motivasi Anda bergabung dengan perusahaan">{{ old('motivation') }}</textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="strengths">Kelebihan Anda <small class="text-gray-500"></small></label>
                        <textarea name="strengths" id="strengths" class="form-input" rows="3" 
                                  placeholder="Sebutkan minimal 3 kelebihan Anda">{{ old('strengths') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="weaknesses">Kekurangan Anda <small class="text-gray-500"></small></label>
                        <textarea name="weaknesses" id="weaknesses" class="form-input" rows="3" 
                                  placeholder="Sebutkan minimal 3 kekurangan Anda">{{ old('weaknesses') }}</textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="other_income">Sumber Penghasilan Lain (Apa dan Berapa)</label>
                    <input type="text" name="other_income" id="other_income" class="form-input" 
                           value="{{ old('other_income') }}" placeholder="contoh: Freelance design - Rp 2.000.000/bulan">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="has_police_record" value="1" {{ old('has_police_record') ? 'checked' : '' }}>
                            Pernah terlibat dengan pihak Kepolisian (kriminal/perdata/pidana)
                        </label>
                        <input type="text" name="police_record_detail" class="form-input mt-2" 
                               placeholder="Jika ya, jelaskan" value="{{ old('police_record_detail') }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="has_serious_illness" value="1" {{ old('has_serious_illness') ? 'checked' : '' }}>
                            Pernah mengalami sakit keras/kronis/kecelakaan berat/operasi
                        </label>
                        <input type="text" name="illness_detail" class="form-input mt-2" 
                               placeholder="Jika ya, jelaskan" value="{{ old('illness_detail') }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="has_tattoo_piercing" value="1" {{ old('has_tattoo_piercing') ? 'checked' : '' }}>
                            Memiliki Tato/Tindik pada tubuh
                        </label>
                        <input type="text" name="tattoo_piercing_detail" class="form-input mt-2" 
                               placeholder="Jika ya, jelaskan lokasi" value="{{ old('tattoo_piercing_detail') }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="has_other_business" value="1" {{ old('has_other_business') ? 'checked' : '' }}>
                            Memiliki kepemilikan/keterikatan dengan perusahaan lain
                        </label>
                        <input type="text" name="other_business_detail" class="form-input mt-2" 
                               placeholder="Jika ya, jelaskan" value="{{ old('other_business_detail') }}">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="absence_days">Berapa hari kerja yang hilang dalam 1 tahun? (Ijin Tidak Masuk)</label>
                        <input type="number" name="absence_days" id="absence_days" class="form-input" 
                               value="{{ old('absence_days') }}" min="0" max="365">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="start_work_date">Jika diterima, kapan Anda dapat mulai bekerja?</label>
                        <input type="date" name="start_work_date" id="start_work_date" class="form-input" 
                               value="{{ old('start_work_date') }}">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="information_source">Sumber informasi lowongan kerja dari?</label>
                    <input type="text" name="information_source" id="information_source" class="form-input" 
                           value="{{ old('information_source') }}" placeholder="contoh: Website, Teman, Media Sosial, JobStreet">
                </div>
            </div>

            <!-- 9. Upload Dokumen & Pernyataan -->
            <div class="form-section" data-section="9">
                <h2 class="section-title">Upload Dokumen & Pernyataan</h2>
                <p class="text-sm text-gray-600 mb-4">Format yang diterima: PDF, JPG, PNG (Maksimal 2MB per file)</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="cv">CV/Resume</label>
                        <input type="file" name="documents[cv]" id="cv" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="photo">Foto</label>
                        <input type="file" name="documents[photo]" id="photo" class="form-input" accept=".jpg,.jpeg,.png">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="certificates">Sertifikat (bisa lebih dari satu)</label>
                        <input type="file" name="documents[certificates][]" id="certificates" class="form-input" 
                               accept=".pdf,.jpg,.jpeg,.png" multiple>
                        <div class="file-list" id="certificateList"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="transcript">Transkrip Nilai</label>
                        <input type="file" name="documents[transcript]" id="transcript" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                
                <!-- Pernyataan Pelamar -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-4">Pernyataan Pelamar</h3>
                    <p class="text-gray-700 mb-4 italic">
                        "Dengan ini saya menerangkan dan menyatakan bahwa saya memberikan wewenang kepada PT. Kayu Mebel Indonesia 
                        untuk menjaga informasi sehubungan dengan data pribadi dan menggunakannya untuk kepentingan proses seleksi. 
                        Semua data yang saya tuliskan diatas adalah benar, saya menyadari bahwa ketidakjujuran mengenai data-data 
                        di atas dapat mengakibatkan pembatalan atau pemutusan hubungan kerja dari pihak perusahaan."
                    </p>
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="agreement" value="1" required {{ old('agreement') ? 'checked' : '' }}>
                            <span class="ml-2">Saya setuju dengan pernyataan di atas <span class="required-star">*</span></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center mt-8">
                <button type="submit" class="btn-primary px-8 py-3 text-lg">
                    Kirim Lamaran
                </button>
                <p class="text-sm text-gray-500 mt-2">
                    Pastikan semua data telah diisi dengan benar sebelum mengirim
                </p>
            </div>
        </form>
    </div>

    <!-- Save Indicator -->
    <div class="save-indicator" id="saveIndicator">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span>Data tersimpan otomatis</span>
    </div>

    <script>
        // Form State Preservation
        const STORAGE_KEY = 'jobApplicationFormData';
        const form = document.getElementById('applicationForm');
        const saveIndicator = document.getElementById('saveIndicator');
        
        // Load saved data on page load
        window.addEventListener('DOMContentLoaded', function() {
            loadFormData();
            
            // Add event listeners for auto-save
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    saveFormData();
                });
                input.addEventListener('input', debounce(function() {
                    saveFormData();
                }, 1000));
            });
            
            // Certificate file input handler
            const certificateInput = document.getElementById('certificates');
            certificateInput.addEventListener('change', function(e) {
                const fileList = document.getElementById('certificateList');
                fileList.innerHTML = '';
                
                if (e.target.files.length > 0) {
                    fileList.innerHTML = '<p class="text-sm text-gray-600 mt-2">File yang dipilih:</p>';
                    Array.from(e.target.files).forEach(file => {
                        const fileItem = document.createElement('div');
                        fileItem.className = 'file-item';
                        fileItem.innerHTML = `
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>${file.name}</span>
                        `;
                        fileList.appendChild(fileItem);
                    });
                }
            });
        });
        
        // Save form data to localStorage
        function saveFormData() {
            const formData = new FormData(form);
            const data = {};
            
            // Handle regular inputs
            for (let [key, value] of formData.entries()) {
                if (!key.includes('documents[')) { // Skip file inputs
                    if (data[key]) {
                        if (!Array.isArray(data[key])) {
                            data[key] = [data[key]];
                        }
                        data[key].push(value);
                    } else {
                        data[key] = value;
                    }
                }
            }
            
            // Handle checkboxes
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                if (!checkbox.name.includes('[]')) {
                    data[checkbox.name] = checkbox.checked ? '1' : '0';
                }
            });
            
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            showSaveIndicator();
        }
        
        // Load form data from localStorage
        function loadFormData() {
            const savedData = localStorage.getItem(STORAGE_KEY);
            if (!savedData) return;
            
            try {
                const data = JSON.parse(savedData);
                
                // Restore regular inputs
                Object.keys(data).forEach(key => {
                    const elements = form.querySelectorAll(`[name="${key}"]`);
                    
                    elements.forEach((element, index) => {
                        if (element.type === 'checkbox') {
                            element.checked = data[key] === '1' || data[key] === true;
                        } else if (element.type === 'radio') {
                            if (Array.isArray(data[key])) {
                                element.checked = data[key].includes(element.value);
                            } else {
                                element.checked = element.value === data[key];
                            }
                        } else if (element.tagName === 'SELECT' || element.type === 'text' || element.type === 'number' || element.type === 'date' || element.type === 'email' || element.tagName === 'TEXTAREA') {
                            if (Array.isArray(data[key])) {
                                element.value = data[key][index] || '';
                            } else {
                                element.value = data[key] || '';
                            }
                        }
                    });
                });
                
                // Handle checkbox arrays (like driving_licenses[])
                const checkboxArrays = ['driving_licenses'];
                checkboxArrays.forEach(name => {
                    if (data[name + '[]'] && Array.isArray(data[name + '[]'])) {
                        data[name + '[]'].forEach(value => {
                            const checkbox = form.querySelector(`input[name="${name}[]"][value="${value}"]`);
                            if (checkbox) checkbox.checked = true;
                        });
                    }
                });
                
            } catch (e) {
                console.error('Error loading form data:', e);
            }
        }
        
        // Clear form data
        function clearFormData() {
            if (confirm('Apakah Anda yakin ingin menghapus semua data yang telah diisi?')) {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
            }
        }
        
        // Show save indicator
        function showSaveIndicator() {
            saveIndicator.classList.add('show');
            setTimeout(() => {
                saveIndicator.classList.remove('show');
            }, 2000);
        }
        
        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Dynamic form functions
        let familyIndex = 0;
        let educationIndex = 0;
        let nonFormalEducationIndex = 0;
        let workIndex = 0;
        let languageIndex = 0;
        let socialActivityIndex = 0;
        let achievementIndex = 0;

        function addFamilyMember() {
            familyIndex++;
            const container = document.getElementById('familyMembers');
            const template = `
                <div class="dynamic-group" data-index="${familyIndex}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Hubungan Keluarga</label>
                            <select name="family_members[${familyIndex}][relationship]" class="form-input">
                                <option value="">Pilih Hubungan</option>
                                <option value="Pasangan">Pasangan</option>
                                <option value="Anak">Anak</option>
                                <option value="Ayah">Ayah</option>
                                <option value="Ibu">Ibu</option>
                                <option value="Saudara">Saudara</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama</label>
                            <input type="text" name="family_members[${familyIndex}][name]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Usia</label>
                            <input type="number" name="family_members[${familyIndex}][age]" class="form-input" min="0" max="120">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pendidikan</label>
                            <input type="text" name="family_members[${familyIndex}][education]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" name="family_members[${familyIndex}][occupation]" class="form-input">
                        </div>
                        <div class="form-group flex items-end">
                            <button type="button" class="btn-remove" onclick="removeFamilyMember(this)">Hapus</button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
            attachEventListeners();
        }

        function removeFamilyMember(button) {
            button.closest('.dynamic-group').remove();
            saveFormData();
        }

        function addEducation() {
            educationIndex++;
            const container = document.getElementById('formalEducation');
            const template = `
                <div class="dynamic-group" data-index="${educationIndex}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Jenjang Pendidikan</label>
                            <select name="formal_education[${educationIndex}][education_level]" class="form-input">
                                <option value="">Pilih Jenjang</option>
                                <option value="SMA/SMK">SMA/SMK</option>
                                <option value="Diploma">Diploma</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Institusi</label>
                            <input type="text" name="formal_education[${educationIndex}][institution_name]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jurusan</label>
                            <input type="text" name="formal_education[${educationIndex}][major]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun Mulai</label>
                            <input type="number" name="formal_education[${educationIndex}][start_year]" class="form-input" min="1950" max="2030">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun Selesai</label>
                            <input type="number" name="formal_education[${educationIndex}][end_year]" class="form-input" min="1950" max="2030">
                        </div>
                        <div class="form-group">
                            <label class="form-label">IPK/Nilai</label>
                            <input type="number" name="formal_education[${educationIndex}][gpa]" class="form-input" step="0.01" min="0" max="4">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn-remove" onclick="removeEducation(this)">Hapus Pendidikan</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
            attachEventListeners();
        }

        function removeEducation(button) {
            button.closest('.dynamic-group').remove();
            saveFormData();
        }

        function addNonFormalEducation() {
            nonFormalEducationIndex++;
            const container = document.getElementById('nonFormalEducation');
            const template = `
                <div class="dynamic-group" data-index="${nonFormalEducationIndex}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Nama Kursus/Pelatihan</label>
                            <input type="text" name="non_formal_education[${nonFormalEducationIndex}][course_name]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Penyelenggara</label>
                            <input type="text" name="non_formal_education[${nonFormalEducationIndex}][organizer]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="non_formal_education[${nonFormalEducationIndex}][date]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="non_formal_education[${nonFormalEducationIndex}][description]" class="form-input">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn-remove" onclick="removeNonFormalEducation(this)">Hapus</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
            attachEventListeners();
        }

        function removeNonFormalEducation(button) {
            button.closest('.dynamic-group').remove();
            saveFormData();
        }

        function addWorkExperience() {
            workIndex++;
            const container = document.getElementById('workExperiences');
            const template = `
                <div class="dynamic-group" data-index="${workIndex}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Nama Perusahaan</label>
                            <input type="text" name="work_experiences[${workIndex}][company_name]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat Perusahaan</label>
                            <input type="text" name="work_experiences[${workIndex}][company_address]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bergerak di Bidang</label>
                            <input type="text" name="work_experiences[${workIndex}][company_field]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Posisi/Jabatan</label>
                            <input type="text" name="work_experiences[${workIndex}][position]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun Mulai</label>
                            <input type="number" name="work_experiences[${workIndex}][start_year]" class="form-input" min="1950" max="2030">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun Selesai</label>
                            <input type="number" name="work_experiences[${workIndex}][end_year]" class="form-input" min="1950" max="2030">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gaji Terakhir</label>
                            <input type="number" name="work_experiences[${workIndex}][salary]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alasan Berhenti</label>
                            <input type="text" name="work_experiences[${workIndex}][reason_for_leaving]" class="form-input">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label">Nama & No Telp Atasan Langsung</label>
                            <input type="text" name="work_experiences[${workIndex}][supervisor_contact]" class="form-input" 
                                   placeholder="contoh: Bpk. Ahmad - 081234567890">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn-remove" onclick="removeWorkExperience(this)">Hapus Pengalaman</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
            attachEventListeners();
        }

        function removeWorkExperience(button) {
            button.closest('.dynamic-group').remove();
            saveFormData();
        }

        function addLanguageSkill() {
            languageIndex++;
            const container = document.getElementById('languageSkills');
            const template = `
                <div class="dynamic-group" data-index="${languageIndex}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Bahasa</label>
                            <select name="language_skills[${languageIndex}][language]" class="form-input">
                                <option value="">Pilih Bahasa</option>
                                <option value="Bahasa Inggris">Bahasa Inggris</option>
                                <option value="Bahasa Mandarin">Bahasa Mandarin</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kemampuan Berbicara</label>
                            <select name="language_skills[${languageIndex}][speaking_level]" class="form-input">
                                <option value="">Pilih Level</option>
                                <option value="Pemula">Pemula</option>
                                <option value="Menengah">Menengah</option>
                                <option value="Mahir">Mahir</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kemampuan Menulis</label>
                            <select name="language_skills[${languageIndex}][writing_level]" class="form-input">
                                <option value="">Pilih Level</option>
                                <option value="Pemula">Pemula</option>
                                <option value="Menengah">Menengah</option>
                                <option value="Mahir">Mahir</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn-remove" onclick="removeLanguageSkill(this)">Hapus Bahasa</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
            attachEventListeners();
        }

        function removeLanguageSkill(button) {
            button.closest('.dynamic-group').remove();
            saveFormData();
        }

        function addSocialActivity() {
            socialActivityIndex++;
            const container = document.getElementById('socialActivities');
            const template = `
                <div class="dynamic-group" data-index="${socialActivityIndex}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Nama Organisasi</label>
                            <input type="text" name="social_activities[${socialActivityIndex}][organization_name]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bidang</label>
                            <input type="text" name="social_activities[${socialActivityIndex}][field]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Periode Kepesertaan</label>
                            <input type="text" name="social_activities[${socialActivityIndex}][period]" class="form-input" 
                                   placeholder="contoh: 2020-2022">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="social_activities[${socialActivityIndex}][description]" class="form-input">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn-remove" onclick="removeSocialActivity(this)">Hapus</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
            attachEventListeners();
        }

        function removeSocialActivity(button) {
            button.closest('.dynamic-group').remove();
            saveFormData();
        }

        function addAchievement() {
            achievementIndex++;
            const container = document.getElementById('achievements');
            const template = `
                <div class="dynamic-group" data-index="${achievementIndex}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Prestasi</label>
                            <input type="text" name="achievements[${achievementIndex}][achievement]" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun</label>
                            <input type="number" name="achievements[${achievementIndex}][year]" class="form-input" min="1950" max="2030">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="achievements[${achievementIndex}][description]" class="form-input">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn-remove" onclick="removeAchievement(this)">Hapus</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
            attachEventListeners();
        }

        function removeAchievement(button) {
            button.closest('.dynamic-group').remove();
            saveFormData();
        }

        // Attach event listeners to newly added dynamic fields
        function attachEventListeners() {
            const newInputs = form.querySelectorAll('input:not([data-listener]), select:not([data-listener]), textarea:not([data-listener])');
            newInputs.forEach(input => {
                input.setAttribute('data-listener', 'true');
                input.addEventListener('change', function() {
                    saveFormData();
                });
                input.addEventListener('input', debounce(function() {
                    saveFormData();
                }, 1000));
            });
        }

        // Form validation
        document.getElementById('applicationForm').addEventListener('submit', function(e) {
            const requiredFields = ['full_name', 'email', 'position_applied'];
            let hasError = false;

            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    hasError = true;
                    input.style.borderColor = '#ef4444';
                } else {
                    input.style.borderColor = '#d1d5db';
                }
            });

            // Check agreement checkbox
            const agreementCheckbox = document.querySelector('input[name="agreement"]');
            if (!agreementCheckbox.checked) {
                hasError = true;
                alert('Anda harus menyetujui pernyataan untuk melanjutkan');
                e.preventDefault();
                return;
            }

            if (hasError) {
                e.preventDefault();
                alert('Harap isi semua field yang wajib diisi (*)');
                
                // Scroll to first error
                const firstError = form.querySelector('input[style*="border-color: rgb(239, 68, 68)"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            } else {
                // Clear localStorage on successful submission
                localStorage.removeItem(STORAGE_KEY);
            }
        });

        // Add clear data button
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.textContent = 'Hapus Semua Data';
        clearButton.className = 'btn-secondary mt-4';
        clearButton.onclick = clearFormData;
        form.appendChild(clearButton);
    </script>
</body>
</html>