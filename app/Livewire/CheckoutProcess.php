<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Template;
use App\Models\ProductAddon;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CheckoutProcess extends Component
{
    public $product;
    public $template;
    public $selectedProduct;
    public $selectedAddons = [];
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $phone = '';
    public $company = '';
    public $billing_cycle;
    public $step = 1; // 1: Template & Product Selection, 2: Addons, 3: Customer Info
    
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'required|string|max:20',
        'company' => 'nullable|string|max:255',
        'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually',
        'selectedProduct' => 'required|exists:products,id',
    ];
    
    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'phone.required' => 'Nomor telepon wajib diisi.',
        'billing_cycle.required' => 'Siklus tagihan wajib dipilih.',
    ];
    
    public function mount($product = null, $template = null)
    {
        if ($template) {
            // Template-based checkout
            $this->template = Template::findOrFail($template);
            $this->step = 1;
            
            // Check if product is pre-selected via query parameter
            $productId = request()->get('product');
            if ($productId) {
                $selectedProduct = Product::find($productId);
                if ($selectedProduct && $selectedProduct->is_active) {
                    $this->selectedProduct = $selectedProduct->id;
                    $this->billing_cycle = $selectedProduct->billing_cycle;
                    $this->step = 2; // Skip to addon selection
                }
            }
        } else {
            // Product-based checkout (legacy)
            $this->product = Product::findOrFail($product);
            $this->selectedProduct = $this->product->id;
            $this->billing_cycle = $this->product->billing_cycle;
            $this->step = 2;
        }
    }
    
    public function selectProduct($productId)
    {
        $this->selectedProduct = $productId;
        $product = Product::find($productId);
        if ($product) {
            $this->billing_cycle = $product->billing_cycle;
        }
    }
    
    public function toggleAddon($addonId)
    {
        if (in_array($addonId, $this->selectedAddons)) {
            $this->selectedAddons = array_filter($this->selectedAddons, fn($id) => $id != $addonId);
        } else {
            $this->selectedAddons[] = $addonId;
        }
    }
    
    public function nextStep()
    {
        if ($this->step == 1 && $this->selectedProduct) {
            $this->step = 2;
        } elseif ($this->step == 2) {
            $this->step = 3;
        }
    }
    
    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }
    
    public function getTotalAmount()
    {
        $total = 0;
        
        if ($this->selectedProduct) {
            $product = Product::find($this->selectedProduct);
            $total += $product ? $product->price : 0;
        }
        
        foreach ($this->selectedAddons as $addonId) {
            $addon = ProductAddon::find($addonId);
            if ($addon) {
                $total += $addon->price;
            }
        }
        
        return $total;
    }
    
    public function getFormattedTotal()
    {
        return 'Rp ' . number_format($this->getTotalAmount(), 0, ',', '.');
    }
    
    public function submitOrder()
    {
        \Log::info('Starting submitOrder method');
        
        try {
            $this->validate();
            \Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'data' => [
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'billing_cycle' => $this->billing_cycle,
                    'selectedProduct' => $this->selectedProduct
                ]
            ]);
            throw $e;
        }
        
        try {
            $product = Product::findOrFail($this->selectedProduct);
            $totalAmount = $this->getTotalAmount();
            
            \Log::info('Starting submitOrder process', [
                'name' => $this->name,
                'email' => $this->email,
                'product_id' => $product->id,
                'template_id' => $this->template?->id,
                'billing_cycle' => $this->billing_cycle,
                'total_amount' => $totalAmount,
                'addons' => $this->selectedAddons
            ]);
            
            DB::beginTransaction();
            
            // Create new user
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'phone' => $this->phone,
                'company' => $this->company,
                'role' => 'client',
                'email_verified_at' => now(),
            ]);
            
            \Log::info('User created successfully', ['user_id' => $user->id]);
            
            // Prepare order details
            $orderDetails = [
                'product_name' => $product->name,
                'billing_cycle' => $this->billing_cycle,
                'features' => $product->features,
            ];
            
            if ($this->template) {
                $orderDetails['template'] = [
                    'id' => $this->template->id,
                    'name' => $this->template->name,
                    'category' => $this->template->category,
                    'demo_url' => $this->template->demo_url,
                ];
            }
            
            // Add addon details
            if (!empty($this->selectedAddons)) {
                $addons = ProductAddon::whereIn('id', $this->selectedAddons)->get();
                $orderDetails['addons'] = $addons->map(function($addon) {
                    return [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'price' => $addon->price,
                        'billing_type' => $addon->billing_type,
                    ];
                })->toArray();
            }
            
            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . date('Ymd') . '-' . uniqid(),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'amount' => $totalAmount,
                'setup_fee' => 0,
                'billing_cycle' => $this->billing_cycle,
                'status' => 'pending',
                'next_due_date' => $this->calculateNextDueDate(),
                'order_details' => $orderDetails,
            ]);
            
            \Log::info('Order created successfully', ['order_id' => $order->id]);
            
            // Create invoice with 7 days due date
            $taxAmount = round($totalAmount * 0.11, 2); // PPN 11%
            $totalWithTax = round($totalAmount + $taxAmount, 2);
            
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . date('Ymd') . '-' . uniqid(),
                'user_id' => $user->id,
                'order_id' => $order->id,
                'amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalWithTax,
                'status' => 'draft', // Invoice starts as draft
                'due_date' => now()->addDays(7), // H+7 untuk pembayaran
            ]);
            
            \Log::info('Invoice created successfully', ['invoice_id' => $invoice->id]);
            
            // Create project with pending status
            $projectDescription = 'Pembuatan website menggunakan template ' . ($this->template?->name ?? $product->name);
            if ($this->template) {
                $projectDescription .= ' (Kategori: ' . $this->template->category_text . ')';
            }
            
            $project = Project::create([
                'project_name' => 'Website ' . ($this->company ?: $this->name),
                'order_id' => $order->id,
                'user_id' => $user->id,
                'status' => 'pending', // Will be activated after payment
                'description' => $projectDescription,
            ]);
            
            \Log::info('Project created successfully', ['project_id' => $project->id]);
            
            // Auto-login user after successful registration
            auth()->login($user);
            \Log::info('User auto-logged in after registration', ['user_id' => $user->id]);
            
            DB::commit();
            
            // For testing, just flash success message without redirect
            if (app()->environment('testing')) {
                session()->flash('success', 'Pesanan berhasil dibuat! Invoice akan otomatis dibatalkan jika tidak dibayar dalam 7 hari.');
                return;
            }
            
            // Redirect to invoice payment page
            return redirect()->route('client.invoices.show', $invoice->id)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran dalam 7 hari. Invoice akan otomatis dibatalkan jika tidak dibayar.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in submitOrder: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Error details', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode()
            ]);
            
            // In test environment, re-throw the exception to see what's wrong
            if (app()->environment('testing')) {
                throw $e;
            }
            
            session()->flash('error', 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
        }
    }
    
    private function calculateNextDueDate()
    {
        $now = now();
        
        return match($this->billing_cycle) {
            'monthly' => $now->addMonth(),
            'quarterly' => $now->addMonths(3),
            'semi_annually' => $now->addMonths(6),
            'annually' => $now->addYear(),
            default => $now->addMonth(),
        };
    }
    
    public function render()
    {
        $products = Product::active()->orderBy('sort_order')->get();
        $addons = ProductAddon::active()->orderBy('sort_order')->get();
        
        return view('livewire.checkout-process', [
            'products' => $products,
            'addons' => $addons,
        ]);
    }
}
