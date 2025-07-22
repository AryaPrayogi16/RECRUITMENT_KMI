<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Kandidat - HR System</title>
    
    {{-- External Assets --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    {{-- Custom Assets --}}
    <link href="{{ asset('css/candidate-detail.css') }}" rel="stylesheet">
    <link href="{{ asset('css/disc-3d-styles.css') }}" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-building"></i>
                    <span class="logo-text">HR System</span>
                </div>
            </div>

            <div class="user-info">
                <div class="user-avatar">
                    @if(Auth::user()->role == 'admin')
                        <i class="fas fa-user-crown"></i>
                    @elseif(Auth::user()->role == 'hr')
                        <i class="fas fa-user-tie"></i>
                    @else
                        <i class="fas fa-user"></i>
                    @endif
                </div>
                <div class="user-details">
                    <div class="user-name">{{ Auth::user()->full_name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>

            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                @if(in_array(Auth::user()->role, ['admin']))
                    <div class="nav-item">
                        <a href="{{ route('admin.users') }}" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>User Management</span>
                        </a>
                    </div>
                @endif
                @if(in_array(Auth::user()->role, ['admin', 'hr']))
                    <div class="nav-item">
                        <a href="{{ route('candidates.index') }}" class="nav-link active">
                            <i class="fas fa-user-tie"></i>
                            <span>Kandidat</span>
                        </a>
                    </div>
                @endif
            </nav>
        </aside>

        {{-- Main Content --}}
        <main class="main-content" id="mainContent">
            <header class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <div class="breadcrumb">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                            <span>/</span>
                            <a href="{{ route('candidates.index') }}">Kandidat</a>
                            <span>/</span>
                            <span>Detail</span>
                        </div>
                        <h1 class="page-title">Detail Kandidat</h1>
                    </div>
                </div>
                <div class="header-right">
                    <button class="btn btn-info" onclick="showHistoryModal()">
                        <i class="fas fa-history"></i>
                        <span>Riwayat</span>
                    </button>

                    <a href="{{ route('candidates.preview', $candidate->id) }}" class="btn btn-info" target="_blank">
                        <i class="fas fa-eye"></i>
                        <span>Export</span>
                    </a>

                    <a href="{{ route('candidates.edit', $candidate->id) }}" class="btn btn-secondary">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>
                    
                    <button class="btn btn-primary" onclick="showStatusModal()">
                        <i class="fas fa-sync"></i>
                        <span>Update Status</span>
                    </button>
                </div>
            </header>

            <div class="content">
                {{-- Candidate Header Card --}}
                <div class="candidate-header">
                    <div class="candidate-banner">
                        <div class="candidate-info-header">
                            <div class="candidate-photo">
                                @php
                                    $photoDocument = $candidate->documentUploads->where('document_type', 'photo')->first();
                                @endphp
                                @if($photoDocument)
                                    <img src="{{ Storage::url($photoDocument->file_path) }}" 
                                        alt="Foto {{ $candidate->full_name ?? 'Kandidat' }}" 
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                            <div class="candidate-details-header">
                                <h2 class="candidate-name">{{ $candidate->full_name ?? 'N/A' }}</h2>
                                <p class="candidate-position">
                                    {{ $candidate->position->position_name ?? $candidate->position_applied }}
                                    @if($candidate->expected_salary)
                                        • Gaji Harapan: Rp {{ number_format($candidate->expected_salary, 0, ',', '.') }}
                                    @endif
                                </p>
                                <div class="candidate-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-id-badge"></i>
                                        <span>{{ $candidate->candidate_code }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-envelope"></i>
                                        <span>{{ $candidate->email ?? 'N/A' }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $candidate->phone_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="status-badge status-{{ $candidate->application_status }}">
                                            {{ ucfirst($candidate->application_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Navigation --}}
                <nav class="section-nav">
                    <ul class="section-nav-list">
                        <li class="section-nav-item">
                            <a href="#personal-section" class="section-nav-link active">
                                <i class="fas fa-user"></i>
                                <span>Data Pribadi</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#education-section" class="section-nav-link">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Pendidikan</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#experience-section" class="section-nav-link">
                                <i class="fas fa-briefcase"></i>
                                <span>Pengalaman</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#skills-section" class="section-nav-link">
                                <i class="fas fa-cogs"></i>
                                <span>Keterampilan</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#activities-section" class="section-nav-link">
                                <i class="fas fa-hands-helping"></i>
                                <span>Aktivitas</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#general-section" class="section-nav-link">
                                <i class="fas fa-info-circle"></i>
                                <span>Info Umum</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#kraeplin-section" class="section-nav-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Hasil Kraeplin</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#disc-section" class="section-nav-link">
                                <i class="fas fa-chart-pie"></i>
                                <span>Hasil DISC</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#documents-section" class="section-nav-link">
                                <i class="fas fa-file-alt"></i>
                                <span>Dokumen</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                {{-- Personal Data Section --}}
                <section id="personal-section" class="content-section" style="margin-top: 0;">
                    <h2 class="section-title">
                        <i class="fas fa-user-circle"></i>
                        Data Pribadi
                    </h2>
                    <div class="info-grid">
                        <div class="info-card">
                            <h3 class="info-card-title">
                                <i class="fas fa-id-card"></i>
                                Informasi Dasar
                            </h3>
                            <div class="info-row">
                                <span class="info-label">Nama Lengkap</span>
                                <span class="info-value">{{ $candidate->full_name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">NIK</span>
                                <span class="info-value">{{ $candidate->nik ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tempat, Tanggal Lahir</span>
                                <span class="info-value">
                                    {{ $candidate->birth_place ?? 'N/A' }}, 
                                    {{ $candidate->birth_date ? \Carbon\Carbon::parse($candidate->birth_date)->format('d M Y') : 'N/A' }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Jenis Kelamin</span>
                                <span class="info-value">{{ $candidate->gender ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Agama</span>
                                <span class="info-value">{{ $candidate->religion ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status Pernikahan</span>
                                <span class="info-value">{{ $candidate->marital_status ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Suku Bangsa</span>
                                <span class="info-value">{{ $candidate->ethnicity ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="info-card">
                            <h3 class="info-card-title">
                                <i class="fas fa-phone-alt"></i>
                                Kontak
                            </h3>
                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $candidate->email ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">No. Telepon</span>
                                <span class="info-value">{{ $candidate->phone_number ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">No. Alternatif</span>
                                <span class="info-value">{{ $candidate->phone_alternative ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="info-card">
                            <h3 class="info-card-title">
                                <i class="fas fa-home"></i>
                                Alamat
                            </h3>
                            <div class="info-row">
                                <span class="info-label">Alamat Saat Ini</span>
                                <span class="info-value">{{ $candidate->current_address ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status Tempat Tinggal</span>
                                <span class="info-value">{{ $candidate->current_address_status ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Alamat KTP</span>
                                <span class="info-value">{{ $candidate->ktp_address ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="info-card">
                            <h3 class="info-card-title">
                                <i class="fas fa-ruler-vertical"></i>
                                Data Fisik & Kesehatan
                            </h3>
                            <div class="info-row">
                                <span class="info-label">Tinggi Badan</span>
                                <span class="info-value">{{ $candidate->height_cm ? $candidate->height_cm . ' cm' : 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Berat Badan</span>
                                <span class="info-value">{{ $candidate->weight_kg ? $candidate->weight_kg . ' kg' : 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status Vaksinasi</span>
                                <span class="info-value">{{ $candidate->vaccination_status ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($candidate->familyMembers->count() > 0)
                        <h3 class="info-card-title" style="margin-top: 30px;">
                            <i class="fas fa-users"></i>
                            Data Keluarga
                        </h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Hubungan</th>
                                    <th>Nama</th>
                                    <th>Usia</th>
                                    <th>Pendidikan</th>
                                    <th>Pekerjaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($candidate->familyMembers as $member)
                                    <tr>
                                        <td>{{ $member->relationship ?? 'N/A' }}</td>
                                        <td>{{ $member->name ?? 'N/A' }}</td>
                                        <td>{{ $member->age ? $member->age . ' tahun' : 'N/A' }}</td>
                                        <td>{{ $member->education ?? 'N/A' }}</td>
                                        <td>{{ $member->occupation ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>

                <section id="education-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-graduation-cap"></i>
                        Pendidikan
                    </h2>

                    @php
                        // ✅ UPDATED: Use separate education relationships
                        $formalEducation = $candidate->formalEducation;
                        $nonFormalEducation = $candidate->nonFormalEducation;
                    @endphp

                    @if($formalEducation->count() > 0)
                        <h3 class="info-card-title">
                            <i class="fas fa-university"></i>
                            Pendidikan Formal
                        </h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Jenjang</th>
                                    <th>Institusi</th>
                                    <th>Jurusan</th>
                                    <th>Tahun</th>
                                    <th>IPK</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formalEducation as $edu)
                                    <tr>
                                        <td>{{ $edu->education_level ?? 'N/A' }}</td>
                                        <td>{{ $edu->institution_name ?? 'N/A' }}</td>
                                        <td>{{ $edu->major ?? 'N/A' }}</td>
                                        <td>{{ $edu->start_year }} - {{ $edu->end_year ?? 'Sekarang' }}</td>
                                        <td>{{ $edu->gpa ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($nonFormalEducation->count() > 0)
                        <h3 class="info-card-title" style="margin-top: 30px;">
                            <i class="fas fa-certificate"></i>
                            Pendidikan Non-Formal
                        </h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Kursus/Pelatihan</th>
                                    <th>Penyelenggara</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nonFormalEducation as $course)
                                    <tr>
                                        <td>{{ $course->course_name ?? 'N/A' }}</td>
                                        <td>{{ $course->organizer ?? 'N/A' }}</td>
                                        <td>{{ $course->date ? \Carbon\Carbon::parse($course->date)->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ $course->description ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($formalEducation->count() == 0 && $nonFormalEducation->count() == 0)
                        <div class="empty-state">
                            <i class="fas fa-graduation-cap"></i>
                            <p>Tidak ada data pendidikan</p>
                        </div>
                    @endif
                </section>

                {{-- Experience Section --}}
                <section id="experience-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-briefcase"></i>
                        Pengalaman Kerja
                    </h2>

                    @if($candidate->workExperiences->count() > 0)
                        @foreach($candidate->workExperiences as $exp)
                            <div class="info-card" style="margin-bottom: 20px;">
                                <h3 class="info-card-title">
                                    <i class="fas fa-building"></i>
                                    {{ $exp->company_name ?? 'N/A' }}
                                </h3>
                                <div class="info-row">
                                    <span class="info-label">Posisi</span>
                                    <span class="info-value">{{ $exp->position ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Periode</span>
                                    <span class="info-value">{{ $exp->start_year ?? 'N/A' }} - {{ $exp->end_year ?? 'Sekarang' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Alamat Perusahaan</span>
                                    <span class="info-value">{{ $exp->company_address ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Bidang Usaha</span>
                                    <span class="info-value">{{ $exp->company_field ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Gaji Terakhir</span>
                                    <span class="info-value">
                                        @if($exp->salary)
                                            Rp {{ number_format($exp->salary, 0, ',', '.') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Alasan Berhenti</span>
                                    <span class="info-value">{{ $exp->reason_for_leaving ?? 'N/A' }}</span>
                                </div>
                                @if($exp->supervisor_contact)
                                    <div class="info-row">
                                        <span class="info-label">Kontak Atasan</span>
                                        <span class="info-value">{{ $exp->supervisor_contact }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <i class="fas fa-briefcase"></i>
                            <p>Tidak ada pengalaman kerja</p>
                        </div>
                    @endif
                </section>

                {{-- Skills Section --}}
                <section id="skills-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-cogs"></i>
                        Keterampilan
                    </h2>

                    <div class="info-grid">
                        @if($candidate->languageSkills->count() > 0)
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-language"></i>
                                    Kemampuan Bahasa
                                </h3>
                                @foreach($candidate->languageSkills as $lang)
                                    <div class="info-row">
                                        <span class="info-label">{{ $lang->language ?? 'N/A' }}</span>
                                        <span class="info-value">
                                            Bicara: {{ $lang->speaking_level ?? 'N/A' }}, 
                                            Tulis: {{ $lang->writing_level ?? 'N/A' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($candidate->additionalInfo && ($candidate->additionalInfo->hardware_skills || $candidate->additionalInfo->software_skills))
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-laptop"></i>
                                    Kemampuan Komputer
                                </h3>
                                @if($candidate->additionalInfo->hardware_skills)
                                    <div class="info-row">
                                        <span class="info-label">Hardware</span>
                                        <span class="info-value">{{ $candidate->additionalInfo->hardware_skills }}</span>
                                    </div>
                                @endif
                                @if($candidate->additionalInfo->software_skills)
                                    <div class="info-row">
                                        <span class="info-label">Software</span>
                                        <span class="info-value">{{ $candidate->additionalInfo->software_skills }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($candidate->drivingLicenses->count() > 0)
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-car"></i>
                                    SIM yang Dimiliki
                                </h3>
                                @foreach($candidate->drivingLicenses as $license)
                                    <div class="info-row">
                                        <span class="info-label">SIM {{ $license->license_type }}</span>
                                        <span class="info-value">✓</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($candidate->additionalInfo && $candidate->additionalInfo->other_skills)
                        <div class="info-card" style="margin-top: 20px;">
                            <h3 class="info-card-title">
                                <i class="fas fa-star"></i>
                                Kemampuan Lainnya
                            </h3>
                            <p class="info-text">{{ $candidate->additionalInfo->other_skills }}</p>
                        </div>
                    @endif

                    @if($candidate->languageSkills->count() == 0 && (!$candidate->additionalInfo || (!$candidate->additionalInfo->hardware_skills && !$candidate->additionalInfo->software_skills && !$candidate->additionalInfo->other_skills)) && $candidate->drivingLicenses->count() == 0)
                        <div class="empty-state">
                            <i class="fas fa-cogs"></i>
                            <p>Tidak ada data keterampilan</p>
                        </div>
                    @endif
                </section>

                {{-- Activities & Achievements Section --}}
                <section id="activities-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-hands-helping"></i>
                        Aktivitas & Prestasi
                    </h2>

                    @php
                        $socialActivities = $candidate->activities->where('activity_type', 'social_activity');
                        $achievements = $candidate->activities->where('activity_type', 'achievement');
                    @endphp

                    @if($socialActivities->count() > 0)
                        <h3 class="info-card-title">
                            <i class="fas fa-users"></i>
                            Kegiatan Sosial/Organisasi
                        </h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Organisasi</th>
                                    <th>Bidang</th>
                                    <th>Periode</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($socialActivities as $activity)
                                    <tr>
                                        <td>{{ $activity->title ?? 'N/A' }}</td>
                                        <td>{{ $activity->field_or_year ?? 'N/A' }}</td>
                                        <td>{{ $activity->period ?? 'N/A' }}</td>
                                        <td>{{ $activity->description ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($achievements->count() > 0)
                        <h3 class="info-card-title" style="margin-top: 30px;">
                            <i class="fas fa-trophy"></i>
                            Prestasi
                        </h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Prestasi</th>
                                    <th>Tahun</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($achievements as $achievement)
                                    <tr>
                                        <td>{{ $achievement->title ?? 'N/A' }}</td>
                                        <td>{{ $achievement->field_or_year ?? 'N/A' }}</td>
                                        <td>{{ $achievement->description ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($socialActivities->count() == 0 && $achievements->count() == 0)
                        <div class="empty-state">
                            <i class="fas fa-hands-helping"></i>
                            <p>Tidak ada data aktivitas atau prestasi</p>
                        </div>
                    @endif
                </section>

                {{-- General Information Section --}}
                <section id="general-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Informasi Umum
                    </h2>
                    @if($candidate->additionalInfo)
                        <div class="info-grid">
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-briefcase"></i>
                                    Informasi Pekerjaan
                                </h3>
                                <div class="info-row">
                                    <span class="info-label">Bersedia Dinas Luar Kota</span>
                                    <span class="info-value">
                                        <span class="{{ $candidate->additionalInfo->willing_to_travel ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->additionalInfo->willing_to_travel ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Memiliki Kendaraan</span>
                                    <span class="info-value">
                                        <span class="{{ $candidate->additionalInfo->has_vehicle ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->additionalInfo->has_vehicle ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->additionalInfo->vehicle_types)
                                    <div class="info-row">
                                        <span class="info-label">Jenis Kendaraan</span>
                                        <span class="info-value">{{ $candidate->additionalInfo->vehicle_types }}</span>
                                    </div>
                                @endif
                                <div class="info-row">
                                    <span class="info-label">Tanggal Bisa Mulai Kerja</span>
                                    <span class="info-value">
                                        {{ $candidate->additionalInfo->start_work_date ? \Carbon\Carbon::parse($candidate->additionalInfo->start_work_date)->format('d M Y') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Sumber Informasi Lowongan</span>
                                    <span class="info-value">{{ $candidate->additionalInfo->information_source ?? 'N/A' }}</span>
                                </div>
                                @if($candidate->additionalInfo->absence_days)
                                    <div class="info-row">
                                        <span class="info-label">Hari Tidak Masuk (per tahun)</span>
                                        <span class="info-value">{{ $candidate->additionalInfo->absence_days }} hari</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-user-check"></i>
                                    Data Lainnya
                                </h3>
                                <div class="info-row">
                                    <span class="info-label">Catatan Kriminal</span>
                                    <span class="info-value">
                                        <span class="{{ !$candidate->additionalInfo->has_police_record ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->additionalInfo->has_police_record ? 'Ada' : 'Tidak Ada' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->additionalInfo->police_record_detail)
                                    <div class="info-row">
                                        <span class="info-label">Detail Catatan</span>
                                        <span class="info-value">{{ $candidate->additionalInfo->police_record_detail }}</span>
                                    </div>
                                @endif
                                <div class="info-row">
                                    <span class="info-label">Riwayat Penyakit Serius</span>
                                    <span class="info-value">
                                        <span class="{{ !$candidate->additionalInfo->has_serious_illness ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->additionalInfo->has_serious_illness ? 'Ada' : 'Tidak Ada' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->additionalInfo->illness_detail)
                                    <div class="info-row">
                                        <span class="info-label">Detail Penyakit</span>
                                        <span class="info-value">{{ $candidate->additionalInfo->illness_detail }}</span>
                                    </div>
                                @endif
                                <div class="info-row">
                                    <span class="info-label">Tato/Tindik</span>
                                    <span class="info-value">
                                        <span class="{{ !$candidate->additionalInfo->has_tattoo_piercing ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->additionalInfo->has_tattoo_piercing ? 'Ada' : 'Tidak Ada' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->additionalInfo->tattoo_piercing_detail)
                                    <div class="info-row">
                                        <span class="info-label">Detail Tato/Tindik</span>
                                        <span class="info-value">{{ $candidate->additionalInfo->tattoo_piercing_detail }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-store"></i>
                                    Informasi Bisnis
                                </h3>
                                <div class="info-row">
                                    <span class="info-label">Memiliki Usaha Lain</span>
                                    <span class="info-value">
                                        <span class="{{ $candidate->additionalInfo->has_other_business ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->additionalInfo->has_other_business ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->additionalInfo->other_business_detail)
                                    <div class="info-row">
                                        <span class="info-label">Detail Usaha</span>
                                        <span class="info-value">{{ $candidate->additionalInfo->other_business_detail }}</span>
                                    </div>
                                @endif
                                @if($candidate->additionalInfo->other_income)
                                    <div class="info-row">
                                        <span class="info-label">Penghasilan Lain</span>
                                        <span class="info-value">{{ $candidate->additionalInfo->other_income }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-clipboard-check"></i>
                                    Persetujuan
                                </h3>
                                <div class="info-row">
                                    <span class="info-label">Persetujuan Data</span>
                                    <span class="info-value">
                                        <span class="{{ $candidate->additionalInfo->agreement ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->additionalInfo->agreement ? 'Setuju' : 'Tidak Setuju' }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        @if($candidate->additionalInfo->motivation || $candidate->additionalInfo->strengths || $candidate->additionalInfo->weaknesses)
                            <div class="info-card" style="margin-top: 20px;">
                                <h3 class="info-card-title">
                                    <i class="fas fa-lightbulb"></i>
                                    Motivasi & Karakter
                                </h3>
                                @if($candidate->additionalInfo->motivation)
                                    <div style="margin-bottom: 15px;">
                                        <strong>Motivasi Bekerja:</strong>
                                        <p class="info-text">{{ $candidate->additionalInfo->motivation }}</p>
                                    </div>
                                @endif
                                @if($candidate->additionalInfo->strengths)
                                    <div style="margin-bottom: 15px;">
                                        <strong>Kelebihan:</strong>
                                        <p class="info-text">{{ $candidate->additionalInfo->strengths }}</p>
                                    </div>
                                @endif
                                @if($candidate->additionalInfo->weaknesses)
                                    <div>
                                        <strong>Kekurangan:</strong>
                                        <p class="info-text">{{ $candidate->additionalInfo->weaknesses }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p>Tidak ada informasi umum</p>
                        </div>
                    @endif
                </section>

                {{-- ✅ KRAEPLIN TEST RESULTS - MOVED TO SEPARATE FILE --}}
                @include('candidates.partials.kraeplin-results', ['candidate' => $candidate])

                {{-- ✅ DISC TEST RESULTS - MOVED TO SEPARATE FILE --}}
                @include('candidates.partials.disc-results', ['candidate' => $candidate])

                {{-- Documents Section --}}
                <section id="documents-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-file-alt"></i>
                        Dokumen
                    </h2>

                    @if($candidate->documentUploads->count() > 0)
                        <div class="documents-grid">
                            @php
                                $documentTypes = [
                                    'cv' => ['icon' => 'fa-file-alt', 'label' => 'CV/Resume'],
                                    'photo' => ['icon' => 'fa-image', 'label' => 'Foto'],
                                    'transcript' => ['icon' => 'fa-file-pdf', 'label' => 'Transkrip Nilai'],
                                    'certificates' => ['icon' => 'fa-certificate', 'label' => 'Sertifikat']
                                ];
                            @endphp

                            @foreach($documentTypes as $type => $config)
                                @php
                                    $documents = $candidate->documentUploads->where('document_type', $type);
                                @endphp
                                
                                <div class="document-category">
                                    <h3 class="document-category-title">
                                        <i class="fas {{ $config['icon'] }}"></i>
                                        {{ $config['label'] }}
                                        <span style="font-size: 0.8rem; color: #6b7280; font-weight: normal;">
                                            ({{ $documents->count() }} file{{ $documents->count() > 1 ? 's' : '' }})
                                        </span>
                                    </h3>
                                    
                                    @if($documents->count() > 0)
                                        @foreach($documents as $doc)
                                            <div class="document-item">
                                                <div class="document-info">
                                                    <div class="document-icon">
                                                        <i class="fas {{ $config['icon'] }}"></i>
                                                    </div>
                                                    <div class="document-details">
                                                        <div class="document-name">{{ $doc->document_name ?: $doc->original_filename ?: 'Dokumen' }}</div>
                                                        <div class="document-meta">
                                                            {{ $doc->file_size ? number_format($doc->file_size / 1024, 2) . ' KB' : 'Unknown size' }} • 
                                                            {{ $doc->created_at->format('d M Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="document-actions">
                                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-primary btn-small">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ Storage::url($doc->file_path) }}" download class="btn btn-secondary btn-small">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="no-documents">
                                            <i class="fas {{ $config['icon'] }}"></i>
                                            <p>Tidak ada {{ strtolower($config['label']) }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <p>Tidak ada dokumen yang diupload</p>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>

    {{-- History Modal --}}
    <div id="historyModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 class="modal-title">Riwayat Aktivitas</h2>
                <span class="close" onclick="closeHistoryModal()">&times;</span>
            </div>
            <div style="padding: 30px; max-height: 400px; overflow-y: auto;">
                @if($candidate->applicationLogs->count() > 0)
                    <div class="timeline">
                        @foreach($candidate->applicationLogs->sortByDesc('created_at') as $log)
                            <div class="timeline-item">
                                <div class="timeline-header">
                                    <div class="timeline-title">{{ $log->action_description }}</div>
                                    <div class="timeline-date">{{ $log->created_at->format('d M Y H:i') }}</div>
                                </div>
                                @if($log->user)
                                    <div class="timeline-content">
                                        Oleh: {{ $log->user->full_name }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>Tidak ada riwayat aktivitas</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Status Update Modal --}}
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Update Status Kandidat</h2>
                <span class="close" onclick="closeStatusModal()">&times;</span>
            </div>
            <form id="statusForm" style="padding: 0 30px 30px;">
                <div class="form-group">
                    <label class="form-label">Status Baru</label>
                    <select id="newStatus" class="form-select" required>
                        <option value="">Pilih Status</option>
                        <option value="screening">Screening</option>
                        <option value="interview">Interview</option>
                        <option value="offered">Offered</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan (Opsional)</label>
                    <textarea id="statusNotes" class="form-textarea" placeholder="Tambahkan catatan..."></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script Configuration --}}
    <script>
        // Set configuration for candidate detail page
        window.candidateDetailConfig = {
            updateStatusUrl: '{{ route('candidates.update-status', $candidate->id) }}',
            kraeplinData: null, // Will be set when test results are available
            discData: null      // Will be set when test results are available
        };
    </script>

    {{-- Load JS modules --}}
    <script src="{{ asset('js/kraeplin-chart.js') }}" defer></script>
    <script src="{{ asset('js/disc-3d-manager.js') }}" defer></script>
    <script src="{{ asset('js/candidate-detail.js') }}" defer></script>
</body>
</html>