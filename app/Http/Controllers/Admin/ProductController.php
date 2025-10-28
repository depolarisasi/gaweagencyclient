<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:website,hosting,domain,maintenance',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually',
            'features' => 'nullable|string',
            'setup_time_days' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Parse features textarea into array
        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = collect(preg_split('/\r?\n/', $validated['features']))
                ->map(fn($f) => trim($f))
                ->filter()
                ->values()
                ->toArray();
        }

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'price' => $validated['price'],
            'billing_cycle' => $validated['billing_cycle'],
            'features' => $featuresArray,
            'setup_time_days' => $validated['setup_time_days'] ?? 7,
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        if ($product) {
            alert()->success('Success', 'Product created successfully.');
        } else {
            alert()->error('Error', 'Failed to create product.');
        }

        return redirect()->route('admin.products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['orders']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => ['required', Rule::in(['website','hosting','domain','maintenance'])],
            'price' => 'required|numeric|min:0',
            'billing_cycle' => ['required', Rule::in(['monthly','quarterly','semi_annually','annually'])],
            'features' => 'nullable|string',
            'setup_time_days' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = collect(preg_split('/\r?\n/', $validated['features']))
                ->map(fn($f) => trim($f))
                ->filter()
                ->values()
                ->toArray();
        }

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'price' => $validated['price'],
            'billing_cycle' => $validated['billing_cycle'],
            'features' => $featuresArray,
            'setup_time_days' => $validated['setup_time_days'] ?? $product->setup_time_days,
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'] ?? $product->sort_order,
        ]);

        if ($product) {
            alert()->success('Success', 'Product updated successfully.');
        } else {
            alert()->error('Error', 'Failed to update product.');
        }

        return redirect()->route('admin.products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Check related orders
        $hasOrders = $product->orders()->exists();

        if ($hasOrders) {
            // Deactivate instead of delete
            $product->update(['is_active' => false]);
            $orderCount = $product->orders()->count();
            alert()->warning('Product Deactivated', "Product cannot be deleted because it has {$orderCount} related order(s). The product has been deactivated instead. To permanently delete this product, you must first delete or reassign all related orders.");
            return redirect()->route('admin.products.index');
        }

        $product->delete();

        if ($product) {
            alert()->success('Success', 'Product deleted successfully.');
        } else {
            alert()->error('Error', 'Failed to delete product.');
        }

        return redirect()->route('admin.products.index');
    }

    /**
     * Force delete a product (including related orders)
     */
    public function forceDestroy(Product $product)
    {
        try {
            // Delete related orders first
            $orderCount = $product->orders()->count();
            $product->orders()->delete();
            
            // Then delete the product
            $product->delete();

            alert()->success('Success', "Product and {$orderCount} related order(s) deleted successfully.");
        } catch (\Exception $e) {
            alert()->error('Error', 'Failed to delete product: ' . $e->getMessage());
        }

        return redirect()->route('admin.products.index');
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $newStatus = $product->is_active ? 'active' : 'inactive';
        if ($product) {
            alert()->success('Success', "Product status changed to {$newStatus}.");
        } else {
            alert()->error('Error', 'Failed to change product status.');
        }
        return redirect()->route('admin.products.index');
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $productIds = $request->product_ids;

        switch ($request->action) {
            case 'activate':
                Product::whereIn('id', $productIds)->update(['is_active' => true]);
                $message = 'Products activated successfully';
                break;
            case 'deactivate':
                Product::whereIn('id', $productIds)->update(['is_active' => false]);
                $message = 'Products deactivated successfully';
                break;
            case 'delete':
                // Only delete products without orders
                $productsWithOrders = Product::whereIn('id', $productIds)
                    ->whereHas('orders')
                    ->pluck('id');

                $productsToDelete = array_diff($productIds, $productsWithOrders->toArray());
                Product::whereIn('id', $productsToDelete)->delete();

                $productsToDeactivate = $productsWithOrders->toArray();
                if (!empty($productsToDeactivate)) {
                    Product::whereIn('id', $productsToDeactivate)->update(['is_active' => false]);
                }

                $message = 'Deleted ' . count($productsToDelete) . ' products';
                if (!empty($productsToDeactivate)) {
                    $message .= ' and deactivated ' . count($productsToDeactivate) . ' products (with orders)';
                }
                break;
        }

        alert()->success('Success', $message);
        return redirect()->route('admin.products.index');
    }
}