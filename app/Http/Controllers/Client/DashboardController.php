<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cookie;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'active_projects' => Project::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            'completed_projects' => Project::where('user_id', $user->id)->where('status', 'completed')->count(),
            'pending_invoices' => Invoice::where('user_id', $user->id)->where('status', 'sent')->count(),
        ];
        
        $recent_projects = Project::where('user_id', $user->id)->latest()->take(5)->get();
        $popular_products = Product::where('is_active', true)->take(3)->get();
        
        return view('client.dashboard', compact('stats', 'recent_projects', 'popular_products'));
    }
    
    public function products()
    {
        $products = Product::where('is_active', true)->paginate(12);
        return view('client.products', compact('products'));
    }
    
    public function orders()
    {
        $user = auth()->user();
        $orders = Order::where('user_id', $user->id)
            ->with(['product', 'subscriptionPlan', 'template'])
            ->latest()
            ->paginate(10);
        return view('client.orders', compact('orders'));
    }

    public function showOrder(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order.');
        }

        $order->load([
            'product',
            'subscriptionPlan',
            'template',
            'orderAddons.productAddon',
        ]);

        return view('client.orders.show', compact('order'));
    }

    public function cancelAddon(Request $request, Order $order, OrderAddon $orderAddon)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order.');
        }

        if ($orderAddon->order_id !== $order->id) {
            abort(404, 'Addon tidak ditemukan untuk order ini.');
        }

        if ($orderAddon->status === 'cancelled') {
            alert()->info('Info', 'Add-on sudah berstatus cancelled.');
            return redirect()->route('client.orders.show', $order);
        }

        // Jika recurring (billing_cycle ada), tandai cancel di akhir periode
        if (!empty($orderAddon->billing_cycle)) {
            $orderAddon->cancel_at_period_end = true;
            $orderAddon->save();
            alert()->success('Berhasil', 'Add-on akan dibatalkan di akhir periode berjalan.');
        } else {
            // One-time: batalkan segera
            $orderAddon->status = 'cancelled';
            $orderAddon->cancelled_at = now();
            $orderAddon->cancel_at_period_end = false;
            $orderAddon->next_due_date = null;
            $orderAddon->save();
            alert()->success('Berhasil', 'Add-on one-time dibatalkan sekarang.');
        }

        return redirect()->route('client.orders.show', $order);
    }
    
    public function projects()
    {
        $user = auth()->user();
        $projects = Project::where('user_id', $user->id)
            ->with(['order.product', 'template', 'assignedStaff'])
            ->latest()
            ->paginate(9);
        
        // Calculate stats
        $activeCount = Project::where('user_id', $user->id)->where('status', 'active')->count();
        $inProgressCount = Project::where('user_id', $user->id)->where('status', 'in_progress')->count();
        $completedCount = Project::where('user_id', $user->id)->where('status', 'completed')->count();
        
        return view('client.projects.index', compact('projects', 'activeCount', 'inProgressCount', 'completedCount'));
    }
    
    public function showProject(Project $project)
    {
        // Ensure user can only view their own projects
        if ($project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to project.');
        }
        
        $project->load(['template', 'assignedStaff', 'order.product']);
        
        return view('client.projects.show', compact('project'));
    }
    
    public function invoices()
    {
        $user = auth()->user();
        $invoices = Invoice::where('user_id', $user->id)->with(['order.product'])->latest()->paginate(10);
        
        // Calculate stats
        $pendingCount = Invoice::where('user_id', $user->id)->where('status', 'sent')->count();
        $paidCount = Invoice::where('user_id', $user->id)->where('status', 'paid')->count();
        $totalAmount = 'Rp ' . number_format(
            Invoice::where('user_id', $user->id)->where('status', 'paid')->sum('total_amount'), 
            0, ',', '.'
        );
        
        return view('client.invoices.index', compact('invoices', 'pendingCount', 'paidCount', 'totalAmount'));
    }
    
    public function showInvoice(Invoice $invoice)
    {
        // Ensure user can only view their own invoices
        if ($invoice->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to invoice.');
        }
        
        return view('client.invoices.show', compact('invoice'));
    }
    
    public function support()
    {
        // TODO: Implement support ticket system
        return view('client.support');
    }
    
    public function profile()
    {
        $user = auth()->user();
        return view('client.profile', compact('user'));
    }
    
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'company_name' => 'nullable|string|max:255',
        ]);
        
        $user->update($validated);

        // Handle theme preference (persist to cookie and session without DB change)
        $theme = $request->input('theme');
        if ($theme && in_array($theme, ['light', 'dark'])) {
            session(['theme' => $theme]);
            // Store for 1 year (minutes)
            Cookie::queue('theme', $theme, 60 * 24 * 365);
        }
        
        if ($user) {
            alert()->success('Success', 'Profile updated successfully.');
        } else {
            alert()->error('Error', 'Failed to update profile.');
        }
        
        return redirect()->route('client.profile');
    }
    
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        
        $user->update([
            'password' => Hash::make($request->password)
        ]);
        
        alert()->success('Success', 'Password changed successfully.');
        
        return redirect()->route('client.profile');
    }
}
