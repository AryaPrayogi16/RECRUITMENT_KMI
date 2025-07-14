<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Posisi - HR System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
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

        /* Sidebar Styles (sama dengan struktur lain) */
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
            gap: 15px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .content {
            padding: 30px;
        }

        /* Filter Section */
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .filters-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-size: 0.9rem;
            color: #4a5568;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .filter-input, .filter-select {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-input-container {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding-left: 40px;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
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

        .positions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .positions-table th {
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

        .positions-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .positions-table tbody tr {
            transition: all 0.3s ease;
        }

        .positions-table tbody tr:hover {
            background: #f7fafc;
        }

        /* Position Info */
        .position-info {
            display: flex;
            flex-direction: column;
        }

        .position-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 4px;
            font-size: 1rem;
        }

        .position-department {
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 2px;
        }

        .position-location {
            font-size: 0.8rem;
            color: #9ca3af;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-closed {
            background: #f3f4f6;
            color: #374151;
        }

        /* Employment Type Badges */
        .employment-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .employment-full-time {
            background: #dbeafe;
            color: #1e40af;
        }

        .employment-part-time {
            background: #fef3c7;
            color: #92400e;
        }

        .employment-contract {
            background: #e0e7ff;
            color: #3730a3;
        }

        .employment-internship {
            background: #f3e8ff;
            color: #6b21a8;
        }

        /* Application Stats */
        .application-stats {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
        }

        .stat-label {
            color: #6b7280;
        }

        .stat-value {
            font-weight: 600;
            color: #1a202c;
        }

        .stat-value.active {
            color: #059669;
        }

        /* Action Buttons */
        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-btn {
            background: none;
            border: none;
            padding: 8px;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            border-radius: 6px;
        }

        .action-btn:hover {
            color: #4f46e5;
            background: #f8fafc;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            min-width: 180px;
            z-index: 1000;
            display: none;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            padding: 10px 15px;
            color: #4a5568;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            border: none;
            background: none;
            width: 100%;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: #f7fafc;
            color: #4f46e5;
        }

        .dropdown-item i {
            width: 16px;
        }

        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 5px 0;
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

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            display: none;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .filters-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

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

            .page-title {
                font-size: 1.5rem;
            }

            .content {
                padding: 20px;
            }

            .positions-table {
                font-size: 0.85rem;
            }

            .positions-table th,
            .positions-table td {
                padding: 10px;
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
                        <a href="{{ route('positions.index') }}" class="nav-link active">
                            <i class="fas fa-briefcase"></i>
                            <span>Posisi</span>
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
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                            <span>/</span>
                            <span>Posisi</span>
                        </div>
                        <h1 class="page-title">Manajemen Posisi</h1>
                    </div>
                </div>
                <div class="header-right">
                    <a href="{{ route('positions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Posisi
                    </a>
                    <a href="{{ route('positions.trashed') }}" class="btn btn-secondary">
                        <i class="fas fa-trash"></i>
                        Posisi Terhapus
                    </a>
                </div>
            </header>

            <div class="content">
                <!-- Filter Section -->
                <div class="filters-section">
                    <form id="filterForm" method="GET">
                        <div class="filters-row">
                            <div class="filter-group">
                                <label class="filter-label">Pencarian</label>
                                <div class="search-input-container">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" name="search" class="filter-input search-input" 
                                           placeholder="Cari posisi, departemen, atau lokasi..."
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label">Departemen</label>
                                <select name="department" class="filter-select">
                                    <option value="">Semua Departemen</option>
                                    @foreach(\App\Models\Position::getDepartments() as $dept)
                                        <option value="{{ $dept }}" 
                                                {{ request('department') == $dept ? 'selected' : '' }}>
                                            {{ $dept }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label">Status</label>
                                <select name="status" class="filter-select">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Terbuka</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label">Tipe</label>
                                <select name="employment_type" class="filter-select">
                                    <option value="">Semua Tipe</option>
                                    @foreach(\App\Models\Position::getEmploymentTypes() as $key => $label)
                                        <option value="{{ $key }}" 
                                                {{ request('employment_type') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i>
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Daftar Posisi</h3>
                        <span class="table-info">Total: {{ $positions->total() }} posisi</span>
                    </div>
                    
                    <table class="positions-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Posisi</th>
                                <th width="15%">Tipe & Gaji</th>
                                <th width="15%">Aplikasi</th>
                                <th width="10%">Status</th>
                                <th width="15%">Tanggal</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($positions as $index => $position)
                            <tr>
                                <td>{{ $positions->firstItem() + $index }}</td>
                                <td>
                                    <div class="position-info">
                                        <div class="position-name">{{ $position->position_name }}</div>
                                        <div class="position-department">{{ $position->department }}</div>
                                        @if($position->location)
                                            <div class="position-location">
                                                <i class="fas fa-map-marker-alt"></i> {{ $position->location }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="margin-bottom: 8px;">
                                        <span class="employment-badge employment-{{ str_replace('-', '-', $position->employment_type) }}">
                                            {{ $position->employment_type_label }}
                                        </span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #6b7280;">
                                        {{ $position->salary_range }}
                                    </div>
                                </td>
                                <td>
                                    <div class="application-stats">
                                        <div class="stat-item">
                                            <span class="stat-label">Total:</span>
                                            <span class="stat-value">{{ $position->total_applications_count }}</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Aktif:</span>
                                            <span class="stat-value active">{{ $position->active_applications_count }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($position->is_active)
                                        @if($position->is_open)
                                            <span class="status-badge status-active">Terbuka</span>
                                        @else
                                            <span class="status-badge status-inactive">Tutup</span>
                                        @endif
                                    @else
                                        <span class="status-badge status-inactive">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">
                                        @if($position->posted_date)
                                            <div style="color: #6b7280; margin-bottom: 4px;">
                                                Posted: {{ $position->posted_date->format('d M Y') }}
                                            </div>
                                        @endif
                                        @if($position->closing_date)
                                            <div style="color: #dc2626;">
                                                Tutup: {{ $position->closing_date->format('d M Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="action-dropdown">
                                        <button class="action-btn" onclick="toggleDropdown(this)">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <!-- <a href="{{ route('positions.show', $position->id) }}" class="dropdown-item">
                                                <i class="fas fa-eye"></i>
                                                Lihat Detail
                                            </a> -->
                                            <a href="{{ route('positions.edit', $position->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            @if($position->is_active)
                                                <button class="dropdown-item" onclick="closePosition({{ $position->id }}, '{{ addslashes($position->position_name) }}')">
                                                    <i class="fas fa-ban"></i>
                                                    Tutup Posisi
                                                </button>
                                            @else
                                                <button class="dropdown-item" onclick="activatePosition({{ $position->id }}, '{{ addslashes($position->position_name) }}')">
                                                    <i class="fas fa-check"></i>
                                                    Aktifkan
                                                </button>
                                            @endif
                                            <div class="dropdown-divider"></div>
                                            <button class="dropdown-item" onclick="deletePosition({{ $position->id }}, '{{ addslashes($position->position_name) }}', {{ $position->total_applications_count }})">
                                                <i class="fas fa-trash"></i>
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-briefcase"></i>
                                    <p>Tidak ada posisi yang tersedia</p>
                                    @if(request()->hasAny(['search', 'department', 'status', 'employment_type']))
                                        <small>Coba ubah filter pencarian Anda</small>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    @if($positions->hasPages())
                    <div class="pagination-container">
                        {{ $positions->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
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

        // Dropdown menu
        function toggleDropdown(button) {
            const dropdown = button.nextElementSibling;
            const allDropdowns = document.querySelectorAll('.dropdown-menu');
            
            allDropdowns.forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('show');
                }
            });
            
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.action-dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(d => {
                    d.classList.remove('show');
                });
            }
        });

        // Get CSRF token
        function getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        // Auto-submit filter form
        document.querySelectorAll('.filter-select, .search-input').forEach(element => {
            element.addEventListener('change', function() {
                if (this.classList.contains('search-input')) {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.closest('form').submit();
                    }, 500);
                } else {
                    this.closest('form').submit();
                }
            });
        });

        // Delete position function
        function deletePosition(positionId, positionName, candidateCount) {
            if (candidateCount > 0) {
                Swal.fire({
                    title: 'Tidak Dapat Menghapus',
                    html: `
                        <div style="text-align: left; margin: 20px 0;">
                            <p style="margin-bottom: 15px;">Posisi <strong>"${positionName}"</strong> tidak dapat dihapus karena masih memiliki <strong>${candidateCount} kandidat</strong>.</p>
                            <div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 12px; font-size: 0.9rem;">
                                <strong style="color: #92400e;">Opsi yang tersedia:</strong>
                                <ul style="margin: 8px 0 0 20px; color: #78350f;">
                                    <li>Tutup posisi (set tidak aktif)</li>
                                    <li>Transfer kandidat ke posisi lain</li>
                                    <li>Hapus paksa (tidak disarankan)</li>
                                </ul>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Transfer & Hapus',
                    cancelButtonText: 'Batal',
                    showDenyButton: true,
                    denyButtonText: 'Tutup Posisi',
                    denyButtonColor: '#f59e0b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        showTransferDialog(positionId, positionName);
                    } else if (result.isDenied) {
                        closePosition(positionId, positionName);
                    }
                });
            } else {
                Swal.fire({
                    title: 'Hapus Posisi?',
                    text: `Apakah Anda yakin ingin menghapus posisi "${positionName}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDelete(positionId);
                    }
                });
            }
        }

        // Perform actual delete
        function performDelete(positionId) {
            showLoading();
            
            fetch(`/positions/${positionId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
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
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghapus posisi',
                    icon: 'error'
                });
            });
        }

        // Close position function
        function closePosition(positionId, positionName) {
            Swal.fire({
                title: 'Tutup Posisi?',
                input: 'textarea',
                inputLabel: 'Alasan penutupan (opsional)',
                inputPlaceholder: 'Masukkan alasan penutupan posisi...',
                text: `Posisi "${positionName}" akan ditutup dan tidak menerima aplikasi baru.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Tutup',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    
                    fetch(`/positions/${positionId}/close`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCSRFToken()
                        },
                        body: JSON.stringify({
                            reason: result.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
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
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menutup posisi',
                            icon: 'error'
                        });
                    });
                }
            });
        }

        // Activate position function
        function activatePosition(positionId, positionName) {
            Swal.fire({
                title: 'Aktifkan Posisi?',
                text: `Posisi "${positionName}" akan diaktifkan dan dapat menerima aplikasi.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Aktifkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    
                    fetch(`/positions/${positionId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCSRFToken()
                        },
                        body: JSON.stringify({
                            is_active: true
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: `Posisi "${positionName}" berhasil diaktifkan`,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat mengaktifkan posisi',
                            icon: 'error'
                        });
                    });
                }
            });
        }

        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    </script>
</body>
</html>