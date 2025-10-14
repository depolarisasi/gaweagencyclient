<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }
        
        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff,client',
            'status' => 'required|in:active,inactive',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'company_name' => 'nullable|string|max:255',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);
        
        if ($user) {
          alert()->success('Success', 'User created successfully.');
        } else {
         alert()->error('Error', 'Failed to create user.');
        } 
        return redirect()->route('admin.users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['orders', 'projects', 'supportTickets']);
         
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,staff,client',
            'status' => 'required|in:active,inactive',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'company_name' => 'nullable|string|max:255',
        ]);
        
        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }
        
        $user->update($validated);
        
        if ($user) {
          alert()->success('Success', 'User updated successfully.');
        } else {
            alert()->error('Error', 'Failed to update user.');
        }  
        
        return redirect()->route('admin.users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) { 
              alert()->error('Error', 'Failed to delete user.');
              return redirect()->route('admin.users.index');
            }
            
        
        
        // Check if user has related data
        $hasOrders = $user->orders()->exists();
        $hasProjects = $user->projects()->exists();
        $hasTickets = $user->supportTickets()->exists();
        
        if ($hasOrders || $hasProjects || $hasTickets) {
            // Instead of deleting, deactivate the user
            $user->update(['status' => 'inactive']);
            
           
              alert()->error('Error', 'Successfully deactivated user.');
            
            return redirect()->route('admin.users.index');
        }
        
        $user->delete();
        if ($user) {
          alert()->success('Success', 'User deleted successfully.');
        } else {
            alert()->error('Error', 'Failed to delete user.');
        }  

        return redirect()->route('admin.users.index');
    }
    
    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);
        if ($user) {
          alert()->success('Success', "User status changed to {$newStatus}.");
        } else {
            alert()->error('Error', 'Failed to change user status.');
        }    
        return redirect()->route('admin.users.index');
    }
    
    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);
        
        $userIds = $request->user_ids;
        
        // Remove current user from bulk actions
        $userIds = array_filter($userIds, function($id) {
            return $id != auth()->id();
        });
        
        if (empty($userIds)) {
         alert()->error('Error', 'Failed to perform bulk action.');
         return redirect()->route('admin.users.index');
        }
        
        switch ($request->action) {
            case 'activate':
                User::whereIn('id', $userIds)->update(['status' => 'active']);
                $message = 'Users activated successfully';
                break;
                
            case 'deactivate':
                User::whereIn('id', $userIds)->update(['status' => 'inactive']);
                $message = 'Users deactivated successfully';
                break;
                
            case 'delete':
                // Only delete users without related data
                $usersToDelete = User::whereIn('id', $userIds)
                    ->whereDoesntHave('orders')
                    ->whereDoesntHave('projects')
                    ->whereDoesntHave('supportTickets')
                    ->pluck('id');
                    
                User::whereIn('id', $usersToDelete)->delete();
                
                // Deactivate users with related data
                $usersToDeactivate = array_diff($userIds, $usersToDelete->toArray());
                if (!empty($usersToDeactivate)) {
                    User::whereIn('id', $usersToDeactivate)->update(['status' => 'inactive']);
                }
                
                $deletedCount = count($usersToDelete);
                $deactivatedCount = count($usersToDeactivate);
                
                $message = "Deleted {$deletedCount} users";
                if ($deactivatedCount > 0) {
                    $message .= " and deactivated {$deactivatedCount} users (with related data)";
                }
                break;
        }
        
        alert()->success('Success', $message);
        
        return redirect()->route('admin.users.index');
    }
}