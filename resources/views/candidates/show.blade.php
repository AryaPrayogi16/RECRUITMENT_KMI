<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Kandidat - HR System</title>
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

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
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

        .breadcrumb {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .breadcrumb a {
            color: #4f46e5;
            text-decoration: none;
        }

        .breadcrumb span {
            margin: 0 8px;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a202c;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
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

        .btn-info {
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            color: white;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.4);
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 0.85rem;
        }

        .content {
            padding: 30px;
        }

        /* Candidate Header */
        .candidate-header {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .candidate-banner {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 30px;
        }

        .candidate-info-header {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .candidate-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .candidate-details-header {
            flex: 1;
        }

        .candidate-name {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
        }

        .candidate-position {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 15px;
        }

        .candidate-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
        }

        .meta-item i {
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-reviewing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-interview {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-accepted {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-screening {
            background: #fef3c7;
            color: #92400e;
        }

        .status-offered {
            background: #d1fae5;
            color: #065f46;
        }

        .status-withdrawn {
            background: #f3f4f6;
            color: #374151;
        }

        /* Navigation Menu */
        .section-nav {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            position: sticky;
            top: 100px;
            z-index: 50;
        }

        .section-nav-list {
            display: flex;
            padding: 0;
            margin: 0;
            list-style: none;
            overflow-x: auto;
        }

        .section-nav-item {
            flex: 1;
            min-width: 150px;
        }

        .section-nav-link {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 20px;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            font-weight: 500;
            gap: 8px;
            font-size: 0.95rem;
        }

        .section-nav-link:hover {
            color: #4f46e5;
            background: rgba(79, 70, 229, 0.05);
        }

        .section-nav-link.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
            background: rgba(79, 70, 229, 0.1);
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            scroll-margin-top: 200px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .section-title i {
            color: #4f46e5;
            font-size: 1.3rem;
        }

        /* Info Cards and Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
        }

        .info-card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card-title i {
            color: #4f46e5;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #4a5568;
            min-width: 120px;
        }

        .info-value {
            color: #1a202c;
            text-align: right;
            flex: 1;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .data-table th {
            background: #f7fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #1a202c;
        }

        .data-table tbody tr:hover {
            background: #f7fafc;
        }

        /* Documents Section */
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .document-category {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
        }

        .document-category-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .document-category-title i {
            color: #4f46e5;
        }

        .document-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .document-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .document-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .document-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .document-details {
            display: flex;
            flex-direction: column;
        }

        .document-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 2px;
        }

        .document-meta {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .document-actions {
            display: flex;
            gap: 8px;
        }

        .no-documents {
            text-align: center;
            padding: 30px 20px;
            color: #9ca3af;
            background: white;
            border-radius: 8px;
            border: 2px dashed #e5e7eb;
        }

        .no-documents i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .no-documents p {
            font-size: 0.9rem;
            margin: 0;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a202c;
        }

        .close {
            color: #6b7280;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #1a202c;
        }

        .form-group {
            margin-bottom: 20px;
            padding: 0 30px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }

        .form-select, .form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #9ca3af;
            display: block;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
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

            .section-nav {
                top: 90px;
            }

            .section-nav-list {
                flex-wrap: nowrap;
                overflow-x: scroll;
            }

            .section-nav-item {
                flex: none;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .candidate-info-header {
                flex-direction: column;
                text-align: center;
            }

            .candidate-meta {
                justify-content: center;
                flex-direction: column;
                gap: 10px;
            }

            .header-right {
                flex-direction: column;
                gap: 8px;
            }

            .documents-grid {
                grid-template-columns: 1fr;
            }

            .document-item {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .document-actions {
                justify-content: center;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .data-table {
                font-size: 0.85rem;
            }

            .data-table th,
            .data-table td {
                padding: 10px;
            }

            .content-section {
                scroll-margin-top: 160px;
            }
        }

        @media (max-width: 480px) {
            .content {
                padding: 15px;
            }

            .header {
                padding: 15px 20px;
            }

            .candidate-banner {
                padding: 20px;
            }

            .candidate-name {
                font-size: 1.5rem;
            }

            .candidate-position {
                font-size: 1rem;
            }

            .content-section {
                padding: 20px;
                scroll-margin-top: 140px;
            }

            .section-nav {
                top: 80px;
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

        <!-- Main Content -->
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
                        Riwayat
                    </button>
                    <a href="{{ route('candidates.edit', $candidate->id) }}" class="btn btn-secondary">
                        <i class="fas fa-edit"></i>
                        Edit
                    </a>
                    <button class="btn btn-primary" onclick="showStatusModal()">
                        <i class="fas fa-sync"></i>
                        Update Status
                    </button>
                </div>
            </header>

            <div class="content">
                <!-- Candidate Header Card -->
                <div class="candidate-header">
                    <div class="candidate-banner">
                        <div class="candidate-info-header">
                            <div class="candidate-photo">
                                @php
                                    $photoDocument = $candidate->documentUploads->where('document_type', 'photo')->first();
                                @endphp
                                
                                @if($photoDocument)
                                    <img src="{{ Storage::url($photoDocument->file_path) }}" 
                                        alt="Foto {{ $candidate->personalData->full_name ?? 'Kandidat' }}" 
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                            <div class="candidate-details-header">
                                <h2 class="candidate-name">{{ $candidate->personalData->full_name ?? 'N/A' }}</h2>
                                <p class="candidate-position">{{ $candidate->position_applied }}</p>
                                <div class="candidate-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-id-badge"></i>
                                        <span>{{ $candidate->candidate_code }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-envelope"></i>
                                        <span>{{ $candidate->personalData->email ?? 'N/A' }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $candidate->personalData->phone_number ?? 'N/A' }}</span>
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

                <!-- Section Navigation -->
                <nav class="section-nav">
                    <ul class="section-nav-list">
                        <li class="section-nav-item">
                            <a href="#personal-section" class="section-nav-link active">
                                <i class="fas fa-user"></i>
                                Data Pribadi
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#education-section" class="section-nav-link">
                                <i class="fas fa-graduation-cap"></i>
                                Pendidikan
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#experience-section" class="section-nav-link">
                                <i class="fas fa-briefcase"></i>
                                Pengalaman
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#skills-section" class="section-nav-link">
                                <i class="fas fa-cogs"></i>
                                Keterampilan
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#documents-section" class="section-nav-link">
                                <i class="fas fa-file-alt"></i>
                                Dokumen
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Personal Data Section -->
                <section id="personal-section" class="content-section">
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
                                <span class="info-value">{{ $candidate->personalData->full_name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tempat, Tanggal Lahir</span>
                                <span class="info-value">
                                    {{ $candidate->personalData->birth_place ?? 'N/A' }}, 
                                    {{ $candidate->personalData->birth_date ? \Carbon\Carbon::parse($candidate->personalData->birth_date)->format('d M Y') : 'N/A' }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Jenis Kelamin</span>
                                <span class="info-value">{{ $candidate->personalData->gender ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Agama</span>
                                <span class="info-value">{{ $candidate->personalData->religion ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status Pernikahan</span>
                                <span class="info-value">{{ $candidate->personalData->marital_status ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="info-card">
                            <h3 class="info-card-title">
                                <i class="fas fa-phone-alt"></i>
                                Kontak
                            </h3>
                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $candidate->personalData->email ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">No. Telepon</span>
                                <span class="info-value">{{ $candidate->personalData->phone_number ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">No. Alternatif</span>
                                <span class="info-value">{{ $candidate->personalData->phone_alternative ?? 'N/A' }}</span>
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
                                        <td>{{ $member->relationship }}</td>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->age }} tahun</td>
                                        <td>{{ $member->education }}</td>
                                        <td>{{ $member->occupation }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>

                <!-- Education Section -->
                <section id="education-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-graduation-cap"></i>
                        Pendidikan
                    </h2>

                    @if($candidate->formalEducation->count() > 0)
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
                                @foreach($candidate->formalEducation as $edu)
                                    <tr>
                                        <td>{{ $edu->education_level }}</td>
                                        <td>{{ $edu->institution_name }}</td>
                                        <td>{{ $edu->major }}</td>
                                        <td>{{ $edu->start_year }} - {{ $edu->end_year }}</td>
                                        <td>{{ $edu->gpa ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($candidate->nonFormalEducation->count() > 0)
                        <h3 class="info-card-title" style="margin-top: 30px;">
                            <i class="fas fa-certificates"></i>
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
                                @foreach($candidate->nonFormalEducation as $course)
                                    <tr>
                                        <td>{{ $course->course_name }}</td>
                                        <td>{{ $course->organizer }}</td>
                                        <td>{{ $course->date ? \Carbon\Carbon::parse($course->date)->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ $course->description ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($candidate->formalEducation->count() == 0 && $candidate->nonFormalEducation->count() == 0)
                        <div class="empty-state">
                            <i class="fas fa-graduation-cap"></i>
                            <p>Tidak ada data pendidikan</p>
                        </div>
                    @endif
                </section>

                <!-- Experience Section -->
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
                                    {{ $exp->company_name }}
                                </h3>
                                <div class="info-row">
                                    <span class="info-label">Posisi</span>
                                    <span class="info-value">{{ $exp->position }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Periode</span>
                                    <span class="info-value">{{ $exp->start_year }} - {{ $exp->end_year ?? 'Sekarang' }}</span>
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
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <i class="fas fa-briefcase"></i>
                            <p>Tidak ada pengalaman kerja</p>
                        </div>
                    @endif
                </section>

                <!-- Skills Section -->
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
                                        <span class="info-label">{{ $lang->language }}</span>
                                        <span class="info-value">
                                            Bicara: {{ $lang->speaking_level }}, 
                                            Tulis: {{ $lang->writing_level }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($candidate->computerSkills)
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-laptop"></i>
                                    Kemampuan Komputer
                                </h3>
                                @if($candidate->computerSkills->hardware_skills)
                                    <div class="info-row">
                                        <span class="info-label">Hardware</span>
                                        <span class="info-value">{{ $candidate->computerSkills->hardware_skills }}</span>
                                    </div>
                                @endif
                                @if($candidate->computerSkills->software_skills)
                                    <div class="info-row">
                                        <span class="info-label">Software</span>
                                        <span class="info-value">{{ $candidate->computerSkills->software_skills }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if($candidate->otherSkills)
                        <div class="info-card" style="margin-top: 20px;">
                            <h3 class="info-card-title">
                                <i class="fas fa-star"></i>
                                Kemampuan Lainnya
                            </h3>
                            <p>{{ $candidate->otherSkills->other_skills }}</p>
                        </div>
                    @endif

                    @if($candidate->languageSkills->count() == 0 && !$candidate->computerSkills && !$candidate->otherSkills)
                        <div class="empty-state">
                            <i class="fas fa-cogs"></i>
                            <p>Tidak ada data keterampilan</p>
                        </div>
                    @endif
                </section>

                <!-- Documents Section -->
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
                                    'certificates' => ['icon' => 'fa-certificates', 'label' => 'Sertifikat'],
                                    'other' => ['icon' => 'fa-file', 'label' => 'Dokumen Lainnya']
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
                                                            {{ number_format($doc->file_size / 1024, 2) }} KB • 
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

                            {{-- Show any documents that don't match the predefined types --}}
                            @php
                                $unknownDocuments = $candidate->documentUploads->whereNotIn('document_type', array_keys($documentTypes));
                            @endphp
                            
                            @if($unknownDocuments->count() > 0)
                                <div class="document-category">
                                    <h3 class="document-category-title">
                                        <i class="fas fa-question-circle"></i>
                                        Dokumen Tidak Terkategorisasi
                                        <span style="font-size: 0.8rem; color: #6b7280; font-weight: normal;">
                                            ({{ $unknownDocuments->count() }} file{{ $unknownDocuments->count() > 1 ? 's' : '' }})
                                        </span>
                                    </h3>
                                    
                                    @foreach($unknownDocuments as $doc)
                                        <div class="document-item">
                                            <div class="document-info">
                                                <div class="document-icon">
                                                    <i class="fas fa-file"></i>
                                                </div>
                                                <div class="document-details">
                                                    <div class="document-name">{{ $doc->document_name ?: $doc->original_filename ?: 'Dokumen' }}</div>
                                                    <div class="document-meta">
                                                        Type: {{ $doc->document_type ?: 'Unknown' }} • 
                                                        {{ number_format($doc->file_size / 1024, 2) }} KB • 
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
                                </div>
                            @endif
                        </div>

                        {{-- Debug info - hapus ini setelah testing --}}

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

    <!-- History Modal -->
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

    <!-- Status Update Modal -->
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
                        <option value="withdrawn">Withdrawn</option>
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

        // Section navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit to ensure all elements are rendered
            setTimeout(function() {
                const navLinks = document.querySelectorAll('.section-nav-link');
                const sections = document.querySelectorAll('.content-section');

                console.log('Navigation initialized');
                console.log('Nav links found:', navLinks.length);
                console.log('Sections found:', sections.length);
                
                // Debug: log all section IDs
                sections.forEach(section => {
                    console.log('Section ID:', section.id);
                });

                // Handle navigation clicks
                navLinks.forEach((link, index) => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        console.log('Clicked link:', this.getAttribute('href'));
                        
                        // Remove active class from all links
                        navLinks.forEach(l => l.classList.remove('active'));
                        
                        // Add active class to clicked link
                        this.classList.add('active');
                        
                        // Scroll to target section
                        const targetId = this.getAttribute('href').substring(1);
                        console.log('Target ID:', targetId);
                        
                        const targetSection = document.getElementById(targetId);
                        console.log('Target section found:', !!targetSection);
                        
                        if (targetSection) {
                            // Calculate offset for sticky navigation
                            const headerHeight = document.querySelector('.header').offsetHeight;
                            const navHeight = document.querySelector('.section-nav').offsetHeight;
                            const offset = headerHeight + navHeight + 20; // Add some padding
                            
                            console.log('Header height:', headerHeight);
                            console.log('Nav height:', navHeight);
                            console.log('Total offset:', offset);
                            
                            const elementPosition = targetSection.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - offset;

                            console.log('Element position:', elementPosition);
                            console.log('Scroll to position:', offsetPosition);

                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });
                        } else {
                            console.error('Target section not found:', targetId);
                        }
                    });
                });

                // Handle scroll events for automatic navigation highlighting
                let isScrolling = false;
                
                window.addEventListener('scroll', function() {
                    if (!isScrolling) {
                        window.requestAnimationFrame(function() {
                            let current = '';
                            const scrollPos = window.pageYOffset + 250;
                            
                            sections.forEach(section => {
                                const sectionTop = section.offsetTop;
                                const sectionHeight = section.offsetHeight;
                                const sectionId = section.getAttribute('id');
                                
                                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                                    current = sectionId;
                                }
                            });
                            
                            if (current) {
                                navLinks.forEach(link => {
                                    link.classList.remove('active');
                                    if (link.getAttribute('href') === '#' + current) {
                                        link.classList.add('active');
                                    }
                                });
                            }
                            
                            isScrolling = false;
                        });
                    }
                    isScrolling = true;
                });
            }, 100); // Wait 100ms to ensure DOM is fully rendered
        });

        // History Modal
        function showHistoryModal() {
            document.getElementById('historyModal').style.display = 'block';
        }

        function closeHistoryModal() {
            document.getElementById('historyModal').style.display = 'none';
        }

        // Status Modal
        function showStatusModal() {
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
            document.getElementById('statusForm').reset();
        }

        // Status form submission
        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newStatus = document.getElementById('newStatus').value;
            const notes = document.getElementById('statusNotes').value;
            
            if (!newStatus) {
                alert('Pilih status terlebih dahulu');
                return;
            }

            // Update status via AJAX
            fetch(`/candidates/{{ $candidate->id }}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    status: newStatus,
                    notes: notes 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status berhasil diupdate');
                    window.location.reload();
                } else {
                    alert('Gagal mengupdate status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const statusModal = document.getElementById('statusModal');
            const historyModal = document.getElementById('historyModal');
            
            if (event.target == statusModal) {
                closeStatusModal();
            }
            if (event.target == historyModal) {
                closeHistoryModal();
            }
        }
    </script>
</body>
</html>