<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Template::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }
        
        $templates = $query->orderBy('sort_order')
                          ->orderBy('name')
                          ->paginate(12);
        
        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'demo_url' => 'nullable|url|max:255',
            'thumbnail_url' => 'nullable|url|max:255',
            'category' => 'required|in:business,ecommerce,portfolio,blog,landing',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'features' => 'nullable|string|max:2000',
        ]);
        
        // Process features (convert from textarea to array)
        if ($validated['features']) {
            $validated['features'] = array_filter(explode("\n", $validated['features']));
        }
        
        // Set default sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Template::max('sort_order') + 1;
        }
        
        $template = Template::create($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'template' => $template
            ]);
        }
        
        return redirect()->route('admin.templates.index')
                        ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Template $template)
    {
        $template->load(['products']);
        
        if (request()->expectsJson()) {
            return response()->json($template);
        }
        
        return view('admin.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Template $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'demo_url' => 'nullable|url|max:255',
            'thumbnail_url' => 'nullable|url|max:255',
            'category' => 'required|in:business,ecommerce,portfolio,blog,landing',
            'is_active' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'features' => 'nullable|string|max:2000',
        ]);
        
        // Process features (convert from textarea to array)
        if ($validated['features']) {
            $validated['features'] = array_filter(explode("\n", $validated['features']));
        }
        
        $template->update($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'template' => $template
            ]);
        }
        
        return redirect()->route('admin.templates.index')
                        ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template)
    {
        // Check if template has related products
        if ($template->products()->exists()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete template that has associated products'
                ], 400);
            }
            
            return redirect()->route('admin.templates.index')
                            ->with('error', 'Cannot delete template that has associated products.');
        }
        
        $template->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);
        }
        
        return redirect()->route('admin.templates.index')
                        ->with('success', 'Template deleted successfully.');
    }
    
    /**
     * Toggle template status
     */
    public function toggleStatus(Template $template)
    {
        $newStatus = !$template->is_active;
        $template->update(['is_active' => $newStatus]);
        
        $statusText = $newStatus ? 'activated' : 'deactivated';
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Template {$statusText} successfully",
                'is_active' => $newStatus
            ]);
        }
        
        return redirect()->route('admin.templates.index')
                        ->with('success', "Template {$statusText} successfully.");
    }
    
    /**
     * Duplicate template
     */
    public function duplicate(Template $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->is_active = false; // New copy starts as inactive
        $newTemplate->sort_order = Template::max('sort_order') + 1;
        $newTemplate->save();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template duplicated successfully',
                'template' => $newTemplate
            ]);
        }
        
        return redirect()->route('admin.templates.index')
                        ->with('success', 'Template duplicated successfully.');
    }
    
    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'template_ids' => 'required|array',
            'template_ids.*' => 'exists:templates,id'
        ]);
        
        $templateIds = $request->template_ids;
        $action = $request->action;
        
        switch ($action) {
            case 'activate':
                Template::whereIn('id', $templateIds)->update(['is_active' => true]);
                $message = 'Templates activated successfully';
                break;
                
            case 'deactivate':
                Template::whereIn('id', $templateIds)->update(['is_active' => false]);
                $message = 'Templates deactivated successfully';
                break;
                
            case 'delete':
                // Only delete templates without associated products
                $templatesWithProducts = Template::whereIn('id', $templateIds)
                                                ->whereHas('products')
                                                ->pluck('id');
                
                $templatesToDelete = array_diff($templateIds, $templatesWithProducts->toArray());
                
                Template::whereIn('id', $templatesToDelete)->delete();
                
                $deletedCount = count($templatesToDelete);
                $skippedCount = count($templatesWithProducts);
                
                $message = "Deleted {$deletedCount} templates";
                if ($skippedCount > 0) {
                    $message .= ", skipped {$skippedCount} templates with associated products";
                }
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
    
    /**
     * Update sort order
     */
    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'templates' => 'required|array',
            'templates.*.id' => 'required|exists:templates,id',
            'templates.*.sort_order' => 'required|integer|min:0'
        ]);
        
        foreach ($request->templates as $templateData) {
            Template::where('id', $templateData['id'])
                   ->update(['sort_order' => $templateData['sort_order']]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully'
        ]);
    }
    
    /**
     * Get template statistics
     */
    public function statistics()
    {
        $stats = [
            'total_templates' => Template::count(),
            'active_templates' => Template::where('is_active', true)->count(),
            'categories_count' => Template::distinct('category')->count('category'),
            'most_popular_category' => Template::selectRaw('category, COUNT(*) as count')
                                              ->groupBy('category')
                                              ->orderBy('count', 'desc')
                                              ->first()?->category ?? 'business',
            'templates_by_category' => Template::selectRaw('category, COUNT(*) as count')
                                              ->groupBy('category')
                                              ->pluck('count', 'category'),
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Search templates for API
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'category' => 'nullable|in:business,ecommerce,portfolio,blog,landing',
            'active_only' => 'boolean'
        ]);
        
        $query = Template::where('name', 'like', '%' . $request->q . '%')
                        ->orWhere('description', 'like', '%' . $request->q . '%');
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        
        $templates = $query->orderBy('sort_order')
                          ->orderBy('name')
                          ->limit(20)
                          ->get();
        
        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }
}