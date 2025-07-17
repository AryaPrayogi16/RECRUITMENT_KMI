<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Posisi - HR System</title>
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

        /* Sidebar Styles (konsisten dengan pages lain) */
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

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .content {
            padding: 30px;
        }

        /* Enhanced Candidate Info Alerts */
        .candidate-info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #e2e8f0;
        }

        .info-card.has-candidates {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fefbf2, #ffffff);
        }

        .info-card.no-candidates {
            border-left-color: #10b981;
            background: linear-gradient(135deg, #f0fdf8, #ffffff);
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .info-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .info-card.has-candidates .info-card-icon {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .info-card.no-candidates .info-card-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .info-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a202c;
        }

        .info-card-subtitle {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .candidate-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 12px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            display: block;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .warning-note {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            color: #92400e;
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .success-note {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form Styles */
        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 20px 30px;
        }

        .form-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .form-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .form-body {
            padding: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-label.required::after {
            content: ' *';
            color: #dc2626;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-textarea.large {
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-help {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 5px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .checkbox-input {
            width: 18px;
            height: 18px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
        }

        .checkbox-input:checked {
            background: #f59e0b;
            border-color: #f59e0b;
        }

        .checkbox-label {
            font-size: 0.9rem;
            color: #374151;
            cursor: pointer;
        }

        /* Form Actions */
        .form-actions {
            background: #f9fafb;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-actions-left {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .form-actions-right {
            display: flex;
            gap: 12px;
        }

        /* Validation Styles */
        .form-input.error, .form-select.error, .form-textarea.error {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .error-message {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .success-message {
            color: #059669;
            font-size: 0.8rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Alert Styles */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        /* Loading State */
        .btn.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .btn.loading::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

            .page-title {
                font-size: 1.5rem;
            }

            .content {
                padding: 20px;
            }

            .candidate-info-section {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
                gap: 15px;
                text-align: center;
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
                            <a href="{{ route('positions.index') }}">Posisi</a>
                            <span>/</span>
                            <span>Edit</span>
                        </div>
                        <h1 class="page-title">Edit Posisi: {{ $position->position_name }}</h1>
                    </div>
                </div>
                <div class="header-right">
                    <a href="{{ route('positions.show', $position) }}" class="btn btn-primary">
                        <i class="fas fa-eye"></i>
                        Lihat Detail
                    </a>
                    <a href="{{ route('positions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
            </header>

            <div class="content">
                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Enhanced Candidate Information -->
                <div class="candidate-info-section">
                    @if($hasActiveCandidates || $totalCandidates > 0)
                        <div class="info-card has-candidates">
                            <div class="info-card-header">
                                <div class="info-card-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <div class="info-card-title">Kandidat Terdaftar</div>
                                    <div class="info-card-subtitle">Posisi ini memiliki kandidat yang mendaftar</div>
                                </div>
                            </div>
                            <div class="candidate-stats">
                                <div class="stat-item">
                                    <span class="stat-number">{{ $totalCandidates }}</span>
                                    <span class="stat-label">Total Kandidat</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number" style="color: {{ $activeCandidates > 0 ? '#f59e0b' : '#6b7280' }};">{{ $activeCandidates }}</span>
                                    <span class="stat-label">Sedang Proses</span>
                                </div>
                            </div>
                            @if($activeCandidates > 0)
                                <div class="warning-note">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <div>
                                        <strong>Perhatian!</strong> {{ $activeCandidates }} kandidat sedang dalam proses rekrutmen. 
                                        Perubahan pada posisi ini dapat mempengaruhi proses yang sedang berjalan.
                                    </div>
                                </div>
                            @else
                                <div class="success-note">
                                    <i class="fas fa-info-circle"></i>
                                    <div>
                                        Semua aplikasi untuk posisi ini sudah selesai diproses.
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="info-card no-candidates">
                            <div class="info-card-header">
                                <div class="info-card-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div>
                                    <div class="info-card-title">Belum Ada Kandidat</div>
                                    <div class="info-card-subtitle">Posisi ini belum memiliki kandidat yang mendaftar</div>
                                </div>
                            </div>
                            <div class="candidate-stats">
                                <div class="stat-item">
                                    <span class="stat-number">0</span>
                                    <span class="stat-label">Total Kandidat</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">0</span>
                                    <span class="stat-label">Sedang Proses</span>
                                </div>
                            </div>
                            <div class="success-note">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    Anda dapat mengubah semua informasi posisi dengan aman karena belum ada kandidat yang mendaftar.
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Position Status Card -->
                    <div class="info-card {{ $position->detailed_status === 'aktif' ? 'no-candidates' : 'has-candidates' }}">
                        <div class="info-card-header">
                            <div class="info-card-icon">
                                <i class="fas {{ $position->detailed_status === 'aktif' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                            </div>
                            <div>
                                <div class="info-card-title">Status Posisi</div>
                                <div class="info-card-subtitle">{{ $position->detailed_status === 'aktif' ? 'Menerima Aplikasi' : 'Tidak Menerima Aplikasi' }}</div>
                            </div>
                        </div>
                        <div style="text-align: center; padding: 20px 0;">
                            <span style="
                                display: inline-block;
                                padding: 10px 24px;
                                border-radius: 25px;
                                font-weight: 600;
                                font-size: 1rem;
                                background: {{ $position->detailed_status === 'aktif' ? '#d1fae5' : '#fef3c7' }};
                                color: {{ $position->detailed_status === 'aktif' ? '#065f46' : '#92400e' }};
                            ">
                                {{ $position->detailed_status === 'aktif' ? '✅ AKTIF' : '⏸️ TUTUP' }}
                            </span>
                        </div>
                        
                        <!-- Status Details -->
                        <div style="background: rgba(255,255,255,0.7); border-radius: 8px; padding: 12px; margin: 10px 0; font-size: 0.85rem;">
                            @if($position->posted_date)
                                <div style="color: #6b7280; margin-bottom: 4px;">
                                    <i class="fas fa-calendar-plus"></i> Dibuat: {{ $position->posted_date->format('d M Y') }}
                                </div>
                            @endif
                            
                            @if($position->closing_date)
                                @php
                                    $isExpired = $position->closing_date->isPast();
                                    $daysUntilClose = $position->closing_date->diffInDays(now(), false);
                                @endphp
                                <div style="color: {{ $isExpired ? '#dc2626' : '#f59e0b' }}; margin-bottom: 4px;">
                                    <i class="fas fa-calendar-times"></i> 
                                    Tutup: {{ $position->closing_date->format('d M Y') }}
                                    @if($isExpired)
                                        <span style="font-size: 0.75rem; background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 10px; margin-left: 5px;">
                                            Sudah Lewat {{ abs($daysUntilClose) }} hari
                                        </span>
                                    @else
                                        <span style="font-size: 0.75rem; background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 10px; margin-left: 5px;">
                                            {{ $daysUntilClose }} hari lagi
                                        </span>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Status Logic Explanation -->
                            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.8rem;">
                                @if($position->detailed_status === 'aktif')
                                    <i class="fas fa-info-circle"></i> 
                                    Posisi dapat menerima aplikasi baru
                                @else
                                    <i class="fas fa-info-circle"></i> 
                                    @if(!$position->is_active)
                                        Posisi dinonaktifkan secara manual
                                    @elseif($position->closing_date && $position->closing_date->isPast())
                                        Posisi ditutup karena melewati tanggal penutupan
                                    @else
                                        Posisi tidak menerima aplikasi baru
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Edit Informasi Posisi</h2>
                        <p class="form-subtitle">Perbarui informasi posisi sesuai kebutuhan</p>
                    </div>

                    <form method="POST" action="{{ route('positions.update', $position) }}" id="positionForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-body">
                            <!-- Basic Information -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label required">Nama Posisi</label>
                                    <input type="text" name="position_name" class="form-input" 
                                           value="{{ old('position_name', $position->position_name) }}" 
                                           placeholder="Contoh: Senior Software Engineer" required>
                                    @error('position_name')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label required">Departemen</label>
                                    <input type="text" name="department" class="form-input" 
                                           value="{{ old('department', $position->department) }}" 
                                           placeholder="Contoh: Technology" 
                                           list="departments" required>
                                    <datalist id="departments">
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}">
                                        @endforeach
                                    </datalist>
                                    @error('department')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" name="location" class="form-input" 
                                           value="{{ old('location', $position->location) }}" 
                                           placeholder="Contoh: Jakarta Pusat"
                                           list="locations">
                                    <datalist id="locations">
                                        @foreach($locations as $location)
                                            <option value="{{ $location }}">
                                        @endforeach
                                    </datalist>
                                    @error('location')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label required">Tipe Pekerjaan</label>
                                    <select name="employment_type" class="form-select" required>
                                        <option value="">Pilih Tipe Pekerjaan</option>
                                        @foreach($employmentTypes as $key => $label)
                                            <option value="{{ $key }}" 
                                                    {{ old('employment_type', $position->employment_type) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employment_type')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Salary Range -->
                            <div class="form-group">
                                <label class="form-label">Rentang Gaji</label>
                                <div class="form-row">
                                    <div>
                                        <input type="number" name="salary_range_min" class="form-input" 
                                               value="{{ old('salary_range_min', $position->salary_range_min) }}" 
                                               placeholder="Gaji minimum" min="0">
                                        <div class="form-help">Gaji minimum (Rp)</div>
                                        @error('salary_range_min')
                                            <div class="error-message">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div>
                                        <input type="number" name="salary_range_max" class="form-input" 
                                               value="{{ old('salary_range_max', $position->salary_range_max) }}" 
                                               placeholder="Gaji maksimum" min="0">
                                        <div class="form-help">Gaji maksimum (Rp)</div>
                                        @error('salary_range_max')
                                            <div class="error-message">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-help">Kosongkan jika gaji dapat dinegosiasikan</div>
                            </div>

                            <!-- Description -->
                            <div class="form-group full-width">
                                <label class="form-label">Deskripsi Posisi</label>
                                <textarea name="description" class="form-textarea large" 
                                          placeholder="Deskripsikan tugas, tanggung jawab, dan lingkup pekerjaan...">{{ old('description', $position->description) }}</textarea>
                                <div class="form-help">Jelaskan secara detail tentang posisi ini</div>
                                @error('description')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Requirements -->
                            <div class="form-group full-width">
                                <label class="form-label">Persyaratan</label>
                                <textarea name="requirements" class="form-textarea large" 
                                          placeholder="Sebutkan persyaratan pendidikan, pengalaman, keahlian yang dibutuhkan...">{{ old('requirements', $position->requirements) }}</textarea>
                                <div class="form-help">Jelaskan kriteria kandidat yang diharapkan</div>
                                @error('requirements')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Dates -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Tanggal Posting</label>
                                    <input type="date" name="posted_date" class="form-input" 
                                           value="{{ old('posted_date', $position->posted_date?->format('Y-m-d')) }}">
                                    <div class="form-help">Tanggal posisi mulai dibuka</div>
                                    @error('posted_date')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Tanggal Penutupan</label>
                                    <input type="date" name="closing_date" class="form-input" 
                                           value="{{ old('closing_date', $position->closing_date?->format('Y-m-d')) }}">
                                    <div class="form-help">Batas akhir aplikasi (opsional)</div>
                                    @error('closing_date')
                                        <div class="error-message">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="checkbox-input" 
                                           id="is_active" {{ old('is_active', $position->is_active) ? 'checked' : '' }}>
                                    <label for="is_active" class="checkbox-label">
                                        Buka posisi ini (dapat menerima aplikasi)
                                    </label>
                                </div>
                                <div class="form-help">
                                    @if($activeCandidates > 0)
                                        @if($position->is_active)
                                            <strong style="color: #059669;">✅ Info:</strong> Posisi sedang aktif dengan <strong>{{ $activeCandidates }} kandidat dalam proses</strong>.
                                            <br><small style="color: #dc2626;">⚠️ Menutup posisi akan menghentikan penerimaan aplikasi baru, tapi tidak mempengaruhi kandidat yang sudah mendaftar.</small>
                                        @else
                                            <strong style="color: #f59e0b;">⚠️ Peringatan:</strong> Posisi ini memiliki <strong>{{ $activeCandidates }} kandidat yang masih dalam proses</strong>.
                                            <br><small style="color: #059669;">✅ Membuka kembali akan memungkinkan aplikasi baru masuk.</small>
                                        @endif
                                    @else
                                        @if($position->is_active)
                                            <span style="color: #059669;">✅ Posisi sedang terbuka untuk menerima aplikasi baru.</span>
                                        @else
                                            <span style="color: #6b7280;">📝 Centang untuk membuka posisi dan menerima aplikasi baru.</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="form-actions-left">
                                <i class="fas fa-info-circle"></i>
                                Terakhir diubah: {{ $position->updated_at->format('d M Y H:i') }}
                            </div>
                            <div class="form-actions-right">
                                <a href="{{ route('positions.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-warning" id="submitBtn">
                                    <i class="fas fa-save"></i>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
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

        // Form validation and submission
        const form = document.getElementById('positionForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', function(e) {
            // Prevent double submission
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Basic validation
            let isValid = true;
            const requiredFields = ['position_name', 'department', 'employment_type'];
            
            requiredFields.forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });

            // Salary range validation
            const minSalary = document.querySelector('[name="salary_range_min"]');
            const maxSalary = document.querySelector('[name="salary_range_max"]');
            
            if (minSalary.value && maxSalary.value) {
                if (parseFloat(minSalary.value) > parseFloat(maxSalary.value)) {
                    maxSalary.classList.add('error');
                    isValid = false;
                    
                    // Show error message if not exists
                    if (!maxSalary.parentNode.querySelector('.error-message')) {
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gaji maksimum harus lebih besar dari minimum';
                        maxSalary.parentNode.appendChild(errorMsg);
                    }
                } else {
                    maxSalary.classList.remove('error');
                    const errorMsg = maxSalary.parentNode.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            }

            // Date validation
            const postedDate = document.querySelector('[name="posted_date"]');
            const closingDate = document.querySelector('[name="closing_date"]');
            
            if (postedDate.value && closingDate.value) {
                if (new Date(postedDate.value) > new Date(closingDate.value)) {
                    closingDate.classList.add('error');
                    isValid = false;
                    
                    if (!closingDate.parentNode.querySelector('.error-message')) {
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Tanggal penutupan harus setelah tanggal posting';
                        closingDate.parentNode.appendChild(errorMsg);
                    }
                } else {
                    closingDate.classList.remove('error');
                    const errorMsg = closingDate.parentNode.querySelector('.error-message');
                    if (errorMsg && errorMsg.textContent.includes('Tanggal penutupan')) {
                        errorMsg.remove();
                    }
                }
            }

            if (!isValid) {
                e.preventDefault();
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                
                // Scroll to first error
                const firstError = document.querySelector('.form-input.error, .form-select.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });

        // Real-time validation
        document.querySelectorAll('.form-input, .form-select').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });

            field.addEventListener('input', function() {
                if (this.classList.contains('error') && this.value.trim()) {
                    this.classList.remove('error');
                }
            });
        });

        // Salary range real-time validation
        const minSalaryInput = document.querySelector('[name="salary_range_min"]');
        const maxSalaryInput = document.querySelector('[name="salary_range_max"]');

        function validateSalaryRange() {
            if (minSalaryInput.value && maxSalaryInput.value) {
                if (parseFloat(minSalaryInput.value) > parseFloat(maxSalaryInput.value)) {
                    maxSalaryInput.classList.add('error');
                } else {
                    maxSalaryInput.classList.remove('error');
                    const errorMsg = maxSalaryInput.parentNode.querySelector('.error-message');
                    if (errorMsg && errorMsg.textContent.includes('maksimum harus')) {
                        errorMsg.remove();
                    }
                }
            }
        }

        minSalaryInput.addEventListener('input', validateSalaryRange);
        maxSalaryInput.addEventListener('input', validateSalaryRange);

        // Date validation
        const postedDateInput = document.querySelector('[name="posted_date"]');
        const closingDateInput = document.querySelector('[name="closing_date"]');

        function validateDates() {
            if (postedDateInput.value && closingDateInput.value) {
                if (new Date(postedDateInput.value) > new Date(closingDateInput.value)) {
                    closingDateInput.classList.add('error');
                } else {
                    closingDateInput.classList.remove('error');
                    const errorMsg = closingDateInput.parentNode.querySelector('.error-message');
                    if (errorMsg && errorMsg.textContent.includes('Tanggal penutupan')) {
                        errorMsg.remove();
                    }
                }
            }
        }

        postedDateInput.addEventListener('change', validateDates);
        closingDateInput.addEventListener('change', validateDates);

        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });

        // Format number inputs (salary)
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value) {
                    // Add thousand separators for display
                    const value = parseFloat(this.value);
                    if (!isNaN(value)) {
                        // Update help text to show formatted value
                        const helpText = this.parentNode.querySelector('.form-help');
                        if (helpText && this.name.includes('salary')) {
                            const formatted = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(value);
                            helpText.textContent = `${helpText.textContent.split('(')[0]}(${formatted})`;
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>