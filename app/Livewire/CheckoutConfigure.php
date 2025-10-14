<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Template;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class CheckoutConfigure extends Component
{
    public $template;
    public $products;
    public $selectedTemplateId;
    public $selectedProductId;
    public $addons = []; // Untuk add-on

    public function mount()
    {
        $this->selectedTemplateId = Session::get('selected_template_id');

        if (!$this->selectedTemplateId) {
            return redirect('/'); // Kembali ke halaman utama jika tidak ada template yang dipilih
        }

        $this->template = Template::findOrFail($this->selectedTemplateId);
        $this->products = Product::all(); // Asumsi semua produk adalah siklus penagihan

        // Inisialisasi add-on jika ada
        // $this->addons = Addon::all(); // Jika ada model Addon
    }

    public function updatedSelectedProductId()
    {
        // Logika tambahan jika diperlukan saat produk (siklus penagihan) berubah
    }

    public function configureProduct()
    {
        // Simpan konfigurasi ke session atau Livewire state
        Session::put('cart', [
            'template_id' => $this->selectedTemplateId,
            'product_id' => $this->selectedProductId,
            'addons' => $this->addons, // Akan diisi nanti jika ada add-on
            'created_at' => now()->timestamp,
        ]);

        return redirect()->route('checkout.summary'); // Arahkan ke halaman ringkasan checkout
    }

    public function render()
    {
        return view('livewire.checkout-configure');
    }
}
