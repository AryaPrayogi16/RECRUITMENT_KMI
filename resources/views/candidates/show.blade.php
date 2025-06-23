<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Kandidat - HR System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
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
            margin-left: 230px;
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
            flex-wrap: wrap;
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
            padding-top: 10px;
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

        .status-submitted {
            background: #fef3c7;
            color: #92400e;
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

        /* Section Navigation */
        .section-nav {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            position: sticky;
            top: 120px;
            z-index: 50;
            overflow: hidden;
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
            min-width: 120px;
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

        .section-nav-list::-webkit-scrollbar {
            height: 3px;
        }

        .section-nav-list::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .section-nav-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .section-nav-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            scroll-margin-top: 180px;
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

        /* Documents Section - Improved */
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
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
            font-size: 1.2rem;
        }

        .document-item {
            background: white;
            padding: 20px;
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
            gap: 15px;
            flex: 1;
        }

        .document-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        .document-details {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .document-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 4px;
            font-size: 1rem;
        }

        .document-meta {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .document-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .no-documents {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
            background: white;
            border-radius: 8px;
            border: 2px dashed #e5e7eb;
        }

        .no-documents i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: #e5e7eb;
        }

        .no-documents p {
            font-size: 1rem;
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

        /* Timeline for History */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 8px;
            width: 12px;
            height: 12px;
            background: #4f46e5;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #e2e8f0;
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }

        .timeline-title {
            font-weight: 600;
            color: #1a202c;
        }

        .timeline-date {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .timeline-content {
            font-size: 0.9rem;
            color: #6b7280;
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

        /* Additional Info Text */
        .info-text {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            color: #4a5568;
            line-height: 1.6;
        }

        /* Yes/No Badge */
        .yes-no-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .yes-badge {
            background: #d1fae5;
            color: #065f46;
        }

        .no-badge {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Kraeplin-specific styles */
        .grade-a { background: #d1fae5; color: #065f46; }
        .grade-b { background: #dbeafe; color: #1e40af; }
        .grade-c { background: #fef3c7; color: #92400e; }
        .grade-d { background: #fed7aa; color: #c2410c; }
        .grade-e { background: #fee2e2; color: #991b1b; }

        .performance-excellent { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
        .performance-good { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; }
        .performance-average { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
        .performance-below_average { background: linear-gradient(135deg, #fed7aa, #fdba74); color: #c2410c; }
        .performance-poor { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; }

        .chart-tab.active {
            background: #4f46e5 !important;
            color: white !important;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
        }

        .chart-tab:hover:not(.active) {
            background: #e2e8f0 !important;
            color: #4a5568 !important;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Print styles */
        @media print {
            .chart-nav { display: none !important; }
            #kraeplinChart { max-height: none !important; height: 400px !important; }
        }

        #kraeplinChart {
            width: 100% !important;
            height: 450px !important;
            max-width: 100%;
            display: block;
        }

        .chart-container {
            position: relative;
            height: 500px;
            width: 100%;
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
                top: 100px;
                margin-bottom: 20px;
            }

            .section-nav-list {
                flex-wrap: nowrap;
                overflow-x: scroll;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                padding: 0 10px;
            }

            .section-nav-list::-webkit-scrollbar {
                display: none;
            }

            .section-nav-item {
                flex: none;
                min-width: 100px;
            }

            .section-nav-link {
                padding: 12px 15px;
                font-size: 0.85rem;
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

            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .header-right {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .btn {
                font-size: 0.85rem;
                padding: 8px 12px;
                justify-content: center;
            }

            .btn i {
                display: none;
            }

            .documents-grid {
                grid-template-columns: 1fr;
            }

            .document-item {
                padding: 15px;
            }

            .document-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }

            .document-details {
                flex: 1;
            }

            .document-name {
                font-size: 0.9rem;
            }

            .document-actions {
                flex-direction: column;
                gap: 5px;
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
                scroll-margin-top: 140px;
                padding: 20px;
                margin-bottom: 20px;
            }

            .page-title {
                font-size: 1.3rem;
            }

            .candidate-name {
                font-size: 1.5rem;
            }

            .candidate-position {
                font-size: 1rem;
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
                font-size: 1.3rem;
            }

            .candidate-position {
                font-size: 0.9rem;
            }

            .content-section {
                padding: 15px;
                scroll-margin-top: 120px;
                margin-bottom: 15px;
            }

            .section-nav {
                top: 85px;
                margin-bottom: 15px;
            }

            .section-title {
                font-size: 1.2rem;
            }

            .info-card-title {
                font-size: 1rem;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-value {
                text-align: left;
                margin-top: 5px;
            }

            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .document-category {
                padding: 15px;
            }

            .document-category-title {
                font-size: 1rem;
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
                        <span>Riwayat</span>
                    </button>

                    <a href="{{ route('candidates.preview', $candidate->id) }}" class="btn btn-info" target="_blank">
                        <i class="fas fa-eye"></i>
                        <span>Export</span>
                    </a>

                    <a href="{{ route('candidates.edit', $candidate->id) }}" class="btn btn-secondary">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>
                    
                    <button class="btn btn-primary" onclick="showStatusModal()">
                        <i class="fas fa-sync"></i>
                        <span>Update Status</span>
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
                                <p class="candidate-position">
                                    {{ $candidate->position->position_name ?? $candidate->position_applied }}
                                    @if($candidate->expected_salary)
                                        • Gaji Harapan: Rp {{ number_format($candidate->expected_salary, 0, ',', '.') }}
                                    @endif
                                </p>
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
                                <span>Data Pribadi</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#education-section" class="section-nav-link">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Pendidikan</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#experience-section" class="section-nav-link">
                                <i class="fas fa-briefcase"></i>
                                <span>Pengalaman</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#skills-section" class="section-nav-link">
                                <i class="fas fa-cogs"></i>
                                <span>Keterampilan</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#activities-section" class="section-nav-link">
                                <i class="fas fa-hands-helping"></i>
                                <span>Aktivitas</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#general-section" class="section-nav-link">
                                <i class="fas fa-info-circle"></i>
                                <span>Info Umum</span>
                            </a>
                        </li>

                        <li class="section-nav-item">
                            <a href="#kraeplin-section" class="section-nav-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Hasil Kraeplin</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#disc-section" class="section-nav-link">
                                <i class="fas fa-chart-pie"></i>
                                <span>Hasil DISC</span>
                            </a>
                        </li>
                        <li class="section-nav-item">
                            <a href="#documents-section" class="section-nav-link">
                                <i class="fas fa-file-alt"></i>
                                <span>Dokumen</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Personal Data Section -->
                <section id="personal-section" class="content-section" style="margin-top: 0;">
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
                            <div class="info-row">
                                <span class="info-label">Suku Bangsa</span>
                                <span class="info-value">{{ $candidate->personalData->ethnicity ?? 'N/A' }}</span>
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

                        <div class="info-card">
                            <h3 class="info-card-title">
                                <i class="fas fa-home"></i>
                                Alamat
                            </h3>
                            <div class="info-row">
                                <span class="info-label">Alamat Saat Ini</span>
                                <span class="info-value">{{ $candidate->personalData->current_address ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status Tempat Tinggal</span>
                                <span class="info-value">{{ $candidate->personalData->current_address_status ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Alamat KTP</span>
                                <span class="info-value">{{ $candidate->personalData->ktp_address ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="info-card">
                            <h3 class="info-card-title">
                                <i class="fas fa-ruler-vertical"></i>
                                Data Fisik & Kesehatan
                            </h3>
                            <div class="info-row">
                                <span class="info-label">Tinggi Badan</span>
                                <span class="info-value">{{ $candidate->personalData->height_cm ?? 'N/A' }} cm</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Berat Badan</span>
                                <span class="info-value">{{ $candidate->personalData->weight_kg ?? 'N/A' }} kg</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status Vaksinasi</span>
                                <span class="info-value">{{ $candidate->personalData->vaccination_status ?? 'N/A' }}</span>
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
                            <i class="fas fa-certificate"></i>
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
                                @if($exp->supervisor_contact)
                                    <div class="info-row">
                                        <span class="info-label">Kontak Atasan</span>
                                        <span class="info-value">{{ $exp->supervisor_contact }}</span>
                                    </div>
                                @endif
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

                        @if($candidate->drivingLicenses->count() > 0)
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-car"></i>
                                    SIM yang Dimiliki
                                </h3>
                                @foreach($candidate->drivingLicenses as $license)
                                    <div class="info-row">
                                        <span class="info-label">SIM {{ $license->license_type }}</span>
                                        <span class="info-value">
                                            No: {{ $license->license_number ?? 'N/A' }}
                                            @if($license->expiry_date)
                                                <br>Exp: {{ \Carbon\Carbon::parse($license->expiry_date)->format('d M Y') }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($candidate->otherSkills)
                        <div class="info-card" style="margin-top: 20px;">
                            <h3 class="info-card-title">
                                <i class="fas fa-star"></i>
                                Kemampuan Lainnya
                            </h3>
                            <p class="info-text">{{ $candidate->otherSkills->other_skills }}</p>
                        </div>
                    @endif

                    @if($candidate->languageSkills->count() == 0 && !$candidate->computerSkills && !$candidate->otherSkills && $candidate->drivingLicenses->count() == 0)
                        <div class="empty-state">
                            <i class="fas fa-cogs"></i>
                            <p>Tidak ada data keterampilan</p>
                        </div>
                    @endif
                </section>

                <!-- Activities & Achievements Section -->
                <section id="activities-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-hands-helping"></i>
                        Aktivitas & Prestasi
                    </h2>

                    @if($candidate->socialActivities->count() > 0)
                        <h3 class="info-card-title">
                            <i class="fas fa-users"></i>
                            Kegiatan Sosial/Organisasi
                        </h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Organisasi</th>
                                    <th>Posisi</th>
                                    <th>Periode</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($candidate->socialActivities as $activity)
                                    <tr>
                                        <td>{{ $activity->organization_name }}</td>
                                        <td>{{ $activity->position }}</td>
                                        <td>{{ $activity->start_year }} - {{ $activity->end_year ?? 'Sekarang' }}</td>
                                        <td>{{ $activity->description ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($candidate->achievements->count() > 0)
                        <h3 class="info-card-title" style="margin-top: 30px;">
                            <i class="fas fa-trophy"></i>
                            Prestasi
                        </h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Prestasi</th>
                                    <th>Penyelenggara</th>
                                    <th>Tahun</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($candidate->achievements as $achievement)
                                    <tr>
                                        <td>{{ $achievement->achievement_name }}</td>
                                        <td>{{ $achievement->issuer }}</td>
                                        <td>{{ $achievement->year }}</td>
                                        <td>{{ $achievement->description ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($candidate->socialActivities->count() == 0 && $candidate->achievements->count() == 0)
                        <div class="empty-state">
                            <i class="fas fa-hands-helping"></i>
                            <p>Tidak ada data aktivitas atau prestasi</p>
                        </div>
                    @endif
                </section>

                <!-- General Information Section -->
                <section id="general-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Informasi Umum
                    </h2>

                    @if($candidate->generalInformation)
                        <div class="info-grid">
                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-briefcase"></i>
                                    Informasi Pekerjaan
                                </h3>
                                <div class="info-row">
                                    <span class="info-label">Bersedia Dinas Luar Kota</span>
                                    <span class="info-value">
                                        <span class="{{ $candidate->generalInformation->willing_to_travel ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->generalInformation->willing_to_travel ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Memiliki Kendaraan</span>
                                    <span class="info-value">
                                        <span class="{{ $candidate->generalInformation->has_vehicle ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->generalInformation->has_vehicle ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->generalInformation->vehicle_types)
                                    <div class="info-row">
                                        <span class="info-label">Jenis Kendaraan</span>
                                        <span class="info-value">{{ $candidate->generalInformation->vehicle_types }}</span>
                                    </div>
                                @endif
                                <div class="info-row">
                                    <span class="info-label">Tanggal Bisa Mulai Kerja</span>
                                    <span class="info-value">
                                        {{ $candidate->generalInformation->start_work_date ? \Carbon\Carbon::parse($candidate->generalInformation->start_work_date)->format('d M Y') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Sumber Informasi Lowongan</span>
                                    <span class="info-value">{{ $candidate->generalInformation->information_source ?? 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-user-check"></i>
                                    Data Lainnya
                                </h3>
                                <div class="info-row">
                                    <span class="info-label">Catatan Kriminal</span>
                                    <span class="info-value">
                                        <span class="{{ !$candidate->generalInformation->has_police_record ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->generalInformation->has_police_record ? 'Ada' : 'Tidak Ada' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->generalInformation->police_record_detail)
                                    <div class="info-row">
                                        <span class="info-label">Detail Catatan</span>
                                        <span class="info-value">{{ $candidate->generalInformation->police_record_detail }}</span>
                                    </div>
                                @endif
                                <div class="info-row">
                                    <span class="info-label">Riwayat Penyakit Serius</span>
                                    <span class="info-value">
                                        <span class="{{ !$candidate->generalInformation->has_serious_illness ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->generalInformation->has_serious_illness ? 'Ada' : 'Tidak Ada' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->generalInformation->illness_detail)
                                    <div class="info-row">
                                        <span class="info-label">Detail Penyakit</span>
                                        <span class="info-value">{{ $candidate->generalInformation->illness_detail }}</span>
                                    </div>
                                @endif
                                <div class="info-row">
                                    <span class="info-label">Tato/Tindik</span>
                                    <span class="info-value">
                                        <span class="{{ !$candidate->generalInformation->has_tattoo_piercing ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->generalInformation->has_tattoo_piercing ? 'Ada' : 'Tidak Ada' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->generalInformation->tattoo_piercing_detail)
                                    <div class="info-row">
                                        <span class="info-label">Detail Tato/Tindik</span>
                                        <span class="info-value">{{ $candidate->generalInformation->tattoo_piercing_detail }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="info-card">
                                <h3 class="info-card-title">
                                    <i class="fas fa-store"></i>
                                    Informasi Bisnis
                                </h3>
                                <div class="info-row">
                                    <span class="info-label">Memiliki Usaha Lain</span>
                                    <span class="info-value">
                                        <span class="{{ $candidate->generalInformation->has_other_business ? 'yes-badge' : 'no-badge' }}">
                                            {{ $candidate->generalInformation->has_other_business ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </span>
                                </div>
                                @if($candidate->generalInformation->other_business_detail)
                                    <div class="info-row">
                                        <span class="info-label">Detail Usaha</span>
                                        <span class="info-value">{{ $candidate->generalInformation->other_business_detail }}</span>
                                    </div>
                                @endif
                                @if($candidate->generalInformation->other_income)
                                    <div class="info-row">
                                        <span class="info-label">Penghasilan Lain</span>
                                        <span class="info-value">{{ $candidate->generalInformation->other_income }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($candidate->generalInformation->motivation || $candidate->generalInformation->strengths || $candidate->generalInformation->weaknesses)
                            <div class="info-card" style="margin-top: 20px;">
                                <h3 class="info-card-title">
                                    <i class="fas fa-lightbulb"></i>
                                    Motivasi & Karakter
                                </h3>
                                @if($candidate->generalInformation->motivation)
                                    <div style="margin-bottom: 15px;">
                                        <strong>Motivasi Bekerja:</strong>
                                        <p class="info-text">{{ $candidate->generalInformation->motivation }}</p>
                                    </div>
                                @endif
                                @if($candidate->generalInformation->strengths)
                                    <div style="margin-bottom: 15px;">
                                        <strong>Kelebihan:</strong>
                                        <p class="info-text">{{ $candidate->generalInformation->strengths }}</p>
                                    </div>
                                @endif
                                @if($candidate->generalInformation->weaknesses)
                                    <div>
                                        <strong>Kekurangan:</strong>
                                        <p class="info-text">{{ $candidate->generalInformation->weaknesses }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p>Tidak ada informasi umum</p>
                        </div>
                    @endif
                </section>

                {{-- Hasil kraeplin section --}}
                <section id="kraeplin-section" class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-chart-line"></i>
                        Hasil Tes Kraeplin
                    </h2>

                    @if($candidate->kraeplinTestResult)
                        <!-- Summary Cards -->
                        <div style="margin-bottom: 30px;">
                            <h3 class="info-card-title">
                                <i class="fas fa-trophy"></i>
                                Ringkasan Hasil Tes
                            </h3>
                            <div class="info-grid">
                                <div class="info-card">
                                    <h4 style="color: #4f46e5; font-size: 1rem; margin-bottom: 15px; font-weight: 600;">
                                        <i class="fas fa-check-circle"></i>
                                        Akurasi & Penyelesaian
                                    </h4>
                                    <div class="info-row">
                                        <span class="info-label">Total Soal Terjawab</span>
                                        <span class="info-value" style="font-weight: 600; color: #1a202c;">
                                            {{ $candidate->kraeplinTestResult->total_questions_answered }}
                                            <span style="font-size: 0.8rem; color: #6b7280;">/ 832 soal</span>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Jawaban Benar</span>
                                        <span class="info-value" style="font-weight: 600; color: #059669;">
                                            {{ $candidate->kraeplinTestResult->total_correct_answers }}
                                            <span style="font-size: 0.8rem; color: #6b7280;">
                                                ({{ number_format($candidate->kraeplinTestResult->accuracy_percentage, 1) }}%)
                                            </span>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Jawaban Salah</span>
                                        <span class="info-value" style="font-weight: 600; color: #dc2626;">
                                            {{ $candidate->kraeplinTestResult->total_wrong_answers }}
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Tingkat Penyelesaian</span>
                                        <span class="info-value" style="font-weight: 600; color: #7c3aed;">
                                            {{ number_format($candidate->kraeplinTestResult->completion_rate, 1) }}%
                                        </span>
                                    </div>
                                </div>

                                <div class="info-card">
                                    <h4 style="color: #4f46e5; font-size: 1rem; margin-bottom: 15px; font-weight: 600;">
                                        <i class="fas fa-tachometer-alt"></i>
                                        Kecepatan & Konsistensi
                                    </h4>
                                    <div class="info-row">
                                        <span class="info-label">Kecepatan Rata-rata</span>
                                        <span class="info-value" style="font-weight: 600; color: #1a202c;">
                                        {{ $candidate->kraeplinTestResult->formatted_average_time }}
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Durasi Total</span>
                                        <span class="info-value" style="font-weight: 600; color: #1a202c;">
                                            {{ $candidate->kraeplinTestResult->testSession->formatted_duration ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Skor Keseluruhan</span>
                                        <span class="info-value" style="font-weight: 700; color: #4f46e5; font-size: 1.1rem;">
                                            {{ number_format($candidate->kraeplinTestResult->overall_score, 1) }}/100
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Grade</span>
                                        <span class="info-value">
                                            <span class="status-badge grade-{{ strtolower($candidate->kraeplinTestResult->grade) }}" 
                                                style="font-weight: 700; font-size: 1rem;">
                                                {{ $candidate->kraeplinTestResult->grade }}
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class="info-card">
                                    <h4 style="color: #4f46e5; font-size: 1rem; margin-bottom: 15px; font-weight: 600;">
                                        <i class="fas fa-award"></i>
                                        Kategori Performa
                                    </h4>
                                    <div style="text-align: center; padding: 20px 0;">
                                        <div class="performance-category performance-{{ $candidate->kraeplinTestResult->performance_category }}" 
                                            style="display: inline-block; padding: 15px 25px; border-radius: 12px; font-weight: 600; font-size: 1.1rem;">
                                            {{ $candidate->kraeplinTestResult->performance_category_label }}
                                        </div>
                                    </div>
                                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-top: 15px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: #4a5568; line-height: 1.5; text-align: center;">
                                            {{ $candidate->kraeplinTestResult->getScoreInterpretation() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Analysis Charts -->
                        <div style="margin-bottom: 30px;">
                            <h3 class="info-card-title">
                                <i class="fas fa-chart-line"></i>
                                Analisis Performa per Kolom
                            </h3>
                            
                            <!-- Chart Loading State -->
                            <div id="chartLoading" style="text-align: center; padding: 60px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top: 4px solid #4f46e5; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                <p style="margin-top: 15px; color: #6b7280;">Memuat grafik analisis...</p>
                            </div>

                            <!-- Chart Container -->
                            <div id="chartContainer" style="display: none;">
                                <!-- Chart Navigation Tabs -->
                                <div style="margin-bottom: 20px;">
                                    <div class="chart-nav" style="display: flex; background: white; border-radius: 12px; padding: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow-x: auto;">
                                        <button class="chart-tab active" data-chart="combined" style="flex: 1; min-width: 150px; padding: 12px 20px; border: none; background: #4f46e5; color: white; border-radius: 8px; margin-right: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                                            <i class="fas fa-chart-line"></i>
                                            Gabungan (3 in 1)
                                        </button>
                                        <button class="chart-tab" data-chart="accuracy" style="flex: 1; min-width: 120px; padding: 12px 20px; border: none; background: #f8fafc; color: #6b7280; border-radius: 8px; margin-right: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                                            <i class="fas fa-bullseye"></i>
                                            Akurasi
                                        </button>
                                        <button class="chart-tab" data-chart="speed" style="flex: 1; min-width: 120px; padding: 12px 20px; border: none; background: #f8fafc; color: #6b7280; border-radius: 8px; margin-right: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                                            <i class="fas fa-tachometer-alt"></i>
                                            Kecepatan
                                        </button>
                                        <button class="chart-tab" data-chart="time" style="flex: 1; min-width: 120px; padding: 12px 20px; border: none; background: #f8fafc; color: #6b7280; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s ease;">
                                            <i class="fas fa-clock"></i>
                                            Waktu
                                        </button>
                                    </div>
                                </div>

                                <!-- Chart Canvas -->
                                <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: relative; height: 500px;">
                                    <canvas id="kraeplinChart"></canvas>
                                </div>

                                <!-- Chart Legend & Info -->
                                <div style="background: white; border-radius: 12px; padding: 20px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                                        <div style="text-align: center;">
                                            <div style="color: #1e40af; font-weight: 600; margin-bottom: 5px;">
                                                <i class="fas fa-circle" style="color: #1e40af; margin-right: 8px;"></i>
                                                Jawaban Benar
                                            </div>
                                            <div style="font-size: 0.85rem; color: #6b7280;">Jumlah jawaban benar per kolom (0-26)</div>
                                        </div>
                                        <div style="text-align: center;">
                                            <div style="color: #059669; font-weight: 600; margin-bottom: 5px;">
                                                <i class="fas fa-circle" style="color: #059669; margin-right: 8px;"></i>
                                                Soal Dijawab
                                            </div>
                                            <div style="font-size: 0.85rem; color: #6b7280;">Total soal yang dikerjakan per kolom (0-26)</div>
                                        </div>
                                        <div style="text-align: center;">
                                            <div style="color: #dc2626; font-weight: 600; margin-bottom: 5px;">
                                                <i class="fas fa-circle" style="color: #dc2626; margin-right: 8px;"></i>
                                                Waktu Rata-rata
                                            </div>
                                            <div style="font-size: 0.85rem; color: #6b7280;">Rata-rata waktu pengerjaan per soal dalam detik</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test Session Details -->
                        <div class="info-card">
                            <h3 class="info-card-title">
                                <i class="fas fa-info-circle"></i>
                                Detail Sesi Tes
                            </h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <div class="info-row">
                                    <span class="info-label">Kode Tes</span>
                                    <span class="info-value">{{ $candidate->kraeplinTestResult->testSession->test_code ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Tanggal Tes</span>
                                    <span class="info-value">
                                        {{ $candidate->kraeplinTestResult->testSession->completed_at ? $candidate->kraeplinTestResult->testSession->completed_at->format('d M Y H:i') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Status</span>
                                    <span class="info-value">
                                        <span class="status-badge status-accepted">Selesai</span>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Durasi</span>
                                    <span class="info-value">{{ $candidate->kraeplinTestResult->testSession->formatted_duration ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <p>Kandidat belum menyelesaikan tes Kraeplin</p>
                            @if($candidate->canStartKraeplinTest())
                                <div style="margin-top: 20px;">
                                    <a href="{{ route('kraeplin.instructions', $candidate->candidate_code) }}" 
                                    class="btn btn-primary" target="_blank">
                                        <i class="fas fa-play"></i>
                                        Mulai Tes Kraeplin
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </section>


{{-- DISC Test Results Section - OPTIMIZED VERSION --}}
<section id="disc-section" class="content-section">
    <h2 class="section-title">
        <i class="fas fa-chart-pie"></i>
        Hasil Tes DISC
    </h2>

    @if($candidate->discTestResult)
        {{-- COMPACT HEADER SUMMARY --}}
        <div class="disc-header-summary" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); border-radius: 15px; padding: 25px; margin-bottom: 25px; color: white;">
            <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 20px; align-items: center;">
                {{-- Profile Type --}}
                <div style="text-align: center;">
                    <div style="background: rgba(255,255,255,0.2); padding: 15px 20px; border-radius: 12px; margin-bottom: 10px;">
                        <div style="font-size: 2rem; font-weight: 800; margin-bottom: 5px;">
                            {{ $candidate->discTestResult->primary_type }}{{ $candidate->discTestResult->secondary_type }}
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Profile Type</div>
                    </div>
                </div>
                
                {{-- Primary Info --}}
                <div>
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 8px; color: white;">
                        {{ $candidate->discTestResult->primary_type_label }}
                    </h3>
                    <p style="opacity: 0.9; margin-bottom: 10px; font-size: 1rem;">
                        Sekunder: {{ $candidate->discTestResult->secondary_type_label }}
                    </p>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 8px; font-size: 0.85rem;">
                            <i class="fas fa-percentage"></i> {{ number_format($candidate->discTestResult->primary_percentage, 1) }}% Dominan
                        </span>
                        <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 8px; font-size: 0.85rem;">
                            <i class="fas fa-chart-bar"></i> {{ $candidate->discTestResult->d_segment }}-{{ $candidate->discTestResult->i_segment }}-{{ $candidate->discTestResult->s_segment }}-{{ $candidate->discTestResult->c_segment }}
                        </span>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div style="text-align: center;">
                    <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 12px;">
                        <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 5px;">Completed</div>
                        <div style="font-size: 1.1rem; font-weight: 600;">
                            {{ $candidate->discTestResult->testSession->completed_at ? $candidate->discTestResult->testSession->completed_at->format('d M Y') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DISC GRAPH & SCORES - REVISED LAYOUT --}}
        <div style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 25px;">
            <h3 style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-size: 1.2rem; color: #1a202c;">
                <i class="fas fa-chart-line" style="color: #4f46e5;"></i>
                Profil DISC & Grafik
            </h3>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: center;">
                {{-- Large DISC Graph --}}
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; min-height: 450px;">
                    <div id="discGraph" style="width: 100%; height: 400px;"></div>
                </div>

                {{-- Compact Scores Grid --}}
                <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
                    <div style="background: #fee2e2; padding: 12px; border-radius: 8px; border-left: 4px solid #dc2626;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600; color: #dc2626; font-size: 0.95rem;">Dominance (D)</div>
                                <div style="font-size: 0.75rem; color: #7f1d1d; margin-top: 1px;">Direct, Results</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.1rem; font-weight: 700; color: #dc2626;">{{ number_format($candidate->discTestResult->d_percentage, 1) }}%</div>
                                <div style="font-size: 0.7rem; color: #7f1d1d;">Seg. {{ $candidate->discTestResult->d_segment }}</div>
                            </div>
                        </div>
                    </div>

                    <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600; color: #f59e0b; font-size: 0.95rem;">Influence (I)</div>
                                <div style="font-size: 0.75rem; color: #92400e; margin-top: 1px;">Outgoing, Social</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.1rem; font-weight: 700; color: #f59e0b;">{{ number_format($candidate->discTestResult->i_percentage, 1) }}%</div>
                                <div style="font-size: 0.7rem; color: #92400e;">Seg. {{ $candidate->discTestResult->i_segment }}</div>
                            </div>
                        </div>
                    </div>

                    <div style="background: #d1fae5; padding: 12px; border-radius: 8px; border-left: 4px solid #10b981;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600; color: #10b981; font-size: 0.95rem;">Steadiness (S)</div>
                                <div style="font-size: 0.75rem; color: #065f46; margin-top: 1px;">Patient, Loyal</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.1rem; font-weight: 700; color: #10b981;">{{ number_format($candidate->discTestResult->s_percentage, 1) }}%</div>
                                <div style="font-size: 0.7rem; color: #065f46;">Seg. {{ $candidate->discTestResult->s_segment }}</div>
                            </div>
                        </div>
                    </div>

                    <div style="background: #dbeafe; padding: 12px; border-radius: 8px; border-left: 4px solid #3b82f6;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600; color: #3b82f6; font-size: 0.95rem;">Conscientiousness (C)</div>
                                <div style="font-size: 0.75rem; color: #1e40af; margin-top: 1px;">Analytical, Systematic</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.1rem; font-weight: 700; color: #3b82f6;">{{ number_format($candidate->discTestResult->c_percentage, 1) }}%</div>
                                <div style="font-size: 0.7rem; color: #1e40af;">Seg. {{ $candidate->discTestResult->c_segment }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Color Legend --}}
                    <div style="background: #f8fafc; padding: 10px; border-radius: 8px; margin-top: 10px;">
                        <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; margin-bottom: 8px; text-align: center;">Legenda Warna</div>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; font-size: 0.7rem;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="width: 12px; height: 12px; background: #dc2626; border-radius: 2px;"></div>
                                <span style="color: #4a5568;">D</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="width: 12px; height: 12px; background: #f59e0b; border-radius: 2px;"></div>
                                <span style="color: #4a5568;">I</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="width: 12px; height: 12px; background: #10b981; border-radius: 2px;"></div>
                                <span style="color: #4a5568;">S</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="width: 12px; height: 12px; background: #3b82f6; border-radius: 2px;"></div>
                                <span style="color: #4a5568;">C</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PERSONALITY INSIGHTS - COMPACT VERSION --}}
        @if($candidate->discTestResult->full_profile)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 25px;">
                
                {{-- Strengths & Development Areas --}}
                <div style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <h4 style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px; font-size: 1.1rem; color: #1a202c;">
                        <i class="fas fa-star" style="color: #059669;"></i>
                        Kelebihan & Pengembangan
                    </h4>
                
                    {{-- Strengths - Improved Condition Check --}}
                    @if(isset($candidate->discTestResult->full_profile['analysis']['strengths']) && is_array($candidate->discTestResult->full_profile['analysis']['strengths']) && count($candidate->discTestResult->full_profile['analysis']['strengths']) > 0)
                        <div style="margin-bottom: 20px;">
                            <div style="color: #059669; font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-plus-circle"></i> Kelebihan
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @foreach($candidate->discTestResult->full_profile['analysis']['strengths'] as $strength)
                                    <span style="background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 500;">
                                        {{ $strength }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div style="margin-bottom: 20px;">
                            <div style="color: #6b7280; font-size: 0.9rem; font-style: italic; text-align: center; padding: 15px; background: #f9fafb; border-radius: 8px;">
                                <i class="fas fa-info-circle"></i> Data kelebihan belum tersedia
                            </div>
                        </div>
                    @endif

                    {{-- Development Areas --}}
                    @if(isset($candidate->discTestResult->full_profile['analysis']['development_areas']) && is_array($candidate->discTestResult->full_profile['analysis']['development_areas']) && count($candidate->discTestResult->full_profile['analysis']['development_areas']) > 0)
                        <div>
                            <div style="color: #dc2626; font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-arrow-up"></i> Area Pengembangan
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @foreach($candidate->discTestResult->full_profile['analysis']['development_areas'] as $area)
                                    <span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 500;">
                                        {{ $area }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Work & Communication Style --}}
                <div style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <h4 style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px; font-size: 1.1rem; color: #1a202c;">
                        <i class="fas fa-briefcase" style="color: #4f46e5;"></i>
                        Gaya Kerja & Komunikasi
                    </h4>
                    
                    <div style="margin-bottom: 15px;">
                        <div style="color: #4f46e5; font-weight: 600; margin-bottom: 6px; font-size: 0.9rem;">
                            <i class="fas fa-cog"></i> Gaya Kerja
                        </div>
                        <p style="color: #4a5568; font-size: 0.9rem; line-height: 1.5; margin: 0; background: #f8fafc; padding: 10px; border-radius: 6px;">
                            {{ $candidate->discTestResult->full_profile['analysis']['work_style'] ?? 'Belum tersedia' }}
                        </p>
                    </div>

                    <div>
                        <div style="color: #7c3aed; font-weight: 600; margin-bottom: 6px; font-size: 0.9rem;">
                            <i class="fas fa-comments"></i> Gaya Komunikasi
                        </div>
                        <p style="color: #4a5568; font-size: 0.9rem; line-height: 1.5; margin: 0; background: #f8fafc; padding: 10px; border-radius: 6px;">
                            {{ $candidate->discTestResult->full_profile['analysis']['communication_style'] ?? 'Belum tersedia' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- RECOMMENDATIONS - COMPACT --}}
            @if(isset($candidate->discTestResult->full_profile['recommendations']))
                <div style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 25px;">
                    <h4 style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px; font-size: 1.1rem; color: #1a202c;">
                        <i class="fas fa-lightbulb" style="color: #f59e0b;"></i>
                        Rekomendasi Karir & Pengembangan
                    </h4>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        @if(isset($candidate->discTestResult->full_profile['recommendations']['roles']))
                            <div>
                                <div style="color: #4f46e5; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">
                                    <i class="fas fa-user-tie"></i> Peran Cocok
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach(array_slice($candidate->discTestResult->full_profile['recommendations']['roles'], 0, 4) as $role)
                                        <span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">
                                            {{ $role }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(isset($candidate->discTestResult->full_profile['recommendations']['environments']))
                            <div>
                                <div style="color: #059669; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">
                                    <i class="fas fa-building"></i> Lingkungan Ideal
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach(array_slice($candidate->discTestResult->full_profile['recommendations']['environments'], 0, 4) as $env)
                                        <span style="background: #d1fae5; color: #065f46; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">
                                            {{ $env }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- PROFILE SUMMARY & SESSION INFO - COMPACT --}}
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            {{-- Profile Summary --}}
            @if($candidate->discTestResult->profile_summary)
                <div style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <h4 style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px; font-size: 1.1rem; color: #1a202c;">
                        <i class="fas fa-file-alt" style="color: #6b7280;"></i>
                        Ringkasan Profil
                    </h4>
                    <p style="color: #4a5568; line-height: 1.6; margin: 0; font-size: 0.95rem;">
                        {{ $candidate->discTestResult->profile_summary }}
                    </p>
                </div>
            @endif

            {{-- Session Details --}}
            <div style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <h4 style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px; font-size: 1.1rem; color: #1a202c;">
                    <i class="fas fa-info-circle" style="color: #6b7280;"></i>
                    Detail Sesi
                </h4>
                <div style="space-y: 8px;">
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6;">
                        <span style="color: #6b7280; font-size: 0.85rem;">Kode Tes</span>
                        <span style="color: #1a202c; font-size: 0.85rem; font-weight: 500;">{{ $candidate->discTestResult->testSession->test_code ?? 'N/A' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6;">
                        <span style="color: #6b7280; font-size: 0.85rem;">Tanggal</span>
                        <span style="color: #1a202c; font-size: 0.85rem; font-weight: 500;">
                            {{ $candidate->discTestResult->testSession->completed_at ? $candidate->discTestResult->testSession->completed_at->format('d M Y') : 'N/A' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                        <span style="color: #6b7280; font-size: 0.85rem;">Durasi</span>
                        <span style="color: #1a202c; font-size: 0.85rem; font-weight: 500;">{{ $candidate->discTestResult->testSession->formatted_duration ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

    @else
        <div class="empty-state">
            <i class="fas fa-chart-pie"></i>
            <p>Kandidat belum menyelesaikan tes DISC</p>
            @if($candidate->canStartDiscTest())
                <div style="margin-top: 20px;">
                    <a href="{{ route('disc.instructions', $candidate->candidate_code) }}" 
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-play"></i>
                        Mulai Tes DISC
                    </a>
                </div>
            @elseif(!$candidate->hasCompletedKraeplinTest())
                <div style="margin-top: 20px;">
                    <p style="color: #6b7280; font-size: 0.9rem;">
                        Kandidat harus menyelesaikan tes Kraeplin terlebih dahulu
                    </p>
                </div>
            @endif
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
                                    'certificates' => ['icon' => 'fa-certificate', 'label' => 'Sertifikat'],
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
                        </div>
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
    // ============================================
    // SHARED UTILITIES & CORE FUNCTIONALITY
    // ============================================
    
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

    // ============================================
    // MODAL FUNCTIONS
    // ============================================
    
    function showHistoryModal() {
        document.getElementById('historyModal').style.display = 'block';
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').style.display = 'none';
    }

    function showStatusModal() {
        document.getElementById('statusModal').style.display = 'block';
    }

    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
    }

    // ============================================
    // SECTION NAVIGATION
    // ============================================
    
    function initSectionNavigation() {
        const sectionNavLinks = document.querySelectorAll('.section-nav-link');
        const sections = document.querySelectorAll('.content-section');

        function updateActiveNav() {
            let current = '';
            sections.forEach((section) => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            sectionNavLinks.forEach((link) => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        }

        window.addEventListener('scroll', updateActiveNav);

        sectionNavLinks.forEach((link) => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                sectionNavLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                const targetId = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                
                if (targetSection) {
                    const headerHeight = document.querySelector('.header').offsetHeight;
                    const navHeight = document.querySelector('.section-nav').offsetHeight;
                    const offsetTop = targetSection.offsetTop - headerHeight - navHeight - 20;
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        setTimeout(() => {
            if (sectionNavLinks.length > 0) {
                sectionNavLinks[0].classList.add('active');
            }
            updateActiveNav();
        }, 100);
    }

    // ============================================
    // KRAEPLIN CHART FUNCTIONALITY
    // ============================================
    
    @if($candidate->kraeplinTestResult)
    function initKraeplinChart() {
        console.log('=== KRAEPLIN CHART INITIALIZATION ===');
        
        // Parse data
        let correctCount = @json($candidate->kraeplinTestResult->column_correct_count);
        let answeredCount = @json($candidate->kraeplinTestResult->column_answered_count);
        let avgTime = @json($candidate->kraeplinTestResult->column_avg_time);
        let accuracy = @json($candidate->kraeplinTestResult->column_accuracy);
        
        // Ensure data is properly parsed
        if (typeof correctCount === 'string') correctCount = JSON.parse(correctCount);
        if (typeof answeredCount === 'string') answeredCount = JSON.parse(answeredCount);
        if (typeof avgTime === 'string') avgTime = JSON.parse(avgTime);
        if (typeof accuracy === 'string') accuracy = JSON.parse(accuracy);
        
        const chartData = {
            labels: Array.from({length: 32}, (_, i) => String(i + 1)),
            correctCount: Array.isArray(correctCount) ? correctCount : Array(32).fill(0),
            answeredCount: Array.isArray(answeredCount) ? answeredCount : Array(32).fill(0),
            avgTime: Array.isArray(avgTime) ? avgTime : Array(32).fill(0),
            accuracy: Array.isArray(accuracy) ? accuracy : Array(32).fill(0)
        };
        
        let currentChart = null;
        
        const chartConfigs = {
            combined: {
                title: 'Analisis Performa Lengkap (3 in 1)',
                datasets: [
                    {
                        label: 'Jawaban Benar',
                        data: chartData.correctCount,
                        borderColor: '#1e40af',
                        backgroundColor: 'rgba(30, 64, 175, 0.1)',
                        yAxisID: 'y',
                        tension: 0.4,
                        pointRadius: 4,
                        borderWidth: 3
                    },
                    {
                        label: 'Soal Dijawab',
                        data: chartData.answeredCount,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        yAxisID: 'y',
                        tension: 0.4,
                        pointRadius: 4,
                        borderWidth: 3
                    },
                    {
                        label: 'Rata-rata Waktu (detik)',
                        data: chartData.avgTime,
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        yAxisID: 'y1',
                        tension: 0.4,
                        pointRadius: 4,
                        borderWidth: 3
                    }
                ],
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Jumlah Soal (0-26)' },
                        min: 0,
                        max: 26
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Waktu (detik)' },
                        min: 0,
                        max: 15
                    }
                }
            },
            // ... other chart configs
        };

        function createChart(type) {
            const canvas = document.getElementById('kraeplinChart');
            if (!canvas) return;
            
            if (currentChart) {
                currentChart.destroy();
                currentChart = null;
            }
            
            const config = chartConfigs[type];
            const ctx = canvas.getContext('2d');
            
            currentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: config.datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: config.title
                        }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Kolom Soal (1-32)' }
                        },
                        ...config.scales
                    }
                }
            });
        }

        // Tab switching
        document.querySelectorAll('.chart-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                createChart(this.dataset.chart);
            });
        });

        // Initialize
        setTimeout(() => {
            document.getElementById('chartLoading').style.display = 'none';
            document.getElementById('chartContainer').style.display = 'block';
            createChart('combined');
        }, 500);
    }
    @endif

    // ============================================
    // DISC GRAPH FUNCTIONALITY
    // ============================================
    
    @if($candidate->discTestResult)
    function initDiscGraph() {
        console.log('=== DISC GRAPH INITIALIZATION ===');
        
        // DISC Graph Data
        const discData = {
            D: {{ $candidate->discTestResult->d_segment }},
            I: {{ $candidate->discTestResult->i_segment }},
            S: {{ $candidate->discTestResult->s_segment }},
            C: {{ $candidate->discTestResult->c_segment }}
        };
        
        const percentages = {
            D: {{ $candidate->discTestResult->d_percentage }},
            I: {{ $candidate->discTestResult->i_percentage }},
            S: {{ $candidate->discTestResult->s_percentage }},
            C: {{ $candidate->discTestResult->c_percentage }}
        };

        function createDiscGraph() {
            const container = document.getElementById('discGraph');
            if (!container) {
                console.log('DISC Graph container not found');
                return;
            }

            // Create SVG
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('width', '100%');
            svg.setAttribute('height', '400');
            svg.setAttribute('viewBox', '0 0 500 400');
            
            // Graph background
            const bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            bg.setAttribute('width', '400');
            bg.setAttribute('height', '320');
            bg.setAttribute('x', '50');
            bg.setAttribute('y', '40');
            bg.setAttribute('fill', '#f8fafc');
            bg.setAttribute('stroke', '#e2e8f0');
            bg.setAttribute('stroke-width', '2');
            svg.appendChild(bg);

            // Grid lines (segments 1-7)
            for (let i = 1; i <= 7; i++) {
                const y = 40 + (320 - (i * 45.71));
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', '50');
                line.setAttribute('x2', '450');
                line.setAttribute('y1', y);
                line.setAttribute('y2', y);
                line.setAttribute('stroke', '#e2e8f0');
                line.setAttribute('stroke-width', '1');
                line.setAttribute('stroke-dasharray', '5,5');
                svg.appendChild(line);
                
                // Segment labels
                const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                label.setAttribute('x', '460');
                label.setAttribute('y', y + 5);
                label.setAttribute('fill', '#6b7280');
                label.setAttribute('font-size', '14');
                label.setAttribute('font-weight', '600');
                label.textContent = i;
                svg.appendChild(label);
            }

            // DISC dimensions
            const dimensions = ['D', 'I', 'S', 'C'];
            const colors = ['#dc2626', '#f59e0b', '#10b981', '#3b82f6'];
            const columnWidth = 80;
            const startX = 90;

            dimensions.forEach((dim, index) => {
                const x = startX + (index * 100);
                
                // Column background
                const col = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                col.setAttribute('width', columnWidth);
                col.setAttribute('height', '320');
                col.setAttribute('x', x);
                col.setAttribute('y', '40');
                col.setAttribute('fill', 'white');
                col.setAttribute('stroke', '#e2e8f0');
                svg.appendChild(col);
                
                // Dimension header
                const header = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                header.setAttribute('width', columnWidth);
                header.setAttribute('height', '30');
                header.setAttribute('x', x);
                header.setAttribute('y', '10');
                header.setAttribute('fill', colors[index]);
                svg.appendChild(header);
                
                const headerText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                headerText.setAttribute('x', x + (columnWidth / 2));
                headerText.setAttribute('y', '30');
                headerText.setAttribute('fill', 'white');
                headerText.setAttribute('font-size', '18');
                headerText.setAttribute('font-weight', 'bold');
                headerText.setAttribute('text-anchor', 'middle');
                headerText.textContent = dim;
                svg.appendChild(headerText);
                
                // Score bar
                const segment = discData[dim];
                const barHeight = (segment / 7) * 320;
                const barY = 360 - barHeight;
                
                const bar = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                bar.setAttribute('width', columnWidth - 10);
                bar.setAttribute('height', barHeight);
                bar.setAttribute('x', x + 5);
                bar.setAttribute('y', barY);
                bar.setAttribute('fill', colors[index]);
                bar.setAttribute('opacity', '0.8');
                svg.appendChild(bar);
                
                // Score point
                const pointY = 40 + (320 - ((segment - 1) * 45.71 + 22.86));
                const point = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                point.setAttribute('cx', x + (columnWidth / 2));
                point.setAttribute('cy', pointY);
                point.setAttribute('r', '8');
                point.setAttribute('fill', colors[index]);
                point.setAttribute('stroke', 'white');
                point.setAttribute('stroke-width', '3');
                svg.appendChild(point);
                
                // Percentage text
                const percentText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                percentText.setAttribute('x', x + (columnWidth / 2));
                percentText.setAttribute('y', '380');
                percentText.setAttribute('fill', colors[index]);
                percentText.setAttribute('font-size', '14');
                percentText.setAttribute('font-weight', 'bold');
                percentText.setAttribute('text-anchor', 'middle');
                percentText.textContent = Math.round(percentages[dim]) + '%';
                svg.appendChild(percentText);
            });

            // Connect points with line
            const pathData = dimensions.map((dim, index) => {
                const x = startX + (index * 100) + (columnWidth / 2);
                const segment = discData[dim];
                const y = 40 + (320 - ((segment - 1) * 45.71 + 22.86));
                return index === 0 ? `M ${x} ${y}` : `L ${x} ${y}`;
            }).join(' ');
            
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', pathData);
            path.setAttribute('stroke', '#4f46e5');
            path.setAttribute('stroke-width', '3');
            path.setAttribute('fill', 'none');
            path.setAttribute('opacity', '0.8');
            svg.appendChild(path);

            container.appendChild(svg);
            console.log('DISC Graph created successfully');
        }

        // Initialize DISC graph with delay
        setTimeout(createDiscGraph, 500);
    }
    @endif

    // ============================================
    // MAIN INITIALIZATION
    // ============================================
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== INITIALIZING ALL COMPONENTS ===');
        
        // Initialize core functionality
        initSectionNavigation();
        
        // Initialize charts if data exists
        @if($candidate->kraeplinTestResult)
            initKraeplinChart();
        @endif
        
        @if($candidate->discTestResult)
            initDiscGraph();
        @endif
        
        console.log('=== ALL COMPONENTS INITIALIZED ===');
    });

    // ============================================
    // STATUS UPDATE FUNCTIONALITY
    // ============================================
    
    document.getElementById('statusForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const status = document.getElementById('newStatus').value;
        const notes = document.getElementById('statusNotes').value;
        
        if (!status) {
            alert('Pilih status baru');
            return;
        }
        
        // AJAX call untuk update status
        fetch(`{{ route('candidates.update-status', $candidate->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                status: status,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal update status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    });
</script>

</body>
</html>