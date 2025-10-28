<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionPlan::query();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by billing cycle
        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->billing_cycle);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $subscriptionPlans = $query->orderBy('sort_order')->paginate(10);

        return view('admin.subscription-plans.index', compact('subscriptionPlans'));
    }

    public function create()
    {
        return view('admin.subscription-plans.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annual,annual',
            'cycle_months' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Set default sort order if not provided
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = SubscriptionPlan::max('sort_order') + 1;
        }

        // Convert features array to proper format
        if (isset($data['features'])) {
            $data['features'] = array_filter($data['features']);
        }

        SubscriptionPlan::create($data);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Paket langganan berhasil dibuat.');
    }

    public function show(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.show', compact('subscriptionPlan'));
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.edit', compact('subscriptionPlan'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annual,annual',
            'cycle_months' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Convert features array to proper format
        if (isset($data['features'])) {
            $data['features'] = array_filter($data['features']);
        }

        $subscriptionPlan->update($data);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Paket langganan berhasil diperbarui.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        // Check if subscription plan is being used in any orders
        if ($subscriptionPlan->orders()->exists()) {
            return redirect()->back()
                ->with('error', 'Paket langganan tidak dapat dihapus karena sedang digunakan dalam pesanan.');
        }

        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Paket langganan berhasil dihapus.');
    }

    public function toggleStatus(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update([
            'is_active' => !$subscriptionPlan->is_active
        ]);

        $status = $subscriptionPlan->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()
            ->with('success', "Paket langganan berhasil {$status}.");
    }
}