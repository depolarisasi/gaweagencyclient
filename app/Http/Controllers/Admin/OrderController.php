<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\SubscriptionPlan;
use App\Models\Template;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'product', 'subscriptionPlan', 'template']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('domain_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
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

        // Order type filter
        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $subscriptionPlans = SubscriptionPlan::where('is_active', true)->orderBy('name')->get();
        $templates = Template::where('is_active', true)->orderBy('name')->get();

        return view('admin.orders.create', compact('users', 'products', 'subscriptionPlans', 'templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'nullable|exists:products,id',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
            'template_id' => 'nullable|exists:templates,id',
            'order_type' => 'required|in:product,subscription,domain,custom',
            'amount' => 'required|numeric|min:0',
            'subscription_amount' => 'nullable|numeric|min:0',
            'addons_amount' => 'nullable|numeric|min:0',
            'setup_fee' => 'nullable|numeric|min:0',
            'billing_cycle' => 'nullable|in:monthly,quarterly,semi_annually,annually,biennially,triennially',
            'status' => 'required|in:pending,active,suspended,cancelled,completed',
            'next_due_date' => 'nullable|date|after:today',
            'domain_name' => 'nullable|string|max:255',
            'domain_type' => 'nullable|in:new,transfer,existing',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Generate unique order number
        do {
            $orderNumber = 'ORD-' . date('Y') . '-' . strtoupper(Str::random(8));
        } while (Order::where('order_number', $orderNumber)->exists());

        $validated['order_number'] = $orderNumber;

        // Set activation date if status is active
        if ($validated['status'] === 'active') {
            $validated['activated_at'] = now();
        }

        $order = Order::create($validated);

        alert()->success('Success', 'Order created successfully.');
        return redirect()->route('admin.orders.show', $order);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'product', 'subscriptionPlan', 'template', 'invoices', 'projects', 'orderAddons.productAddon']);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $users = User::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $subscriptionPlans = SubscriptionPlan::where('is_active', true)->orderBy('name')->get();
        $templates = Template::where('is_active', true)->orderBy('name')->get();

        return view('admin.orders.edit', compact('order', 'users', 'products', 'subscriptionPlans', 'templates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $previousStatus = $order->status;
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'nullable|exists:products,id',
            'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
            'template_id' => 'nullable|exists:templates,id',
            'order_type' => 'required|in:product,subscription,domain,custom',
            'amount' => 'required|numeric|min:0',
            'subscription_amount' => 'nullable|numeric|min:0',
            'addons_amount' => 'nullable|numeric|min:0',
            'setup_fee' => 'nullable|numeric|min:0',
            'billing_cycle' => 'nullable|in:monthly,quarterly,semi_annually,annually,biennially,triennially',
            'status' => 'required|in:pending,active,suspended,cancelled,completed',
            'next_due_date' => 'nullable|date',
            'domain_name' => 'nullable|string|max:255',
            'domain_type' => 'nullable|in:new,transfer,existing',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Handle status changes
        if ($validated['status'] === 'active' && $order->status !== 'active') {
            $validated['activated_at'] = now();
            $validated['suspended_at'] = null;
        } elseif ($validated['status'] === 'suspended' && $order->status !== 'suspended') {
            $validated['suspended_at'] = now();
        } elseif ($validated['status'] !== 'suspended') {
            $validated['suspended_at'] = null;
        }

        $order->update($validated);

        // Buat project otomatis jika status berubah menjadi active melalui update
        if ($previousStatus !== 'active' && $order->status === 'active') {
            $this->createProjectForOrder($order);
        }

        alert()->success('Success', 'Order updated successfully.');
        return redirect()->route('admin.orders.show', $order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        // Check if order has related invoices or projects
        if ($order->invoices()->count() > 0 || $order->projects()->count() > 0) {
            alert()->error('Error', 'Cannot delete order with existing invoices or projects.');
            return redirect()->back();
        }

        $order->delete();

        alert()->success('Success', 'Order deleted successfully.');
        return redirect()->route('admin.orders.index');
    }

    /**
     * Activate an order
     */
    public function activate(Order $order)
    {
        $order->update([
            'status' => 'active',
            'activated_at' => now(),
            'suspended_at' => null,
        ]);

        // Create project automatically when order is activated
        $this->createProjectForOrder($order);

        alert()->success('Success', 'Order activated successfully and project created.');
        return redirect()->back();
    }

    /**
     * Create a project for the activated order
     */
    private function createProjectForOrder(Order $order)
    {
        // Check if project already exists for this order
        $existingProject = Project::where('order_id', $order->id)->first();
        
        if ($existingProject) {
            // If project exists but is pending, activate it
            if ($existingProject->status === 'pending') {
                $existingProject->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
            }
            return $existingProject;
        }

        // Generate project name based on order details
        $projectName = $this->generateProjectName($order);

        // Create new project
        $project = Project::create([
            'project_name' => $projectName,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'template_id' => $order->template_id,
            'status' => 'in_progress',
            'description' => "Project created from order #{$order->order_number}",
            'started_at' => now(),
        ]);

        return $project;
    }

    /**
     * Generate a project name based on order details
     */
    private function generateProjectName(Order $order)
    {
        $baseName = '';

        // Use domain name if available
        if ($order->domain_name) {
            $baseName = "Website for " . $order->domain_name;
        }
        // Use template name if available
        elseif ($order->template) {
            $baseName = $order->template->name . " Project";
        }
        // Use product name if available
        elseif ($order->product) {
            $baseName = $order->product->name . " Project";
        }
        // Fallback to generic name
        else {
            $baseName = "Project";
        }

        // Add user name for clarity
        if ($order->user) {
            $baseName .= " for " . $order->user->name;
        }

        return $baseName;
    }

    /**
     * Suspend an order
     */
    public function suspend(Order $order)
    {
        $order->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);

        alert()->success('Success', 'Order suspended successfully.');
        return redirect()->back();
    }

    /**
     * Cancel an order
     */
    public function cancel(Order $order)
    {
        $order->update([
            'status' => 'cancelled',
        ]);

        alert()->success('Success', 'Order cancelled successfully.');
        return redirect()->back();
    }

    /**
     * Bulk actions for orders (currently supports delete)
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete',
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        $ids = $validated['order_ids'];
        $deleted = 0;
        $skipped = [];

        switch ($validated['action']) {
            case 'delete':
                $orders = Order::whereIn('id', $ids)->get();
                foreach ($orders as $order) {
                    // Skip deletion if order has invoices or projects
                    if ($order->invoices()->count() > 0 || $order->projects()->count() > 0) {
                        $skipped[] = $order->order_number;
                        continue;
                    }
                    $order->delete();
                    $deleted++;
                }

                $message = "Berhasil menghapus {$deleted} order.";
                if (count($skipped) > 0) {
                    $message .= " Dilewati: " . implode(', ', $skipped) . ".";
                }

                return response()->json(['success' => true, 'message' => $message]);
        }

        return response()->json(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
    }
}