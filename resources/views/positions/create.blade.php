<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tambah Posisi Baru - HR System</title>
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

        .content {
            padding: 30px;
        }

        /* Form Styles */
        .form-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
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
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
            background: #4f46e5;
            border-color: #4f46e5;
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
                            <span>Tambah Baru</span>
                        </div>
                        <h1 class="page-title">Tambah Posisi Baru</h1>
                    </div>
                </div>
                <div class="header-right">
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

                <!-- Form -->
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Informasi Posisi</h2>
                        <p class="form-subtitle">Lengkapi informasi untuk posisi baru yang akan dibuka</p>
                    </div>

                    <form method="POST" action="{{ route('positions.store') }}" id="positionForm">
                        @csrf
                        
                        <div class="form-body">
                            <!-- Basic Information -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label required">Nama Posisi</label>
                                    <input type="text" name="position_name" class="form-input" 
                                           value="{{ old('position_name') }}" 
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
                                           value="{{ old('department') }}" 
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
                                           value="{{ old('location') }}" 
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
                                            <option value="{{ $key }}" {{ old('employment_type') == $key ? 'selected' : '' }}>
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
                                               value="{{ old('salary_range_min') }}" 
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
                                               value="{{ old('salary_range_max') }}" 
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
                                          placeholder="Deskripsikan tugas, tanggung jawab, dan lingkup pekerjaan...">{{ old('description') }}</textarea>
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
                                          placeholder="Sebutkan persyaratan pendidikan, pengalaman, keahlian yang dibutuhkan...">{{ old('requirements') }}</textarea>
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
                                           value="{{ old('posted_date', date('Y-m-d')) }}">
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
                                           value="{{ old('closing_date') }}">
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
                                           id="is_active" {{ old('is_active', '1') ? 'checked' : '' }}>
                                    <label for="is_active" class="checkbox-label">
                                        Aktifkan posisi ini (dapat menerima aplikasi)
                                    </label>
                                </div>
                                <div class="form-help">Centang untuk langsung mengaktifkan posisi setelah dibuat</div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="form-actions-left">
                                <i class="fas fa-info-circle"></i>
                                Semua field yang wajib diisi ditandai dengan *
                            </div>
                            <div class="form-actions-right">
                                <a href="{{ route('positions.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="fas fa-save"></i>
                                    Simpan Posisi
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

        // Form auto-save to localStorage (draft)
        const formElements = form.querySelectorAll('input, select, textarea');
        const storageKey = 'position_form_draft';

        // Load draft on page load
        window.addEventListener('load', function() {
            const draft = localStorage.getItem(storageKey);
            if (draft) {
                try {
                    const data = JSON.parse(draft);
                    Object.keys(data).forEach(key => {
                        const element = form.querySelector(`[name="${key}"]`);
                        if (element && !element.value) { // Only load if field is empty
                            if (element.type === 'checkbox') {
                                element.checked = data[key];
                            } else {
                                element.value = data[key];
                            }
                        }
                    });
                } catch (e) {
                    console.log('Error loading draft:', e);
                }
            }
        });

        // Save draft on input
        formElements.forEach(element => {
            element.addEventListener('input', function() {
                const formData = new FormData(form);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                localStorage.setItem(storageKey, JSON.stringify(data));
            });
        });

        // Clear draft on successful submission
        form.addEventListener('submit', function() {
            setTimeout(() => {
                localStorage.removeItem(storageKey);
            }, 1000);
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