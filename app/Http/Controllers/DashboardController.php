<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\{Candidate, Position, Interview, User, ApplicationLog};
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'hr' => redirect()->route('hr.dashboard'),
            'interviewer' => redirect()->route('interviewer.dashboard'),
            default => view('dashboard.index', compact('user')),
        };
    }

    public function admin()
    {
        Gate::authorize('admin-access');
        
        $stats = [
            'total_candidates' => Candidate::count(),
            'total_users' => User::where('is_active', true)->count(),
            'open_positions' => Position::where('is_active', true)->count(),
            'interviews_today' => Interview::whereDate('interview_date', today())->count(),
            'users_by_role' => User::where('is_active', true)
                ->selectRaw('role, count(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),
            'candidates_by_status' => Candidate::selectRaw('application_status, count(*) as total')
                ->groupBy('application_status')
                ->pluck('total', 'application_status'),
        ];
        
        $recent_activities = ApplicationLog::with(['candidate', 'user'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('dashboard.admin', compact('stats', 'recent_activities'));
    }

    public function hr()
    {
        Gate::authorize('hr-access');
        
        $today = Carbon::today();
        
        $stats = [
            'new_applications' => Candidate::where('application_status', 'pending')->count(),
            'pending_review' => Candidate::where('application_status', 'reviewing')->count(),
            'interviews_today' => Interview::whereDate('interview_date', $today)->count(),
            'offers_pending' => Candidate::where('application_status', 'accepted')->count(),
        ];
        
        $priority_tasks = [
            'candidates_to_review' => Candidate::where('application_status', 'pending')
                ->with('personalData')
                ->take(5)
                ->get(),
            'interviews_to_schedule' => Candidate::where('application_status', 'reviewing')
                ->with('personalData')
                ->take(3)
                ->get(),
        ];
        
        $todays_schedule = Interview::with(['candidate.personalData', 'interviewer'])
            ->whereDate('interview_date', $today)
            ->orderBy('interview_time')
            ->get();
        
        return view('dashboard.hr', compact('stats', 'priority_tasks', 'todays_schedule'));
    }

    public function interviewer()
    {
        Gate::authorize('interviewer-access');
        
        $user = Auth::user();
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        
        $stats = [
            'interviews_today' => Interview::where('interviewer_id', $user->id)
                ->whereDate('interview_date', $today)
                ->count(),
            'interviews_week' => Interview::where('interviewer_id', $user->id)
                ->whereBetween('interview_date', [
                    $thisWeek, 
                    $thisWeek->copy()->endOfWeek()
                ])
                ->count(),
            'interviews_completed' => Interview::where('interviewer_id', $user->id)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->count(),
            // Fixed: Changed from 'score' to 'feedback' or 'notes' field
            'pending_feedback' => Interview::where('interviewer_id', $user->id)
                ->where('status', 'completed')
                ->where(function($query) {
                    $query->whereNull('feedback')
                          ->orWhere('feedback', '')
                          ->orWhereNull('notes')
                          ->orWhere('notes', '');
                })
                ->count(),
        ];
        
        $todays_interviews = Interview::with(['candidate.personalData'])
            ->where('interviewer_id', $user->id)
            ->whereDate('interview_date', $today)
            ->orderBy('interview_time')
            ->get();
        
        // Fixed: Changed from 'score' to 'feedback' field
        $pending_feedback = Interview::with(['candidate.personalData'])
            ->where('interviewer_id', $user->id)
            ->where('status', 'completed')
            ->where(function($query) {
                $query->whereNull('feedback')
                      ->orWhere('feedback', '')
                      ->orWhereNull('notes')
                      ->orWhere('notes', '');
            })
            ->latest()
            ->take(5)
            ->get();
        
        return view('dashboard.interviewer', compact('stats', 'todays_interviews', 'pending_feedback'));
    }
}