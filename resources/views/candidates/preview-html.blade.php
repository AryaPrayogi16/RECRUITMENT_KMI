<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview FLK - {{ $candidate->full_name ?? 'Kandidat' }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm 2cm 1.5cm 2cm;
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
            background: #f5f5f5;
            padding: 20px;
        }
        
        .page-wrapper {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 2cm 2cm 1.5cm 2cm;
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
        
        .header-content {
            display: flex;
            align-items: center;
            gap: 20pt;
        }
        
        .photo-container {
            width: 80pt;
            height: 100pt;
            overflow: hidden;
            border: 1pt solid #e2e8f0;
            border-radius: 4pt;
            flex-shrink: 0;
        }
        
        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .photo-placeholder {
            width: 100%;
            height: 100%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24pt;
            color: #9ca3af;
        }
        
        .header-text {
            flex: 1;
            text-align: center;
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
        
        /* Page break for print */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .page-wrapper {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .page-break {
                page-break-before: always;
            }
        }
        
        /* Prevent empty space */
        .no-margin { margin: 0; }
        .tight { line-height: 1; }

        /* SVG Chart Styles */
        .chart-container {
            width: 100%;
            margin: 8pt 0;
            page-break-inside: avoid;
            text-align: center;
        }
        .chart-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5pt;
            font-size: 9pt;
        }
        .chart-svg {
            width: 100%;
            max-width: 700px; /* Sesuaikan dengan width SVG */
            height: auto;
            border: 0.5pt solid #e5e7eb;
            background: white;
        }
        .chart-legend {
            margin-top: 5pt;
            font-size: 7pt;
            text-align: center;
        }
        .legend-item {
            display: inline-block;
            margin: 0 5pt;
        }
        .legend-color {
            display: inline-block;
            width: 8pt;
            height: 8pt;
            margin-right: 3pt;
            vertical-align: middle;
        }
        /* Kraeplin Chart Styles */
        .kraeplin-chart-container {
            width: 100%;
            margin: 8pt 0;
            page-break-inside: avoid;
            text-align: center;
        }

        .kraeplin-chart-container h3 {
            color: #1f2937;
            margin-bottom: 5pt;
            font-size: 9pt;
            font-weight: bold;
        }

        .kraeplin-chart-svg {
            width: 100%;
            max-width: 500px; /* Diperkecil dari 600px */
            height: auto;
            border: 0.5pt solid #e5e7eb;
            background: white;
            margin: 0 auto;
            display: block;
        }

        /* Compact chart sections */
        .kraeplin-charts-section {
            margin-top: 15pt;
        }

        .kraeplin-charts-section h3 {
            margin-top: 15pt;
            margin-bottom: 8pt;
            padding-top: 8pt;
            border-top: 0.5pt solid #e5e7eb;
            font-size: 9pt;
        }

        .kraeplin-charts-section h3:first-child {
            margin-top: 15pt;
            border-top: none;
            padding-top: 0;
        }

        /* Navigation buttons */
        .chart-navigation {
            margin: 20pt 0;
            text-align: center;
        }

        .chart-navigation button {
            margin: 3pt;
            padding: 6pt 12pt;
            font-size: 9pt;
            border: none;
            border-radius: 3pt;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .chart-navigation button:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }

        /* Print optimization */
        @media print {
            .kraeplin-chart-container {
                page-break-inside: avoid;
                margin: 10pt 0;
            }
            
            .chart-navigation {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Header -->
        <div class="header-box">
            @php
                $photoDocument = $candidate->documentUploads->where('document_type', 'photo')->first();
            @endphp
            
            <div class="header-content">
                <div class="photo-container">
                    @if($photoDocument)
                        <img src="{{ Storage::url($photoDocument->file_path) }}" alt="Foto">
                    @else
                        <div class="photo-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </div>
                
                <div class="header-text">
                    <h1>{{ $candidate->full_name ?? 'Data Tidak Tersedia' }}</h1>
                    <div class="subtitle">{{ $candidate->email ?? '-' }} | {{ $candidate->phone_number ?? '-' }}</div>
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
                        <span class="info-value">{{ $candidate->full_name ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tempat, Tgl Lahir:</span>
                        <span class="info-value">{{ $candidate->birth_place ?? '-' }}, {{ $candidate->birth_date ? \Carbon\Carbon::parse($candidate->birth_date)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jenis Kelamin:</span>
                        <span class="info-value">{{ $candidate->gender ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Agama:</span>
                        <span class="info-value">{{ $candidate->religion ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status Pernikahan:</span>
                        <span class="info-value">{{ $candidate->marital_status ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Suku Bangsa:</span>
                        <span class="info-value">{{ $candidate->ethnicity ?? '-' }}</span>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $candidate->email ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">No. Telepon:</span>
                        <span class="info-value">{{ $candidate->phone_number ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Telepon Alternatif:</span>
                        <span class="info-value">{{ $candidate->phone_alternative ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tinggi/Berat:</span>
                        <span class="info-value">{{ $candidate->height_cm ?? '-' }} cm / {{ $candidate->weight_kg ?? '-' }} kg</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status Vaksinasi:</span>
                        <span class="info-value">{{ $candidate->vaccination_status ?? '-' }}</span>
                    </div>
                </div>
            </div>
            <h3>Alamat</h3>
            <div class="info-item">
                <span class="info-label">Alamat Saat Ini:</span>
                <span class="info-value">{{ $candidate->current_address ?? '-' }} ({{ $candidate->current_address_status ?? '-' }})</span>
            </div>
            <div class="info-item">
                <span class="info-label">Alamat KTP:</span>
                <span class="info-value">{{ $candidate->ktp_address ?? '-' }}</span>
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
        @else
        <div class="compact-section">
            <h2>7. Latar Belakang Organisasi & Prestasi</h2>
            <p class="empty">Tidak ada data organisasi atau prestasi</p>
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

        <!-- 9. Hasil Tes Kraeplin -->
        @if($candidate->kraeplinTestResult)
        <div class="compact-section">
            <h2>9. Hasil Tes Kraeplin</h2>
            
            <!-- Ringkasan Hasil -->
            <div class="info-grid">
                <div class="info-col">
                    <div class="info-item">
                        <span class="info-label">Total Soal Terjawab:</span>
                        <span class="info-value">{{ $candidate->kraeplinTestResult->total_questions_answered ?? 0 }}/832</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jawaban Benar:</span>
                        <span class="info-value">{{ $candidate->kraeplinTestResult->total_correct_answers ?? 0 }} ({{ number_format($candidate->kraeplinTestResult->accuracy_percentage ?? 0, 1) }}%)</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Kecepatan Rata-rata:</span>
                        <span class="info-value">{{ $candidate->kraeplinTestResult->formatted_average_time ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Durasi Total:</span>
                        <span class="info-value">{{ $candidate->kraeplinTestResult->testSession->formatted_duration ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-item">
                        <span class="info-label">Skor Keseluruhan:</span>
                        <span class="info-value">{{ number_format($candidate->kraeplinTestResult->overall_score ?? 0, 1) }}/100</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Grade:</span>
                        <span class="info-value">{{ $candidate->kraeplinTestResult->grade ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Kategori Performa:</span>
                        <span class="info-value">{{ $candidate->kraeplinTestResult->performance_category_label ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal Tes:</span>
                        <span class="info-value">{{ $candidate->kraeplinTestResult->testSession->completed_at ? $candidate->kraeplinTestResult->testSession->completed_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
            
            @if($candidate->kraeplinTestResult->getScoreInterpretation())
            <div class="text-box">
                <strong>Interpretasi:</strong> {{ $candidate->kraeplinTestResult->getScoreInterpretation() }}
            </div>
            @endif

            <!-- ANALISIS PERFORMA LENGKAP (3 in 1) -->
            <h3>Analisis Performa Lengkap</h3>
            <div class="kraeplin-chart-container">
                {!! \App\Services\KraeplinChartGenerator::generateChart($candidate) !!}
            </div>

            <!-- TINGKAT AKURASI -->
            <h3>Tingkat Akurasi per Kolom</h3>
            <div class="kraeplin-chart-container">
                {!! \App\Services\KraeplinChartGenerator::generateAccuracyChart($candidate) !!}
            </div>

            <!-- SOAL TERJAWAB -->
            <h3>Soal Terjawab per Kolom</h3>
            <div class="kraeplin-chart-container">
                {!! \App\Services\KraeplinChartGenerator::generateAnsweredChart($candidate) !!}
            </div>

            <!-- KECEPATAN PENGERJAAN (WAKTU RATA-RATA) -->
            <h3>Waktu Rata-rata per Kolom</h3>
            <div class="kraeplin-chart-container">
                {!! \App\Services\KraeplinChartGenerator::generateSpeedChart($candidate) !!}
            </div>
        </div>
        @endif

        <!-- 10. Hasil Tes DISC 3D - FIXED VERSION -->
        @if($candidate->disc3DTestResult)
        <div class="compact-section">
            <h2>10. Hasil Tes DISC 3D - Analisis Kepribadian</h2>
            
            <!-- Profile Summary -->
            <div class="info-grid">
                <div class="info-col">
                    <div class="info-item">
                        <span class="info-label">Tipe Kepribadian:</span>
                        <span class="info-value">{{ ($candidate->disc3DTestResult->primary_type ?? 'D') . ($candidate->disc3DTestResult->secondary_type ?? 'I') }} - {{ $candidate->disc3DTestResult->primary_type_label ?? 'Unknown Type' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Sekunder:</span>
                        <span class="info-value">{{ $candidate->disc3DTestResult->secondary_type_label ?? 'Unknown' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Pattern Segment:</span>
                        <span class="info-value">{{ ($candidate->disc3DTestResult->most_d_segment ?? 1) }}-{{ ($candidate->disc3DTestResult->most_i_segment ?? 1) }}-{{ ($candidate->disc3DTestResult->most_s_segment ?? 1) }}-{{ ($candidate->disc3DTestResult->most_c_segment ?? 1) }}</span>
                    </div>
                </div>
                <div class="info-col">
                    <div class="info-item">
                        <span class="info-label">Dominan:</span>
                        <span class="info-value">{{ number_format($candidate->disc3DTestResult->primary_percentage ?? 0, 1) }}%</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal Tes:</span>
                        <span class="info-value">{{ $candidate->latestDisc3DTest->completed_at ? $candidate->latestDisc3DTest->completed_at->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Durasi:</span>
                        <span class="info-value">{{ $candidate->latestDisc3DTest->formatted_duration ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <!-- Interpretasi 3 Grafik -->
            <h3>Interpretasi Grafik</h3>
            <table>
                <tr>
                    <td style="width: 33%; vertical-align: top; padding: 4pt;">
                        <strong style="font-size: 8.5pt; color: #4f46e5;">📊 MOST (Topeng/Publik)</strong>
                        <div class="text-box" style="margin-top: 2pt; font-size: 8pt;">
                            Menampilkan bagaimana Anda berperilaku di depan umum atau dalam situasi kerja formal. Grafik ini menunjukkan adaptasi perilaku sesuai ekspektasi lingkungan.
                        </div>
                    </td>
                    <td style="width: 33%; vertical-align: top; padding: 4pt;">
                        <strong style="font-size: 8.5pt; color: #4f46e5;">📊 LEAST (Inti/Pribadi)</strong>
                        <div class="text-box" style="margin-top: 2pt; font-size: 8pt;">
                            Menggambarkan kepribadian alami Anda yang sesungguhnya tanpa pengaruh eksternal. Ini adalah "diri sejati" yang cenderung muncul saat stres atau rileks.
                        </div>
                    </td>
                    <td style="width: 34%; vertical-align: top; padding: 4pt;">
                        <strong style="font-size: 8.5pt; color: #4f46e5;">📊 CHANGE (Adaptasi)</strong>
                        <div class="text-box" style="margin-top: 2pt; font-size: 8pt;">
                            Menunjukkan tekanan dan adaptasi yang dialami. Nilai positif (+) = peningkatan, nilai negatif (-) = penurunan dari kondisi natural.
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Analisis Perilaku Mendalam -->
            <h3>Analisis Perilaku Mendalam</h3>
            <table>
                <tr>
                    <td style="width: 50%; vertical-align: top; padding: 4pt;">
                        <div style="margin-bottom: 6pt;">
                            <strong style="font-size: 8.5pt; color: #4f46e5;">💼 Gaya Kerja Detail</strong>
                            <div class="text-box" style="margin-top: 2pt; font-size: 8pt;">
                                {{ \Illuminate\Support\Str::limit($candidate->disc3DTestResult->overall_profile ?? 'Bekerja dengan tempo tinggi dan fokus pada hasil. Menyukai lingkungan yang dinamis dengan kebebasan untuk mengambil keputusan.', 180) }}
                            </div>
                        </div>
                        <div style="margin-bottom: 6pt;">
                            <strong style="font-size: 8.5pt; color: #4f46e5;">🎤 Gaya Komunikasi Detail</strong>
                            <div class="text-box" style="margin-top: 2pt; font-size: 8pt;">
                                {{ \Illuminate\Support\Str::limit($candidate->disc3DTestResult->personality_profile ?? 'Komunikasi yang langsung, jelas, dan persuasif. Mampu menyampaikan visi dan memotivasi tim.', 180) }}
                            </div>
                        </div>
                        <div style="margin-bottom: 6pt;">
                            <strong style="font-size: 8.5pt; color: #4f46e5;">🎭 Analisis Diri Publik (MOST)</strong>
                            <div class="text-box" style="margin-top: 2pt; font-size: 8pt;">
                                {{ \Illuminate\Support\Str::limit($candidate->disc3DTestResult->public_self_summary ?? 'Di lingkungan publik, menampilkan sosok yang percaya diri, tegas, dan berorientasi pada hasil.', 180) }}
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; vertical-align: top; padding: 4pt;">
                        <div style="margin-bottom: 6pt;">
                            <strong style="font-size: 8.5pt; color: #4f46e5;">❤️ Analisis Diri Pribadi (LEAST)</strong>
                            <div class="text-box" style="margin-top: 2pt; font-size: 8pt;">
                                {{ \Illuminate\Support\Str::limit($candidate->disc3DTestResult->private_self_summary ?? 'Secara pribadi, lebih reflektif dan mempertimbangkan berbagai aspek sebelum mengambil keputusan.', 180) }}
                            </div>
                        </div>
                        <div style="margin-bottom: 6pt;">
                            <strong style="font-size: 8.5pt; color: #4f46e5;">🔄 Analisis Adaptasi (CHANGE)</strong>
                            <div class="text-box" style="margin-top: 2pt; font-size: 8pt;">
                                {{ \Illuminate\Support\Str::limit($candidate->disc3DTestResult->adaptation_summary ?? 'Mengalami tekanan untuk tampil lebih dominan dan ekspresif di lingkungan kerja.', 180) }}
                            </div>
                        </div>
                        <div style="margin-bottom: 6pt;">
                            <strong style="font-size: 8.5pt; color: #4f46e5;">📄 Ringkasan Profil Keseluruhan</strong>
                            <div class="text-box" style="margin-top: 2pt; font-size: 8pt;">
                                {{ \Illuminate\Support\Str::limit($candidate->disc3DTestResult->summary ?? $candidate->disc3DTestResult->brief_summary ?? 'Belum tersedia', 180) }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- FIXED: Kelebihan & Area Pengembangan (TANPA Indikator Stres) -->
            @if($candidate->disc3DTestResult->behavioral_insights)
            <h3>Kelebihan & Area Pengembangan</h3>
            <table>
                <tr>
                    <td style="width: 50%; vertical-align: top; padding: 4pt;">
                        <strong style="font-size: 8.5pt; color: #059669;">⭐ Kelebihan & Kekuatan</strong>
                        <div class="text-box" style="margin-top: 2pt; font-size: 7.5pt;">
                            @php
                                $strengths = $candidate->disc3DTestResult->behavioral_insights['strengths'] ?? [
                                    'Kepemimpinan Natural', 'Pengambilan Keputusan Cepat', 'Orientasi Hasil Tinggi'
                                ];
                            @endphp
                            @if(is_array($strengths))
                                {{ implode(', ', array_slice($strengths, 0, 8)) }}
                            @else
                                {{ $strengths }}
                            @endif
                        </div>
                    </td>
                    <td style="width: 50%; vertical-align: top; padding: 4pt;">
                        <strong style="font-size: 8.5pt; color: #dc2626;">📈 Area Pengembangan</strong>
                        <div class="text-box" style="margin-top: 2pt; font-size: 7.5pt;">
                            @php
                                $developmentAreas = $candidate->disc3DTestResult->behavioral_insights['development_areas'] ?? [
                                    'Kesabaran dalam Proses', 'Perhatian pada Detail', 'Konsistensi Follow-up'
                                ];
                            @endphp
                            @if(is_array($developmentAreas))
                                {{ implode(', ', array_slice($developmentAreas, 0, 8)) }}
                            @else
                                {{ $developmentAreas }}
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
            @endif

            <!-- Motivator Utama (MENGHAPUS Indikator Stres) -->
            @if($candidate->disc3DTestResult->behavioral_insights)
            <h3>Motivator Utama</h3>
            <div class="text-box">
                <strong style="font-size: 8.5pt; color: #ea580c;">🔥 Motivator:</strong>
                @php
                    $motivators = $candidate->disc3DTestResult->behavioral_insights['motivators'] ?? [
                        'Pencapaian Target', 'Pengakuan Prestasi', 'Tantangan Baru'
                    ];
                @endphp
                @if(is_array($motivators))
                    {{ implode(', ', array_slice($motivators, 0, 8)) }}
                @else
                    {{ $motivators }}
                @endif
            </div>
            @endif

            <!-- Profesi yang Cocok -->
            @if($candidate->disc3DTestResult->recommended_roles)
            <h3>Profesi yang Cocok</h3>
            <div class="text-box">
                @if(is_array($candidate->disc3DTestResult->recommended_roles))
                    @foreach(array_slice($candidate->disc3DTestResult->recommended_roles, 0, 8) as $role)
                        <span style="display: inline-block; background: #e5e7eb; padding: 2pt 6pt; margin: 2pt; border-radius: 3pt; font-size: 8pt;">{{ $role }}</span>
                    @endforeach
                @else
                    {{ $candidate->disc3DTestResult->recommended_roles }}
                @endif
            </div>
            @endif

            <!-- FIXED: DISC CHART with segment values and negative display -->
            <h3>Grafik DISC 3D (Segment Values)</h3>
            <div class="chart-container">
                {!! \App\Services\DiscChartGenerator::generateChart($candidate) !!}
            </div>
            
            <!-- Note about methodology -->
            <div style="margin-top: 8pt; padding: 4pt; background: #f0f9ff; border: 0.5pt solid #0ea5e9; border-radius: 3pt;">
                <strong style="font-size: 7pt; color: #0c4a6e;">📌 Catatan Metodologi:</strong>
                <span style="font-size: 7pt; color: #0c4a6e;">
                    Grafik MOST & LEAST menggunakan skala segment 1-7 (bukan persentase). 
                    Grafik CHANGE menampilkan nilai adaptasi yang dapat positif (+) atau negatif (-). 
                    Persentase ditampilkan sebagai referensi tambahan.
                </span>
            </div>
        </div>
        @else
        <div class="compact-section">
            <h2>10. Hasil Tes DISC 3D - Analisis Kepribadian</h2>
            <p class="empty">Kandidat belum menyelesaikan tes DISC 3D</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            Dokumen ini digenerate pada {{ now()->format('d F Y H:i') }} oleh {{ Auth::user()->full_name }} | {{ config('app.name') }} - PT Kayu Mebel Indonesia
        </div>
    </div>
</body>
</html>