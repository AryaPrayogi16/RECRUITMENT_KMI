<!DOCTYPE html>
<html lang="id-ID">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="id-ID">
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
        .form-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
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
        .btn-primary:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            transform: none;
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
        
        /* Enhanced File Upload Styles */
        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }
        
        .file-upload-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            background-color: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .file-upload-label:hover {
            background-color: #e5e7eb;
            border-color: #9ca3af;
        }
        
        .file-upload-label.has-file {
            background-color: #dbeafe;
            border-color: #3b82f6;
            border-style: solid;
        }
        
        .file-upload-label.error {
            background-color: #fee2e2;
            border-color: #ef4444;
        }
        
        .file-preview {
            margin-top: 8px;
            padding: 8px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .file-preview-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 0;
        }
        
        .file-preview-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .file-size {
            color: #6b7280;
            font-size: 12px;
        }
        
        .file-remove {
            color: #ef4444;
            cursor: pointer;
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        
        .file-remove:hover {
            background-color: #fee2e2;
        }
        
        .validation-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }
        
        .validation-message.show {
            display: block;
        }
        
        .upload-progress {
            width: 100%;
            height: 4px;
            background-color: #e5e7eb;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
            display: none;
        }
        
        .upload-progress.show {
            display: block;
        }
        
        .upload-progress-bar {
            height: 100%;
            background-color: #3b82f6;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .custom-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ef4444;
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            max-width: 400px;
            z-index: 1001;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .custom-alert.success {
            background: #10b981;
        }
        .custom-alert.warning {
            background: #f59e0b;
        }
        
        .file-icon {
            width: 20px;
            height: 20px;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f4f6;
            border-radius: 50%;
            border-top-color: #3b82f6;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Header with Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Form Lamaran Kerja</h1>
            <p class="text-lg text-gray-600">PT Kayu Mebel Indonesia</p>
            <p class="text-sm text-gray-500 mt-2">Silakan lengkapi semua data dengan benar. Field dengan tanda <span class="required-star">*</span> wajib diisi.</p>
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
                        <label class="form-label" for="expected_salary">Gaji yang Diharapkan (Rp) <span class="required-star">*</span></label>
                        <input type="number" name="expected_salary" id="expected_salary" class="form-input" 
                               value="{{ old('expected_salary') }}" placeholder="contoh: 5000000" required>
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
                        <label class="form-label" for="phone_number">Nomor Telepon <span class="required-star">*</span></label>
                        <input type="text" name="phone_number" id="phone_number" class="form-input" 
                               value="{{ old('phone_number') }}" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone_alternative">Telepon Alternatif <span class="required-star">*</span></label>
                        <input type="text" name="phone_alternative" id="phone_alternative" class="form-input" 
                               value="{{ old('phone_alternative') }}" placeholder="08xxxxxxxxxx" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="birth_place">Tempat Lahir <span class="required-star">*</span></label>
                        <input type="text" name="birth_place" id="birth_place" class="form-input" 
                               value="{{ old('birth_place') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="birth_date">Tanggal Lahir <span class="required-star">*</span></label>
                        <input type="date" name="birth_date" id="birth_date" class="form-input" 
                               value="{{ old('birth_date') }}" lang="id-ID" required>
                        <small class="text-xs text-gray-500 mt-1">Format: DD/MM/YYYY</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="gender">Jenis Kelamin <span class="required-star">*</span></label>
                        <select name="gender" id="gender" class="form-input" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="religion">Agama <span class="required-star">*</span></label>
                        <input type="text" name="religion" id="religion" class="form-input" 
                               value="{{ old('religion') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="marital_status">Status Pernikahan <span class="required-star">*</span></label>
                        <select name="marital_status" id="marital_status" class="form-input" required>
                            <option value="">Pilih Status</option>
                            <option value="Lajang" {{ old('marital_status') == 'Lajang' ? 'selected' : '' }}>Lajang</option>
                            <option value="Menikah" {{ old('marital_status') == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                            <option value="Janda" {{ old('marital_status') == 'Janda' ? 'selected' : '' }}>Janda</option>
                            <option value="Duda" {{ old('marital_status') == 'Duda' ? 'selected' : '' }}>Duda</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="ethnicity">Suku Bangsa <span class="required-star">*</span></label>
                        <input type="text" name="ethnicity" id="ethnicity" class="form-input" 
                               value="{{ old('ethnicity') }}" required>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="form-group">
                        <label class="form-label" for="current_address">Alamat Tempat Tinggal Saat Ini <span class="required-star">*</span></label>
                        <textarea name="current_address" id="current_address" class="form-input" rows="3" required>{{ old('current_address') }}</textarea>
                        <div class="mt-2">
                            <label class="form-label" for="current_address_status">Status Tempat Tinggal <span class="required-star">*</span></label>
                            <select name="current_address_status" id="current_address_status" class="form-input" required>
                                <option value="">Pilih Status</option>
                                <option value="Milik Sendiri" {{ old('current_address_status') == 'Milik Sendiri' ? 'selected' : '' }}>Milik Sendiri</option>
                                <option value="Orang Tua" {{ old('current_address_status') == 'Orang Tua' ? 'selected' : '' }}>Orang Tua</option>
                                <option value="Kontrak" {{ old('current_address_status') == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                                <option value="Sewa" {{ old('current_address_status') == 'Sewa' ? 'selected' : '' }}>Sewa</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="ktp_address">Alamat Sesuai KTP <span class="required-star">*</span></label>
                        <textarea name="ktp_address" id="ktp_address" class="form-input" rows="3" required>{{ old('ktp_address') }}</textarea>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="form-group">
                        <label class="form-label" for="height_cm">Tinggi Badan (cm) <span class="required-star">*</span></label>
                        <input type="number" name="height_cm" id="height_cm" class="form-input" 
                               value="{{ old('height_cm') }}" min="100" max="250" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="weight_kg">Berat Badan (kg) <span class="required-star">*</span></label>
                        <input type="number" name="weight_kg" id="weight_kg" class="form-input" 
                               value="{{ old('weight_kg') }}" min="30" max="200" required>
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
                <h2 class="section-title">Data Keluarga <span class="required-star">*</span></h2>
                <p class="text-sm text-gray-600 mb-4">Minimal harus mengisi 1 anggota keluarga</p>
                
                <div id="familyMembers">
                    <div class="dynamic-group" data-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Hubungan Keluarga <span class="required-star">*</span></label>
                                <select name="family_members[0][relationship]" class="form-input" required>
                                    <option value="">Pilih Hubungan</option>
                                    <option value="Pasangan">Pasangan</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Ayah">Ayah</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Saudara">Saudara</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama <span class="required-star">*</span></label>
                                <input type="text" name="family_members[0][name]" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Usia <span class="required-star">*</span></label>
                                <input type="number" name="family_members[0][age]" class="form-input" min="0" max="120" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pendidikan <span class="required-star">*</span></label>
                                <input type="text" name="family_members[0][education]" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pekerjaan <span class="required-star">*</span></label>
                                <input type="text" name="family_members[0][occupation]" class="form-input" required>
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
                <h3 class="text-lg font-medium mb-4">Pendidikan Formal <span class="required-star">*</span></h3>
                <p class="text-sm text-gray-600 mb-4">Minimal harus mengisi 1 pendidikan formal</p>
                <div id="formalEducation">
                    <div class="dynamic-group" data-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Jenjang Pendidikan <span class="required-star">*</span></label>
                                <select name="formal_education[0][education_level]" class="form-input" required>
                                    <option value="">Pilih Jenjang</option>
                                    <option value="SMA/SMK">SMA/SMK</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama Institusi <span class="required-star">*</span></label>
                                <input type="text" name="formal_education[0][institution_name]" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jurusan <span class="required-star">*</span></label>
                                <input type="text" name="formal_education[0][major]" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tahun Mulai <span class="required-star">*</span></label>
                                <input type="number" name="formal_education[0][start_year]" class="form-input" min="1950" max="2030" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tahun Selesai <span class="required-star">*</span></label>
                                <input type="number" name="formal_education[0][end_year]" class="form-input" min="1950" max="2030" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">IPK/Nilai <span class="required-star">*</span></label>
                                <input type="number" name="formal_education[0][gpa]" class="form-input" step="0.01" min="0" max="4" required>
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
                <p class="text-sm text-gray-600 mb-4">Opsional - dapat dikosongkan</p>
                <div id="nonFormalEducation"></div>
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
                    <h3 class="text-lg font-medium mb-4">Kemampuan Bahasa <span class="required-star">*</span></h3>
                    <p class="text-sm text-gray-600 mb-4">Minimal harus mengisi 1 kemampuan bahasa</p>
                    <div id="languageSkills">
                        <div class="dynamic-group" data-index="0">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Bahasa <span class="required-star">*</span></label>
                                    <select name="language_skills[0][language]" class="form-input" required>
                                        <option value="">Pilih Bahasa</option>
                                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                                        <option value="Bahasa Mandarin">Bahasa Mandarin</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Kemampuan Berbicara <span class="required-star">*</span></label>
                                    <select name="language_skills[0][speaking_level]" class="form-input" required>
                                        <option value="">Pilih Level</option>
                                        <option value="Pemula">Pemula</option>
                                        <option value="Menengah">Menengah</option>
                                        <option value="Mahir">Mahir</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Kemampuan Menulis <span class="required-star">*</span></label>
                                    <select name="language_skills[0][writing_level]" class="form-input" required>
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
                <p class="text-sm text-gray-600 mb-4">Bagian ini opsional - dapat dikosongkan</p>
                
                <!-- Aktivitas Sosial -->
                <h3 class="text-lg font-medium mb-4">Aktivitas Sosial/Organisasi</h3>
                <div id="socialActivities"></div>
                <button type="button" class="btn-add" onclick="addSocialActivity()">+ Tambah Aktivitas</button>
                
                <!-- Penghargaan -->
                <h3 class="text-lg font-medium mb-4 mt-8">Penghargaan/Prestasi</h3>
                <div id="achievements"></div>
                <button type="button" class="btn-add" onclick="addAchievement()">+ Tambah Prestasi</button>
            </div>

            <!-- 7. Pengalaman Kerja -->
            <div class="form-section" data-section="7">
                <h2 class="section-title">Pengalaman Kerja</h2>
                <p class="text-sm text-gray-600 mb-4">Bagian ini opsional - dapat dikosongkan jika belum memiliki pengalaman kerja</p>
                <div id="workExperiences"></div>
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
                    <label class="form-label" for="motivation">Motivasi untuk bergabung dengan PT Kayu Mebel Indonesia <span class="required-star">*</span></label>
                    <textarea name="motivation" id="motivation" class="form-input" rows="3" 
                              placeholder="Jelaskan motivasi Anda bergabung dengan perusahaan" required>{{ old('motivation') }}</textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="strengths">Kelebihan Anda <span class="required-star">*</span></label>
                        <textarea name="strengths" id="strengths" class="form-input" rows="3" 
                                  placeholder="Sebutkan minimal 3 kelebihan Anda" required>{{ old('strengths') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="weaknesses">Kekurangan Anda <span class="required-star">*</span></label>
                        <textarea name="weaknesses" id="weaknesses" class="form-input" rows="3" 
                                  placeholder="Sebutkan minimal 3 kekurangan Anda" required>{{ old('weaknesses') }}</textarea>
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
                        <label class="form-label" for="start_work_date">Jika diterima, kapan Anda dapat mulai bekerja? <span class="required-star">*</span></label>
                        <input type="date" name="start_work_date" id="start_work_date" class="form-input" 
                               value="{{ old('start_work_date') }}" lang="id-ID" required>
                        <small class="text-xs text-gray-500 mt-1">Format: DD/MM/YYYY (harus setelah hari ini)</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="information_source">Sumber informasi lowongan kerja dari? <span class="required-star">*</span></label>
                    <input type="text" name="information_source" id="information_source" class="form-input" 
                           value="{{ old('information_source') }}" placeholder="contoh: Website, Teman, Media Sosial, JobStreet" required>
                </div>
            </div>

            <!-- 9. Upload Dokumen & Pernyataan -->
            <div class="form-section" data-section="9">
                <h2 class="section-title">Upload Dokumen & Pernyataan</h2>
                <p class="text-sm text-gray-600 mb-4">Format yang diterima: PDF, JPG, PNG (Maksimal 2MB per file)</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- CV Upload -->
                    <div class="form-group">
                        <label class="form-label" for="cv">CV/Resume <span class="required-star">*</span></label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="cv" id="cv" class="file-upload-input" accept=".pdf" required>
                            <label for="cv" class="file-upload-label" id="cv-label">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span>Pilih file PDF</span>
                            </label>
                            <div class="validation-message" id="cv-error"></div>
                            <div class="file-preview" id="cv-preview" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <!-- Photo Upload -->
                    <div class="form-group">
                        <label class="form-label" for="photo">Foto <span class="required-star">*</span></label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="photo" id="photo" class="file-upload-input" accept=".jpg,.jpeg,.png" required>
                            <label for="photo" class="file-upload-label" id="photo-label">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Pilih file JPG/PNG</span>
                            </label>
                            <div class="validation-message" id="photo-error"></div>
                            <div class="file-preview" id="photo-preview" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <!-- Transcript Upload -->
                    <div class="form-group">
                        <label class="form-label" for="transcript">Transkrip Nilai <span class="required-star">*</span></label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="transcript" id="transcript" class="file-upload-input" accept=".pdf" required>
                            <label for="transcript" class="file-upload-label" id="transcript-label">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Pilih file PDF</span>
                            </label>
                            <div class="validation-message" id="transcript-error"></div>
                            <div class="file-preview" id="transcript-preview" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <!-- Certificates Upload (Multiple) -->
                    <div class="form-group">
                        <label class="form-label" for="certificates">Sertifikat (opsional - bisa lebih dari satu)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="certificates[]" id="certificates" class="file-upload-input" accept=".pdf" multiple>
                            <label for="certificates" class="file-upload-label" id="certificates-label">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <span>Pilih file PDF (dapat lebih dari 1)</span>
                            </label>
                            <div class="validation-message" id="certificates-error"></div>
                            <div class="file-preview" id="certificates-preview" style="display: none;"></div>
                        </div>
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
                <button type="submit" class="btn-primary px-8 py-3 text-lg" id="submitBtn">
                    Kirim Lamaran
                </button>
                <p class="text-sm text-gray-500 mt-2">
                    Pastikan semua data wajib telah diisi dengan benar sebelum mengirim
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
        // Check if form was successfully submitted (for clearing localStorage)
        @if(session('form_submitted'))
            localStorage.removeItem('jobApplicationFormData');
        @endif

        // Enhanced File Upload System dengan validasi foto yang diperbaiki
        const fileValidation = {
            cv: {
                types: ['application/pdf'],
                extensions: ['pdf'],
                maxSize: 2 * 1024 * 1024,
                required: true
            },
            photo: {
                types: ['image/jpeg', 'image/jpg', 'image/png', 'image/pjpeg', 'image/x-png'],
                extensions: ['jpg', 'jpeg', 'png'],
                maxSize: 2 * 1024 * 1024,
                required: true
            },
            transcript: {
                types: ['application/pdf'],
                extensions: ['pdf'],
                maxSize: 2 * 1024 * 1024,
                required: true
            },
            certificates: {
                types: ['application/pdf'],
                extensions: ['pdf'],
                maxSize: 2 * 1024 * 1024,
                required: false
            }
        };

        // Enhanced file validation function
        async function validateFile(file, validation) {
            console.log('Validating file:', {
                name: file.name,
                type: file.type,
                size: file.size,
                lastModified: file.lastModified
            });

            // Check if file is valid
            if (!file || file.size === 0) {
                return { valid: false, error: 'File tidak valid atau kosong' };
            }

            // Get file extension
            const extension = file.name.toLowerCase().split('.').pop();
            
            // Check file extension first
            if (!validation.extensions.includes(extension)) {
                const allowedExtensions = validation.extensions.join(', ').toUpperCase();
                return { valid: false, error: `Format file harus ${allowedExtensions}. File Anda: ${extension.toUpperCase()}` };
            }

            // Check file size
            if (file.size > validation.maxSize) {
                return { valid: false, error: 'Ukuran file maksimal 2MB' };
            }

            // For photo files, do additional image validation
            if (validation.extensions.includes('jpg') || validation.extensions.includes('jpeg') || validation.extensions.includes('png')) {
                return await validateImageFile(file, validation);
            }

            // Check MIME type for non-image files
            if (!validation.types.includes(file.type)) {
                console.warn('MIME type mismatch:', {
                    detected: file.type,
                    allowed: validation.types
                });
                const allowedExtensions = validation.extensions.join(', ').toUpperCase();
                return { valid: false, error: `Format file harus ${allowedExtensions}. Tipe file terdeteksi: ${file.type}` };
            }

            return { valid: true };
        }

        // Enhanced image validation function
        function validateImageFile(file, validation) {
            return new Promise((resolve) => {
                // Check MIME type first, but be more lenient for images
                const extension = file.name.toLowerCase().split('.').pop();
                
                if (!validation.types.includes(file.type)) {
                    console.warn('MIME type mismatch for image:', {
                        detected: file.type,
                        allowed: validation.types,
                        extension: extension
                    });
                    
                    // If extension is correct but MIME type is wrong, try to validate as image anyway
                    if (!validation.extensions.includes(extension)) {
                        resolve({ valid: false, error: `Format file harus JPG atau PNG. Tipe file terdeteksi: ${file.type}` });
                        return;
                    }
                }

                // Try to load as image to verify it's actually a valid image
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = new Image();
                    
                    img.onload = function() {
                        console.log('Image validation successful:', {
                            width: img.width,
                            height: img.height,
                            size: file.size,
                            type: file.type
                        });
                        resolve({ valid: true });
                    };
                    
                    img.onerror = function() {
                        console.error('Image validation failed - not a valid image');
                        resolve({ valid: false, error: 'File bukan gambar yang valid atau file rusak' });
                    };
                    
                    img.src = e.target.result;
                };
                
                reader.onerror = function() {
                    console.error('FileReader error');
                    resolve({ valid: false, error: 'Tidak dapat membaca file. File mungkin rusak.' });
                };
                
                // Read as data URL to validate image
                reader.readAsDataURL(file);
            });
        }

        // Enhanced file upload handlers
        document.getElementById('cv').addEventListener('change', function(e) {
            handleFileUpload(e, 'cv');
        });

        document.getElementById('photo').addEventListener('change', function(e) {
            handlePhotoUpload(e, 'photo');
        });

        document.getElementById('transcript').addEventListener('change', function(e) {
            handleFileUpload(e, 'transcript');
        });

        document.getElementById('certificates').addEventListener('change', function(e) {
            handleMultipleFileUpload(e, 'certificates');
        });

        // Standard file upload handler
        async function handleFileUpload(event, fieldName) {
            const file = event.target.files[0];
            const validation = fileValidation[fieldName];
            const label = document.getElementById(`${fieldName}-label`);
            const preview = document.getElementById(`${fieldName}-preview`);
            const error = document.getElementById(`${fieldName}-error`);

            // Reset states
            label.classList.remove('has-file', 'error');
            preview.style.display = 'none';
            error.textContent = '';
            error.classList.remove('show');

            if (!file) {
                label.innerHTML = getDefaultLabelContent(fieldName);
                return;
            }

            try {
                // Validate file
                const validationResult = await validateFile(file, validation);
                
                if (!validationResult.valid) {
                    showFileError(fieldName, validationResult.error);
                    event.target.value = '';
                    return;
                }

                // Show preview
                showFilePreview(fieldName, file);
            } catch (error) {
                console.error('File validation error:', error);
                showFileError(fieldName, 'Terjadi kesalahan saat memvalidasi file. Silakan coba lagi.');
                event.target.value = '';
            }
        }

        // Enhanced photo upload handler
        async function handlePhotoUpload(event, fieldName) {
            const file = event.target.files[0];
            const validation = fileValidation[fieldName];
            const label = document.getElementById(`${fieldName}-label`);
            const preview = document.getElementById(`${fieldName}-preview`);
            const error = document.getElementById(`${fieldName}-error`);

            // Reset states
            label.classList.remove('has-file', 'error');
            preview.style.display = 'none';
            error.textContent = '';
            error.classList.remove('show');

            if (!file) {
                label.innerHTML = getDefaultLabelContent(fieldName);
                return;
            }

            // Show loading state
            label.innerHTML = `
                <div class="loading-spinner mr-2"></div>
                <span>Memvalidasi foto...</span>
            `;

            try {
                // Debug file info
                console.log('Photo upload debug:', {
                    name: file.name,
                    type: file.type,
                    size: file.size,
                    lastModified: new Date(file.lastModified).toISOString()
                });

                // Validate file (this returns a Promise for images)
                const validationResult = await validateFile(file, validation);
                
                if (!validationResult.valid) {
                    showFileError(fieldName, validationResult.error);
                    event.target.value = '';
                    return;
                }

                // Show preview
                showFilePreview(fieldName, file);
                
                // Log successful validation
                console.log('Photo validation successful:', {
                    name: file.name,
                    type: file.type,
                    size: file.size
                });
                
            } catch (error) {
                console.error('Photo validation error:', error);
                showFileError(fieldName, 'Terjadi kesalahan saat memvalidasi file. Silakan coba lagi.');
                event.target.value = '';
            }
        }

        function handleMultipleFileUpload(event, fieldName) {
            const files = Array.from(event.target.files);
            const validation = fileValidation[fieldName];
            const label = document.getElementById(`${fieldName}-label`);
            const preview = document.getElementById(`${fieldName}-preview`);
            const error = document.getElementById(`${fieldName}-error`);

            // Reset states
            label.classList.remove('has-file', 'error');
            preview.style.display = 'none';
            preview.innerHTML = '';
            error.textContent = '';
            error.classList.remove('show');

            if (files.length === 0) {
                label.innerHTML = getDefaultLabelContent(fieldName);
                return;
            }

            let validFiles = [];
            let errors = [];

            // Process files sequentially to avoid Promise issues
            Promise.all(files.map(async (file, index) => {
                try {
                    const validationResult = await validateFile(file, validation);
                    if (validationResult.valid) {
                        validFiles.push(file);
                    } else {
                        errors.push(`File ${index + 1} (${file.name}): ${validationResult.error}`);
                    }
                } catch (error) {
                    errors.push(`File ${index + 1} (${file.name}): Gagal memvalidasi`);
                }
            })).then(() => {
                if (errors.length > 0) {
                    showFileError(fieldName, errors.join('<br>'));
                    event.target.value = '';
                    return;
                }

                // Show preview for multiple files
                showMultipleFilePreview(fieldName, validFiles);
            });
        }

        // Enhanced error display
        function showFileError(fieldName, errorMessage) {
            const label = document.getElementById(`${fieldName}-label`);
            const error = document.getElementById(`${fieldName}-error`);
            
            label.classList.add('error');
            label.innerHTML = `
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span>File tidak valid</span>
            `;
            
            error.innerHTML = `
                <div class="text-red-600 font-medium">Error:</div>
                <div>${errorMessage}</div>
                <div class="text-xs mt-1 text-gray-600">
                    ${fieldName === 'photo' ? 
                        'Pastikan file yang Anda upload adalah foto dengan format JPG atau PNG dan ukuran maksimal 2MB.' :
                        'Pastikan file sesuai dengan format yang diminta dan ukuran maksimal 2MB.'
                    }
                </div>
            `;
            error.classList.add('show');
        }

        function showFilePreview(fieldName, file) {
            const label = document.getElementById(`${fieldName}-label`);
            const preview = document.getElementById(`${fieldName}-preview`);
            
            label.classList.add('has-file');
            label.innerHTML = `
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>File dipilih</span>
            `;

            preview.innerHTML = `
                <div class="file-preview-item">
                    <div class="file-preview-info">
                        ${getFileIcon(file.type)}
                        <span>${file.name}</span>
                        <span class="file-size">(${formatFileSize(file.size)})</span>
                    </div>
                    <span class="file-remove" onclick="removeFile('${fieldName}')">×</span>
                </div>
            `;
            preview.style.display = 'block';
        }

        function showMultipleFilePreview(fieldName, files) {
            const label = document.getElementById(`${fieldName}-label`);
            const preview = document.getElementById(`${fieldName}-preview`);
            
            label.classList.add('has-file');
            label.innerHTML = `
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>${files.length} file dipilih</span>
            `;

            let previewHtml = '';
            files.forEach((file, index) => {
                previewHtml += `
                    <div class="file-preview-item">
                        <div class="file-preview-info">
                            ${getFileIcon(file.type)}
                            <span>${file.name}</span>
                            <span class="file-size">(${formatFileSize(file.size)})</span>
                        </div>
                        <span class="file-remove" onclick="removeMultipleFile('${fieldName}', ${index})">×</span>
                    </div>
                `;
            });
            
            preview.innerHTML = previewHtml;
            preview.style.display = 'block';
        }

        function removeFile(fieldName) {
            const input = document.getElementById(fieldName);
            const label = document.getElementById(`${fieldName}-label`);
            const preview = document.getElementById(`${fieldName}-preview`);
            
            input.value = '';
            label.classList.remove('has-file');
            label.innerHTML = getDefaultLabelContent(fieldName);
            preview.style.display = 'none';
        }

        function removeMultipleFile(fieldName, indexToRemove) {
            const input = document.getElementById(fieldName);
            const dt = new DataTransfer();
            const files = input.files;
            
            for (let i = 0; i < files.length; i++) {
                if (i !== indexToRemove) {
                    dt.items.add(files[i]);
                }
            }
            
            input.files = dt.files;
            
            if (dt.files.length === 0) {
                removeFile(fieldName);
            } else {
                handleMultipleFileUpload({ target: input }, fieldName);
            }
        }

        function getDefaultLabelContent(fieldName) {
            const contents = {
                cv: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                     </svg>
                     <span>Pilih file PDF</span>`,
                photo: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                     </svg>
                     <span>Pilih file JPG/PNG</span>`,
                transcript: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                         </svg>
                         <span>Pilih file PDF</span>`,
                certificates: `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                           </svg>
                           <span>Pilih file PDF (dapat lebih dari 1)</span>`
            };
            return contents[fieldName];
        }

        function getFileIcon(fileType) {
            if (fileType.includes('image')) {
                return `<svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>`;
            } else {
                return `<svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>`;
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Form State Preservation
        const STORAGE_KEY = 'jobApplicationFormData';
        const form = document.getElementById('applicationForm');
        const saveIndicator = document.getElementById('saveIndicator');
        
        // Required field IDs for validation
        const requiredFields = [
            'position_applied', 'expected_salary', 'full_name', 'email', 'phone_number', 
            'phone_alternative', 'birth_place', 'birth_date', 'gender', 'religion', 
            'marital_status', 'ethnicity', 'current_address', 'current_address_status', 
            'ktp_address', 'height_cm', 'weight_kg', 'motivation', 'strengths', 
            'weaknesses', 'start_work_date', 'information_source', 'cv', 'photo', 'transcript'
        ];
        
        // Load saved data on page load
        window.addEventListener('DOMContentLoaded', function() {
            loadFormData();
            
            // Add event listeners for auto-save
            const inputs = form.querySelectorAll('input:not([type="file"]), select, textarea');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    saveFormData();
                });
                input.addEventListener('input', debounce(function() {
                    saveFormData();
                }, 1000));
            });
        });
        
        // Save form data to localStorage
        function saveFormData() {
            const formData = new FormData(form);
            const data = {};
            
            // Handle regular inputs
            for (let [key, value] of formData.entries()) {
                if (!key.includes('cv') && !key.includes('photo') && !key.includes('transcript') && !key.includes('certificates')) {
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
        
        // Show save indicator
        function showSaveIndicator() {
            saveIndicator.classList.add('show');
            setTimeout(() => {
                saveIndicator.classList.remove('show');
            }, 2000);
        }
        
        // Show custom alert
        function showAlert(message, type = 'error') {
            const alert = document.createElement('div');
            alert.className = `custom-alert ${type}`;
            alert.innerHTML = `
                <div class="font-medium">${type === 'error' ? 'Error!' : type === 'success' ? 'Berhasil!' : 'Peringatan!'}</div>
                <div class="text-sm mt-1">${message}</div>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
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

        // Get default templates
        function getDefaultFamilyMember(index) {
            return `
                <div class="dynamic-group" data-index="${index}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Hubungan Keluarga <span class="required-star">*</span></label>
                            <select name="family_members[${index}][relationship]" class="form-input" required>
                                <option value="">Pilih Hubungan</option>
                                <option value="Pasangan">Pasangan</option>
                                <option value="Anak">Anak</option>
                                <option value="Ayah">Ayah</option>
                                <option value="Ibu">Ibu</option>
                                <option value="Saudara">Saudara</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama <span class="required-star">*</span></label>
                            <input type="text" name="family_members[${index}][name]" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Usia <span class="required-star">*</span></label>
                            <input type="number" name="family_members[${index}][age]" class="form-input" min="0" max="120" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pendidikan <span class="required-star">*</span></label>
                            <input type="text" name="family_members[${index}][education]" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pekerjaan <span class="required-star">*</span></label>
                            <input type="text" name="family_members[${index}][occupation]" class="form-input" required>
                        </div>
                        <div class="form-group flex items-end">
                            <button type="button" class="btn-remove" onclick="removeFamilyMember(this)" ${index === 0 ? 'style="display:none"' : ''}>Hapus</button>
                        </div>
                    </div>
                </div>
            `;
        }

        function getDefaultEducation(index) {
            return `
                <div class="dynamic-group" data-index="${index}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Jenjang Pendidikan <span class="required-star">*</span></label>
                            <select name="formal_education[${index}][education_level]" class="form-input" required>
                                <option value="">Pilih Jenjang</option>
                                <option value="SMA/SMK">SMA/SMK</option>
                                <option value="Diploma">Diploma</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Institusi <span class="required-star">*</span></label>
                            <input type="text" name="formal_education[${index}][institution_name]" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jurusan <span class="required-star">*</span></label>
                            <input type="text" name="formal_education[${index}][major]" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun Mulai <span class="required-star">*</span></label>
                            <input type="number" name="formal_education[${index}][start_year]" class="form-input" min="1950" max="2030" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun Selesai <span class="required-star">*</span></label>
                            <input type="number" name="formal_education[${index}][end_year]" class="form-input" min="1950" max="2030" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">IPK/Nilai <span class="required-star">*</span></label>
                            <input type="number" name="formal_education[${index}][gpa]" class="form-input" step="0.01" min="0" max="4" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn-remove" onclick="removeEducation(this)" ${index === 0 ? 'style="display:none"' : ''}>Hapus Pendidikan</button>
                    </div>
                </div>
            `;
        }

        function getDefaultLanguageSkill(index) {
            return `
                <div class="dynamic-group" data-index="${index}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label class="form-label">Bahasa <span class="required-star">*</span></label>
                            <select name="language_skills[${index}][language]" class="form-input" required>
                                <option value="">Pilih Bahasa</option>
                                <option value="Bahasa Inggris">Bahasa Inggris</option>
                                <option value="Bahasa Mandarin">Bahasa Mandarin</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kemampuan Berbicara <span class="required-star">*</span></label>
                            <select name="language_skills[${index}][speaking_level]" class="form-input" required>
                                <option value="">Pilih Level</option>
                                <option value="Pemula">Pemula</option>
                                <option value="Menengah">Menengah</option>
                                <option value="Mahir">Mahir</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kemampuan Menulis <span class="required-star">*</span></label>
                            <select name="language_skills[${index}][writing_level]" class="form-input" required>
                                <option value="">Pilih Level</option>
                                <option value="Pemula">Pemula</option>
                                <option value="Menengah">Menengah</option>
                                <option value="Mahir">Mahir</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn-remove" onclick="removeLanguageSkill(this)" ${index === 0 ? 'style="display:none"' : ''}>Hapus Bahasa</button>
                    </div>
                </div>
            `;
        }

        function addFamilyMember() {
            familyIndex++;
            const container = document.getElementById('familyMembers');
            container.insertAdjacentHTML('beforeend', getDefaultFamilyMember(familyIndex));
            attachEventListeners();
            updateRemoveButtons('familyMembers');
        }

        function removeFamilyMember(button) {
            const container = document.getElementById('familyMembers');
            if (container.children.length > 1) {
                button.closest('.dynamic-group').remove();
                updateRemoveButtons('familyMembers');
                saveFormData();
            } else {
                showAlert('Minimal harus ada 1 anggota keluarga.', 'warning');
            }
        }

        function addEducation() {
            educationIndex++;
            const container = document.getElementById('formalEducation');
            container.insertAdjacentHTML('beforeend', getDefaultEducation(educationIndex));
            attachEventListeners();
            updateRemoveButtons('formalEducation');
        }

        function removeEducation(button) {
            const container = document.getElementById('formalEducation');
            if (container.children.length > 1) {
                button.closest('.dynamic-group').remove();
                updateRemoveButtons('formalEducation');
                saveFormData();
            } else {
                showAlert('Minimal harus ada 1 pendidikan formal.', 'warning');
            }
        }

        function addLanguageSkill() {
            languageIndex++;
            const container = document.getElementById('languageSkills');
            container.insertAdjacentHTML('beforeend', getDefaultLanguageSkill(languageIndex));
            attachEventListeners();
            updateRemoveButtons('languageSkills');
        }

        function removeLanguageSkill(button) {
            const container = document.getElementById('languageSkills');
            if (container.children.length > 1) {
                button.closest('.dynamic-group').remove();
                updateRemoveButtons('languageSkills');
                saveFormData();
            } else {
                showAlert('Minimal harus ada 1 kemampuan bahasa.', 'warning');
            }
        }

        // Optional dynamic functions
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

        // Update remove button visibility
        function updateRemoveButtons(containerId) {
            const container = document.getElementById(containerId);
            const removeButtons = container.querySelectorAll('.btn-remove');
            removeButtons.forEach((btn, index) => {
                btn.style.display = index === 0 && container.children.length === 1 ? 'none' : 'inline-block';
            });
        }

        // Attach event listeners to newly added dynamic fields
        function attachEventListeners() {
            const newInputs = form.querySelectorAll('input:not([data-listener]):not([type="file"]), select:not([data-listener]), textarea:not([data-listener])');
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

        // Clean empty optional dynamic fields before submit
        function cleanEmptyOptionalFields() {
            const optionalSections = ['non_formal_education', 'work_experiences', 'social_activities', 'achievements'];
            
            optionalSections.forEach(section => {
                const inputs = document.querySelectorAll(`input[name^="${section}["], select[name^="${section}["], textarea[name^="${section}["]`);
                const groups = {};
                
                inputs.forEach(input => {
                    const match = input.name.match(new RegExp(`${section}\\[(\\d+)\\]`));
                    if (match) {
                        const index = match[1];
                        if (!groups[index]) groups[index] = [];
                        groups[index].push(input);
                    }
                });
                
                Object.keys(groups).forEach(index => {
                    const groupInputs = groups[index];
                    const hasValue = groupInputs.some(input => {
                        if (input.type === 'checkbox') return input.checked;
                        return input.value && input.value.trim() !== '';
                    });
                    
                    if (!hasValue) {
                        groupInputs.forEach(input => {
                            input.removeAttribute('name');
                        });
                    }
                });
            });
        }

        // Enhanced form validation dengan async file validation
        document.getElementById('applicationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            let errors = [];
            let hasError = false;
            
            // Clean empty optional fields first
            cleanEmptyOptionalFields();
            
            // Reset all field styles
            form.querySelectorAll('.form-input').forEach(input => {
                input.classList.remove('error');
            });
            
            // Check required basic fields
            requiredFields.forEach(fieldId => {
                const input = document.getElementById(fieldId);
                if (input && (!input.value || input.value.trim() === '')) {
                    hasError = true;
                    input.classList.add('error');
                    if (input.type === 'file') {
                        document.getElementById(`${fieldId}-label`).classList.add('error');
                    }
                    errors.push(`${input.previousElementSibling.textContent.replace(' *', '')} harus diisi`);
                }
            });
            
            // ✅ PERUBAHAN: Enhanced date validation dengan explicit parsing
            const startWorkDate = document.getElementById('start_work_date');
            if (startWorkDate && startWorkDate.value) {
                console.log('Validating start_work_date:', startWorkDate.value);
                
                // Parse date using explicit format (YYYY-MM-DD)
                const selectedDateParts = startWorkDate.value.split('-');
                if (selectedDateParts.length === 3) {
                    const selectedDate = new Date(
                        parseInt(selectedDateParts[0]), // year
                        parseInt(selectedDateParts[1]) - 1, // month (0-based)
                        parseInt(selectedDateParts[2]) // day
                    );
                    
                    const today = new Date();
                    today.setHours(23, 59, 59, 999); // Set to end of today
                    
                    console.log('Selected date:', selectedDate);
                    console.log('Today (end of day):', today);
                    console.log('Is selected date after today?', selectedDate > today);
                    
                    if (selectedDate <= today) {
                        hasError = true;
                        startWorkDate.classList.add('error');
                        const todayStr = new Date().toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: '2-digit', 
                            year: 'numeric'
                        });
                        errors.push(`Tanggal mulai kerja harus setelah ${todayStr}`);
                    }
                } else {
                    hasError = true;
                    startWorkDate.classList.add('error');
                    errors.push('Format tanggal mulai kerja tidak valid');
                }
            }

            const familyContainer = document.getElementById('familyMembers');
            const educationContainer = document.getElementById('formalEducation');
            const languageContainer = document.getElementById('languageSkills');
            
            if (familyContainer.children.length === 0) {
                hasError = true;
                errors.push('Data keluarga minimal harus diisi 1 anggota');
            }
            
            if (educationContainer.children.length === 0) {
                hasError = true;
                errors.push('Pendidikan formal minimal harus diisi 1 pendidikan');
            }
            
            if (languageContainer.children.length === 0) {
                hasError = true;
                errors.push('Kemampuan bahasa minimal harus diisi 1 bahasa');
            }
            
            [
                {container: familyContainer, name: 'Data Keluarga'},
                {container: educationContainer, name: 'Pendidikan Formal'},
                {container: languageContainer, name: 'Kemampuan Bahasa'}
            ].forEach(section => {
                Array.from(section.container.children).forEach((group, index) => {
                    const requiredInputs = group.querySelectorAll('input[required], select[required]');
                    requiredInputs.forEach(input => {
                        if (!input.value || input.value.trim() === '') {
                            hasError = true;
                            input.classList.add('error');
                            errors.push(`${section.name} #${index + 1}: ${input.previousElementSibling.textContent.replace(' *', '')} harus diisi`);
                        }
                    });
                });
            });

            const agreementCheckbox = document.querySelector('input[name="agreement"]');
            if (!agreementCheckbox.checked) {
                hasError = true;
                errors.push('Anda harus menyetujui pernyataan untuk melanjutkan');
            }

            const fileInputs = ['cv', 'photo', 'transcript'];
            for (const fieldName of fileInputs) {
                const input = document.getElementById(fieldName);
                if (input && input.files.length > 0) {
                    const file = input.files[0];
                    const validation = fileValidation[fieldName];
                    
                    try {
                        const validationResult = await validateFile(file, validation);
                        
                        if (!validationResult.valid) {
                            hasError = true;
                            errors.push(`${fieldName.toUpperCase()}: ${validationResult.error}`);
                            showFileError(fieldName, validationResult.error);
                        }
                    } catch (error) {
                        hasError = true;
                        errors.push(`${fieldName.toUpperCase()}: Gagal memvalidasi file`);
                        console.error(`Validation error for ${fieldName}:`, error);
                    }
                } else if (fileValidation[fieldName].required) {
                    hasError = true;
                    errors.push(`${fieldName.toUpperCase()}: File harus diupload`);
                }
            }
            
            if (hasError) {
                let errorMessage = 'Harap lengkapi data berikut:\n\n';
                errors.slice(0, 10).forEach((error, index) => {
                    errorMessage += `${index + 1}. ${error}\n`;
                });
                if (errors.length > 10) {
                    errorMessage += `\n... dan ${errors.length - 10} field lainnya`;
                }
                
                showAlert(errorMessage.replace(/\n/g, '<br>'), 'error');
                
                // Scroll to first error
                const firstError = form.querySelector('.form-input.error, .file-upload-label.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (firstError.classList.contains('form-input')) {
                        firstError.focus();
                    }
                }
            } else {
                // Disable submit button to prevent double submission
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading-spinner mr-2"></span> Mengirim...';
                
                // Submit form
                console.log('All validations passed, submitting form...');
                this.submit();
            }
        });

        // Initialize remove button states
        document.addEventListener('DOMContentLoaded', function() {
            updateRemoveButtons('familyMembers');
            updateRemoveButtons('formalEducation');
            updateRemoveButtons('languageSkills');
        });
    </script>
</body>
</html>