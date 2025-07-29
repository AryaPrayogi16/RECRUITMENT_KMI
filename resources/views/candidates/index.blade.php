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

        /* Sidebar Styles (sama dengan sebelumnya) */
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
            min-width: 180px;
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

        /* Dynamic Status Badges */
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

        /* ✅ NEW: Test Result Badges */
        .test-score {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .score-value {
            font-weight: 600;
            color: #1a202c;
        }

        .score-category {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Kraeplin Categories */
        .kraeplin-excellent {
            background: #dcfce7;
            color: #166534;
        }

        .kraeplin-good {
            background: #dbeafe;
            color: #1e40af;
        }

        .kraeplin-average {
            background: #fef3c7;
            color: #92400e;
        }

        .kraeplin-poor {
            background: #fee2e2;
            color: #991b1b;
        }

        /* DISC Type Badges */
        .disc-type {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .disc-d {
            background: #fecaca;
            color: #991b1b;
        }

        .disc-i {
            background: #fed7aa;
            color: #9a3412;
        }

        .disc-s {
            background: #d1fae5;
            color: #166534;
        }

        .disc-c {
            background: #dbeafe;
            color: #1e40af;
        }

        .test-not-taken {
            color: #9ca3af;
            font-style: italic;
            font-size: 0.85rem;
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

        /* Bulk Action Toolbar */
        .bulk-action-toolbar {
            background: #4f46e5;
            color: white;
            padding: 15px 20px;
            display: none;
            align-items: center;
            justify-content: space-between;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .bulk-action-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .bulk-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
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
        @media (max-width: 1200px) {
            .candidates-table {
                font-size: 0.85rem;
            }
            
            .candidates-table th,
            .candidates-table td {
                padding: 10px 8px;
            }

            .filter-row {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }
        }

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

            .search-box {
                width: 100%;
            }

            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }

            .candidates-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
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
                        <a href="{{ route('candidates.index') }}" class="nav-link active">
                            <i class="fas fa-user-tie"></i>
                            <span>Kandidat</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="{{ route('positions.index') }}" class="nav-link">
                            <i class="fas fa-briefcase"></i>
                            <span>Posisi</span>
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
                    <a href="{{ route('candidates.trashed') }}" class="btn-secondary">
                        <i class="fas fa-trash"></i>
                        Kandidat Terhapus
                    </a>
                    
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
                                    @foreach(\App\Models\Candidate::getStatusOptions() as $statusKey => $statusLabel)
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

                            <!-- ✅ NEW: Test Status Filter -->
                            <div class="filter-group">
                                <label class="filter-label">Status Test</label>
                                <select class="filter-select" id="testStatusFilter">
                                    <option value="">Semua</option>
                                    <option value="all_tests_completed" {{ request('test_status') == 'all_tests_completed' ? 'selected' : '' }}>
                                        Semua Test Selesai
                                    </option>
                                    <option value="kraeplin_completed" {{ request('test_status') == 'kraeplin_completed' ? 'selected' : '' }}>
                                        Kraeplin Selesai
                                    </option>
                                    <option value="disc_completed" {{ request('test_status') == 'disc_completed' ? 'selected' : '' }}>
                                        DISC Selesai
                                    </option>
                                    <option value="no_tests" {{ request('test_status') == 'no_tests' ? 'selected' : '' }}>
                                        Belum Test
                                    </option>
                                </select>
                            </div>

                            <!-- ✅ NEW: Kraeplin Category Filter -->
                            <div class="filter-group">
                                <label class="filter-label">Kategori Kraeplin</label>
                                <select class="filter-select" id="kraeplinCategoryFilter">
                                    <option value="">Semua Kategori</option>
                                    @foreach($kraeplinCategories as $category)
                                        <option value="{{ $category }}" 
                                                {{ request('kraeplin_category') == $category ? 'selected' : '' }}>
                                            {{ ucfirst($category) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- ✅ NEW: DISC Type Filter -->
                            <div class="filter-group">
                                <label class="filter-label">Tipe DISC</label>
                                <select class="filter-select" id="discTypeFilter">
                                    <option value="">Semua Tipe</option>
                                    @foreach($discTypes as $type)
                                        <option value="{{ $type }}" 
                                                {{ request('disc_type') == $type ? 'selected' : '' }}>
                                            {{ strtoupper($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="button" class="btn-secondary" onclick="resetFilters()">
                                    <i class="fas fa-redo"></i>
                                    Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="bulk-action-toolbar" id="bulkActionToolbar" style="display: none;">
                    <div class="bulk-action-content">
                        <span><span id="selectedCount">0</span> kandidat terpilih</span>
                        <div class="bulk-actions">
                            <button type="button" class="btn-small btn-danger" onclick="bulkDelete()">
                                <i class="fas fa-trash"></i> Hapus Terpilih
                            </button>
                        </div>
                    </div>
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
                                <th width="4%">No</th>
                                <th width="3%">
                                    <input type="checkbox" id="selectAll" style="margin: 0;">
                                </th>
                                <th width="8%">Kode</th>
                                <th width="20%">Kandidat</th>
                                <th width="12%">Posisi</th>
                                <th width="8%">Status</th>
                                <th width="10%">Tanggal Apply</th>
                                <th width="10%">Gaji Harapan</th>
                                <!-- ✅ NEW: Test Result Columns -->
                                <th width="10%">Skor Kraeplin</th>
                                <th width="8%">Tipe DISC</th>
                                <th width="7%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($candidates as $index => $candidate)
                            <tr>
                                <td>{{ $candidates->firstItem() + $index }}</td>
                                <td>
                                    <input type="checkbox" class="candidate-checkbox" value="{{ $candidate->id }}" style="margin: 0;">
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: #4f46e5;">
                                        {{ $candidate->candidate_code }}
                                    </span>
                                </td>
                                <td>
                                    <div class="candidate-info">
                                        <div class="candidate-avatar">
                                            @php
                                                $photoDocument = $candidate->documentUploads->where('document_type', 'photo')->first();
                                            @endphp
                                            @if($photoDocument)
                                                <img src="{{ Storage::url($photoDocument->file_path) }}" 
                                                    alt="Foto {{ $candidate->full_name ?? 'Kandidat' }}" 
                                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                            @else
                                                {{ substr($candidate->full_name ?? 'N/A', 0, 2) }}
                                            @endif
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
                                <td>{{ $candidate->position_applied }}</td>
                                <td>
                                    <span class="status-badge {{ $candidate->status_badge_class }}">
                                        {{ \App\Models\Candidate::getStatusOptions()[$candidate->application_status] ?? ucfirst($candidate->application_status) }}
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
                                <!-- ✅ FIXED: Kraeplin Score Column using correct property name -->
                                <td>
                                    @if($candidate->kraeplinTestResult)
                                        <div class="test-score">
                                            <span class="score-value">{{ $candidate->kraeplinTestResult->overall_score ?? 0 }}</span>
                                            @if($candidate->kraeplinTestResult->performance_category)
                                                <span class="score-category kraeplin-{{ strtolower($candidate->kraeplinTestResult->performance_category) }}">
                                                    {{ ucfirst($candidate->kraeplinTestResult->performance_category) }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="test-not-taken">Belum test</span>
                                    @endif
                                </td>
                                <!-- ✅ NEW: DISC Type Column -->
                                <td>
                                    @if($candidate->disc3DResult && $candidate->disc3DResult->primary_type)
                                        <div class="test-score">
                                            <span class="disc-type disc-{{ strtolower($candidate->disc3DResult->primary_type) }}">
                                                {{ strtoupper($candidate->disc3DResult->primary_type) }}
                                            </span>
                                            @if($candidate->disc3DResult->primary_percentage)
                                                <span style="font-size: 0.8rem; color: #6b7280;">
                                                    {{ round($candidate->disc3DResult->primary_percentage) }}%
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="test-not-taken">Belum test</span>
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
                                                Edit
                                            </a>
                                            <!-- ✅ NEW: Test Result Links -->
                                            @if($candidate->kraeplinTestResult)
                                                <a href="{{ route('candidates.show', $candidate->id) }}#kraeplin-section" class="dropdown-item">
                                                    <i class="fas fa-chart-line"></i>
                                                    Hasil Kraeplin
                                                </a>
                                            @endif
                                            @if($candidate->disc3DResult)
                                                <a href="{{ route('candidates.show', $candidate->id) }}#disc-section" class="dropdown-item">
                                                    <i class="fas fa-chart-pie"></i>
                                                    Hasil DISC
                                                </a>
                                            @endif
                                            <div class="dropdown-divider"></div>
                                            <a href="#" class="dropdown-item" onclick="deleteCandidate({{ $candidate->id }}, '{{ $candidate->full_name ?? 'Unknown' }}')">
                                                <i class="fas fa-trash"></i>
                                                Hapus
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 40px; color: #718096;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>
                                    Tidak ada data kandidat
                                    @if(request()->hasAny(['search', 'status', 'position', 'test_status', 'kraeplin_category', 'disc_type']))
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
        // ✅ UPDATED: JavaScript with new filter support
        let isProcessing = false;

        function getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        }

        function showLoading(message = 'Processing...', subtitle = '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: message,
                    html: subtitle ? `<div style="text-align: center;"><p>${subtitle}</p></div>` : '',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            } else {
                document.getElementById('loadingOverlay').style.display = 'flex';
            }
        }

        function hideLoading() {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            } else {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        }

        function showSuccess(title, message, timer = 3000) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({
                    title: title,
                    html: message,
                    icon: 'success',
                    timer: timer,
                    showConfirmButton: timer > 5000,
                    timerProgressBar: true
                });
            } else {
                alert(title + ': ' + message);
                return Promise.resolve();
            }
        }

        function showError(title, message) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({
                    title: title,
                    html: message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else {
                alert(title + ': ' + message);
                return Promise.resolve();
            }
        }

        async function makeRequest(url, options = {}) {
            if (isProcessing) {
                showError('Sedang Diproses', 'Harap tunggu operasi sebelumnya selesai');
                return null;
            }

            isProcessing = true;

            try {
                const defaultOptions = {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                        'Accept': 'application/json'
                    }
                };

                const response = await fetch(url, { ...defaultOptions, ...options });
                
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                }

                return await response.json();
            } catch (error) {
                console.error('Request error:', error);
                throw error;
            } finally {
                isProcessing = false;
            }
        }

        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (sidebarToggle && sidebar && mainContent) {
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
        }

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

        // ✅ UPDATED: Filter functionality with new test filters
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const positionFilter = document.getElementById('positionFilter');
        const testStatusFilter = document.getElementById('testStatusFilter');
        const kraeplinCategoryFilter = document.getElementById('kraeplinCategoryFilter');
        const discTypeFilter = document.getElementById('discTypeFilter');
        let searchTimeout;

        function applyFilters() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const params = new URLSearchParams();
                
                if (searchInput && searchInput.value) params.append('search', searchInput.value);
                if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);
                if (positionFilter && positionFilter.value) params.append('position', positionFilter.value);
                if (testStatusFilter && testStatusFilter.value) params.append('test_status', testStatusFilter.value);
                if (kraeplinCategoryFilter && kraeplinCategoryFilter.value) params.append('kraeplin_category', kraeplinCategoryFilter.value);
                if (discTypeFilter && discTypeFilter.value) params.append('disc_type', discTypeFilter.value);
                
                const baseUrl = window.location.pathname;
                window.location.href = `${baseUrl}?${params.toString()}`;
            }, 500);
        }

        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (statusFilter) statusFilter.addEventListener('change', applyFilters);
        if (positionFilter) positionFilter.addEventListener('change', applyFilters);
        if (testStatusFilter) testStatusFilter.addEventListener('change', applyFilters);
        if (kraeplinCategoryFilter) kraeplinCategoryFilter.addEventListener('change', applyFilters);
        if (discTypeFilter) discTypeFilter.addEventListener('change', applyFilters);

        function resetFilters() {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (positionFilter) positionFilter.value = '';
            if (testStatusFilter) testStatusFilter.value = '';
            if (kraeplinCategoryFilter) kraeplinCategoryFilter.value = '';
            if (discTypeFilter) discTypeFilter.value = '';
            window.location.href = window.location.pathname;
        }

        // Checkbox & bulk action functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        const candidateCheckboxes = document.querySelectorAll('.candidate-checkbox');
        const bulkActionToolbar = document.getElementById('bulkActionToolbar');
        const selectedCountElement = document.getElementById('selectedCount');

        function updateSelectedCount() {
            const selectedCount = document.querySelectorAll('.candidate-checkbox:checked').length;
            
            if (selectedCountElement) {
                selectedCountElement.innerText = selectedCount;
            }

            if (bulkActionToolbar) {
                if (selectedCount > 0) {
                    bulkActionToolbar.style.display = 'flex';
                } else {
                    bulkActionToolbar.style.display = 'none';
                }
            }

            // Update select all state
            if (selectAllCheckbox && candidateCheckboxes.length > 0) {
                const totalBoxes = candidateCheckboxes.length;
                
                if (selectedCount === totalBoxes && totalBoxes > 0) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else if (selectedCount > 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
            }
        }

        // Select all functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                candidateCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                updateSelectedCount();
            });
        }

        // Individual checkbox functionality
        candidateCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        async function deleteCandidate(candidateId, candidateName) {
            const confirmResult = typeof Swal !== 'undefined' 
                ? await Swal.fire({
                    title: 'Hapus Kandidat?',
                    text: `Apakah Anda yakin ingin menghapus kandidat "${candidateName}" ke trash?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                })
                : { isConfirmed: confirm(`Apakah Anda yakin ingin menghapus kandidat "${candidateName}"?`) };

            if (!confirmResult.isConfirmed) return;

            try {
                showLoading('Menghapus...', 'Memindahkan kandidat ke trash');

                const data = await makeRequest(`/candidates/${candidateId}`, {
                    method: 'DELETE'
                });

                await showSuccess('Berhasil!', data.message);
                window.location.reload();

            } catch (error) {
                hideLoading();
                showError('Gagal Menghapus', `Terjadi kesalahan: ${error.message}`);
            }
        }

        async function bulkDelete() {
            const selectedIds = [];
            const selectedNames = [];
            
            document.querySelectorAll('.candidate-checkbox:checked').forEach(checkbox => {
                selectedIds.push(checkbox.value);
                const row = checkbox.closest('tr');
                const nameElement = row.querySelector('.candidate-name');
                selectedNames.push(nameElement ? nameElement.textContent.trim() : 'Unknown');
            });

            if (selectedIds.length === 0) {
                showError('Tidak Ada Yang Dipilih', 'Pilih minimal satu kandidat untuk dihapus');
                return;
            }

            const confirmResult = typeof Swal !== 'undefined' 
                ? await Swal.fire({
                    title: 'Hapus ke Trash?',
                    html: `
                        <div style="text-align: left; margin: 20px 0;">
                            <p style="margin-bottom: 15px;">Anda akan menghapus <strong>${selectedIds.length} kandidat</strong> ke trash:</p>
                            <div style="max-height: 150px; overflow-y: auto; background: #f7fafc; padding: 10px; border-radius: 6px; margin: 10px 0;">
                                ${selectedNames.slice(0, 5).map(name => `<div style="margin: 2px 0;">• ${name}</div>`).join('')}
                                ${selectedNames.length > 5 ? `<div style="margin: 2px 0; color: #6b7280;">• dan ${selectedNames.length - 5} kandidat lainnya...</div>` : ''}
                            </div>
                            <p style="color: #718096; font-size: 0.9rem; margin-top: 10px;">
                                <i class="fas fa-info-circle"></i> Kandidat akan dipindahkan ke trash dan dapat dipulihkan nanti
                            </p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: `Ya, Hapus ${selectedIds.length} Kandidat`,
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    width: '500px'
                })
                : { isConfirmed: confirm(`Apakah Anda yakin ingin menghapus ${selectedIds.length} kandidat terpilih?`) };

            if (!confirmResult.isConfirmed) return;

            try {
                showLoading('Menghapus...', `Memproses ${selectedIds.length} kandidat`);

                const data = await makeRequest('/candidates/bulk-delete', {
                    method: 'POST',
                    body: JSON.stringify({ ids: selectedIds })
                });

                await showSuccess('Berhasil Dihapus!', data.message);
                window.location.reload();

            } catch (error) {
                hideLoading();
                showError('Gagal Menghapus', `Terjadi kesalahan: ${error.message}`);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedCount();
            console.log('✅ Candidates Index page with test results initialized');
        });

        // Expose functions to global scope for inline onclick handlers
        window.toggleDropdown = toggleDropdown;
        window.resetFilters = resetFilters;
        window.deleteCandidate = deleteCandidate;
        window.bulkDelete = bulkDelete;
    </script>
</body>
</html>