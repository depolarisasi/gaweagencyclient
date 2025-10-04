<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Order;
use App\Models\Invoice;
// TODO: Create Task and SupportTicket models
// use App\Models\Task;
// use App\Models\SupportTicket;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_projects' => Project::where('status', 'in_progress')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_invoices' => Invoice::count(),
        ];
        
        $recent_projects = Project::with('user')->latest()->take(5)->get();
        $recent_orders = Order::with(['user', 'product'])->latest()->take(5)->get();
        
        return view('staff.dashboard', compact('stats', 'recent_projects', 'recent_orders'));
    }
    
    public function projects()
    {
        $projects = Project::with(['user', 'order.product'])->latest()->paginate(10);
        return view('staff.projects', compact('projects'));
    }
    
    public function orders()
    {
        $orders = Order::with(['user', 'product'])->latest()->paginate(10);
        return view('staff.orders', compact('orders'));
    }
    
    public function invoices()
    {
        $invoices = Invoice::with(['user', 'order.product'])->latest()->paginate(10);
        return view('staff.invoices', compact('invoices'));
    }
    
    public function support()
    {
        // TODO: Implement support ticket system for staff
        return view('staff.support');
    }
}
