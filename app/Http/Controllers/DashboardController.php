<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Redirect berdasarkan role
        switch ($user->role) {
            case 'admin':
                return $this->admin();
            case 'hr':
                return $this->hr();
            case 'interviewer':
                return $this->interviewer();
            default:
                return view('dashboard.index', compact('user'));
        }
    }

    public function admin()
    {
        // Logic untuk admin dashboard
        return view('dashboard.admin');
    }

    public function hr()
    {
        // Logic untuk HR dashboard
        return view('dashboard.hr');
    }

    public function interviewer()
    {
        // Logic untuk interviewer dashboard
        return view('dashboard.interviewer');
    }
}