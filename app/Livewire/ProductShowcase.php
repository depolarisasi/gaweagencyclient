<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Template;
use App\Models\Product;

class ProductShowcase extends Component
{
    public function selectTemplate($templateId)
    {
        // Redirect ke halaman checkout dengan template yang dipilih
        return redirect()->route('checkout.template', ['template' => $templateId]);
    }

    public function render()
    {
        // Ambil template yang aktif untuk ditampilkan
        $templates = Template::active()
            ->ordered()
            ->get();
            
        // Ambil produk untuk billing options
        $products = Product::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.product-showcase', compact('templates', 'products'))
            ->layout('components.layouts.app');
    }
}
