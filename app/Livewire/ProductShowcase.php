<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Template;
use App\Models\Product;

class ProductShowcase extends Component
{
    public $templates;
    public $products;
    public $selectedTemplateId;

    public function mount()
    {
        $this->templates = Template::where('is_active', true)->orderBy('sort_order')->get();
        $this->products = Product::where('is_active', true)->orderBy('sort_order')->orderBy('price')->get();
    }

    public function render()
    {
        return view('livewire.product-showcase', [
            'templates' => $this->templates,
            'products' => $this->products,
        ]);
    }

    public function selectTemplate($templateId)
    {
        $this->selectedTemplateId = $templateId;
        session(['selected_template_id' => $templateId]);
        // Redirect or emit event to proceed to configuration
        return redirect()->route('checkout.configure');
    }
}
