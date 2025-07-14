<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }
        
        return view('auth.login');
    }

    /**
     * Handle login process
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'credential' => 'required|string',
            'password' => 'required|string|min:6',
        ], [
            'credential.required' => 'Username atau email harus diisi',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        // Tentukan field login (email atau username)
        $loginField = filter_var($validated['credential'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $credentials = [
            $loginField => $validated['credential'],
            'password' => $validated['password'],
            'is_active' => true
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return $this->redirectToDashboard(Auth::user());
        }

        return back()
            ->withErrors(['credential' => 'Username/email atau password salah. Pastikan akun Anda aktif.'])
            ->withInput($request->except('password'));
    }

    /**
     * Redirect based on role
     */
    private function redirectToDashboard($user)
    {
        $message = 'Selamat datang, ' . $user->full_name;
        
        return match($user->role) {
            'admin' => redirect()->route('candidates.index')->with('success', $message),
            'hr' => redirect()->route('hr.dashboard')->with('success', $message),
            'interviewer' => redirect()->route('interviewer.dashboard')->with('success', $message),
            default => redirect()->route('candidates.index')->with('success', $message),
        };
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
{
    $userName = Auth::user()->full_name; // Simpan nama sebelum logout
    
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    // Custom message dengan nama user
    return redirect()->route('login')
        ->with('success', "Terima kasih {$userName}, Anda telah berhasil logout.")
        ->with('alert-type', 'info'); // Untuk styling alert yang berbeda
}

    /**
     * Get demo users for development
     */
    public function getDemoUsers()
    {
        if (!app()->environment('local')) {
            abort(403, 'Only available in development');
        }
        
        $users = User::select('username', 'email', 'full_name', 'role')
            ->where('is_active', true)
            ->get()
            ->groupBy('role');
            
        return response()->json([
            'demo_users' => $users,
            'passwords' => [
                'admin' => 'admin123',
                'hr' => 'hr1234', 
                'interviewer' => 'int1234'
            ]
        ]);
    }
}