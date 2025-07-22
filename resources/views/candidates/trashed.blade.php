<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kandidat Terhapus - HR System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/candidate.css') }}" rel="stylesheet">
    <style>
        .bulk-action-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: none;
            justify-content: space-between;
            align-items: center;
        }

        .bulk-action-bar.show {
            display: flex;
        }

        .bulk-action-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .bulk-action-buttons {
            display: flex;
            gap: 10px;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #1a202c;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 230px;
            background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #4a5568;
            text-align: center;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .logo i {
            font-size: 2rem;
            color: #46e54e;
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .user-info {
            padding: 20px;
            border-bottom: 1px solid #4a5568;
            text-align: center;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 1.5rem;
        }

        .user-details {
            transition: opacity 0.3s ease;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .user-role {
            font-size: 0.85rem;
            color: #a0aec0;
            background: rgba(79, 70, 229, 0.2);
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
        }

        .nav-menu {
            padding: 20px 0;
        }

        .nav-item {
            margin: 8px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #a0aec0;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #4f46e5;
        }

        .nav-link.active {
            background: rgba(79, 70, 229, 0.2);
            color: white;
            border-left-color: #4f46e5;
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 230px;
            transition: margin-left 0.3s ease;
        }

        .header {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #4a5568;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: #f7fafc;
            color: #2d3748;
        }

        .breadcrumb {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .breadcrumb a {
            color: #4f46e5;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a202c;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }

        .content {
            padding: 30px;
        }

        /* Search Form */
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-input-container {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 12px 50px 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-button {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: color 0.3s ease;
        }

        .search-button:hover {
            color: #4f46e5;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a202c;
        }

        .table-info {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .candidates-table {
            width: 100%;
            border-collapse: collapse;
        }

        .candidates-table th {
            background: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9rem;
        }

        .candidates-table td {
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .candidates-table tbody tr:hover {
            background: #f9fafb;
        }

        .candidate-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .candidate-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .candidate-details {
            flex: 1;
        }

        .candidate-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 2px;
        }

        .candidate-email {
            font-size: 0.85rem;
            color: #6b7280;
        }

        /* File Size Badge */
        .file-size-badge {
            background: #e2e8f0;
            color: #4a5568;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .file-size-badge.large {
            background: #fef2f2;
            color: #dc2626;
        }

        /* Checkbox Styles */
        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-checkbox {
            position: relative;
            width: 20px;
            height: 20px;
            margin: 0;
            cursor: pointer;
            accent-color: #4f46e5;
        }

        /* Button Styles */
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 5px;
            text-decoration: none;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #d1d5db;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: #6b7280;
        }

        .empty-state small {
            font-size: 0.9rem;
            color: #9ca3af;
        }

        /* Pagination */
        .pagination-container {
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .header {
                padding: 15px 20px;
            }

            .header-left {
                gap: 15px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .content {
                padding: 20px;
            }

            .search-form {
                flex-direction: column;
                gap: 10px;
            }

            .candidates-table {
                font-size: 0.85rem;
            }

            .candidates-table th,
            .candidates-table td {
                padding: 10px;
            }

            .btn-small {
                font-size: 0.8rem;
                padding: 5px 10px;
            }

            .bulk-action-bar {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .bulk-action-buttons {
                justify-content: center;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
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
                        <a href="{{ route('candidates.index') }}" class="nav-link">
                            <i class="fas fa-user-tie"></i>
                            <span>Kandidat</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('candidates.trashed') }}" class="nav-link active">
                            <i class="fas fa-trash-restore"></i>
                            <span>Kandidat Terhapus</span>
                        </a>
                    </div>
                @endif
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <div class="breadcrumb">
                            <a href="{{ route('candidates.index') }}">Kandidat</a>
                            <span>/</span>
                            <span>Kandidat Terhapus</span>
                        </div>
                        <h1 class="page-title">Kandidat Terhapus</h1>
                    </div>
                </div>
                <div class="header-right">
                    <a href="{{ route('candidates.index') }}" class="btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Kandidat
                    </a>
                </div>
            </header>

            <div class="content">
                <!-- Bulk Action Bar -->
                <div class="bulk-action-bar" id="bulkActionBar">
                    <div class="bulk-action-info">
                        <i class="fas fa-check-circle" style="color: #10b981;"></i>
                        <span id="selectedCount">0 kandidat dipilih</span>
                    </div>
                    <div class="bulk-action-buttons">
                        <button class="btn-small btn-danger" onclick="bulkForceDelete()">
                            <i class="fas fa-trash"></i> 
                            Hapus Permanen Terpilih
                        </button>
                    </div>
                </div>
                
                <!-- Search Filter -->
                <div class="filters-section">
                    <form class="search-form" method="GET">
                        <div class="search-input-container">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                            </button>
                            <input type="text" name="search" class="search-input" 
                                   placeholder="Cari kandidat terhapus berdasarkan nama, email, atau kode..." 
                                   value="{{ request('search') }}">
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Kandidat Terhapus</h3>
                        <span class="table-info">Total: {{ $candidates->total() }} kandidat</span>
                    </div>
                    
                    <table class="candidates-table">
                        <thead>
                            <tr>
                                <th width="5%">
                                    <div class="checkbox-container">
                                        <input type="checkbox" class="custom-checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th width="8%">No</th>
                                <th width="12%">Kode</th>
                                <th width="25%">Kandidat</th>
                                <th width="15%">Posisi</th>
                                <th width="12%">Dihapus Pada</th>
                                <th width="8%">File Size</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($candidates as $index => $candidate)
                            <tr>
                                <td>
                                    <div class="checkbox-container">
                                        <input type="checkbox" class="custom-checkbox candidate-checkbox" 
                                               value="{{ $candidate->id }}"
                                               data-candidate-name="{{ $candidate->full_name ?? 'Unknown' }}">
                                    </div>
                                </td>
                                <td>{{ $candidates->firstItem() + $index }}</td>
                                <td>
                                    <span style="font-weight: 600; color: #4f46e5;">
                                        {{ $candidate->candidate_code }}
                                    </span>
                                </td>
                                <td>
                                    <div class="candidate-info">
                                        <div class="candidate-avatar">
                                            {{ substr($candidate->full_name ?? 'N/A', 0, 2) }}
                                        </div>
                                        <div class="candidate-details">
                                            <div class="candidate-name">
                                                {{ $candidate->full_name ?? 'N/A' }}
                                            </div>
                                            <div class="candidate-email">
                                                {{ $candidate->email ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $candidate->position_applied ?? 'N/A' }}</td>
                                <td>
                                    @if($candidate->deleted_at)
                                        <div style="color: #6b7280;">
                                            {{ $candidate->deleted_at->format('d M Y') }}
                                            <br>
                                            <small style="font-size: 0.8rem;">{{ $candidate->deleted_at->format('H:i') }}</small>
                                        </div>
                                    @else
                                        <span style="color: #9ca3af;">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // Calculate file size from DocumentUpload relationship if loaded
                                        $totalFileSize = 0;
                                        if(isset($candidate->documentUploads)) {
                                            foreach($candidate->documentUploads as $doc) {
                                                if($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                                                    $totalFileSize += Storage::disk('public')->size($doc->file_path);
                                                }
                                            }
                                        }
                                        
                                        // Format bytes
                                        $units = ['B', 'KB', 'MB', 'GB'];
                                        $fileSize = $totalFileSize;
                                        $unitIndex = 0;
                                        
                                        while ($fileSize >= 1024 && $unitIndex < count($units) - 1) {
                                            $fileSize /= 1024;
                                            $unitIndex++;
                                        }
                                        
                                        $formattedSize = $fileSize > 0 ? round($fileSize, 1) . ' ' . $units[$unitIndex] : '0 B';
                                        $isLarge = $totalFileSize > 10485760; // 10MB
                                    @endphp
                                    <span class="file-size-badge {{ $isLarge ? 'large' : '' }}">
                                        {{ $formattedSize }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-small btn-success" 
                                            onclick="restoreCandidate({{ $candidate->id }}, '{{ addslashes($candidate->full_name ?? 'Unknown') }}')"
                                            title="Pulihkan kandidat">
                                        <i class="fas fa-undo"></i> Pulihkan
                                    </button>
                                    <button class="btn-small btn-danger" 
                                            onclick="forceDeleteCandidate({{ $candidate->id }}, '{{ addslashes($candidate->full_name ?? 'Unknown') }}')"
                                            title="Hapus permanen (termasuk semua file)">
                                        <i class="fas fa-trash"></i> Hapus Permanen
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Tidak ada kandidat terhapus</p>
                                    @if(request('search'))
                                        <small>Coba ubah kata kunci pencarian Anda</small>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    @if($candidates->hasPages())
                    <div class="pagination-container">
                        {{ $candidates->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script>
        // Function to get CSRF token
        function getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        // Restore candidate function
        function restoreCandidate(id, name) {
            Swal.fire({
                title: 'Pulihkan Kandidat?',
                text: `Apakah Anda yakin ingin memulihkan kandidat "${name}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Pulihkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang memulihkan kandidat',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`/candidates/${id}/restore`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': getCSRFToken(),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan sistem. Silakan coba lagi.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        }

        // Force delete candidate function
        function forceDeleteCandidate(id, name) {
            Swal.fire({
                title: 'Hapus Permanen?',
                html: `<div style="text-align: left;">
                    <p><strong>PERINGATAN:</strong> Kandidat <strong>"${name}"</strong> akan dihapus <strong>PERMANEN</strong>!</p>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; margin: 15px 0;">
                        <p style="color: #dc2626; margin: 0 0 8px 0;"><strong>Yang akan dihapus:</strong></p>
                        <ul style="color: #7f1d1d; margin: 0; padding-left: 20px;">
                            <li>Semua data kandidat</li>
                            <li>File dokumen (CV, foto, dll)</li>
                            <li>Riwayat interview</li>
                            <li>Log aktivitas</li>
                        </ul>
                    </div>
                    <p style="color: #dc2626;"><strong>Tindakan ini TIDAK DAPAT dibatalkan!</strong></p>
                </div>`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Permanen!',
                cancelButtonText: 'Batal',
                width: '500px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Double confirmation
                    Swal.fire({
                        title: 'Konfirmasi Terakhir',
                        text: 'Ketik "HAPUS" untuk melanjutkan (huruf kapital)',
                        input: 'text',
                        inputPlaceholder: 'Ketik: HAPUS',
                        inputValidator: (value) => {
                            if (value !== 'HAPUS') {
                                return 'Ketik "HAPUS" dengan huruf kapital untuk melanjutkan';
                            }
                        },
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Hapus Permanen',
                        cancelButtonText: 'Batal'
                    }).then((confirmResult) => {
                        if (confirmResult.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Menghapus...',
                                text: 'Sedang menghapus kandidat dan semua file',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                willOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            fetch(`/candidates/${id}/force`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': getCSRFToken(),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Terhapus!',
                                        text: data.message,
                                        icon: 'success',
                                        confirmButtonColor: '#28a745'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message,
                                        icon: 'error',
                                        confirmButtonColor: '#dc3545'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan sistem. Silakan coba lagi.',
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545'
                                });
                            });
                        }
                    });
                }
            });
        }

        // Bulk force delete function
        function bulkForceDelete() {
            const selectedCheckboxes = document.querySelectorAll('.candidate-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Pilih minimal satu kandidat untuk dihapus',
                    icon: 'warning',
                    confirmButtonColor: '#fbbf24'
                });
                return;
            }

            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            const selectedNames = Array.from(selectedCheckboxes).map(cb => cb.getAttribute('data-candidate-name'));

            Swal.fire({
                title: 'Hapus Permanen Multiple?',
                html: `<div style="text-align: left;">
                    <p><strong>PERINGATAN:</strong> ${selectedIds.length} kandidat akan dihapus <strong>PERMANEN</strong>!</p>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; margin: 15px 0;">
                        <p style="color: #dc2626; margin: 0 0 8px 0;"><strong>Kandidat yang akan dihapus:</strong></p>
                        <ul style="color: #7f1d1d; margin: 0; padding-left: 20px; max-height: 150px; overflow-y: auto;">
                            ${selectedNames.map(name => `<li>${name}</li>`).join('')}
                        </ul>
                    </div>
                    <p style="color: #dc2626;"><strong>Semua data dan file akan dihapus PERMANEN!</strong></p>
                </div>`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                width: '500px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Double confirmation
                    Swal.fire({
                        title: 'Konfirmasi Terakhir',
                        text: 'Ketik "HAPUS SEMUA" untuk melanjutkan (huruf kapital)',
                        input: 'text',
                        inputPlaceholder: 'Ketik: HAPUS SEMUA',
                        inputValidator: (value) => {
                            if (value !== 'HAPUS SEMUA') {
                                return 'Ketik "HAPUS SEMUA" dengan huruf kapital untuk melanjutkan';
                            }
                        },
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Hapus Semua Permanen',
                        cancelButtonText: 'Batal'
                    }).then((confirmResult) => {
                        if (confirmResult.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Menghapus...',
                                text: `Sedang menghapus ${selectedIds.length} kandidat dan semua file`,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                willOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            fetch('/candidates/bulk-force-delete', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': getCSRFToken(),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ ids: selectedIds })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Terhapus!',
                                        text: data.message,
                                        icon: 'success',
                                        confirmButtonColor: '#28a745'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message,
                                        icon: 'error',
                                        confirmButtonColor: '#dc3545'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan sistem. Silakan coba lagi.',
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545'
                                });
                            });
                        }
                    });
                }
            });
        }

        // Select all checkbox functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.candidate-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkActionBar();
                });
            }

            // Individual checkbox change
            document.querySelectorAll('.candidate-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkActionBar();
                    
                    // Update select all checkbox
                    const allCheckboxes = document.querySelectorAll('.candidate-checkbox');
                    const checkedCheckboxes = document.querySelectorAll('.candidate-checkbox:checked');
                    const selectAllCheckbox = document.getElementById('selectAll');
                    
                    if (checkedCheckboxes.length === 0) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = false;
                    } else if (checkedCheckboxes.length === allCheckboxes.length) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = true;
                    } else {
                        selectAllCheckbox.indeterminate = true;
                        selectAllCheckbox.checked = false;
                    }
                });
            });

            // Update bulk action bar
            function updateBulkActionBar() {
                const checkedCheckboxes = document.querySelectorAll('.candidate-checkbox:checked');
                const bulkActionBar = document.getElementById('bulkActionBar');
                const selectedCount = document.getElementById('selectedCount');
                
                if (checkedCheckboxes.length > 0) {
                    bulkActionBar.classList.add('show');
                    selectedCount.textContent = `${checkedCheckboxes.length} kandidat dipilih`;
                } else {
                    bulkActionBar.classList.remove('show');
                }
            }

            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    const sidebar = document.getElementById('sidebar');
                    const mainContent = document.getElementById('mainContent');
                    
                    if (window.innerWidth <= 768) {
                        sidebar.classList.toggle('show');
                    } else {
                        sidebar.classList.toggle('collapsed');
                        if (sidebar.classList.contains('collapsed')) {
                            mainContent.style.marginLeft = '70px';
                        } else {
                            mainContent.style.marginLeft = '230px';
                        }
                    }
                });
            }

            // Close sidebar on mobile when clicking outside
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    const sidebar = document.getElementById('sidebar');
                    const sidebarToggle = document.getElementById('sidebarToggle');
                    
                    if (sidebar && sidebarToggle && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });

            // Make updateBulkActionBar available globally
            window.updateBulkActionBar = updateBulkActionBar;
        });
    </script>
</body>
</html>