<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        // Jika sudah login, redirect ke dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login process
     */
    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'credential' => 'required|string',
            'password' => 'required|string|min:6',
        ], [
            'credential.required' => 'Username atau email harus diisi',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        // Tentukan field login (email atau username)
        $loginField = filter_var($request->credential, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $credentials = [
            $loginField => $request->credential,
            'password' => $request->password,
            'is_active' => true  // Hanya user yang aktif
        ];

        // Attempt login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Redirect berdasarkan role
            $user = Auth::user();
            switch ($user->role) {
                case 'admin':
                    return redirect()->intended('/admin/dashboard');
                case 'hr':
                    return redirect()->intended('/hr/dashboard');
                case 'interviewer':
                    return redirect()->intended('/interviewer/dashboard');
                default:
                    return redirect()->intended('/dashboard');
            }
        }

        // Login gagal
        return back()
            ->withErrors(['credential' => 'Username/email atau password salah'])
            ->withInput($request->except('password'));
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Berhasil logout');
    }
}

