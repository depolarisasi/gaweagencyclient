<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::with(['user', 'assignedUser', 'order']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Assigned to filter
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        // Due date filter
        if ($request->filled('due_filter')) {
            switch ($request->due_filter) {
                case 'overdue':
                    $query->where('status', '!=', 'completed')
                          ->where('due_date', '<', now());
                    break;
                case 'this_week':
                    $query->where('status', '!=', 'completed')
                          ->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->where('status', '!=', 'completed')
                          ->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
            }
        }
        
        $projects = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = User::where('role', 'client')->where('status', 'active')->get();
        $staff = User::where('role', 'staff')->where('status', 'active')->get();
        
        return view('admin.projects.create', compact('clients', 'staff'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,active,completed,on_hold,cancelled',
            'description' => 'nullable|string|max:1000',
            'requirements' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Process requirements (convert from textarea to array)
        if ($validated['requirements']) {
            $validated['requirements'] = array_filter(explode("\n", $validated['requirements']));
        }
        
        // Set initial progress
        $validated['progress_percentage'] = 0;
        
        $project = Project::create($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'project' => $project
            ]);
        }
        
        return redirect()->route('admin.projects.index')
                        ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load(['user', 'assignedUser', 'order']);
        
        if (request()->expectsJson()) {
            return response()->json($project);
        }
        
        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $clients = User::where('role', 'client')->where('status', 'active')->get();
        $staff = User::where('role', 'staff')->where('status', 'active')->get();
        
        return view('admin.projects.edit', compact('project', 'clients', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,active,completed,on_hold,cancelled',
            'description' => 'nullable|string|max:1000',
            'requirements' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Process requirements (convert from textarea to array)
        if ($validated['requirements']) {
            $validated['requirements'] = array_filter(explode("\n", $validated['requirements']));
        }
        
        // If status is changed to completed, set completed date
        if ($validated['status'] === 'completed' && $project->status !== 'completed') {
            $validated['completed_date'] = now();
            $validated['progress_percentage'] = 100;
        }
        
        $project->update($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'project' => $project
            ]);
        }
        
        return redirect()->route('admin.projects.index')
                        ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        // Check if project has related data that should prevent deletion
        // For now, we'll allow deletion but could add checks here
        
        $project->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ]);
        }
        
        return redirect()->route('admin.projects.index')
                        ->with('success', 'Project deleted successfully.');
    }
    
    /**
     * Assign project to staff member
     */
    public function assign(Request $request, Project $project)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);
        
        // Verify the user is staff
        $staff = User::findOrFail($request->assigned_to);
        if ($staff->role !== 'staff') {
            return response()->json([
                'success' => false,
                'message' => 'Selected user is not a staff member'
            ], 400);
        }
        
        $project->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'active' // Auto-activate when assigned
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Project assigned successfully'
        ]);
    }
    
    /**
     * Update project progress
     */
    public function updateProgress(Request $request, Project $project)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100'
        ]);
        
        $progress = $request->progress_percentage;
        
        // If progress is 100%, mark as completed
        if ($progress >= 100) {
            $project->update([
                'progress_percentage' => 100,
                'status' => 'completed',
                'completed_date' => now()
            ]);
        } else {
            $project->update([
                'progress_percentage' => $progress
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Progress updated successfully'
        ]);
    }
    
    /**
     * Mark project as completed
     */
    public function complete(Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Project is already completed'
            ], 400);
        }
        
        $project->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_date' => now()
        ]);
        
        // TODO: Send notification to client
        
        return response()->json([
            'success' => true,
            'message' => 'Project marked as completed'
        ]);
    }
    
    /**
     * Put project on hold
     */
    public function hold(Project $project)
    {
        if ($project->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed projects cannot be put on hold'
            ], 400);
        }
        
        $project->update(['status' => 'on_hold']);
        
        return response()->json([
            'success' => true,
            'message' => 'Project put on hold'
        ]);
    }
    
    /**
     * Resume project from hold
     */
    public function resume(Project $project)
    {
        if ($project->status !== 'on_hold') {
            return response()->json([
                'success' => false,
                'message' => 'Project is not on hold'
            ], 400);
        }
        
        $project->update(['status' => 'active']);
        
        return response()->json([
            'success' => true,
            'message' => 'Project resumed'
        ]);
    }
    
    /**
     * Get project statistics
     */
    public function statistics()
    {
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'overdue_projects' => Project::where('status', '!=', 'completed')
                                        ->where('due_date', '<', now())
                                        ->count(),
            'projects_this_month' => Project::whereMonth('created_at', now()->month)->count(),
            'completion_rate' => Project::count() > 0 
                ? round((Project::where('status', 'completed')->count() / Project::count()) * 100, 2)
                : 0,
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:assign,activate,hold,complete,delete',
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id',
            'assigned_to' => 'required_if:action,assign|exists:users,id'
        ]);
        
        $projectIds = $request->project_ids;
        $action = $request->action;
        
        switch ($action) {
            case 'assign':
                Project::whereIn('id', $projectIds)
                       ->update([
                           'assigned_to' => $request->assigned_to,
                           'status' => 'active'
                       ]);
                $message = 'Projects assigned successfully';
                break;
                
            case 'activate':
                Project::whereIn('id', $projectIds)
                       ->where('status', '!=', 'completed')
                       ->update(['status' => 'active']);
                $message = 'Projects activated successfully';
                break;
                
            case 'hold':
                Project::whereIn('id', $projectIds)
                       ->where('status', '!=', 'completed')
                       ->update(['status' => 'on_hold']);
                $message = 'Projects put on hold successfully';
                break;
                
            case 'complete':
                Project::whereIn('id', $projectIds)
                       ->where('status', '!=', 'completed')
                       ->update([
                           'status' => 'completed',
                           'progress_percentage' => 100,
                           'completed_date' => now()
                       ]);
                $message = 'Projects marked as completed successfully';
                break;
                
            case 'delete':
                Project::whereIn('id', $projectIds)->delete();
                $message = 'Projects deleted successfully';
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}