<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Recruitment</title>
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

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
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .stat-icon.primary { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
        .stat-icon.success { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-icon.warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-icon.danger { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-change.positive { color: #10b981; }
        .stat-change.negative { color: #ef4444; }

        /* Charts and Tables */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .chart-card, .activity-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a202c;
        }

        .chart-placeholder {
            height: 300px;
            background: #f7fafc;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #718096;
            font-size: 1.1rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
            font-size: 0.9rem;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 0.9rem;
            color: #2d3748;
            margin-bottom: 2px;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #718096;
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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
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
                <!-- <div class="user-avatar">
                    <i class="fas fa-user-crown"></i>
                </div> -->
                <div class="user-details">
                    <div class="user-name">{{ Auth::user()->full_name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>

            <nav class="nav-menu">
                <!-- <div class="nav-item">
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
                </div> -->
                <div class="nav-item">
                    <a href="{{ route('candidates.index') }}" class="nav-link {{ request()->routeIs('candidates.*') ? 'active' : '' }}">
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
                <!-- <div class="nav-item">
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
                </div> -->
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <header class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Dashboard Admin</h1>
                </div>
               <div class="header-right">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">5</span>
                    </button>
                    
                    <!-- Form Logout dengan CSRF Protection -->
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
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Kandidat</div>
                            <div class="stat-icon primary">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-value">1,234</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+12% dari bulan lalu</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Active Users</div>
                            <div class="stat-icon success">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                        <div class="stat-value">25</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>HR: 8, Interviewer: 17</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Open Positions</div>
                            <div class="stat-icon warning">
                                <i class="fas fa-briefcase"></i>
                            </div>
                        </div>
                        <div class="stat-value">15</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+3 posisi baru</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Interview Hari Ini</div>
                            <div class="stat-icon danger">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                        <div class="stat-value">12</div>
                        <div class="stat-change negative">
                            <i class="fas fa-arrow-down"></i>
                            <span>2 reschedule</span>
                        </div>
                    </div>
                </div>

                <!-- Charts and Activity -->
                <div class="dashboard-grid">
                    <div class="chart-card">
                        <div class="card-header">
                            <h3 class="card-title">Recruitment Analytics</h3>
                            <select style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <option>Last 30 days</option>
                                <option>Last 3 months</option>
                                <option>Last 6 months</option>
                            </select>
                        </div>
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-line" style="margin-right: 10px;"></i>
                            Chart akan ditampilkan di sini (Chart.js/D3.js)
                        </div>
                    </div>

                    <div class="activity-card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Activity</h3>
                            <a href="#" style="color: #4f46e5; text-decoration: none; font-size: 0.9rem;">View All</a>
                        </div>
                        <div class="activity-item">
                            <div class="activity-avatar">HR</div>
                            <div class="activity-content">
                                <div class="activity-text"><strong>Sarah HR</strong> menambahkan kandidat baru</div>
                                <div class="activity-time">2 menit yang lalu</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-avatar">IN</div>
                            <div class="activity-content">
                                <div class="activity-text"><strong>John Interviewer</strong> menyelesaikan interview</div>
                                <div class="activity-time">15 menit yang lalu</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-avatar">AD</div>
                            <div class="activity-content">
                                <div class="activity-text"><strong>Admin</strong> membuat user baru</div>
                                <div class="activity-time">1 jam yang lalu</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-avatar">HR</div>
                            <div class="activity-content">
                                <div class="activity-text"><strong>Lisa HR</strong> menjadwalkan interview</div>
                                <div class="activity-time">2 jam yang lalu</div>
                            </div>
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

        // Nav link active state
        document.querySelectorAll('.nav-link[href="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
            e.preventDefault();
            });
        });

        // Logout confirmation
        document.querySelector('.logout-btn').addEventListener('click', () => {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                console.log('Logout clicked');
                // window.location.href = '/logout';
            }
        });

        // Animate stat cards
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

        document.querySelectorAll('.stat-card').forEach(card => {
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