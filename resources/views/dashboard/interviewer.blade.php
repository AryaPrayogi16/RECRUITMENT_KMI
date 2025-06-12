<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Interviewer - Sistem Recruitment</title>
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
            background: linear-gradient(180deg, #8b5cf6 0%, #7c3aed 100%);
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

        .time-info {
            text-align: center;
            margin-right: 20px;
        }

        .current-time {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
        }

        .current-date {
            font-size: 0.9rem;
            color: #718096;
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
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card.today { border-left-color: #8b5cf6; }
        .stat-card.week { border-left-color: #3b82f6; }
        .stat-card.completed { border-left-color: #10b981; }
        .stat-card.pending { border-left-color: #f59e0b; }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 0.9rem;
            color: #718096;
            font-weight: 500;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: white;
        }

        .stat-icon.purple { background: #8b5cf6; }
        .stat-icon.blue { background: #3b82f6; }
        .stat-icon.green { background: #10b981; }
        .stat-icon.yellow { background: #f59e0b; }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .stat-description {
            font-size: 0.85rem;
            color: #718096;
        }

        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .interview-schedule, .pending-feedback {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 25px 15px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a202c;
        }

        .view-all-btn {
            color: #8b5cf6;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            margin-left: auto;
        }

        .card-content {
            padding: 20px 25px;
        }

        /* Interview Schedule Items */
        .interview-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f7fafc;
        }

        .interview-item:last-child {
            border-bottom: none;
        }

        .interview-time {
            width: 80px;
            text-align: center;
            font-weight: 600;
            color: #8b5cf6;
            margin-right: 20px;
        }

        .interview-info {
            flex: 1;
        }

        .candidate-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .position-name {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 2px;
        }

        .interview-type {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-block;
        }

        .interview-type.phone {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .interview-type.video {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .interview-type.in-person {
            background: #d1fae5;
            color: #065f46;
        }

        .interview-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn.start {
            background: #10b981;
            color: white;
        }

        .action-btn.reschedule {
            background: #f59e0b;
            color: white;
        }

        .action-btn.view {
            background: #6b7280;
            color: white;
        }

        .action-btn:hover {
            transform: scale(1.05);
        }

        /* Pending Feedback */
        .feedback-item {
            padding: 15px 0;
            border-bottom: 1px solid #f7fafc;
        }

        .feedback-item:last-child {
            border-bottom: none;
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .feedback-candidate {
            font-weight: 600;
            color: #2d3748;
        }

        .feedback-date {
            font-size: 0.8rem;
            color: #718096;
        }

        .feedback-position {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 10px;
        }

        .feedback-action {
            display: flex;
            gap: 8px;
        }

        .feedback-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .feedback-btn.submit {
            background: #8b5cf6;
            color: white;
        }

        .feedback-btn.view {
            background: #e5e7eb;
            color: #374151;
        }

        .feedback-btn:hover {
            transform: scale(1.05);
        }

        /* Performance Stats */
        .performance-stats {
            grid-column: 1 / -1;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 25px;
        }

        .performance-stat {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            background: #f8fafc;
        }

        .performance-value {
            font-size: 2rem;
            font-weight: 700;
            color: #8b5cf6;
            margin-bottom: 5px;
        }

        .performance-label {
            font-size: 0.9rem;
            color: #718096;
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #718096;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-grid {
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

            .header-right .time-info {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .quick-stats {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
                    <i class="fas fa-comments"></i>
                    <span class="logo-text">Interview Portal</span>
                </div>
            </div>

            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="user-details">
                    <div class="user-name">{{ Auth::user()->full_name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
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
                        <i class="fas fa-calendar-alt"></i>
                        <span>My Schedule</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Assigned Candidates</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Interview Forms</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-star"></i>
                        <span>Submit Feedback</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>My Performance</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-clock"></i>
                        <span>Interview History</span>
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
                    <h1 class="page-title">Dashboard Interviewer</h1>
                </div>
                <div class="header-right">
                    <div class="time-info">
                        <div class="current-time" id="currentTime">14:30</div>
                        <div class="current-date" id="currentDate">Rabu, 11 Jun 2025</div>
                    </div>
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
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
                    <div class="stat-card today">
                        <div class="stat-header">
                            <div class="stat-title">Interview Hari Ini</div>
                            <div class="stat-icon purple">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                        <div class="stat-value">4</div>
                        <div class="stat-description">2 sudah selesai, 2 mendatang</div>
                    </div>

                    <div class="stat-card week">
                        <div class="stat-header">
                            <div class="stat-title">Interview Minggu Ini</div>
                            <div class="stat-icon blue">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                        </div>
                        <div class="stat-value">12</div>
                        <div class="stat-description">Senin-Jumat jadwal penuh</div>
                    </div>

                    <div class="stat-card completed">
                        <div class="stat-header">
                            <div class="stat-title">Interview Selesai</div>
                            <div class="stat-icon green">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value">45</div>
                        <div class="stat-description">Total bulan ini</div>
                    </div>

                    <div class="stat-card pending">
                        <div class="stat-header">
                            <div class="stat-title">Pending Feedback</div>
                            <div class="stat-icon yellow">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="stat-value">2</div>
                        <div class="stat-description">Perlu submit feedback</div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="main-grid">
                    <!-- Interview Schedule -->
                    <div class="interview-schedule">
                        <div class="card-header">
                            <h3 class="card-title">Jadwal Interview Hari Ini</h3>
                            <a href="#" class="view-all-btn">Lihat Semua</a>
                        </div>
                        <div class="card-content">
                            <div class="interview-item">
                                <div class="interview-time">14:00</div>
                                <div class="interview-info">
                                    <div class="candidate-name">Ahmad Rizki Pratama</div>
                                    <div class="position-name">Marketing Manager</div>
                                    <span class="interview-type in-person">In-Person</span>
                                </div>
                                <div class="interview-actions">
                                    <button class="action-btn start">
                                        <i class="fas fa-play"></i> Start
                                    </button>
                                    <button class="action-btn view">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>

                            <div class="interview-item">
                                <div class="interview-time">16:00</div>
                                <div class="interview-info">
                                    <div class="candidate-name">Maya Sari Putri</div>
                                    <div class="position-name">Senior Frontend Developer</div>
                                    <span class="interview-type video">Video Call</span>
                                </div>
                                <div class="interview-actions">
                                    <button class="action-btn start">
                                        <i class="fas fa-video"></i> Join
                                    </button>
                                    <button class="action-btn view">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>

                            <div class="interview-item">
                                <div class="interview-time">09:00</div>
                                <div class="interview-info">
                                    <div class="candidate-name">Budi Santoso</div>
                                    <div class="position-name">Accountant</div>
                                    <span class="interview-type phone">Phone</span>
                                </div>
                                <div class="interview-actions">
                                    <button class="action-btn view" disabled style="opacity: 0.6;">
                                        <i class="fas fa-check"></i> Done
                                    </button>
                                </div>
                            </div>

                            <div class="interview-item">
                                <div class="interview-time">11:30</div>
                                <div class="interview-info">
                                    <div class="candidate-name">Siti Nurhaliza</div>
                                    <div class="position-name">UI/UX Designer</div>
                                    <span class="interview-type video">Video Call</span>
                                </div>
                                <div class="interview-actions">
                                    <button class="action-btn view" disabled style="opacity: 0.6;">
                                        <i class="fas fa-check"></i> Done
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Feedback -->
                    <div class="pending-feedback">
                        <div class="card-header">
                            <h3 class="card-title">Pending Feedback</h3>
                            <a href="#" class="view-all-btn">Lihat Semua</a>
                        </div>
                        <div class="card-content">
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <div class="feedback-candidate">Budi Santoso</div>
                                    <div class="feedback-date">Hari ini, 09:00</div>
                                </div>
                                <div class="feedback-position">Accountant</div>
                                <div class="feedback-action">
                                    <button class="feedback-btn submit">
                                        <i class="fas fa-star"></i> Submit Feedback
                                    </button>
                                    <button class="feedback-btn view">
                                        <i class="fas fa-file-alt"></i> View CV
                                    </button>
                                </div>
                            </div>

                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <div class="feedback-candidate">Siti Nurhaliza</div>
                                    <div class="feedback-date">Hari ini, 11:30</div>
                                </div>
                                <div class="feedback-position">UI/UX Designer</div>
                                <div class="feedback-action">
                                    <button class="feedback-btn submit">
                                        <i class="fas fa-star"></i> Submit Feedback
                                    </button>
                                    <button class="feedback-btn view">
                                        <i class="fas fa-file-alt"></i> View CV
                                    </button>
                                </div>
                            </div>

                            <div class="empty-state" style="padding: 20px;">
                                <i class="fas fa-clipboard-check"></i>
                                <p>Tidak ada feedback pending lainnya</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Stats -->
                <div class="performance-stats">
                    <div class="card-header">
                        <h3 class="card-title">My Performance Stats</h3>
                        <select style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <option>This Month</option>
                            <option>Last 3 Months</option>
                            <option>This Year</option>
                        </select>
                    </div>
                    <div class="stats-grid">
                        <div class="performance-stat">
                            <div class="performance-value">45</div>
                            <div class="performance-label">Total Interviews</div>
                        </div>
                        <div class="performance-stat">
                            <div class="performance-value">7.8</div>
                            <div class="performance-label">Average Score Given</div>
                        </div>
                        <div class="performance-stat">
                            <div class="performance-value">65%</div>
                            <div class="performance-label">Recommend Rate</div>
                        </div>
                        <div class="performance-stat">
                            <div class="performance-value">2.3h</div>
                            <div class="performance-label">Avg Response Time</div>
                        </div>
                        <div class="performance-stat">
                            <div class="performance-value">98%</div>
                            <div class="performance-label">Attendance Rate</div>
                        </div>
                        <div class="performance-stat">
                            <div class="performance-value">4.9</div>
                            <div class="performance-label">Candidate Rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeOptions = { hour: '2-digit', minute: '2-digit' };
            const dateOptions = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            };
            
            document.getElementById('currentTime').textContent = 
                now.toLocaleTimeString('id-ID', timeOptions);
            document.getElementById('currentDate').textContent = 
                now.toLocaleDateString('id-ID', dateOptions);
        }

        // Update time every minute
        updateTime();
        setInterval(updateTime, 60000);

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
                e.preventDefault();
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            });
        });

        // Action button interactions
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (btn.textContent.includes('Start') || btn.textContent.includes('Join')) {
                    btn.style.background = '#059669';
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Starting...';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-video"></i> In Progress';
                    }, 2000);
                }
            });
        });

        // Feedback button interactions
        document.querySelectorAll('.feedback-btn.submit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('Submit feedback clicked');
                btn.style.background = '#059669';
                btn.innerHTML = '<i class="fas fa-check"></i> Submitted';
                setTimeout(() => {
                    btn.closest('.feedback-item').style.opacity = '0.5';
                }, 1000);
            });
        });

        // Logout confirmation
        // document.querySelector('.logout-btn').addEventListener('click', () => {
        //     if (confirm('Apakah Anda yakin ingin logout?')) {
        //         console.log('Logout clicked');
        //         // window.location.href = '/logout';
        //     }
        // });

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

        document.querySelectorAll('.stat-card, .interview-schedule, .pending-feedback').forEach(card => {
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