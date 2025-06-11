<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .background-animation {
            position: absolute;
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
        }

        @keyframes backgroundMove {
            0% { transform: translateX(0); }
            100% { transform: translateX(20px); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 500px;
            display: flex;
            position: relative;
            z-index: 10;
            margin: 20px;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="rgba(255,255,255,0.1)"/><stop offset="100%" stop-color="rgba(255,255,255,0)"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/></svg>');
            opacity: 0.6;
        }

        .login-left-content {
            position: relative;
            z-index: 2;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            backdrop-filter: blur(10px);
        }

        .company-logo i {
            font-size: 40px;
            color: white;
        }

        .login-left h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .login-left p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .login-header p {
            color: #6b7280;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            background: white;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #4f46e5;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #4f46e5;
        }

        .forgot-password {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #3730a3;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 1.1rem;
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
        }

        .login-btn .btn-text {
            transition: opacity 0.3s ease;
        }

        .login-btn.loading .btn-text {
            opacity: 0;
        }

        .spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-btn.loading .spinner {
            opacity: 1;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
            font-size: 0.9rem;
            display: none;
        }

        .success-message {
            background: #f0fdf4;
            color: #16a34a;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #16a34a;
            font-size: 0.9rem;
            display: none;
        }

        .role-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }

        .role-option {
            flex: 1;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .role-option:hover {
            border-color: #4f46e5;
            background: rgba(79, 70, 229, 0.05);
        }

        .role-option.active {
            border-color: #4f46e5;
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
        }

        .role-option i {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .role-option span {
            font-size: 0.85rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
            }

            .login-left {
                padding: 40px 30px;
                min-height: 200px;
            }

            .login-left h1 {
                font-size: 2rem;
            }

            .login-right {
                padding: 40px 30px;
            }

            .role-selector {
                flex-direction: column;
            }
        }

        .footer-info {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="background-animation"></div>
    
    <div class="login-container">
        <div class="login-left">
            <div class="login-left-content">
                <div class="company-logo">
                    <i class="fas fa-building"></i>
                </div>
                <h1>HR Recruitment</h1>
                <p>Sistem manajemen recruitment modern untuk mengelola kandidat dan proses interview secara efisien</p>
                
                <!-- Demo Accounts Info -->
                <div style="margin-top: 40px; padding: 20px; background: rgba(255, 255, 255, 0.1); border-radius: 12px; backdrop-filter: blur(10px); text-align: left; font-size: 0.9rem;">
                    <h3 style="margin-bottom: 15px; text-align: center;">🔐 Demo Accounts</h3>
                    
                    <div style="margin-bottom: 12px;">
                        <strong>👑 Admin:</strong><br>
                        Username: <code style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px;">admin</code><br>
                        Password: <code style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px;">admin123</code>
                    </div>
                    
                    <div style="margin-bottom: 12px;">
                        <strong>👥 HR Staff:</strong><br>
                        Username: <code style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px;">hr1</code><br>
                        Password: <code style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px;">hr1234</code>
                    </div>
                    
                    <div>
                        <strong>🎯 Interviewer:</strong><br>
                        Username: <code style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px;">interviewer1</code><br>
                        Password: <code style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px;">int1234</code>
                    </div>
                    
                    <p style="margin-top: 15px; font-size: 0.8rem; opacity: 0.8; text-align: center;">
                        💡 Bisa juga login dengan email:<br>
                        admin@company.com, sarah@company.com, michael@company.com
                    </p>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="login-header">
                <h2>Selamat Datang</h2>
                <p>Silakan login untuk mengakses sistem</p>
            </div>

            <div class="error-message" id="errorMessage">
                <i class="fas fa-exclamation-triangle"></i>
                <span id="errorText"></span>
            </div>

            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i>
                <span id="successText"></span>
            </div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="loginType">Pilih Metode Login</label>
                    <div class="role-selector">
                        <div class="role-option active" data-type="username">
                            <i class="fas fa-user"></i>
                            <span>Username</span>
                        </div>
                        <div class="role-option" data-type="email">
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="credential" id="credentialLabel">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon" id="credentialIcon"></i>
                        <input type="text" id="credential" name="credential" placeholder="Masukkan username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                        <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="forgot-password">Lupa password?</a>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <span class="btn-text">Masuk</span>
                    <div class="spinner"></div>
                </button>

                <!-- Quick Login Buttons for Demo -->
                <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" class="quick-login-btn" data-role="admin" style="flex: 1; min-width: 100px; background: #4f46e5; color: white; border: none; padding: 8px 12px; border-radius: 6px; font-size: 0.8rem; cursor: pointer;">
                        👑 Login as Admin
                    </button>
                    <button type="button" class="quick-login-btn" data-role="hr" style="flex: 1; min-width: 100px; background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 6px; font-size: 0.8rem; cursor: pointer;">
                        👥 Login as HR
                    </button>
                    <button type="button" class="quick-login-btn" data-role="interviewer" style="flex: 1; min-width: 100px; background: #8b5cf6; color: white; border: none; padding: 8px 12px; border-radius: 6px; font-size: 0.8rem; cursor: pointer;">
                        🎯 Login as Interviewer
                    </button>
                </div>
            </form>

            <div class="footer-info">
                <p>&copy; 2025 HR Recruitment System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        // Login type switcher
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                
                const type = this.dataset.type;
                const credentialInput = document.getElementById('credential');
                const credentialLabel = document.getElementById('credentialLabel');
                const credentialIcon = document.getElementById('credentialIcon');
                
                if (type === 'email') {
                    credentialInput.type = 'email';
                    credentialInput.placeholder = 'Masukkan email';
                    credentialLabel.textContent = 'Email';
                    credentialIcon.className = 'fas fa-envelope input-icon';
                } else {
                    credentialInput.type = 'text';
                    credentialInput.placeholder = 'Masukkan username';
                    credentialLabel.textContent = 'Username';
                    credentialIcon.className = 'fas fa-user input-icon';
                }
            });
        });

        // Password toggle
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash password-toggle';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye password-toggle';
            }
        });

        // Demo users untuk testing
        const demoUsers = {
            // Admin users
            'admin': { password: 'admin123', role: 'admin', name: 'Super Admin' },
            'admin@company.com': { password: 'admin123', role: 'admin', name: 'Super Admin' },
            
            // HR users  
            'hr1': { password: 'hr1234', role: 'hr', name: 'Sarah Johnson' },
            'sarah@company.com': { password: 'hr1234', role: 'hr', name: 'Sarah Johnson' },
            'hr2': { password: 'hr1234', role: 'hr', name: 'Lisa Wong' },
            'lisa@company.com': { password: 'hr1234', role: 'hr', name: 'Lisa Wong' },
            
            // Interviewer users
            'interviewer1': { password: 'int1234', role: 'interviewer', name: 'Dr. Michael Chen' },
            'michael@company.com': { password: 'int1234', role: 'interviewer', name: 'Dr. Michael Chen' },
            'interviewer2': { password: 'int1234', role: 'interviewer', name: 'John Smith' },
            'john@company.com': { password: 'int1234', role: 'interviewer', name: 'John Smith' }
        };

        // Form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const loginBtn = document.getElementById('loginBtn');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            
            // Hide previous messages
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
            
            // Show loading state
            loginBtn.classList.add('loading');
            
            // Get form data
            const formData = new FormData(this);
            const credential = formData.get('credential').toLowerCase().trim();
            const password = formData.get('password');
            const loginType = document.querySelector('.role-option.active').dataset.type;
            
            // Simulate login process
            setTimeout(() => {
                // Validation
                if (!credential || !password) {
                    showError('Username/email dan password harus diisi');
                    loginBtn.classList.remove('loading');
                    return;
                }
                
                if (password.length < 6) {
                    showError('Password minimal 6 karakter');
                    loginBtn.classList.remove('loading');
                    return;
                }
                
                // Check demo users
                const user = demoUsers[credential];
                if (user && user.password === password) {
                    // Successful login
                    showSuccess(`Login berhasil! Welcome ${user.name}. Redirecting to ${user.role} dashboard...`);
                    
                    // Store user data for demo
                    localStorage.setItem('currentUser', JSON.stringify({
                        username: credential,
                        name: user.name,
                        role: user.role,
                        loginTime: new Date().toISOString()
                    }));
                    
                    setTimeout(() => {
                        // Redirect based on role
                        switch(user.role) {
                            case 'admin':
                                // Simulate opening admin dashboard in new tab
                                console.log('Redirecting to Admin Dashboard...');
                                showInfo('🔴 Opening Admin Dashboard...');
                                break;
                            case 'hr':
                                console.log('Redirecting to HR Dashboard...');
                                showInfo('🟢 Opening HR Dashboard...');
                                break;
                            case 'interviewer':
                                console.log('Redirecting to Interviewer Dashboard...');
                                showInfo('🟣 Opening Interviewer Dashboard...');
                                break;
                            default:
                                showError('Role tidak dikenali');
                        }
                    }, 2000);
                } else {
                    // Login failed
                    showError('Username/email atau password salah!');
                    loginBtn.classList.remove('loading');
                }
            }, 1500);
        });

        function showError(message) {
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            errorText.textContent = message;
            errorMessage.style.display = 'block';
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }

        function showSuccess(message) {
            const successMessage = document.getElementById('successMessage');
            const successText = document.getElementById('successText');
            successText.textContent = message;
            successMessage.style.display = 'block';
        }

        function showInfo(message) {
            const successMessage = document.getElementById('successMessage');
            const successText = document.getElementById('successText');
            successText.textContent = message;
            successMessage.style.display = 'block';
            successMessage.style.background = '#e0f2fe';
            successMessage.style.color = '#0277bd';
            successMessage.style.borderLeftColor = '#0277bd';
        }

        // Quick login buttons
        document.querySelectorAll('.quick-login-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const role = this.dataset.role;
                const credentialInput = document.getElementById('credential');
                const passwordInput = document.getElementById('password');
                
                switch(role) {
                    case 'admin':
                        credentialInput.value = 'admin';
                        passwordInput.value = 'admin123';
                        break;
                    case 'hr':
                        credentialInput.value = 'hr1';
                        passwordInput.value = 'hr1234';
                        break;
                    case 'interviewer':
                        credentialInput.value = 'interviewer1';
                        passwordInput.value = 'int1234';
                        break;
                }
                
                // Auto submit after filling
                setTimeout(() => {
                    document.getElementById('loginForm').dispatchEvent(new Event('submit'));
                }, 500);
            });
        });

        // Add input focus effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.input-wrapper').style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.closest('.input-wrapper').style.transform = 'scale(1)';
            });
        });

        // Add some interactive animations
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            option.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>