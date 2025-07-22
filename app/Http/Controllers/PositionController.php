<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller
{
    /**
     * Display a listing of positions
     */
    public function index(Request $request)
    {
        Gate::authorize('hr-access');
        
        $query = Position::with(['candidates' => function($q) {
            $q->select('id', 'position_id', 'application_status');
        }]);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('position_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        
        // ✅ SIMPLIFIED: Filter by status (hanya aktif atau tutup)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active(); // Gunakan scope active yang sudah diperbaiki
            } elseif ($request->status === 'closed') {
                $query->closed(); // Gunakan scope closed yang sudah diperbaiki
            }
        }
        
        $positions = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Calculate candidate counts for each position
        $positions->getCollection()->transform(function ($position) {
            $position->total_applications_count = $position->getTotalApplicationsCount();
            $position->active_applications_count = $position->getActiveApplicationsCount();
            return $position;
        });
        
        return view('positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new position
     */
    public function create()
    {
        Gate::authorize('hr-access');
        
        $departments = Position::getDepartments();
        $locations = Position::getLocations();
        $employmentTypes = Position::getEmploymentTypes();
        
        return view('positions.create', compact('departments', 'locations', 'employmentTypes'));
    }

    /**
     * Store a newly created position
     */
    public function store(Request $request)
    {
        Gate::authorize('hr-access');
        
        $validated = $request->validate([
            'position_name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0|gte:salary_range_min',
            'location' => 'nullable|string|max:255',
            'employment_type' => 'required|in:full-time,part-time,contract,internship',
            'posted_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after:posted_date',
            'is_active' => 'boolean'
        ]);
        
        try {
            $position = Position::create($validated);
            
            return redirect()->route('positions.index')
                ->with('success', 'Posisi berhasil dibuat');
                
        } catch (\Exception $e) {
            Log::error('Error creating position', [
                'data' => $validated,
                'error' => $e->getMessage()
            ]);
            
            return back()->withInput()
                ->with('error', 'Gagal membuat posisi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified position
     */
    public function show(Position $position)
    {
        Gate::authorize('hr-access');
        
        $position->load([
            'candidates' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);
        
        return view('positions.show', compact('position'));
    }

    /**
     * Show the form for editing the position
     */
    public function edit(Position $position)
    {
        Gate::authorize('hr-access');
        
        $departments = Position::getDepartments();
        $locations = Position::getLocations();
        $employmentTypes = Position::getEmploymentTypes();
        
        // Get accurate candidate counts
        $totalCandidates = $position->getTotalApplicationsCount();
        $activeCandidates = $position->getActiveApplicationsCount();
        $hasActiveCandidates = $activeCandidates > 0;
        
        return view('positions.edit', compact(
            'position', 'departments', 'locations', 'employmentTypes', 
            'hasActiveCandidates', 'totalCandidates', 'activeCandidates'
        ));
    }

    /**
     * Update the specified position
     */
    public function update(Request $request, Position $position)
    {
        Gate::authorize('hr-access');
        
        $validated = $request->validate([
            'position_name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0|gte:salary_range_min',
            'location' => 'nullable|string|max:255',
            'employment_type' => 'required|in:full-time,part-time,contract,internship',
            'posted_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after:posted_date',
            'is_active' => 'boolean'
        ]);
        
        try {
            // Use safe update with change tracking
            $position->safeUpdate($validated, true);
            
            return redirect()->route('positions.index')
                ->with('success', 'Posisi berhasil diperbarui');
                
        } catch (\Exception $e) {
            Log::error('Error updating position', [
                'position_id' => $position->id,
                'data' => $validated,
                'error' => $e->getMessage()
            ]);
            
            return back()->withInput()
                ->with('error', 'Gagal memperbarui posisi: ' . $e->getMessage());
        }
    }

    /**
     * Enhanced DELETE - dengan validasi lengkap dan opsi transfer
     */
    public function destroy(Request $request, Position $position)
    {
        Gate::authorize('hr-access');
        
        try {
            // Get detailed candidate information
            $candidateCount = $position->getTotalApplicationsCount();
            $activeCount = $position->getActiveApplicationsCount();
            
            // Check if position can be safely deleted
            if (!$position->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'canDelete' => false,
                    'message' => "Tidak dapat menghapus posisi '{$position->position_name}'",
                    'details' => [
                        'total_candidates' => $candidateCount,
                        'active_candidates' => $activeCount,
                        'position_info' => [
                            'name' => $position->position_name,
                            'department' => $position->department,
                            'status' => $position->detailed_status
                        ],
                        'options' => [
                            'transfer' => 'Transfer kandidat ke posisi lain',
                            'close' => 'Tutup posisi (kandidat tetap ada)',
                            'force' => 'Hapus paksa (tidak disarankan)'
                        ]
                    ],
                    // Enhanced transfer options
                    'transferable_positions' => Position::getTransferablePositions($position->id)
                        ->map(function($pos) {
                            return [
                                'id' => $pos->id,
                                'name' => $pos->position_name,
                                'department' => $pos->department,
                                'location' => $pos->location,
                                'employment_type' => $pos->employment_type_label,
                                'total_candidates' => $pos->getTotalApplicationsCount()
                            ];
                        })
                ], 400);
            }
            
            // Safe delete (soft delete)
            $position->safeDelete();
            
            return response()->json([
                'success' => true,
                'message' => "Posisi '{$position->position_name}' berhasil dihapus"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting position', [
                'position_id' => $position->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus posisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer candidates before delete
     */
    public function transferCandidates(Request $request, Position $position)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'new_position_id' => 'required|exists:positions,id',
            'reason' => 'nullable|string|max:500'
        ]);
        
        try {
            $result = $position->transferCandidatesAndDelete(
                $request->new_position_id, 
                $request->reason
            );
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'transferred_count' => $result['transferred_count']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error transferring candidates', [
                'position_id' => $position->id,
                'new_position_id' => $request->new_position_id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal transfer kandidat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ SIMPLIFIED: Close Position (menggantikan deactivate)
     */
    public function close(Request $request, Position $position)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'reason' => 'nullable|string|max:500',
            'set_closing_date' => 'boolean'
        ]);
        
        try {
            $position->closePosition(
                $request->reason, 
                $request->boolean('set_closing_date', false)
            );
            
            return response()->json([
                'success' => true,
                'message' => "Posisi '{$position->position_name}' berhasil ditutup"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error closing position', [
                'position_id' => $position->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup posisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ SIMPLIFIED: Open Position (menggantikan activate)
     */
    public function open(Request $request, Position $position)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'reason' => 'nullable|string|max:500',
            'closing_date' => 'nullable|date|after:now'
        ]);
        
        try {
            $position->openPosition(
                $request->reason, 
                $request->closing_date
            );
            
            return response()->json([
                'success' => true,
                'message' => "Posisi '{$position->position_name}' berhasil dibuka"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error opening position', [
                'position_id' => $position->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuka posisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ SIMPLIFIED: TOGGLE STATUS - hanya antara AKTIF dan TUTUP
     */
    public function toggleStatus(Request $request, Position $position)
    {
        Gate::authorize('hr-access');
        
        try {
            $activeCount = $position->getActiveApplicationsCount();
            $totalCount = $position->getTotalApplicationsCount();
            $currentStatus = $position->detailed_status;
            $willClose = ($currentStatus === 'aktif'); // Jika aktif, akan ditutup
            
            // Jika akan menutup dan ada kandidat aktif, minta konfirmasi
            if ($willClose && $activeCount > 0) {
                return response()->json([
                    'success' => false,
                    'requiresConfirmation' => true,
                    'message' => "Posisi ini memiliki {$activeCount} kandidat yang sedang dalam proses. Yakin ingin menutup posisi?",
                    'details' => [
                        'current_status' => $currentStatus,
                        'active_candidates' => $activeCount,
                        'total_candidates' => $totalCount,
                        'action' => $willClose ? 'close' : 'open',
                        'position_name' => $position->position_name
                    ]
                ], 400);
            }
            
            // Lakukan toggle
            if ($willClose) {
                $position->closePosition($request->reason);
                $newStatus = 'tutup';
                $message = "Posisi '{$position->position_name}' berhasil ditutup";
            } else {
                $position->openPosition($request->reason, $request->closing_date);
                $newStatus = 'aktif';
                $message = "Posisi '{$position->position_name}' berhasil dibuka";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'new_status' => $newStatus,
                'position_info' => [
                    'id' => $position->id,
                    'name' => $position->position_name,
                    'is_active' => $position->fresh()->is_active,
                    'detailed_status' => $position->fresh()->detailed_status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error toggling position status', [
                'position_id' => $position->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status posisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore from soft delete
     */
    public function restore($id)
    {
        Gate::authorize('hr-access');
        
        try {
            $position = Position::onlyTrashed()->findOrFail($id);
            $position->restore();
            
            return response()->json([
                'success' => true,
                'message' => "Posisi '{$position->position_name}' berhasil dipulihkan"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error restoring position', [
                'position_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan posisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get position statistics
     */
    public function statistics(Position $position)
    {
        Gate::authorize('hr-access');
        
        $stats = [
            'total_applications' => $position->getTotalApplicationsCount(),
            'active_applications' => $position->getActiveApplicationsCount(),
            'position_status' => $position->detailed_status,
            'status_breakdown' => $position->candidates()
                ->selectRaw('application_status, COUNT(*) as count')
                ->groupBy('application_status')
                ->pluck('count', 'application_status')
                ->toArray(),
            'monthly_applications' => $position->candidates()
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get()
        ];
        
        return response()->json($stats);
    }

    /**
     * Display trashed positions
     */
    public function trashed(Request $request)
    {
        Gate::authorize('hr-access');
        
        $query = Position::onlyTrashed()->with(['candidates' => function($q) {
            $q->select('id', 'position_id', 'application_status');
        }]);
        
        // Search functionality untuk posisi terhapus
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('position_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        $positions = $query->orderBy('deleted_at', 'desc')->paginate(15);
        
        // Add application counts untuk setiap posisi
        $positions->getCollection()->transform(function ($position) {
            $position->total_applications_count = $position->getTotalApplicationsCount();
            $position->active_applications_count = $position->getActiveApplicationsCount();
            return $position;
        });
        
        return view('positions.trashed', compact('positions'));
    }

    /**
     * Force delete a position permanently
     */
    public function forceDelete($id)
    {
        Gate::authorize('hr-access');
        
        try {
            $position = Position::onlyTrashed()->findOrFail($id);
            
            // Check if there are still candidates
            $candidateCount = $position->candidates()->count();
            if ($candidateCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak dapat menghapus permanen. Posisi masih memiliki {$candidateCount} kandidat terkait."
                ], 400);
            }
            
            // Force delete
            $positionName = $position->position_name;
            $position->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => "Posisi '{$positionName}' berhasil dihapus permanen"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error force deleting position', [
                'position_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus posisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Auto-close expired positions (untuk dijadwalkan)
     */
    public function autoCloseExpired()
    {
        Gate::authorize('hr-access');
        
        try {
            $closedCount = Position::autoCloseExpiredPositions();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully auto-closed {$closedCount} expired positions",
                'closed_count' => $closedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error auto-closing expired positions', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup posisi yang kedaluwarsa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display active positions for public viewing
     * Dapat digunakan untuk career page atau job board
     */
    public function publicActivePositions(Request $request)
    {
        // Query untuk mengambil posisi yang aktif
        $query = Position::active() // Menggunakan scope active yang sudah ada
                        ->select([
                            'id',
                            'position_name', 
                            'department',
                            'location',
                            'employment_type',
                            'salary_range_min',
                            'salary_range_max',
                            'description',
                            'posted_date',
                            'closing_date',
                            'created_at'
                        ]);
        
        // Search functionality (optional)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('position_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        // Filter by department (optional)
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        
        // Filter by location (optional)
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }
        
        // Filter by employment type (optional)
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }
        
        // Urutkan berdasarkan tanggal posting terbaru
        $positions = $query->orderBy('posted_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(12);
        
        // Get filter options untuk dropdown
        $departments = Position::active()
                              ->select('department')
                              ->distinct()
                              ->orderBy('department')
                              ->pluck('department');
        
        $locations = Position::active()
                            ->select('location')
                            ->distinct()
                            ->whereNotNull('location')
                            ->orderBy('location')
                            ->pluck('location');
        
        $employmentTypes = Position::getEmploymentTypes();
        
        // Return view dengan data
        return view('active-positions', compact(
            'positions', 
            'departments', 
            'locations', 
            'employmentTypes'
        ));
    }

    /**
     * Show single active position detail for public
     */
    public function publicPositionDetail($id)
    {
        $position = Position::active()
                           ->findOrFail($id);
        
        return view('position-detail', compact('position'));
    }

}