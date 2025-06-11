<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Sistem Recruitment</title>
    <!-- CSS dari artifact sebelumnya -->
</head>
<body>
    <!-- HTML dari artifact sebelumnya, tapi dengan beberapa modifikasi: -->
    
    <div class="login-container">
        <div class="login-left">
            <!-- Content sama seperti sebelumnya -->
        </div>

        <div class="login-right">
            <div class="login-header">
                <h2>Selamat Datang</h2>
                <p>Silakan login untuk mengakses sistem</p>
            </div>

            <!-- Display Laravel validation errors -->
            @if ($errors->any())
                <div class="error-message" style="display: block;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Display success message -->
            @if (session('success'))
                <div class="success-message" style="display: block;">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
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
                        <input type="text" id="credential" name="credential" 
                               placeholder="Masukkan username" 
                               value="{{ old('credential') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" 
                               placeholder="Masukkan password" required>
                        <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" id="remember" name="remember" 
                               {{ old('remember') ? 'checked' : '' }}>
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="forgot-password">Lupa password?</a>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <span class="btn-text">Masuk</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <div class="footer-info">
                <p>&copy; 2025 HR Recruitment System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- JavaScript sama seperti sebelumnya -->
</body>
</html>