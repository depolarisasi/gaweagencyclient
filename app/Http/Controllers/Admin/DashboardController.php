<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Project;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Setting;

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

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string|max:50',
            'company_website' => 'nullable|url',
            'company_address' => 'nullable|string|max:1000',
            'tripay_merchant_code' => 'nullable|string|max:255',
            'tripay_api_key' => 'nullable|string|max:255',
            'tripay_private_key' => 'nullable|string|max:255',
            'tripay_mode' => 'required|in:sandbox,production',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        $groups = [
            'company_name' => 'system',
            'company_email' => 'system',
            'company_phone' => 'system',
            'company_website' => 'system',
            'company_address' => 'system',
            'tripay_merchant_code' => 'tripay',
            'tripay_api_key' => 'tripay',
            'tripay_private_key' => 'tripay',
            'tripay_mode' => 'tripay',
            'mail_host' => 'mail',
            'mail_port' => 'mail',
            'mail_username' => 'mail',
            'mail_password' => 'mail',
            'mail_encryption' => 'mail',
            'mail_from_name' => 'mail',
        ];

        foreach ($data as $key => $value) {
            if ($key === 'mail_port' && $value !== null) {
                $value = (string) intval($value);
            }
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) ($value ?? ''), 'group' => $groups[$key] ?? 'system']
            );
        }

        return redirect()->route('admin.settings')->with('status', 'Settings updated successfully.');
    }
}
