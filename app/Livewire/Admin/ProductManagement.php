<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use Illuminate\Validation\Rule;

class ProductManagement extends Component
{
    use WithPagination;

    // Form properties
    public $name = '';
    public $description = '';
    public $type = 'website';
    public $price = '';
    public $billing_cycle = 'monthly';
    public $setup_time = '';
    public $features = [];
    public $is_active = true;
    public $sort_order = 0;
    
    // Modal and editing state
    public $showModal = false;
    public $editingProduct = null;
    public $modalTitle = 'Tambah Produk Baru';
    
    // Search and filter
    public $search = '';
    public $filterType = '';
    public $filterStatus = '';
    
    // Feature input
    public $newFeature = '';
    
    protected $paginationTheme = 'bootstrap';
    
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:website,mobile_app,web_app,ecommerce,custom',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually',
            'setup_time' => 'required|string|max:100',
            'features' => 'array',
            'features.*' => 'string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ];
    }
    
    protected $messages = [
        'name.required' => 'Nama produk wajib diisi.',
        'description.required' => 'Deskripsi produk wajib diisi.',
        'type.required' => 'Tipe produk wajib dipilih.',
        'price.required' => 'Harga produk wajib diisi.',
        'price.numeric' => 'Harga harus berupa angka.',
        'billing_cycle.required' => 'Siklus tagihan wajib dipilih.',
        'setup_time.required' => 'Waktu setup wajib diisi.'
    ];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingFilterType()
    {
        $this->resetPage();
    }
    
    public function updatingFilterStatus()
    {
        $this->resetPage();
    }
    
    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalTitle = 'Tambah Produk Baru';
        $this->showModal = true;
    }
    
    public function openEditModal($productId)
    {
        $product = Product::findOrFail($productId);
        $this->editingProduct = $product;
        
        $this->name = $product->name;
        $this->description = $product->description;
        $this->type = $product->type;
        $this->price = $product->price;
        $this->billing_cycle = $product->billing_cycle;
        $this->setup_time = $product->setup_time;
        $this->features = $product->features ?? [];
        $this->is_active = $product->is_active;
        $this->sort_order = $product->sort_order;
        
        $this->modalTitle = 'Edit Produk';
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
    
    public function resetForm()
    {
        $this->editingProduct = null;
        $this->name = '';
        $this->description = '';
        $this->type = 'website';
        $this->price = '';
        $this->billing_cycle = 'monthly';
        $this->setup_time = '';
        $this->features = [];
        $this->is_active = true;
        $this->sort_order = 0;
        $this->newFeature = '';
        $this->resetErrorBag();
    }
    
    public function addFeature()
    {
        if (trim($this->newFeature) !== '') {
            $this->features[] = trim($this->newFeature);
            $this->newFeature = '';
        }
    }
    
    public function removeFeature($index)
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }
    
    public function save()
    {
        $this->validate();
        
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'price' => $this->price,
            'billing_cycle' => $this->billing_cycle,
            'setup_time' => $this->setup_time,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order
        ];
        
        if ($this->editingProduct) {
            $this->editingProduct->update($data);
            session()->flash('success', 'Produk berhasil diperbarui!');
        } else {
            Product::create($data);
            session()->flash('success', 'Produk berhasil ditambahkan!');
        }
        
        $this->closeModal();
    }
    
    public function delete($productId)
    {
        $product = Product::findOrFail($productId);
        $product->delete();
        
        session()->flash('success', 'Produk berhasil dihapus!');
    }
    
    public function toggleStatus($productId)
    {
        $product = Product::findOrFail($productId);
        $product->update(['is_active' => !$product->is_active]);
        
        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Produk berhasil {$status}!");
    }
    
    public function render()
    {
        $query = Product::query();
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }
        
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }
        
        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus);
        }
        
        $products = $query->orderBy('sort_order')
                         ->orderBy('name')
                         ->paginate(10);
        
        return view('livewire.admin.product-management', [
            'products' => $products
        ])->layout('components.layouts.admin');
    }
}
