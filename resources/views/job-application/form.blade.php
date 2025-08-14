<!DOCTYPE html>
<html lang="id-ID">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="id-ID">
    <title>Form Lamaran Kerja - PT Kayu Mebel Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="{{ asset('css/form-style.css') }}" rel="stylesheet">
    <!-- <link href="{{ asset('css/ktp-ocr.css') }}" rel="stylesheet"> -->

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

        <form id="applicationForm" method="POST" action="{{ route('job.application.submit') }}" enctype="multipart/form-data">
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
                        <div class="salary-input-wrapper">
                            <input type="text" 
                                   name="expected_salary" 
                                   id="expected_salary" 
                                   class="form-input" 
                                   value="{{ old('expected_salary') }}" 
                                   placeholder="5.000.000" 
                                   required
                                   inputmode="numeric">
                        </div>
                        
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
                        <label class="form-label" for="nik">NIK (Nomor Induk Kependudukan) <span class="required-star">*</span></label>
                        <input type="text" name="nik" id="nik" class="form-input" 
                            value="{{ old('nik') }}" maxlength="16" pattern="[0-9]{16}" 
                            placeholder="Masukkan 16 digit NIK" required>
                        <small class="text-gray-500 text-xs">NIK harus 16 digit angka sesuai KTP</small>
                        <!-- Enhanced OCR Upload Area akan ditambahkan di sini oleh JavaScript -->
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
                        <label class="form-label" for="ktp_address">Alamat Sesuai KTP <span class="required-star">*</span></label>
                        <textarea name="ktp_address" id="ktp_address" class="form-input" rows="3" required>{{ old('ktp_address') }}</textarea>
                    </div>

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
                    
                    <div class="form-group">
                        <label class="form-label" for="current_address">Alamat Tempat Tinggal Saat Ini <span class="required-star">*</span></label>
                        <textarea name="current_address" id="current_address" class="form-input" rows="3" required>{{ old('current_address') }}</textarea>
                        <!-- Address Copy Feature -->                        
                    </div>
                    <div class="address-copy-section">
                            <label class="address-copy-checkbox">
                                <input type="checkbox" id="copy_ktp_address" {{ old('copy_ktp_address') ? 'checked' : '' }}>
                                <span>Sama dengan alamat KTP</span>
                            </label>
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
    
   
    
    <div id="familyMembers">
        <!-- Ayah - Index 0 -->
        <div class="dynamic-group" data-index="0">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label">Hubungan Keluarga <span class="required-star">*</span></label>
                    <select name="family_members[0][relationship]" class="form-input" required>
                        <option value="">Pilih Hubungan</option>
                        <option value="Ayah" selected>Ayah</option>
                        <option value="Ibu">Ibu</option>
                        <option value="Saudara">Saudara</option>
                        <option value="Anak">Anak</option>

                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama <span class="required-star">*</span></label>
                    <input type="text" name="family_members[0][name]" class="form-input" placeholder="Nama lengkap ayah" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Usia <span class="required-star">*</span></label>
                    <input type="number" name="family_members[0][age]" class="form-input" min="0" max="120" placeholder="Contoh: 55" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Pendidikan <span class="required-star">*</span></label>
                    <input type="text" name="family_members[0][education]" class="form-input" placeholder="Contoh: SMA, S1, dll" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Pekerjaan <span class="required-star">*</span></label>
                    <input type="text" name="family_members[0][occupation]" class="form-input" placeholder="Contoh: Pensiunan, Petani, dll" required>
                </div>
                <div class="form-group flex items-end">
                    <button type="button" class="btn-remove" onclick="removeFamilyMember(this)">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Ibu - Index 1 -->
        <div class="dynamic-group" data-index="1">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label">Hubungan Keluarga <span class="required-star">*</span></label>
                    <select name="family_members[1][relationship]" class="form-input" required>
                        <option value="">Pilih Hubungan</option>
                        <option value="Ayah">Ayah</option>
                        <option value="Ibu" selected>Ibu</option>
                        <option value="Pasangan">Pasangan</option>
                        <option value="Anak">Anak</option>
                        <option value="Saudara">Saudara</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama <span class="required-star">*</span></label>
                    <input type="text" name="family_members[1][name]" class="form-input" placeholder="Nama lengkap ibu" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Usia <span class="required-star">*</span></label>
                    <input type="number" name="family_members[1][age]" class="form-input" min="0" max="120" placeholder="Contoh: 45" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Pendidikan <span class="required-star">*</span></label>
                    <input type="text" name="family_members[1][education]" class="form-input" placeholder="Contoh: SMA, S1, dll" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Pekerjaan <span class="required-star">*</span></label>
                    <input type="text" name="family_members[1][occupation]" class="form-input" placeholder="Contoh: Ibu rumah tangga, Guru, dll" required>
                </div>
                <div class="form-group flex items-end">
                    <button type="button" class="btn-remove" onclick="removeFamilyMember(this)">Hapus</button>
                </div>
            </div>
        </div>

        <!-- Pasangan - Index 2 -->
        <div class="dynamic-group" data-index="2">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="form-label">Hubungan Keluarga <span class="required-star">*</span></label>
                    <select name="family_members[2][relationship]" class="form-input" required>
                        <option value="">Pilih Hubungan</option>
                        <option value="Ayah">Ayah</option>
                        <option value="Ibu">Ibu</option>
                        <option value="Pasangan" selected>Pasangan</option>
                        <option value="Anak">Anak</option>
                        <option value="Saudara">Saudara</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama <span class="required-star">*</span></label>
                    <input type="text" name="family_members[2][name]" class="form-input" placeholder="Nama lengkap pasangan (kosongkan jika belum menikah)" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Usia <span class="required-star">*</span></label>
                    <input type="number" name="family_members[2][age]" class="form-input" min="0" max="120" placeholder="Contoh: 30" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Pendidikan <span class="required-star">*</span></label>
                    <input type="text" name="family_members[2][education]" class="form-input" placeholder="Contoh: SMA, S1, dll" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Pekerjaan <span class="required-star">*</span></label>
                    <input type="text" name="family_members[2][occupation]" class="form-input" placeholder="Contoh: Karyawan swasta, Wiraswasta, dll" required>
                </div>
                <div class="form-group flex items-end">
                    <button type="button" class="btn-remove" onclick="removeFamilyMember(this)">Hapus</button>
                </div>
            </div>
        </div>

                    <!-- Anak - Index 3 -->
                    <div class="dynamic-group" data-index="3">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Hubungan Keluarga <span class="required-star">*</span></label>
                                <select name="family_members[3][relationship]" class="form-input" required>
                                    <option value="">Pilih Hubungan</option>
                                    <option value="Ayah">Ayah</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Pasangan">Pasangan</option>
                                    <option value="Anak" selected>Anak</option>
                                    <option value="Saudara">Saudara</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama <span class="required-star">*</span></label>
                                <input type="text" name="family_members[3][name]" class="form-input" placeholder="Nama lengkap anak (kosongkan jika belum punya)" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Usia <span class="required-star">*</span></label>
                                <input type="number" name="family_members[3][age]" class="form-input" min="0" max="120" placeholder="Contoh: 7" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pendidikan <span class="required-star">*</span></label>
                                <input type="text" name="family_members[3][education]" class="form-input" placeholder="Contoh: SD, SMP, belum sekolah" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pekerjaan <span class="required-star">*</span></label>
                                <input type="text" name="family_members[3][occupation]" class="form-input" placeholder="Contoh: Pelajar, belum bekerja" required>
                            </div>
                            <div class="form-group flex items-end">
                                <button type="button" class="btn-remove" onclick="removeFamilyMember(this)">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- UBAH text button menjadi: -->
                <button type="button" class="btn-add" onclick="addFamilyMember()">+ Tambah Anggota Keluarga Lainnya</button>
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
                                <label class="form-label">
                                    IPK / Nilai Sekolah <span class="required-star">*</span>
                                </label>
                                <input
                                    type="number"
                                    name="formal_education[0][gpa]"
                                    class="form-input"
                                    min="1"
                                    max="100"
                                    step="0.01"
                                    required
                                >
                                <small class="text-muted">
                                    Masukkan IPK (1.00–4.00) atau Nilai Sekolah (1–100)
                                </small>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn-remove" onclick="removeEducation(this)" style="display:none">Hapus Pendidikan</button>
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
                                        <option value="Bahasa Inggris">Bahasa Indonesia</option>
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
                                <button type="button" class="btn-remove" onclick="removeLanguageSkill(this)" style="display:none">Hapus Bahasa</button>
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
                    <label class="form-label" for="motivation">Motivasi untuk bergabung dengan PT Kayu Mebel Indonesia Group <span class="required-star">*</span></label>
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

    <!-- Include External JavaScript menggunakan CDN yang sama dengan index.html -->
    <script src='https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js'></script>
    <script>
        // Pass Laravel session data to JavaScript
        @if(session('form_submitted'))
            var formSubmitted = true;
        @endif
        
        // Test Tesseract availability seperti index.html
        window.addEventListener('load', function() {
            console.log('✅ Tesseract loaded:', typeof Tesseract !== 'undefined');
            if (typeof Tesseract !== 'undefined') {
                console.log('📦 Tesseract ready for NIK extraction');
            } else {
                console.error('❌ Tesseract failed to load');
            }
        });
    </script>
    <script src="{{ asset('js/form-style.js') }}"></script>
    <!-- <script src="{{ asset('js/ktp-ocr.js') }}"></script> -->
</body>
</html>