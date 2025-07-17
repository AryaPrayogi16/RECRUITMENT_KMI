<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karir - Lowongan Kerja Tersedia</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
        }
        
        .position-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .position-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .employment-type-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .salary-range {
            color: #28a745;
            font-weight: 600;
        }
        
        .filter-section {
            background-color: #f8f9fa;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .search-box {
            border-radius: 50px;
            border: 2px solid #e9ecef;
            transition: border-color 0.3s ease;
        }
        
        .search-box:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-search {
            border-radius: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .stats-section {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Custom Pagination Styles */
        .pagination {
            justify-content: center;
        }
        
        .pagination .page-link {
            color: #667eea;
            border: 1px solid #dee2e6;
            margin: 0 2px;
            border-radius: 8px;
        }
        
        .pagination .page-link:hover {
            color: #764ba2;
            background-color: #f8f9fa;
            border-color: #667eea;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #667eea;
            border-color: #667eea;
            color: white;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="fas fa-briefcase me-3"></i>
                        Bergabung Bersama Kami
                    </h1>
                    <p class="lead mb-4">
                        Temukan peluang karir terbaik dan wujudkan potensi Anda bersama tim profesional kami
                    </p>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="stats-section">
                        <h3 class="text-primary">{{ $positions->total() }}</h3>
                        <p class="mb-0 text-muted">Lowongan Tersedia</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="container">
            <form method="GET" action="{{ route('careers.index') }}" class="row g-3">
                <!-- Search Input -->
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               class="form-control search-box border-start-0" 
                               placeholder="Cari posisi, departemen..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                
                <!-- Department Filter -->
                <div class="col-md-2">
                    <select name="department" class="form-select">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Location Filter -->
                <div class="col-md-2">
                    <select name="location" class="form-select">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>
                                {{ $loc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Employment Type Filter -->
                <div class="col-md-2">
                    <select name="employment_type" class="form-select">
                        <option value="">Semua Tipe</option>
                        @foreach($employmentTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('employment_type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Search Button -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-search text-white w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Positions Listing -->
    <section class="py-5">
        <div class="container">
            @if($positions->count() > 0)
                <div class="row">
                    @foreach($positions as $position)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card position-card h-100">
                                <div class="card-header bg-white border-0 pb-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="badge bg-primary employment-type-badge">
                                            {{ $position->employment_type_label }}
                                        </span>
                                        @if($position->closing_date)
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                Tutup: {{ $position->closing_date->format('d M Y') }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-primary mb-2">
                                        {{ $position->position_name }}
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-building text-muted me-2"></i>
                                            <span class="text-muted">{{ $position->department }}</span>
                                        </div>
                                        
                                        @if($position->location)
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                <span class="text-muted">{{ $position->location }}</span>
                                            </div>
                                        @endif
                                        
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-money-bill-wave text-muted me-2"></i>
                                            <span class="salary-range">{{ $position->salary_range }}</span>
                                        </div>
                                    </div>
                                    
                                    @if($position->description)
                                        <p class="card-text text-muted">
                                            {{ Str::limit(strip_tags($position->description), 100) }}
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="card-footer bg-white border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Posting: {{ $position->posted_date ? $position->posted_date->format('d M Y') : $position->created_at->format('d M Y') }}
                                        </small>
                                        
                                        <div>
                                            <a href="{{ route('careers.show', $position->id) }}" 
                                               class="btn btn-outline-primary btn-sm me-2">
                                                <i class="fas fa-eye me-1"></i> Detail
                                            </a>
                                            
                                            <a href="{{ route('job.application.form') }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-paper-plane me-1"></i> Lamar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Enhanced Pagination -->
                <div class="row mt-5">
                    <div class="col-12">
                        <nav aria-label="Page navigation">
                            @if ($positions->hasPages())
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($positions->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="fas fa-chevron-left"></i> Sebelumnya
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $positions->previousPageUrl() }}" rel="prev">
                                                <i class="fas fa-chevron-left"></i> Sebelumnya
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($positions->getUrlRange(1, $positions->lastPage()) as $page => $url)
                                        @if ($page == $positions->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($positions->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $positions->nextPageUrl() }}" rel="next">
                                                Selanjutnya <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                Selanjutnya <i class="fas fa-chevron-right"></i>
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                                
                                <!-- Pagination Info -->
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Menampilkan {{ $positions->firstItem() }} - {{ $positions->lastItem() }} 
                                        dari {{ $positions->total() }} lowongan
                                    </small>
                                </div>
                            @endif
                        </nav>
                    </div>
                </div>
            @else
                <!-- No Positions Found -->
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search text-muted mb-3" style="font-size: 4rem;"></i>
                        <h3 class="text-muted">Tidak ada lowongan ditemukan</h3>
                        <p class="text-muted">
                            @if(request()->hasAny(['search', 'department', 'location', 'employment_type']))
                                Coba ubah filter pencarian Anda atau 
                                <a href="{{ route('careers.index') }}" class="text-primary">lihat semua lowongan</a>
                            @else
                                Saat ini belum ada lowongan kerja yang tersedia. Silakan cek kembali nanti.
                            @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </section>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>