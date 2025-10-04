<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Product;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display the specified template.
     */
    public function show(Template $template)
    {
        // Get active products for pricing
        $products = Product::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
            
        return view('templates.show', compact('template', 'products'));
    }
}