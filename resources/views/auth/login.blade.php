<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Sistem Recruitment</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #66ea66 0%, #4ba28f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%), 
                        linear-gradient(-45deg, rgba(255,255,255,0.1) 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.1) 75%), 
                        linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.1) 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            animation: backgroundMove 20s linear infinite;
            opacity: 0.3;
            z-index: 0;
        }

        @keyframes backgroundMove {
            0% { transform: translateX(0); }
            100% { transform: translateX(20px); }
        }

        /* Main container with better responsive handling */
        .main-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
            position: relative;
            z-index: 10;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #00ff00 0%, #00a581 100%);
            color: white;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            min-height: 400px;
        }

        .company-logo {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
            overflow: hidden;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .company-logo::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.3);
            z-index: 1;
        }

        .company-logo i {
            font-size: 40px;
            color: white;
        }

        .login-left h1 {
            font-size: 2rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .login-left p {
            font-size: 1rem;
            opacity: 0.9;
            line-height: 1.5;
        }

        .login-right {
            flex: 1;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            max-height: 100vh;
            overflow-y: auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .login-header h2 {
            color: #1f2937;
            font-size: 1.75rem;
            margin-bottom: 3px;
            font-weight: 600;
        }

        .login-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px 12px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            background: white;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-group input.error {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.85rem;
            flex-wrap: wrap;
            gap: 10px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #4f46e5;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .error-message,
        .success-message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
            border: 1px solid #fecaca;
        }

        .success-message {
            background: #f0fdf4;
            color: #16a34a;
            border-left: 4px solid #16a34a;
            border: 1px solid #bbf7d0;
        }

        .info-message {
            background: #eff6ff;
            color: #2563eb;
            border-left: 4px solid #2563eb;
            border: 1px solid #bfdbfe;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .demo-accounts {
            margin-top: 20px;
            padding: 16px;
            background: rgba(79, 70, 229, 0.1);
            border-radius: 10px;
            font-size: 0.85rem;
        }

        .demo-accounts h4 {
            margin-bottom: 12px;
            color: #4f46e5;
            text-align: center;
            font-size: 1rem;
        }

        .demo-account {
            margin-bottom: 8px;
            padding: 6px 10px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
        }

        .quick-login-btns {
            margin-top: 16px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .quick-login-btn {
            flex: 1;
            min-width: 100px;
            padding: 8px 10px;
            border: none;
            border-radius: 6px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            font-weight: 500;
        }

        .quick-login-btn.admin { background: #4f46e5; }
        .quick-login-btn.hr { background: #10b981; }
        .quick-login-btn.interviewer { background: #8b5cf6; }

        .quick-login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .footer-info {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.75rem;
        }

        /* Enhanced Mobile Responsiveness */
        @media (max-width: 768px) {
            .main-wrapper {
                padding: 10px;
            }

            .login-container {
                flex-direction: column;
                max-width: 100%;
                border-radius: 16px;
            }

            .login-left {
                padding: 30px 20px;
                min-height: auto;
            }

            .company-logo {
                width: 100px;
                height: 100px;
                margin-bottom: 15px;
                border: 2px solid rgba(255, 255, 255, 0.3);
            }
            
            .company-logo::after {
                content: '';
                position: absolute;
                top: 6px;
                left: 6px;
                right: 6px;
                bottom: 6px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                z-index: 0;
            }
            
            .company-logo > div,
            .company-logo img,
            .company-logo svg {
                position: relative;
                z-index: 2;
                max-width: 80%;
                max-height: 80%;
            }

            .login-left h1 {
                font-size: 1.5rem;
                margin-bottom: 10px;
            }

            .login-left p {
                font-size: 0.9rem;
            }

            .login-right {
                padding: 30px 20px;
            }

            .login-header h2 {
                font-size: 1.5rem;
            }

            .login-header p {
                font-size: 0.85rem;
            }

            .form-group {
                margin-bottom: 16px;
            }

            .form-group input {
                padding: 10px 14px 10px 40px;
                font-size: 0.9rem;
            }

            .quick-login-btns {
                gap: 6px;
            }

            .quick-login-btn {
                font-size: 0.7rem;
                padding: 6px 8px;
            }

            .demo-accounts {
                padding: 12px;
            }

            .demo-account {
                flex-direction: column;
                gap: 4px;
                text-align: center;
            }
        }

        /* Extra small devices */
        @media (max-width: 480px) {
            .login-left p {
                font-size: 0.8rem;
                line-height: 1.3;
                display: block;
            }

            .form-options {
                font-size: 0.8rem;
            }

            .remember-me span {
                font-size: 0.8rem;
            }
            
            .company-logo {
                width: 90px;
                height: 90px;
                padding: 0;
                margin-bottom: 15px;
                position: relative;
                overflow: visible;
            }
            
            .company-logo::after {
                content: '';
                position: absolute;
                top: 5px;
                left: 5px;
                right: 5px;
                bottom: 5px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                z-index: 0;
            }
            
            .company-logo img,
            .company-logo svg,
            .company-logo > div {
                position: relative;
                z-index: 2;
                max-width: 80%;
                max-height: 80%;
                width: auto;
                height: auto;
                object-fit: contain;
            }
        }

        @media (min-height: 900px) {
            .login-container {
                min-height: 500px;
            }
        }

        @media (max-height: 600px) and (orientation: landscape) {
            .main-wrapper {
                padding: 10px;
                min-height: auto;
                height: auto;
            }

            .login-left {
                min-height: auto;
                padding: 20px;
            }

            .login-right {
                padding: 20px;
            }

            .company-logo {
                width: 60px;
                height: 60px;
            }

            .demo-accounts {
                margin-top: 10px;
                padding: 10px;
            }
        }
        
        @media (max-width: 360px) {
            .login-container {
                border-radius: 12px;
            }
            
            .login-left, .login-right {
                padding: 20px 15px;
            }
            
            .company-logo {
                width: 80px;
                height: 80px;
                margin-bottom: 12px;
            }
            
            .company-logo::after {
                top: 4px;
                left: 4px;
                right: 4px;
                bottom: 4px;
            }
            
            .company-logo img,
            .company-logo svg,
            .company-logo > div {
                max-width: 75%;
                max-height: 75%;
            }
            
            .login-left h1 {
                font-size: 1.3rem;
            }
            
            .login-header h2 {
                font-size: 1.3rem;
            }
            
            .form-group input {
                padding: 8px 12px 8px 36px;
            }
            
            .input-icon {
                left: 12px;
            }
            
            .quick-login-btns {
                flex-direction: column;
            }
            
            .quick-login-btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="background-animation"></div>
    
    <div class="main-wrapper">
        <div class="login-container">
            <div class="login-left">
                <div class="company-logo">
                    <i class="fas fa-building"></i>
                </div>
                <h1>HR Recruitment</h1>
                <p>Sistem manajemen recruitment modern untuk mengelola kandidat dan proses interview secara efisien</p>
            </div>

            <div class="login-right">
                <div class="login-header">
                    <h2>Selamat Datang</h2>
                    <p>Silakan login untuk mengakses sistem</p>
                </div>

                <!-- Display Error Messages -->
                @if ($errors->any())
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Display Success Messages -->
                @if (session('success'))
                    <div class="success-message {{ session('alert-type') === 'info' ? 'info-message' : '' }}">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Display Info Messages -->
                @if (session('info'))
                    <div class="info-message">
                        <i class="fas fa-info-circle"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="credential">Username atau Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="credential" name="credential" 
                                   placeholder="Masukkan username atau email" 
                                   value="{{ old('credential') }}" 
                                   class="{{ $errors->has('credential') ? 'error' : '' }}"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" 
                                   placeholder="Masukkan password" 
                                   class="{{ $errors->has('password') ? 'error' : '' }}"
                                   required>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Ingat saya</span>
                        </label>
                        <a href="#" style="color: #4f46e5; text-decoration: none;">Lupa password?</a>
                    </div>

                    <button type="submit" class="login-btn" id="loginBtn">
                        <span class="btn-text">Masuk</span>
                    </button>

                    <!-- Quick Login Buttons for Demo -->
                    <!-- <div class="quick-login-btns">
                        <button type="button" class="quick-login-btn admin" onclick="fillCredentials('admin@pawindo.com', 'admin123')">
                            👑 Admin
                        </button>
                        <button type="button" class="quick-login-btn hr" onclick="fillCredentials('hr@pawindo.com', 'hr123')">
                            👥 HR
                        </button>
                        <button type="button" class="quick-login-btn interviewer" onclick="fillCredentials('interviewer@pawindo.com', 'int123')">
                            🎯 Interviewer
                        </button>
                    </div> -->
                </form>

                <div class="footer-info">
                    <p>&copy; 2025 PT. Kayu Mebel Indonesia. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fill credentials for quick login
        function fillCredentials(email, password) {
            document.getElementById('credential').value = email;
            document.getElementById('password').value = password;
        }

        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const btnText = loginBtn.querySelector('.btn-text');
            
            // Show loading state
            loginBtn.classList.add('loading');
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Masuk...';
        });

        // Input focus effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.input-wrapper').style.transform = 'scale(1.02)';
                // Remove error class when user starts typing
                if (this.classList.contains('error')) {
                    this.classList.remove('error');
                }
            });
            
            input.addEventListener('blur', function() {
                this.closest('.input-wrapper').style.transform = 'scale(1)';
            });
        });

        // Quick login button effects
        document.querySelectorAll('.quick-login-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Auto-hide success messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successMessages = document.querySelectorAll('.success-message, .info-message');
            successMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        message.remove();
                    }, 300);
                }, 5000);
            });
        });

        // Adjust layout based on viewport height
        function adjustLayout() {
            const vh = window.innerHeight;
            const mainWrapper = document.querySelector('.main-wrapper');
            
            if (vh < 600) {
                mainWrapper.style.alignItems = 'flex-start';
                mainWrapper.style.paddingTop = '10px';
                mainWrapper.style.paddingBottom = '10px';
            } else {
                mainWrapper.style.alignItems = 'center';
                mainWrapper.style.paddingTop = '20px';
                mainWrapper.style.paddingBottom = '20px';
            }
        }

        // Check for very small screens and adjust quick login buttons
        function adjustForSmallScreens() {
            const width = window.innerWidth;
            const quickLoginBtns = document.querySelector('.quick-login-btns');
            
            if (width <= 360) {
                quickLoginBtns.style.flexDirection = 'column';
            } else {
                quickLoginBtns.style.flexDirection = 'row';
            }
        }
        
        // Ensure logo is perfectly circular on all devices
        function enhanceLogoShape() {
            const logo = document.querySelector('.company-logo');
            const logoWidth = logo.offsetWidth;
            
            // Ensure perfect circle
            logo.style.height = logoWidth + 'px';
            
            // Apply border and shadow for enhanced appearance
            if (window.innerWidth <= 480) {
                logo.style.border = '2px solid rgba(255, 255, 255, 0.3)';
                logo.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.1)';
            }
        }

        // Call on load and resize
        window.addEventListener('load', function() {
            adjustLayout();
            adjustForSmallScreens();
            enhanceLogoShape();
        });
        
        window.addEventListener('resize', function() {
            adjustLayout();
            adjustForSmallScreens();
            enhanceLogoShape();
        });
        
        // Fix for iOS Safari viewport height issues
        function setVH() {
            let vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
        
        window.addEventListener('resize', setVH);
        setVH();
    </script>
</body>
</html>