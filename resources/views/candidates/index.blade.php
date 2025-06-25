<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Kandidat - HR System</title>
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

        /* Sidebar Styles (sama dengan admin.blade.php) */
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

        .sidebar.collapsed .sidebar-header {
            padding: 20px 10px;
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
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .logo-text {
            opacity: 0;
            width: 0;
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

        .sidebar.collapsed .user-avatar {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .user-details {
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .user-details {
            opacity: 0;
            height: 0;
            overflow: hidden;
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

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 70px;
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

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #4a5568;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .notification-btn:hover {
            background: #f7fafc;
            color: #2d3748;
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #e53e3e;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn {
            background: linear-gradient(135deg, #e53e3e, #c53030);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229, 62, 62, 0.4);
        }

        .content {
            padding: 30px;
        }

        /* Filter & Search Section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .filter-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            font-size: 0.9rem;
            color: #4a5568;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .filter-input, .filter-select {
            width: 100%;
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

        .search-box {
            position: relative;
            flex: 2;
            min-width: 300px;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
        }

        .search-input {
            width: 100%;
            padding: 10px 12px 10px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            align-items: flex-end;
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
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }

        .btn-secondary {
            background: #6b7280;
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
        }

        .btn-export {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        /* Candidates Table */
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

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
        }

        /* Dynamic Status Badges - berdasarkan konstanta model */
        .status-draft {
            background: #f3f4f6;
            color: #374151;
        }

        .status-submitted {
            background: #fef3c7;
            color: #92400e;
        }

        .status-screening {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-interview {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-offered {
            background: #fde68a;
            color: #d97706;
        }

        .status-accepted {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-btn {
            background: none;
            border: none;
            padding: 8px;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .action-btn:hover {
            color: #4f46e5;
            transform: scale(1.1);
        }

        .action-dropdown {
            position: relative;
            display: inline-block;
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

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .pagination-info {
            color: #718096;
            font-size: 0.9rem;
        }

        .pagination-controls {
            display: flex;
            gap: 8px;
        }

        .page-btn {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #4a5568;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .page-btn:hover {
            background: #f7fafc;
            border-color: #4f46e5;
            color: #4f46e5;
        }

        .page-btn.active {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Loading State */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
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

            .filter-row {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }

            .search-box {
                width: 100%;
            }

            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }

            .candidates-table {
                font-size: 0.85rem;
            }

            .candidates-table th,
            .candidates-table td {
                padding: 10px;
            }

            .pagination {
                flex-direction: column;
                gap: 15px;
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
                        <a href="{{ route('candidates.index') }}" class="nav-link active">
                            <i class="fas fa-user-tie"></i>
                            <span>Kandidat</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fas fa-briefcase"></i>
                            <span>Posisi</span>
                        </a>
                    </div>
                @endif
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Interview</span>
                    </a>
                </div>
                @if(in_array(Auth::user()->role, ['admin', 'hr']))
                    <div class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fas fa-chart-line"></i>
                            <span>Analytics</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fas fa-envelope"></i>
                            <span>Email Templates</span>
                        </a>
                    </div>
                @endif
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
                @if(Auth::user()->role == 'admin')
                    <div class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fas fa-history"></i>
                            <span>Audit Logs</span>
                        </a>
                    </div>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <header class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Manajemen Kandidat</h1>
                </div>
                <div class="header-right">
                    {{-- <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">{{ $newApplicationsCount ?? 0 }}</span>
                    </button> --}}
                    
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <div class="content">
                <!-- Filter Section -->
                <div class="filter-section">
                    <form id="filterForm">
                        <div class="filter-row">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="search-input" id="searchInput" 
                                       placeholder="Cari berdasarkan nama, email, atau kode kandidat..."
                                       value="{{ request('search') }}">
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label">Status</label>
                                <select class="filter-select" id="statusFilter">
                                    <option value="">Semua Status</option>
                                    @foreach(\App\Models\Candidate::getStatuses() as $statusKey => $statusLabel)
                                        <option value="{{ $statusKey }}" 
                                                {{ request('status') == $statusKey ? 'selected' : '' }}>
                                            {{ $statusLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label">Posisi</label>
                                <select class="filter-select" id="positionFilter">
                                    <option value="">Semua Posisi</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->position_name }}"
                                                {{ request('position') == $position->position_name ? 'selected' : '' }}>
                                            {{ $position->position_name }} - {{ $position->department }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="button" class="btn-secondary" onclick="resetFilters()">
                                    <i class="fas fa-redo"></i>
                                    Reset
                                </button>
                                {{-- <button type="button" class="btn-primary btn-export">
                                    <i class="fas fa-download"></i>
                                    Export
                                </button> --}}
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Candidates Table -->
                <div class="table-container">
                    <div class="loading-overlay" id="loadingOverlay">
                        <div class="loading-spinner"></div>
                    </div>
                    
                    <div class="table-header">
                        <h3 class="table-title">Daftar Kandidat</h3>
                        <span class="table-info">Total: {{ $candidates->total() }} kandidat</span>
                    </div>
                    
                    <table class="candidates-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Kode</th>
                                <th width="25%">Kandidat</th>
                                <th width="15%">Posisi</th>
                                <th width="10%">Status</th>
                                <th width="12%">Tanggal Apply</th>
                                <th width="13%">Gaji Harapan</th>
                                <th width="10%">Aksi</th>
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
                                            {{ substr($candidate->personalData->full_name ?? 'N/A', 0, 2) }}
                                        </div>
                                        <div class="candidate-details">
                                            <div class="candidate-name">
                                                {{ $candidate->personalData->full_name ?? 'N/A' }}
                                            </div>
                                            <div class="candidate-email">
                                                {{ $candidate->personalData->email ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $candidate->position_applied }}</td>
                                <td>
                                    <span class="status-badge {{ $candidate->status_badge_class }}">
                                        {{ \App\Models\Candidate::getStatuses()[$candidate->application_status] ?? ucfirst($candidate->application_status) }}
                                    </span>
                                </td>
                                <td>{{ $candidate->created_at->format('d M Y') }}</td>
                                <td>
                                    @if($candidate->expected_salary)
                                        Rp {{ number_format($candidate->expected_salary, 0, ',', '.') }}
                                    @else
                                        <span style="color: #718096;">Tidak disebutkan</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-dropdown">
                                        <button class="action-btn" onclick="toggleDropdown(this)">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="{{ route('candidates.show', $candidate->id) }}" class="dropdown-item">
                                                <i class="fas fa-eye"></i>
                                                Lihat Detail
                                            </a>
                                            <a href="{{ route('candidates.edit', $candidate->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit"></i>
                                                Edit Data
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: #718096;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>
                                    Tidak ada data kandidat
                                    @if(request()->hasAny(['search', 'status', 'position']))
                                        <br><small>Coba ubah filter pencarian Anda</small>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <div class="pagination">
                        <div class="pagination-info">
                            Menampilkan {{ $candidates->firstItem() ?? 0 }} - {{ $candidates->lastItem() ?? 0 }} 
                            dari {{ $candidates->total() }} kandidat
                        </div>
                        <div class="pagination-controls">
                            @if($candidates->previousPageUrl())
                                <a href="{{ $candidates->appends(request()->query())->previousPageUrl() }}" class="page-btn">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @else
                                <button class="page-btn" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                            @endif
                            
                            @foreach($candidates->appends(request()->query())->getUrlRange(1, $candidates->lastPage()) as $page => $url)
                                @if($page == $candidates->currentPage())
                                    <button class="page-btn active">{{ $page }}</button>
                                @else
                                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                                @endif
                            @endforeach
                            
                            @if($candidates->nextPageUrl())
                                <a href="{{ $candidates->appends(request()->query())->nextPageUrl() }}" class="page-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <button class="page-btn" disabled>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Mobile sidebar
        if (window.innerWidth <= 768) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }

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

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const positionFilter = document.getElementById('positionFilter');
        let searchTimeout;

        function applyFilters() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const params = new URLSearchParams();
                
                if (searchInput.value) params.append('search', searchInput.value);
                if (statusFilter.value) params.append('status', statusFilter.value);
                if (positionFilter.value) params.append('position', positionFilter.value);
                
                window.location.href = `{{ route('candidates.index') }}?${params.toString()}`;
            }, 500);
        }

        searchInput.addEventListener('input', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        positionFilter.addEventListener('change', applyFilters);

        // Reset filters
        function resetFilters() {
            searchInput.value = '';
            statusFilter.value = '';
            positionFilter.value = '';
            window.location.href = '{{ route('candidates.index') }}';
        }

        // Update status
        function updateStatus(candidateId, status) {
            const statusLabels = {!! json_encode(\App\Models\Candidate::getStatuses()) !!};
            const statusLabel = statusLabels[status] || status;
            
            if (confirm(`Apakah Anda yakin ingin mengubah status kandidat ini menjadi "${statusLabel}"?`)) {
                document.getElementById('loadingOverlay').style.display = 'flex';
                
                fetch(`/candidates/${candidateId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Gagal mengubah status kandidat: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengubah status');
                })
                .finally(() => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                });
            }
        }

        // Schedule interview
        function scheduleInterview(candidateId) {
            // Redirect to interview scheduling page
            window.location.href = `/candidates/${candidateId}/schedule-interview`;
        }

        // Export functionality
        document.querySelector('.btn-export').addEventListener('click', function() {
            const params = new URLSearchParams(window.location.search);
            
            // Optional: Jika ingin export hanya yang dipilih
            const selectedIds = [];
            document.querySelectorAll('input[name="candidate_ids[]"]:checked').forEach(cb => {
                selectedIds.push(cb.value);
            });
            
            if (selectedIds.length > 0) {
                params.append('selected_ids', selectedIds.join(','));
            }
            
            window.location.href = `{{ route('candidates.export.multiple') ?? '#' }}?${params.toString()}`;
        });

        // Initialize filter values on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set filter values from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.get('search')) {
                searchInput.value = urlParams.get('search');
            }
            if (urlParams.get('status')) {
                statusFilter.value = urlParams.get('status');
            }
            if (urlParams.get('position')) {
                positionFilter.value = urlParams.get('position');
            }
        });
    </script>
</body>
</html>