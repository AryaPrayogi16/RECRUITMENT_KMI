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
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'open') {
                $query->open();
            }
        }
        
        $positions = $query->orderBy('created_at', 'desc')->paginate(15);
        
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
        
        // Check for potential issues
        $hasActiveCandidates = $position->getActiveApplicationsCount() > 0;
        
        return view('positions.edit', compact(
            'position', 'departments', 'locations', 'employmentTypes', 'hasActiveCandidates'
        ));
    }

    /**
     * ✅ SAFE UPDATE - dengan change tracking
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
            
            return redirect()->route('positions.index', $position)
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
     * ✅ SAFE DELETE - dengan validasi dan opsi transfer
     */
    public function destroy(Request $request, Position $position)
    {
        Gate::authorize('hr-access');
        
        try {
            // Check if position can be safely deleted
            if (!$position->canBeDeleted()) {
                $candidateCount = $position->getTotalApplicationsCount();
                $activeCount = $position->getActiveApplicationsCount();
                
                return response()->json([
                    'success' => false,
                    'canDelete' => false,
                    'message' => "Tidak dapat menghapus posisi '{$position->position_name}'",
                    'details' => [
                        'total_candidates' => $candidateCount,
                        'active_candidates' => $activeCount,
                        'options' => [
                            'transfer' => 'Transfer kandidat ke posisi lain',
                            'close' => 'Tutup posisi (set tidak aktif)',
                            'force' => 'Hapus paksa (tidak disarankan)'
                        ]
                    ],
                    'transferable_positions' => Position::getTransferablePositions($position->id)
                        ->map(function($pos) {
                            return [
                                'id' => $pos->id,
                                'name' => $pos->position_name,
                                'department' => $pos->department
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
     * ✅ TRANSFER CANDIDATES - sebelum delete
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
     * ✅ CLOSE POSITION - alternatif delete
     */
    public function close(Request $request, Position $position)
    {
        Gate::authorize('hr-access');
        
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);
        
        try {
            $position->closePosition($request->reason);
            
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
     * ✅ RESTORE - dari soft delete
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
}