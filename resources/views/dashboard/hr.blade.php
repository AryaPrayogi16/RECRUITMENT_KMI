<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Sistem Recruitment</title>
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

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
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
            color: #fff;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 1.5rem;
            backdrop-filter: blur(10px);
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
            color: rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.2);
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
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #fff;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-left-color: #fff;
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

        .quick-actions {
            display: flex;
            gap: 10px;
        }

        .quick-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .quick-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
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

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card.new-applications { border-left-color: #3b82f6; }
        .stat-card.pending-review { border-left-color: #f59e0b; }
        .stat-card.interviews-today { border-left-color: #10b981; }
        .stat-card.offers-pending { border-left-color: #8b5cf6; }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .stat-title {
            font-size: 0.85rem;
            color: #718096;
            font-weight: 500;
        }

        .stat-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
        }

        .stat-icon.blue { background: #3b82f6; }
        .stat-icon.yellow { background: #f59e0b; }
        .stat-icon.green { background: #10b981; }
        .stat-icon.purple { background: #8b5cf6; }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.8rem;
            color: #718096;
        }

        /* Task Sections */
        .task-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .task-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .task-header {
            padding: 20px 25px 15px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a202c;
        }

        .view-all-btn {
            color: #10b981;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .task-content {
            padding: 20px 25px;
        }

        .task-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f7fafc;
        }

        .task-item:last-child {
            border-bottom: none;
        }

        .task-priority {
            width: 8px;
            height: 40px;
            border-radius: 4px;
            margin-right: 15px;
        }

        .task-priority.high { background: #ef4444; }
        .task-priority.medium { background: #f59e0b; }
        .task-priority.low { background: #10b981; }

        .task-info {
            flex: 1;
        }

        .task-name {
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 2px;
        }

        .task-details {
            font-size: 0.85rem;
            color: #718096;
        }

        .task-action {
            margin-left: 10px;
        }

        .task-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .task-btn:hover {
            background: #059669;
            transform: scale(1.05);
        }

        .task-btn.secondary {
            background: #6b7280;
        }

        .task-btn.secondary:hover {
            background: #4b5563;
        }

        /* Schedule Card */
        .schedule-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            grid-column: 1 / -1;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .schedule-item:last-child {
            border-bottom: none;
        }

        .schedule-time {
            width: 80px;
            text-align: center;
            font-weight: 600;
            color: #4a5568;
            margin-right: 20px;
        }

        .schedule-content {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schedule-info h4 {
            color: #2d3748;
            margin-bottom: 4px;
        }

        .schedule-info p {
            font-size: 0.9rem;
            color: #718096;
        }

        .schedule-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .schedule-status.upcoming {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .schedule-status.in-progress {
            background: #fef3c7;
            color: #d97706;
        }

        .schedule-status.completed {
            background: #d1fae5;
            color: #065f46;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .task-grid {
                grid-template-columns: 1fr;
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

            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .header-right .quick-actions {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .quick-stats {
                grid-template-columns: 1fr;
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
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <div class="user-name">{{ Auth::user()->full_name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>

            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="#" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('candidates.index') }}" class="nav-link {{ request()->routeIs('candidates.*') ? 'active' : '' }}">
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
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Interview</span>
                    </a>
                </div>
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
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-history"></i>
                        <span>Audit Logs</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <header class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Dashboard HR</h1>
                </div>
                <div class="header-right">
                    <div class="quick-actions">
                        <button class="quick-btn">
                            <i class="fas fa-plus"></i>
                            Tambah Kandidat
                        </button>
                        <button class="quick-btn">
                            <i class="fas fa-calendar-plus"></i>
                            Jadwal Interview
                        </button>
                    </div>
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">8</span>
                    </button>
                    <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <div class="content">
                <!-- Quick Stats -->
                <div class="quick-stats">
                    <div class="stat-card new-applications">
                        <div class="stat-header">
                            <div class="stat-title">Aplikasi Baru</div>
                            <div class="stat-icon blue">
                                <i class="fas fa-file-plus"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $stats['new_applications'] ?? 23 }}</div>
                        <div class="stat-change">Menunggu review</div>
                    </div>

                    <div class="stat-card pending-review">
                        <div class="stat-header">
                            <div class="stat-title">Pending Review</div>
                            <div class="stat-icon yellow">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $stats['pending_review'] ?? 45 }}</div>
                        <div class="stat-change">Perlu tindak lanjut</div>
                    </div>

                    <div class="stat-card interviews-today">
                        <div class="stat-header">
                            <div class="stat-title">Interview Hari Ini</div>
                            <div class="stat-icon green">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $stats['interviews_today'] ?? 8 }}</div>
                        <div class="stat-change">4 sudah selesai</div>
                    </div>

                    <div class="stat-card offers-pending">
                        <div class="stat-header">
                            <div class="stat-title">Offers Pending</div>
                            <div class="stat-icon purple">
                                <i class="fas fa-handshake"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $stats['offers_pending'] ?? 3 }}</div>
                        <div class="stat-change">Menunggu respon</div>
                    </div>
                </div>

                <!-- Task Sections -->
                <div class="task-grid">
                    <!-- Priority Tasks -->
                    <div class="task-card">
                        <div class="task-header">
                            <h3 class="task-title">Task Prioritas</h3>
                            <a href="#" class="view-all-btn">Lihat Semua</a>
                        </div>
                        <div class="task-content">
                            @if(isset($priority_tasks['candidates_to_review']) && $priority_tasks['candidates_to_review']->count() > 0)
                                @foreach($priority_tasks['candidates_to_review'] as $candidate)
                                <div class="task-item">
                                    <div class="task-priority high"></div>
                                    <div class="task-info">
                                        <div class="task-name">Review CV {{ $candidate->personalData->full_name ?? 'Unknown' }}</div>
                                        <div class="task-details">Status: {{ $candidate->application_status }}</div>
                                    </div>
                                    <div class="task-action">
                                        <button class="task-btn">Review</button>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="task-item">
                                    <div class="task-priority high"></div>
                                    <div class="task-info">
                                        <div class="task-name">Review CV Sales Manager</div>
                                        <div class="task-details">5 kandidat menunggu review</div>
                                    </div>
                                    <div class="task-action">
                                        <button class="task-btn">Review</button>
                                    </div>
                                </div>
                            @endif
                            <div class="task-item">
                                <div class="task-priority medium"></div>
                                <div class="task-info">
                                    <div class="task-name">Jadwal Interview Developer</div>
                                    <div class="task-details">3 kandidat perlu dijadwalkan</div>
                                </div>
                                <div class="task-action">
                                    <button class="task-btn">Jadwalkan</button>
                                </div>
                            </div>
                            <div class="task-item">
                                <div class="task-priority high"></div>
                                <div class="task-info">
                                    <div class="task-name">Kirim Offer Letter</div>
                                    <div class="task-details">2 kandidat diterima</div>
                                </div>
                                <div class="task-action">
                                    <button class="task-btn">Kirim</button>
                                </div>
                            </div>
                            <div class="task-item">
                                <div class="task-priority low"></div>
                                <div class="task-info">
                                    <div class="task-name">Update Database</div>
                                    <div class="task-details">Sinkronisasi data mingguan</div>
                                </div>
                                <div class="task-action">
                                    <button class="task-btn secondary">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Applications -->
                    <div class="task-card">
                        <div class="task-header">
                            <h3 class="task-title">Aplikasi Terbaru</h3>
                            <a href="#" class="view-all-btn">Lihat Semua</a>
                        </div>
                        <div class="task-content">
                            <div class="task-item">
                                <div class="task-priority medium"></div>
                                <div class="task-info">
                                    <div class="task-name">Ahmad Rizki</div>
                                    <div class="task-details">Marketing Manager • 2 jam lalu</div>
                                </div>
                                <div class="task-action">
                                    <button class="task-btn">Review</button>
                                </div>
                            </div>
                            <div class="task-item">
                                <div class="task-priority low"></div>
                                <div class="task-info">
                                    <div class="task-name">Siti Nurhaliza</div>
                                    <div class="task-details">Frontend Developer • 4 jam lalu</div>
                                </div>
                                <div class="task-action">
                                    <button class="task-btn">Review</button>
                                </div>
                            </div>
                            <div class="task-item">
                                <div class="task-priority medium"></div>
                                <div class="task-info">
                                    <div class="task-name">Budi Santoso</div>
                                    <div class="task-details">Accountant • 6 jam lalu</div>
                                </div>
                                <div class="task-action">
                                    <button class="task-btn">Review</button>
                                </div>
                            </div>
                            <div class="task-item">
                                <div class="task-priority high"></div>
                                <div class="task-info">
                                    <div class="task-name">Maya Putri</div>
                                    <div class="task-details">Senior Developer • 1 hari lalu</div>
                                </div>
                                <div class="task-action">
                                    <button class="task-btn">Review</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="schedule-card">
                    <div class="task-header">
                        <h3 class="task-title">Jadwal Hari Ini</h3>
                        <a href="#" class="view-all-btn">Lihat Kalender</a>
                    </div>
                    <div class="task-content">
                        @if(isset($todays_schedule) && $todays_schedule->count() > 0)
                            @foreach($todays_schedule as $interview)
                            <div class="schedule-item">
                                <div class="schedule-time">{{ \Carbon\Carbon::parse($interview->interview_time)->format('H:i') }}</div>
                                <div class="schedule-content">
                                    <div class="schedule-info">
                                        <h4>Interview - {{ $interview->candidate->personalData->full_name ?? 'Unknown' }}</h4>
                                        <p>Interviewer: {{ $interview->interviewer->full_name ?? 'TBD' }}</p>
                                    </div>
                                    <div class="schedule-status upcoming">{{ ucfirst($interview->status ?? 'Mendatang') }}</div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="schedule-item">
                                <div class="schedule-time">09:00</div>
                                <div class="schedule-content">
                                    <div class="schedule-info">
                                        <h4>Interview - Frontend Developer</h4>
                                        <p>Kandidat: Siti Nurhaliza • Interviewer: John Doe</p>
                                    </div>
                                    <div class="schedule-status completed">Selesai</div>
                                </div>
                            </div>
                            <div class="schedule-item">
                                <div class="schedule-time">11:00</div>
                                <div class="schedule-content">
                                    <div class="schedule-info">
                                        <h4>Phone Interview - Marketing Manager</h4>
                                        <p>Kandidat: Ahmad Rizki • Interviewer: Sarah Wilson</p>
                                    </div>
                                    <div class="schedule-status in-progress">Berlangsung</div>
                                </div>
                            </div>
                            <div class="schedule-item">
                                <div class="schedule-time">14:00</div>
                                <div class="schedule-content">
                                    <div class="schedule-info">
                                        <h4>Team Meeting - Recruitment Review</h4>
                                        <p>Review progress dan diskusi kandidat prioritas</p>
                                    </div>
                                    <div class="schedule-status upcoming">Mendatang</div>
                                </div>
                            </div>
                            <div class="schedule-item">
                                <div class="schedule-time">16:00</div>
                                <div class="schedule-content">
                                    <div class="schedule-info">
                                        <h4>Final Interview - Senior Developer</h4>
                                        <p>Kandidat: Maya Putri • Interviewer: Tech Lead</p>
                                    </div>
                                    <div class="schedule-status upcoming">Mendatang</div>
                                </div>
                            </div>
                        @endif
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

        // Nav link active state
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.getAttribute('href') === '#') {
                    e.preventDefault();
                    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                }
            });
        });

        // Task button interactions
        document.querySelectorAll('.task-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                btn.style.background = '#059669';
                btn.innerHTML = '<i class="fas fa-check"></i> Done';
                setTimeout(() => {
                    btn.closest('.task-item').style.opacity = '0.5';
                }, 500);
            });
        });

        // Quick action buttons
        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('Quick action:', btn.textContent.trim());
            });
        });

        // Animate cards on load
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'slideInUp 0.6s ease forwards';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-card, .task-card').forEach(card => {
            observer.observe(card);
        });

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>