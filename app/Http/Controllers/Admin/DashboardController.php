<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Project;
use App\Models\Order;
use App\Models\Invoice;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'total_projects' => Project::count(),
            'total_orders' => Order::count(),
            'active_users' => User::where('status', 'active')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'in_progress_projects' => Project::where('status', 'in_progress')->count(),
        ];
        
        $recent_users = User::latest()->take(5)->get();
        $recent_orders = Order::with('user')->latest()->take(5)->get();
        $recent_projects = Project::with('user')->latest()->take(5)->get();
        
        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_orders', 'recent_projects'));
    }
    
    public function users()
    {
        $users = User::latest()->paginate(15);
        return view('admin.users', compact('users'));
    }
    
    public function orders()
    {
        // Redirect to the new OrderController index for full CRUD functionality
        return redirect()->route('admin.orders.index');
    }
    
    public function invoices()
    {
        $invoices = Invoice::with(['user', 'order.product'])->latest()->paginate(15);
        return view('admin.invoices', compact('invoices'));
    }
    
    public function projects()
    {
        $projects = Project::with(['user', 'order.product'])->latest()->paginate(15);
        return view('admin.projects', compact('projects'));
    }
    
    public function support()
    {
        // TODO: Implement support ticket management
        return view('admin.support');
    }
    
    public function settings()
    {
        // TODO: Implement system settings
        return view('admin.settings');
    }
}
