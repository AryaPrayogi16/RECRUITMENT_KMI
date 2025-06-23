<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FLK - {{ $candidate->personalData->full_name ?? 'Kandidat' }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm 1cm 1cm 1cm; /* top right bottom left */
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #333;
            padding-left: 0.5cm;
        }
        
        /* Headers */
        h1 {
            color: #1a202c;
            font-size: 18pt;
            margin-bottom: 3pt;
            text-align: center;
        }
        
        h2 {
            color: #2d3748;
            font-size: 11pt;
            margin: 8pt 0 4pt 0;
            padding-bottom: 2pt;
            border-bottom: 1.5pt solid #4f46e5;
        }
        
        h3 {
            color: #4a5568;
            font-size: 9.5pt;
            margin: 6pt 0 3pt 0;
            font-weight: bold;
        }
        
        /* Header Box */
        .header-box {
            text-align: center;
            background: #f8f9fa;
            border: 0.5pt solid #e2e8f0;
            padding: 8pt;
            margin-bottom: 8pt;
            border-radius: 3pt;
        }
        
        .header-box .subtitle {
            font-size: 9pt;
            color: #4a5568;
            margin: 2pt 0;
        }
        
        .header-box .meta {
            font-size: 8pt;
            color: #6b7280;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6pt;
        }
        
        th, td {
            padding: 3pt 4pt;
            text-align: left;
            font-size: 8.5pt;
            border: 0.5pt solid #e2e8f0;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 8pt;
        }
        
        /* Info Layout */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 6pt;
        }
        
        .info-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10pt;
        }
        
        .info-col:last-child {
            padding-right: 0;
            padding-left: 10pt;
        }
        
        .info-item {
            margin-bottom: 2pt;
            font-size: 8.5pt;
            display: flex;
        }
        
        .info-label {
            font-weight: bold;
            width: 100pt;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
            color: #4a5568;
        }
        
        /* Compact styles */
        .compact-section {
            margin-bottom: 6pt;
        }
        
        .work-box {
            border: 0.5pt solid #e2e8f0;
            padding: 4pt 6pt;
            margin-bottom: 5pt;
            background: #fafafa;
        }
        
        .work-header {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 2pt;
            color: #2d3748;
        }
        
        .empty {
            color: #9ca3af;
            font-style: italic;
            font-size: 8pt;
        }
        
        /* Lists */
        ul {
            margin: 0;
            padding-left: 15pt;
        }
        
        li {
            margin-bottom: 1pt;
            font-size: 8.5pt;
        }
        
        .checkbox-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .checkbox-list li {
            display: inline-block;
            margin-right: 12pt;
            font-size: 8.5pt;
        }
        
        /* Text Box */
        .text-box {
            background: #f9fafb;
            border: 0.5pt solid #e5e7eb;
            padding: 4pt;
            margin: 4pt 0;
            font-size: 8pt;
            color: #4b5563;
        }
        
        /* Footer */
        .footer {
            margin-top: 10pt;
            padding-top: 5pt;
            border-top: 0.5pt solid #e2e8f0;
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
        }
        
        /* Page break */
        .page-break {
            page-break-before: always;
        }
        
        /* Prevent empty space */
        .no-margin { margin: 0; }
        .tight { line-height: 1; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-box">
        @php
            $photoDocument = $candidate->documentUploads->where('document_type', 'photo')->first();
            $photoPath = null;
            if ($photoDocument) {
                $fullPath = storage_path('app/public/' . $photoDocument->file_path);
                if (file_exists($fullPath)) {
                    $photoPath = $fullPath;
                }
            }
        @endphp
        
        <div style="display: table; width: 100%;">
            @if($photoPath)
                <div style="display: table-cell; width: 80pt; vertical-align: middle;">
                    <div style="width: 80pt; height: 100pt; overflow: hidden; border: 1pt solid #e2e8f0; border-radius: 4pt;">
                        <img src="data:{{ mime_content_type($photoPath) }};base64,{{ base64_encode(file_get_contents($photoPath)) }}" 
                             style="width: 100%; height: 100%; object-fit: cover;" 
                             alt="Foto">
                    </div>
                </div>
                <div style="display: table-cell; vertical-align: middle; text-align: center; padding-left: 20pt;">
            @else
                <div style="display: table-cell; vertical-align: middle; text-align: center;">
            @endif
                <h1>{{ $candidate->personalData->full_name ?? 'Data Tidak Tersedia' }}</h1>
                <div class="subtitle">{{ $candidate->personalData->email ?? '-' }} | {{ $candidate->personalData->phone_number ?? '-' }}</div>
                <div class="meta">Kode: {{ $candidate->candidate_code }} | Status: {{ ucfirst($candidate->application_status) }} | {{ $candidate->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <!-- 1. Informasi Posisi -->
    <div class="compact-section">
        <h2>1. Informasi Posisi</h2>
        <div class="info-grid">
            <div class="info-col">
                <div class="info-item">
                    <span class="info-label">Posisi yang Dilamar:</span>
                    <span class="info-value">{{ $candidate->position_applied ?: '-' }}</span>
                </div>
            </div>
            <div class="info-col">
                <div class="info-item">
                    <span class="info-label">Gaji Harapan:</span>
                    <span class="info-value">{{ $candidate->expected_salary ? 'Rp ' . number_format($candidate->expected_salary, 0, ',', '.') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Data Pribadi -->
    <div class="compact-section">
        <h2>2. Data Pribadi</h2>
        <div class="info-grid">
            <div class="info-col">
                <div class="info-item">
                    <span class="info-label">Nama Lengkap:</span>
                    <span class="info-value">{{ $candidate->personalData->full_name ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tempat, Tgl Lahir:</span>
                    <span class="info-value">{{ $candidate->personalData->birth_place ?? '-' }}, {{ $candidate->personalData->birth_date ? \Carbon\Carbon::parse($candidate->personalData->birth_date)->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Jenis Kelamin:</span>
                    <span class="info-value">{{ $candidate->personalData->gender ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Agama:</span>
                    <span class="info-value">{{ $candidate->personalData->religion ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status Pernikahan:</span>
                    <span class="info-value">{{ $candidate->personalData->marital_status ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Suku Bangsa:</span>
                    <span class="info-value">{{ $candidate->personalData->ethnicity ?? '-' }}</span>
                </div>
            </div>
            <div class="info-col">
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $candidate->personalData->email ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">No. Telepon:</span>
                    <span class="info-value">{{ $candidate->personalData->phone_number ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Telepon Alternatif:</span>
                    <span class="info-value">{{ $candidate->personalData->phone_alternative ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tinggi/Berat:</span>
                    <span class="info-value">{{ $candidate->personalData->height_cm ?? '-' }} cm / {{ $candidate->personalData->weight_kg ?? '-' }} kg</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status Vaksinasi:</span>
                    <span class="info-value">{{ $candidate->personalData->vaccination_status ?? '-' }}</span>
                </div>
            </div>
        </div>
        
        <h3>Alamat</h3>
        <div class="info-item">
            <span class="info-label">Alamat Saat Ini:</span>
            <span class="info-value">{{ $candidate->personalData->current_address ?? '-' }} ({{ $candidate->personalData->current_address_status ?? '-' }})</span>
        </div>
        <div class="info-item">
            <span class="info-label">Alamat KTP:</span>
            <span class="info-value">{{ $candidate->personalData->ktp_address ?? '-' }}</span>
        </div>
    </div>

    <!-- 3. Data Keluarga -->
    @if($candidate->familyMembers->count() > 0)
    <div class="compact-section">
        <h2>3. Data Keluarga</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Hubungan</th>
                    <th style="width: 25%;">Nama</th>
                    <th style="width: 10%;">Usia</th>
                    <th style="width: 25%;">Pendidikan</th>
                    <th style="width: 25%;">Pekerjaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidate->familyMembers as $member)
                <tr>
                    <td>{{ $member->relationship ?? '-' }}</td>
                    <td>{{ $member->name ?? '-' }}</td>
                    <td>{{ $member->age ? $member->age . ' th' : '-' }}</td>
                    <td>{{ $member->education ?? '-' }}</td>
                    <td>{{ $member->occupation ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- 4. Pendidikan -->
    <div class="compact-section">
        <h2>4. Latar Belakang Pendidikan</h2>
        
        @if($candidate->formalEducation->count() > 0)
        <h3>Pendidikan Formal</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Jenjang</th>
                    <th style="width: 35%;">Institusi</th>
                    <th style="width: 25%;">Jurusan</th>
                    <th style="width: 15%;">Tahun</th>
                    <th style="width: 10%;">IPK</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidate->formalEducation->sortByDesc('end_year') as $edu)
                <tr>
                    <td>{{ $edu->education_level ?? '-' }}</td>
                    <td>{{ $edu->institution_name ?? '-' }}</td>
                    <td>{{ $edu->major ?? '-' }}</td>
                    <td>{{ $edu->start_year ?? '-' }}-{{ $edu->end_year ?? '-' }}</td>
                    <td>{{ $edu->gpa ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="empty">Tidak ada data pendidikan formal</p>
        @endif
        
        @if($candidate->nonFormalEducation->count() > 0)
        <h3>Pendidikan Non-Formal</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 35%;">Kursus/Pelatihan</th>
                    <th style="width: 30%;">Penyelenggara</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 20%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidate->nonFormalEducation as $course)
                <tr>
                    <td>{{ $course->course_name ?? '-' }}</td>
                    <td>{{ $course->organizer ?? '-' }}</td>
                    <td>{{ $course->date ? \Carbon\Carbon::parse($course->date)->format('m/Y') : '-' }}</td>
                    <td>{{ $course->description ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- 5. Pengalaman Kerja -->
    @if($candidate->workExperiences->count() > 0)
    <div class="compact-section">
        <h2>5. Pengalaman Kerja</h2>
        @foreach($candidate->workExperiences->sortByDesc('end_year') as $exp)
        <div class="work-box">
            <div class="work-header">{{ $exp->company_name ?? 'Perusahaan' }} ({{ $exp->start_year ?? '-' }} - {{ $exp->end_year ?? 'Sekarang' }})</div>
            <div class="info-grid">
                <div class="info-col">
                    <div class="info-item">
                        <span class="info-label" style="width: 70pt;">Posisi:</span>
                        <span class="info-value">{{ $exp->position ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label" style="width: 70pt;">Bidang:</span>
                        <span class="info-value">{{ $exp->company_field ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label" style="width: 70pt;">Gaji:</span>
                        <span class="info-value">{{ $exp->salary ? 'Rp ' . number_format($exp->salary, 0, ',', '.') : '-' }}</span>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-item">
                        <span class="info-label" style="width: 70pt;">Alasan Resign:</span>
                        <span class="info-value">{{ $exp->reason_for_leaving ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label" style="width: 70pt;">Atasan:</span>
                        <span class="info-value">{{ $exp->supervisor_contact ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="compact-section">
        <h2>5. Pengalaman Kerja</h2>
        <p class="empty">Fresh Graduate - Belum memiliki pengalaman kerja</p>
    </div>
    @endif

    <!-- Jika data masih muat di halaman 1, lanjutkan. Jika tidak, page break -->
    @if($candidate->workExperiences->count() > 3)
    <div class="page-break"></div>
    @endif

    <!-- 6. Kemampuan & Skills -->
    <div class="compact-section">
        <h2>6. Kemampuan & Skills</h2>
        
        <div class="info-grid">
            <div class="info-col">
                <h3>SIM yang Dimiliki</h3>
                @php
                    $simTypes = ['A', 'B1', 'B2', 'C'];
                    $ownedLicenses = $candidate->drivingLicenses->pluck('license_type')->toArray();
                @endphp
                <ul class="checkbox-list">
                    @foreach($simTypes as $sim)
                        <li>[{{ in_array($sim, $ownedLicenses) ? 'X' : ' ' }}] SIM {{ $sim }}</li>
                    @endforeach
                </ul>
                @if(empty($ownedLicenses))
                    <p class="empty no-margin">Tidak memiliki SIM</p>
                @endif
            </div>
            <div class="info-col">
                @if($candidate->languageSkills->count() > 0)
                <h3>Kemampuan Bahasa</h3>
                <table style="margin-bottom: 3pt;">
                    <thead>
                        <tr>
                            <th>Bahasa</th>
                            <th>Bicara</th>
                            <th>Tulis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($candidate->languageSkills as $lang)
                        <tr>
                            <td>{{ $lang->language ?? '-' }}</td>
                            <td>{{ $lang->speaking_level ?? '-' }}</td>
                            <td>{{ $lang->writing_level ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
        
        <div class="info-grid" style="margin-top: 6pt;">
            <div class="info-col">
                <h3>Kemampuan Komputer</h3>
                <div class="info-item">
                    <span class="info-label" style="width: 60pt;">Hardware:</span>
                    <span class="info-value">{{ $candidate->computerSkills->hardware_skills ?? 'Tidak ada' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label" style="width: 60pt;">Software:</span>
                    <span class="info-value">{{ $candidate->computerSkills->software_skills ?? 'Tidak ada' }}</span>
                </div>
            </div>
            <div class="info-col">
                <h3>Kemampuan Lainnya</h3>
                <div class="text-box">{{ $candidate->otherSkills->other_skills ?? 'Tidak ada data' }}</div>
            </div>
        </div>
    </div>

    <!-- 7. Organisasi & Prestasi -->
    @if($candidate->socialActivities->count() > 0 || $candidate->achievements->count() > 0)
    <div class="compact-section">
        <h2>7. Latar Belakang Organisasi & Prestasi</h2>
        
        @if($candidate->socialActivities->count() > 0)
        <h3>Aktivitas Sosial/Organisasi</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Organisasi</th>
                    <th style="width: 25%;">Bidang</th>
                    <th style="width: 20%;">Periode</th>
                    <th style="width: 25%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidate->socialActivities as $activity)
                <tr>
                    <td>{{ $activity->organization_name ?? '-' }}</td>
                    <td>{{ $activity->field ?? '-' }}</td>
                    <td>{{ $activity->period ?? '-' }}</td>
                    <td>{{ $activity->description ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        
        @if($candidate->achievements->count() > 0)
        <h3>Penghargaan/Prestasi</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Prestasi</th>
                    <th style="width: 15%;">Tahun</th>
                    <th style="width: 45%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($candidate->achievements as $achievement)
                <tr>
                    <td>{{ $achievement->achievement ?? '-' }}</td>
                    <td>{{ $achievement->year ?? '-' }}</td>
                    <td>{{ $achievement->description ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    <!-- 8. Informasi Umum -->
    <div class="compact-section">
        <h2>8. Informasi Umum</h2>
        
        <div class="info-grid">
            <div class="info-col">
                <div class="info-item">
                    <span class="info-label">Bersedia Dinas:</span>
                    <span class="info-value">{{ $candidate->generalInformation && $candidate->generalInformation->willing_to_travel ? 'Ya' : 'Tidak' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Kendaraan:</span>
                    <span class="info-value">{{ $candidate->generalInformation && $candidate->generalInformation->has_vehicle ? 'Ya' : 'Tidak' }} 
                    {{ $candidate->generalInformation && $candidate->generalInformation->vehicle_types ? '(' . $candidate->generalInformation->vehicle_types . ')' : '' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Penghasilan Lain:</span>
                    <span class="info-value">{{ $candidate->generalInformation->other_income ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Absen/Tahun:</span>
                    <span class="info-value">{{ $candidate->generalInformation->absence_days ?? '-' }} hari</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Mulai Kerja:</span>
                    <span class="info-value">{{ $candidate->generalInformation && $candidate->generalInformation->start_work_date ? \Carbon\Carbon::parse($candidate->generalInformation->start_work_date)->format('d/m/Y') : '-' }}</span>
                </div>
            </div>
            <div class="info-col">
                <div class="info-item">
                    <span class="info-label">Catatan Polisi:</span>
                    <span class="info-value">{{ $candidate->generalInformation && $candidate->generalInformation->has_police_record ? 'Ada' : 'Tidak' }}
                    {{ $candidate->generalInformation && $candidate->generalInformation->police_record_detail ? '(' . $candidate->generalInformation->police_record_detail . ')' : '' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Riwayat Sakit:</span>
                    <span class="info-value">{{ $candidate->generalInformation && $candidate->generalInformation->has_serious_illness ? 'Ada' : 'Tidak' }}
                    {{ $candidate->generalInformation && $candidate->generalInformation->illness_detail ? '(' . $candidate->generalInformation->illness_detail . ')' : '' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tato/Tindik:</span>
                    <span class="info-value">{{ $candidate->generalInformation && $candidate->generalInformation->has_tattoo_piercing ? 'Ada' : 'Tidak' }}
                    {{ $candidate->generalInformation && $candidate->generalInformation->tattoo_piercing_detail ? '(' . $candidate->generalInformation->tattoo_piercing_detail . ')' : '' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Usaha Lain:</span>
                    <span class="info-value">{{ $candidate->generalInformation && $candidate->generalInformation->has_other_business ? 'Ada' : 'Tidak' }}
                    {{ $candidate->generalInformation && $candidate->generalInformation->other_business_detail ? '(' . $candidate->generalInformation->other_business_detail . ')' : '' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Sumber Info:</span>
                    <span class="info-value">{{ $candidate->generalInformation->information_source ?? '-' }}</span>
                </div>
            </div>
        </div>
        
        @if($candidate->generalInformation && ($candidate->generalInformation->motivation || $candidate->generalInformation->strengths || $candidate->generalInformation->weaknesses))
        <h3>Motivasi, Kelebihan & Kekurangan</h3>
        <table>
            <tr>
                <td style="width: 33%; vertical-align: top; padding: 4pt;">
                    <strong style="font-size: 8.5pt;">Motivasi Bergabung:</strong>
                    <div class="text-box" style="margin-top: 2pt;">{{ $candidate->generalInformation->motivation ?? 'Tidak ada data' }}</div>
                </td>
                <td style="width: 33%; vertical-align: top; padding: 4pt;">
                    <strong style="font-size: 8.5pt;">Kelebihan:</strong>
                    <div class="text-box" style="margin-top: 2pt;">{{ $candidate->generalInformation->strengths ?? 'Tidak ada data' }}</div>
                </td>
                <td style="width: 34%; vertical-align: top; padding: 4pt;">
                    <strong style="font-size: 8.5pt;">Kekurangan:</strong>
                    <div class="text-box" style="margin-top: 2pt;">{{ $candidate->generalInformation->weaknesses ?? 'Tidak ada data' }}</div>
                </td>
            </tr>
        </table>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        Dokumen ini digenerate pada {{ now()->format('d F Y H:i') }} oleh {{ Auth::user()->full_name }} | {{ config('app.name') }} - PT Kayu Mebel Indonesia
    </div>
</body>
</html>