<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a href="{{ route('dashboard') }}">
                <i class="fas fa-building"></i>
                {{ config('app.name', 'HR System') }}
            </a>
        </div>
        
        <div class="navbar-menu">
            @auth
                <div class="navbar-user">
                    <span class="user-welcome">Welcome, {{ Auth::user()->full_name }}</span>
                    <span class="user-role-badge">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
                
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-button">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            @endauth
        </div>
    </div>
</nav>

<style>
.navbar {
    background: #ffffff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 60px;
}

.navbar-brand a {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #1a202c;
    font-size: 1.3rem;
    font-weight: 600;
}

.navbar-brand i {
    color: #4f46e5;
}

.navbar-menu {
    display: flex;
    align-items: center;
    gap: 20px;
}

.navbar-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-welcome {
    color: #4a5568;
    font-size: 0.9rem;
}

.user-role-badge {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.logout-form {
    margin: 0;
}

.logout-button {
    display: flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.logout-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}

@media (max-width: 768px) {
    .navbar-container {
        padding: 0 15px;
    }
    
    .user-welcome {
        display: none;
    }
    
    .logout-button span {
        display: none;
    }
}
</style>
