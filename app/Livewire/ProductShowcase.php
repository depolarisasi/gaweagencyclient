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
        // Simpan pilihan template di sesi untuk konsistensi
        session(['selected_template_id' => $templateId]);
        session(['checkout.template_id' => $templateId]);
        // Arahkan ke halaman pemilihan template sesuai flow baru
        return redirect()->route('checkout.template');
    }
}
