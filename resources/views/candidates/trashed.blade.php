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

        /* Filters Section */
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-input-container {
            position: relative;
            flex: 1;
        }

        .search-input {
            width: 100%;
            padding: 10px 12px 10px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-button {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a202c;
        }

        .table-info {
            color: #718096;
            font-size: 0.9rem;
        }

        .candidates-table {
            width: 100%;
            border-collapse: collapse;
        }

        .candidates-table th {
            background: #f7fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
        }

        .candidates-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .candidates-table tbody tr {
            transition: all 0.3s ease;
        }

        .candidates-table tbody tr:hover {
            background: #f7fafc;
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
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        .candidate-details {
            display: flex;
            flex-direction: column;
        }

        .candidate-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 2px;
        }

        .candidate-email {
            font-size: 0.85rem;
            color: #718096;
        }

        /* Buttons */
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-right: 8px;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e53e3e, #c53030);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: #e2e8f0;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin: 0;
        }

        /* Pagination */
        .pagination-container {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
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
        }
    </style>
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
                                <th width="8%">No</th>
                                <th width="12%">Kode</th>
                                <th width="30%">Kandidat</th>
                                <th width="20%">Posisi</th>
                                <th width="15%">Dihapus Pada</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($candidates as $index => $candidate)
                            <tr>
                                <td>{{ $candidates->firstItem() + $index }}</td>
                                <td>
                                    <span style="font-weight: 600; color: #4f46e5;">
                                        {{ $candidate->candidate_code }}
                                    </span>
                                </td>
                                <td>
                                    <div class="candidate-info">
                                        <div class="candidate-avatar">
                                            {{-- ✅ FIXED: Gunakan field dari candidates table langsung --}}
                                            {{ substr($candidate->full_name ?? 'N/A', 0, 2) }}
                                        </div>
                                        <div class="candidate-details">
                                            <div class="candidate-name">
                                                {{-- ✅ FIXED: Akses langsung dari candidates table --}}
                                                {{ $candidate->full_name ?? 'N/A' }}
                                            </div>
                                            <div class="candidate-email">
                                                {{-- ✅ FIXED: Akses langsung dari candidates table --}}
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
                                    <button class="btn-small btn-success" 
                                            onclick="restoreCandidate({{ $candidate->id }}, '{{ addslashes($candidate->full_name ?? 'Unknown') }}')"
                                            title="Pulihkan kandidat">
                                        <i class="fas fa-undo"></i> Pulihkan
                                    </button>
                                    <button class="btn-small btn-danger" 
                                            onclick="forceDeleteCandidate({{ $candidate->id }}, '{{ addslashes($candidate->full_name ?? 'Unknown') }}')"
                                            title="Hapus permanen (tidak dapat dikembalikan)">
                                        <i class="fas fa-trash"></i> Hapus Permanen
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="empty-state">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Get CSRF token
        function getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        // Restore candidate function
        function restoreCandidate(candidateId, candidateName) {
            Swal.fire({
                title: 'Pulihkan Kandidat?',
                text: `Apakah Anda yakin ingin memulihkan kandidat "${candidateName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Pulihkan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Memulihkan...',
                        text: 'Sedang memulihkan kandidat',
                        icon: 'info',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send restore request
                    fetch(`{{ url('/candidates') }}/${candidateId}/restore`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCSRFToken()
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message || 'Gagal memulihkan kandidat',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat memulihkan kandidat',
                            icon: 'error'
                        });
                    });
                }
            });
        }

        // Force delete candidate function
        function forceDeleteCandidate(candidateId, candidateName) {
            Swal.fire({
                title: 'Hapus Permanen?',
                html: `
                    <div style="text-align: left; margin: 20px 0;">
                        <p style="margin-bottom: 15px;">Anda akan menghapus permanen kandidat:</p>
                        <p style="font-weight: 600; color: #e53e3e; margin-bottom: 15px;">"${candidateName}"</p>
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; font-size: 0.9rem;">
                            <strong style="color: #dc2626;">⚠️ Peringatan:</strong>
                            <ul style="margin: 8px 0 0 20px; color: #7f1d1d;">
                                <li>Data akan dihapus secara permanen</li>
                                <li>Tidak dapat dikembalikan lagi</li>
                                <li>Semua riwayat akan hilang</li>
                            </ul>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus Permanen',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Sedang menghapus kandidat secara permanen',
                        icon: 'info',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send force delete request
                    fetch(`{{ url('/candidates') }}/${candidateId}/force`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCSRFToken()
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Terhapus!',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message || 'Gagal menghapus kandidat',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menghapus kandidat',
                            icon: 'error'
                        });
                    });
                }
            });
        }

        // Auto-submit search form on enter
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    </script>
</body>
</html>