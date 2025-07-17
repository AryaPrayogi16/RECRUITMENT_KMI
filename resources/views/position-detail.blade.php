<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $position->position_name }} - Detail Lowongan</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        
        .detail-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .employment-type-badge {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
        }
        
        .salary-range {
            color: #28a745;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }
        
        .section-title {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        
        .btn-apply {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
        }
        
        .btn-back {
            background-color: #6c757d;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
        }
        
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
        }
        
        .requirements-list {
            list-style: none;
            padding: 0;
        }
        
        .requirements-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .requirements-list li:last-child {
            border-bottom: none;
        }
        
        .requirements-list li:before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #28a745;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <a href="{{ route('careers.index') }}" class="btn btn-outline-light">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Lowongan
                        </a>
                    </div>
                    <h1 class="display-5 fw-bold mb-3">{{ $position->position_name }}</h1>
                    <p class="lead mb-4">{{ $position->department }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark employment-type-badge">
                            <i class="fas fa-briefcase me-1"></i>
                            {{ $position->employment_type_label }}
                        </span>
                        <span class="badge status-badge {{ $position->detailed_status === 'aktif' ? 'bg-success' : 'bg-warning' }}">
                            <i class="fas fa-circle me-1"></i>
                            {{ $position->status_label }}
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="bg-white rounded-3 p-4 text-dark">
                        <h3 class="text-primary mb-2">Gaji</h3>
                        <div class="salary-range">{{ $position->salary_range }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Position Details -->
                <div class="col-lg-8">
                    <div class="detail-card mb-4">
                        <div class="card-body p-4">
                            <!-- Basic Information -->
                            <h3 class="section-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Informasi Posisi
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div>
                                            <strong>Departemen</strong><br>
                                            <span class="text-muted">{{ $position->department }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($position->location)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div>
                                            <strong>Lokasi</strong><br>
                                            <span class="text-muted">{{ $position->location }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <strong>Tipe Pekerjaan</strong><br>
                                            <span class="text-muted">{{ $position->employment_type_label }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($position->closing_date)
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-calendar-times"></i>
                                        </div>
                                        <div>
                                            <strong>Batas Lamaran</strong><br>
                                            <span class="text-muted">{{ $position->closing_date->format('d F Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Description -->
                            @if($position->description)
                            <h3 class="section-title mt-4">
                                <i class="fas fa-file-alt me-2"></i>
                                Deskripsi Pekerjaan
                            </h3>
                            <div class="position-description">
                                {!! nl2br(e($position->description)) !!}
                            </div>
                            @endif
                            
                            <!-- Requirements -->
                            @if($position->requirements)
                            <h3 class="section-title mt-4">
                                <i class="fas fa-tasks me-2"></i>
                                Persyaratan
                            </h3>
                            <div class="requirements-section">
                                @php
                                    $requirements = explode("\n", $position->requirements);
                                    $requirements = array_filter(array_map('trim', $requirements));
                                @endphp
                                
                                @if(count($requirements) > 1)
                                    <ul class="requirements-list">
                                        @foreach($requirements as $requirement)
                                            @if(!empty($requirement))
                                                <li>{{ $requirement }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="requirements-text">
                                        {!! nl2br(e($position->requirements)) !!}
                                    </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Apply Section -->
                    <div class="detail-card mb-4">
                        <div class="card-body p-4 text-center">
                            <h4 class="mb-3">Tertarik dengan posisi ini?</h4>
                            
                            @if($position->canAcceptApplications())
                                <p class="text-muted mb-4">
                                    Kirimkan lamaran Anda sekarang dan bergabunglah dengan tim kami!
                                </p>
                                
                                <a href="{{ route('job.application.form') }}?position={{ $position->id }}" 
                                   class="btn btn-apply text-white w-100 mb-3">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Lamar Sekarang
                                </a>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Posisi ini sudah ditutup
                                </div>
                            @endif
                            
                            <a href="{{ route('careers.index') }}" class="btn btn-back text-white w-100">
                                <i class="fas fa-list me-2"></i>
                                Lihat Lowongan Lain
                            </a>
                        </div>
                    </div>
                    
                    <!-- Additional Info -->
                    <div class="detail-card">
                        <div class="card-body p-4">
                            <h5 class="section-title">
                                <i class="fas fa-info me-2"></i>
                                Informasi Tambahan
                            </h5>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div>
                                    <strong>Tanggal Posting</strong><br>
                                    <span class="text-muted">
                                        {{ $position->posted_date ? $position->posted_date->format('d F Y') : $position->created_at->format('d F Y') }}
                                    </span>
                                </div>
                            </div>
                            
                            @if($position->closing_date)
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <div>
                                    <strong>Sisa Waktu</strong><br>
                                    <span class="text-muted">
                                        @php
                                            $daysLeft = $position->closing_date->diffInDays(now(), false);
                                        @endphp
                                        @if($daysLeft > 0)
                                            Sudah berakhir
                                        @elseif($daysLeft == 0)
                                            Berakhir hari ini
                                        @else
                                            {{ abs($daysLeft) }} hari lagi
                                        @endif
                                    </span>
                                </div>
                            </div>
                            @endif
                            
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-muted">
                                    <i class="fas fa-share-alt me-1"></i>
                                    Bagikan lowongan ini kepada teman Anda
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Positions (Optional) -->
    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="text-center mb-4">Lowongan Lainnya</h3>
            <div class="text-center">
                <a href="{{ route('careers.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-search me-2"></i>
                    Lihat Semua Lowongan
                </a>
            </div>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>