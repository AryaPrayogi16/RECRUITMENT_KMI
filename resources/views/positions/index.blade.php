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

        /* ✅ SIMPLIFIED: Updated Status Badges - hanya 2 status */
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

        .status-closed {
            background: #fef3c7;
            color: #92400e;
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

        /* Enhanced Application Stats */
        .application-stats {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            padding: 2px 0;
        }

        .stat-label {
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-value {
            font-weight: 600;
            color: #1a202c;
            padding: 2px 8px;
            border-radius: 12px;
            background: #f8fafc;
            min-width: 24px;
            text-align: center;
        }

        .stat-value.active {
            color: #065f46;
            background: #d1fae5;
        }

        .stat-value.warning {
            color: #92400e;
            background: #fef3c7;
        }

        .stat-value.zero {
            color: #6b7280;
            background: #f3f4f6;
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
                <!-- <div class="user-avatar">
                    @if(Auth::user()->role == 'admin')
                        <i class="fas fa-user-crown"></i>
                    @elseif(Auth::user()->role == 'hr')
                        <i class="fas fa-user-tie"></i>
                    @else
                        <i class="fas fa-user"></i>
                    @endif
                </div> -->
                <div class="user-details">
                    <div class="user-name">{{ Auth::user()->full_name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>

            <nav class="nav-menu">
                <!-- <div class="nav-item">
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
                @endif -->
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
                            
                            <!-- ✅ SIMPLIFIED: Filter Status - hanya 2 opsi -->
                            <div class="filter-group">
                                <label class="filter-label">Status</label>
                                <select name="status" class="filter-select">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Tutup</option>
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
                                            <span class="stat-label">
                                                <i class="fas fa-users"></i>
                                                Total:
                                            </span>
                                            <span class="stat-value {{ $position->total_applications_count == 0 ? 'zero' : '' }}">
                                                {{ $position->total_applications_count }}
                                            </span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">
                                                <i class="fas fa-clock"></i>
                                                Proses:
                                            </span>
                                            <span class="stat-value {{ $position->active_applications_count == 0 ? 'zero' : ($position->active_applications_count > 0 ? 'warning' : 'active') }}">
                                                {{ $position->active_applications_count }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{-- ✅ SIMPLIFIED: Hanya 2 Status --}}
                                    @if($position->detailed_status === 'aktif')
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check-circle"></i> Aktif
                                        </span>
                                    @else
                                        <span class="status-badge status-closed">
                                            <i class="fas fa-times-circle"></i> Tutup
                                        </span>
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
                                            <div style="color: {{ $position->closing_date->isPast() ? '#dc2626' : '#f59e0b' }};">
                                                Tutup: {{ $position->closing_date->format('d M Y') }}
                                                @if($position->closing_date->isPast())
                                                    <span style="font-size: 0.7rem;">(Lewat)</span>
                                                @endif
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
                                            <a href="{{ route('positions.edit', $position->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                       
                                            @if($position->detailed_status === 'aktif')
                                                <button class="dropdown-item" 
                                                        onclick="togglePositionStatus({{ $position->id }}, '{{ addslashes($position->position_name) }}', 'close', {{ $position->active_applications_count }}, {{ $position->total_applications_count }})">
                                                    <i class="fas fa-ban"></i>
                                                    Tutup Posisi
                                                </button>
                                            @else
                                                <button class="dropdown-item" 
                                                        onclick="togglePositionStatus({{ $position->id }}, '{{ addslashes($position->position_name) }}', 'open', {{ $position->active_applications_count }}, {{ $position->total_applications_count }})">
                                                    <i class="fas fa-check"></i>
                                                    Buka Posisi
                                                </button>
                                            @endif
                                            <div class="dropdown-divider"></div>
                                            <!-- Enhanced Delete Button -->
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-sm btn-delete-position dropdown-item" 
                                                    data-position-id="{{ $position->id }}"
                                                    data-position-name="{{ $position->position_name }}"
                                                    title="Hapus Posisi">
                                                <i class="fas fa-trash-alt"></i> Hapus
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

        /**
         * ✅ SIMPLIFIED: Enhanced Delete Position Function
         */
        function deletePosition(positionId, positionName, totalCandidates, activeCandidates) {
            if (totalCandidates > 0) {
                let candidateInfo = '';
                let warningLevel = 'warning';
                
                if (activeCandidates > 0) {
                    candidateInfo = `
                        <div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 15px; margin: 15px 0; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 1.2rem;"></i>
                                <strong style="color: #92400e;">Posisi ini memiliki kandidat aktif!</strong>
                            </div>
                            <div style="color: #78350f; font-size: 0.9rem; line-height: 1.4;">
                                📊 <strong>${totalCandidates} total kandidat</strong> terdaftar<br>
                                ⚠️ <strong>${activeCandidates} kandidat sedang dalam proses</strong> rekrutmen<br>
                                <small>Menghapus posisi akan mempengaruhi proses rekrutmen yang sedang berjalan</small>
                            </div>
                        </div>
                        <div style="background: #f0f9ff; border: 1px solid #7dd3fc; border-radius: 8px; padding: 12px; margin: 10px 0; text-align: left;">
                            <strong style="color: #0369a1;">💡 Opsi yang tersedia:</strong>
                            <ul style="margin: 8px 0 0 20px; color: #0c4a6e; font-size: 0.9rem;">
                                <li><strong>Transfer & Hapus:</strong> Pindahkan semua kandidat ke posisi lain</li>
                                <li><strong>Nonaktifkan Posisi:</strong> Hentikan aplikasi baru (kandidat tetap diproses)</li>
                                <li><strong>Hapus Paksa:</strong> Tidak disarankan jika ada kandidat aktif</li>
                            </ul>
                        </div>
                    `;
                    warningLevel = 'error';
                } else {
                    candidateInfo = `
                        <div style="background: #f0fdf8; border: 1px solid #a7f3d0; border-radius: 8px; padding: 15px; margin: 15px 0; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <i class="fas fa-info-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                                <strong style="color: #065f46;">Semua kandidat sudah selesai diproses</strong>
                            </div>
                            <div style="color: #047857; font-size: 0.9rem;">
                                📊 <strong>${totalCandidates} kandidat</strong> pernah mendaftar di posisi ini<br>
                                ✅ Tidak ada kandidat yang sedang dalam proses aktif
                            </div>
                        </div>
                    `;
                    warningLevel = 'warning';
                }

                Swal.fire({
                    title: 'Tidak Dapat Menghapus Langsung',
                    html: `
                        <div style="text-align: left; margin: 10px 0;">
                            <p style="margin-bottom: 15px; text-align: center;">
                                Posisi <strong>"${positionName}"</strong> memiliki data kandidat yang terkait.
                            </p>
                            ${candidateInfo}
                        </div>
                    `,
                    icon: warningLevel,
                    showCancelButton: true,
                    showDenyButton: activeCandidates > 0,
                    confirmButtonColor: activeCandidates > 0 ? '#dc2626' : '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    denyButtonColor: '#f59e0b',
                    confirmButtonText: activeCandidates > 0 ? 'Transfer & Hapus' : 'Nonaktifkan Posisi',
                    denyButtonText: activeCandidates > 0 ? 'Nonaktifkan Posisi' : null,
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'swal-wide'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (activeCandidates > 0) {
                            showTransferDialog(positionId, positionName);
                        } else {
                            // Nonaktifkan posisi
                            togglePositionStatus(positionId, positionName, false, activeCandidates, totalCandidates);
                        }
                    } else if (result.isDenied && activeCandidates > 0) {
                        // Nonaktifkan posisi
                        togglePositionStatus(positionId, positionName, false, activeCandidates, totalCandidates);
                    }
                });
            } else {
                // No candidates, safe to delete
                Swal.fire({
                    title: 'Hapus Posisi?',
                    html: `
                        <div style="text-align: center; margin: 20px 0;">
                            <p>Apakah Anda yakin ingin menghapus posisi:</p>
                            <strong style="color: #1a202c; font-size: 1.1rem;">"${positionName}"</strong>
                            <div style="background: #f0fdf8; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px; margin: 15px 0; color: #065f46; font-size: 0.9rem;">
                                ✅ Posisi ini belum memiliki kandidat yang mendaftar, aman untuk dihapus.
                            </div>
                        </div>
                    `,
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

        /**
         * ✅ SIMPLIFIED: Toggle Position Status - hanya AKTIF/TUTUP
         */
        function togglePositionStatus(positionId, positionName, action, activeCandidates, totalCandidates) {
            let title, message, confirmText, icon, confirmColor;
            
            if (action === 'open') {
                title = 'Aktifkan Posisi?';
                confirmText = 'Ya, Aktifkan';
                icon = 'question';
                confirmColor = '#10b981';
                
                message = `
                    <div style="text-align: center; margin: 15px 0;">
                        <p>Posisi <strong>"${positionName}"</strong> akan diaktifkan dan dapat menerima aplikasi baru.</p>
                        <div style="background: #f0fdf8; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px; margin: 10px 0; color: #065f46; font-size: 0.9rem;">
                            ✅ Posisi ini belum memiliki kandidat yang mendaftar
                        </div>
                    </div>
                `;
            } else {
                title = 'Nonaktifkan Posisi?';
                confirmText = 'Ya, Nonaktifkan';
                icon = 'warning';
                confirmColor = '#f59e0b';
                
                message = `
                    <div style="text-align: left; margin: 15px 0;">
                        <p style="text-align: center; margin-bottom: 15px;">
                            Posisi <strong>"${positionName}"</strong> akan dinonaktifkan untuk aplikasi baru.
                        </p>
                        <div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 12px;">
                            <div style="color: #92400e; font-size: 0.9rem;">
                                📊 <strong>${totalCandidates} total kandidat</strong> terdaftar<br>
                                ⚠️ <strong>${activeCandidates} kandidat sedang dalam proses</strong><br>
                                🔒 Menonaktifkan posisi akan menghentikan aplikasi baru<br>
                                ✅ Kandidat yang sudah mendaftar tetap dapat diproses
                            </div>
                        </div>
                        <div style="margin-top: 10px;">
                            <textarea id="deactivateReason" placeholder="Alasan penonaktifan (opsional)..." 
                                      style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; resize: vertical; min-height: 60px;"></textarea>
                        </div>
                    </div>
                `;
            }

            Swal.fire({
                title: title,
                html: message,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal-wide'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const reason = document.getElementById('deactivateReason')?.value || '';
                    performStatusToggle(positionId, action, reason);
                }
            });
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

        // ✅ SIMPLIFIED: Perform status toggle dengan endpoint yang benar
        function performStatusToggle(positionId, action, reason = '', closingDate = '') {
            showLoading();
            const endpoint = `/positions/${positionId}/toggle-status`;
            const payload = { 
                action: action,
                reason: reason,
                closing_date: closingDate
            };
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                body: JSON.stringify(payload)
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
                    if (data.requiresConfirmation) {
                        Swal.fire({
                            title: 'Konfirmasi Diperlukan',
                            text: data.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#f59e0b',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Ya, Lanjutkan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                payload.force = true;
                                fetch(endpoint, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': getCSRFToken()
                                    },
                                    body: JSON.stringify(payload)
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
                                    }
                                });
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengubah status posisi',
                    icon: 'error'
                });
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

        // Add CSS for wider SweetAlert
        const style = document.createElement('style');
        style.textContent = `
            .swal-wide {
                width: 600px !important;
            }
            .swal-wide .swal2-html-container {
                max-height: 400px;
                overflow-y: auto;
            }
        `;
        document.head.appendChild(style);
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/position-transfer.js') }}"></script>
</body>
</html>