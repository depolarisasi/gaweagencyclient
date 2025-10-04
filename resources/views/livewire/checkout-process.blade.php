<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">Checkout Pesanan</h1>
                <p class="text-lg text-gray-600">Ikuti langkah-langkah berikut untuk menyelesaikan pesanan website Anda</p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <ul class="steps steps-horizontal w-full">
                    <li class="step {{ $step >= 1 ? 'step-primary' : '' }}">Pilih Paket</li>
                    <li class="step {{ $step >= 2 ? 'step-primary' : '' }}">Addon</li>
                    <li class="step {{ $step >= 3 ? 'step-primary' : '' }}">Data Diri</li>
                </ul>
            </div>

            @if (session()->has('error'))
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="card bg-base-100 shadow-xl sticky top-4">
                        <div class="card-body">
                            <h2 class="card-title text-lg font-bold mb-4">
                                <i class="fas fa-receipt text-primary"></i>
                                Ringkasan Pesanan
                            </h2>
                            
                            @if($template)
                                <div class="border-b pb-4 mb-4">
                                    <h3 class="font-semibold text-primary">Template Dipilih</h3>
                                    <p class="text-sm font-medium">{{ $template->name }}</p>
                                    <div class="badge {{ $template->category_badge_class }} badge-sm mt-1">{{ $template->category_text }}</div>
                                </div>
                            @endif
                            
                            @if($selectedProduct)
                                @php $product = $products->find($selectedProduct) @endphp
                                @if($product)
                                    <div class="border-b pb-4 mb-4">
                                        <h3 class="font-semibold text-primary">{{ $product->name }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $product->description }}</p>
                                        <div class="flex justify-between mt-2">
                                            <span class="text-sm">{{ $product->billing_cycle_text }}</span>
                                            <span class="font-medium">{{ $product->formatted_price }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            
                            @if(!empty($selectedAddons))
                                <div class="border-b pb-4 mb-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Addon Dipilih:</h4>
                                    @foreach($selectedAddons as $addonId)
                                        @php $addon = $addons->find($addonId) @endphp
                                        @if($addon)
                                            <div class="flex justify-between text-sm mb-1">
                                                <span>{{ $addon->name }}</span>
                                                <span>{{ $addon->formatted_price }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            
                            <div class="space-y-2">
                                <div class="flex justify-between text-lg font-bold border-t pt-2">
                                    <span>Total:</span>
                                    <span class="text-primary">{{ $this->getFormattedTotal() }}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    *Belum termasuk PPN 11%
                                </div>
                                <div class="text-xs text-warning">
                                    <i class="fas fa-clock mr-1"></i>
                                    Invoice berlaku 7 hari
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <!-- Step 1: Product Selection -->
                    @if($step == 1)
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body">
                                <h2 class="card-title text-xl font-bold mb-6">
                                    <i class="fas fa-box text-primary"></i>
                                    Pilih Paket Website
                                </h2>
                                
                                @if($template)
                                    <div class="alert alert-info mb-6">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Anda telah memilih template <strong>{{ $template->name }}</strong>. Sekarang pilih paket berlangganan yang sesuai.</span>
                                    </div>
                                @endif
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach($products as $product)
                                        <div class="card border-2 {{ $selectedProduct == $product->id ? 'border-primary bg-primary/5' : 'border-base-300' }} hover:border-primary transition-colors cursor-pointer"
                                             wire:click="selectProduct({{ $product->id }})">
                                            <div class="card-body">
                                                <h3 class="card-title text-lg">{{ $product->name }}</h3>
                                                <div class="text-2xl font-bold text-primary mb-2">{{ $product->formatted_price }}</div>
                                                <div class="text-sm text-gray-500 mb-4">{{ $product->billing_cycle_text }}</div>
                                                
                                                <p class="text-sm text-gray-600 mb-4">{{ $product->description }}</p>
                                                
                                                @if($product->features)
                                                    <ul class="text-sm space-y-1">
                                                        @foreach(array_slice($product->features, 0, 4) as $feature)
                                                            <li class="flex items-center">
                                                                <i class="fas fa-check text-success mr-2"></i>
                                                                {{ $feature }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                                
                                                @if($selectedProduct == $product->id)
                                                    <div class="badge badge-primary mt-4">
                                                        <i class="fas fa-check mr-1"></i>
                                                        Dipilih
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="flex justify-between mt-8">
                                    <a href="{{ route('home') }}" class="btn btn-outline">
                                        <i class="fas fa-arrow-left mr-2"></i>
                                        Kembali ke Template
                                    </a>
                                    <button wire:click="nextStep" class="btn btn-primary" {{ !$selectedProduct ? 'disabled' : '' }}>
                                        Lanjut ke Addon
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Step 2: Addon Selection -->
                    @if($step == 2)
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body">
                                <h2 class="card-title text-xl font-bold mb-6">
                                    <i class="fas fa-plus-circle text-primary"></i>
                                    Pilih Addon (Opsional)
                                </h2>
                                
                                <p class="text-gray-600 mb-6">Tingkatkan website Anda dengan addon tambahan berikut:</p>
                                
                                @if($addons->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach($addons as $addon)
                                            <div class="card border-2 {{ in_array($addon->id, $selectedAddons) ? 'border-primary bg-primary/5' : 'border-base-300' }} hover:border-primary transition-colors cursor-pointer"
                                                 wire:click="toggleAddon({{ $addon->id }})">
                                                <div class="card-body">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <h3 class="card-title text-lg">{{ $addon->name }}</h3>
                                                        <div class="badge {{ $addon->category_badge_class }}">{{ $addon->category_text }}</div>
                                                    </div>
                                                    
                                                    <div class="text-xl font-bold text-primary mb-2">{{ $addon->formatted_price }}</div>
                                                    <div class="text-sm text-gray-500 mb-4">{{ $addon->billing_type_text }}</div>
                                                    
                                                    <p class="text-sm text-gray-600">{{ $addon->description }}</p>
                                                    
                                                    @if(in_array($addon->id, $selectedAddons))
                                                        <div class="badge badge-primary mt-4">
                                                            <i class="fas fa-check mr-1"></i>
                                                            Dipilih
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">Belum ada addon yang tersedia saat ini.</p>
                                    </div>
                                @endif
                                
                                <div class="flex justify-between mt-8">
                                    <button wire:click="previousStep" class="btn btn-outline">
                                        <i class="fas fa-arrow-left mr-2"></i>
                                        Kembali
                                    </button>
                                    <button wire:click="nextStep" class="btn btn-primary">
                                        Lanjut ke Data Diri
                                        <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Step 3: Customer Information -->
                    @if($step == 3)
                        <div class="card bg-base-100 shadow-xl">
                            <div class="card-body">
                                <h2 class="card-title text-xl font-bold mb-6">
                                    <i class="fas fa-user-plus text-primary"></i>
                                    Informasi Akun & Pembayaran
                                </h2>
                                
                                <form wire:submit.prevent="submitOrder" class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text font-medium">Nama Lengkap *</span>
                                            </label>
                                            <input 
                                                type="text" 
                                                wire:model="name"
                                                class="input input-bordered @error('name') input-error @enderror" 
                                                placeholder="Masukkan nama lengkap"
                                            >
                                            @error('name')
                                                <label class="label">
                                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                                </label>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text font-medium">Email *</span>
                                            </label>
                                            <input 
                                                type="email" 
                                                wire:model="email"
                                                class="input input-bordered @error('email') input-error @enderror" 
                                                placeholder="nama@email.com"
                                            >
                                            @error('email')
                                                <label class="label">
                                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                                </label>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text font-medium">Password *</span>
                                            </label>
                                            <input 
                                                type="password" 
                                                wire:model="password"
                                                class="input input-bordered @error('password') input-error @enderror" 
                                                placeholder="Minimal 8 karakter"
                                            >
                                            @error('password')
                                                <label class="label">
                                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                                </label>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text font-medium">Konfirmasi Password *</span>
                                            </label>
                                            <input 
                                                type="password" 
                                                wire:model="password_confirmation"
                                                class="input input-bordered @error('password_confirmation') input-error @enderror" 
                                                placeholder="Ulangi password"
                                            >
                                            @error('password_confirmation')
                                                <label class="label">
                                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                                </label>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text font-medium">Nomor Telepon *</span>
                                            </label>
                                            <input 
                                                type="tel" 
                                                wire:model="phone"
                                                class="input input-bordered @error('phone') input-error @enderror" 
                                                placeholder="08xxxxxxxxxx"
                                            >
                                            @error('phone')
                                                <label class="label">
                                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                                </label>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-control">
                                            <label class="label">
                                                <span class="label-text font-medium">Nama Perusahaan</span>
                                            </label>
                                            <input 
                                                type="text" 
                                                wire:model="company"
                                                class="input input-bordered @error('company') input-error @enderror" 
                                                placeholder="Opsional"
                                            >
                                            @error('company')
                                                <label class="label">
                                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                                </label>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <div>
                                            <h3 class="font-bold">Ketentuan Pembayaran:</h3>
                                            <div class="text-sm mt-1">
                                                <p>• Invoice akan dikirim ke email Anda dan berlaku selama 7 hari</p>
                                                <p>• Jika tidak dibayar dalam 7 hari, invoice akan otomatis dibatalkan</p>
                                                <p>• Project akan dimulai setelah pembayaran dikonfirmasi</p>
                                                <p>• Untuk perpanjangan, invoice baru akan digenerate otomatis</p>
                                                <p>• Project akan disuspend jika invoice perpanjangan tidak dibayar dalam 14 hari</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <button type="button" wire:click="previousStep" class="btn btn-outline">
                                            <i class="fas fa-arrow-left mr-2"></i>
                                            Kembali
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                                            <span wire:loading.remove>
                                                <i class="fas fa-credit-card mr-2"></i>
                                                Buat Pesanan & Invoice
                                            </span>
                                            <span wire:loading>
                                                <span class="loading loading-spinner loading-sm mr-2"></span>
                                                Memproses...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
