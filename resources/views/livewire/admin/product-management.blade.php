<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Manajemen Produk</h1>
            <p class="text-gray-600">Kelola produk dan layanan yang tersedia</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>
            Tambah Produk
        </button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Cari Produk</span>
                    </label>
                    <input 
                        type="text" 
                        wire:model.live="search"
                        class="input input-bordered" 
                        placeholder="Nama atau deskripsi..."
                    >
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Tipe Produk</span>
                    </label>
                    <select wire:model.live="filterType" class="select select-bordered">
                        <option value="">Semua Tipe</option>
                        <option value="website">Website</option>
                        <option value="mobile_app">Mobile App</option>
                        <option value="web_app">Web App</option>
                        <option value="ecommerce">E-commerce</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Status</span>
                    </label>
                    <select wire:model.live="filterStatus" class="select select-bordered">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Aksi</span>
                    </label>
                    <button wire:click="$refresh" class="btn btn-outline">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Tipe</th>
                            <th>Harga</th>
                            <th>Siklus</th>
                            <th>Setup Time</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <div>
                                        <div class="font-semibold">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500 truncate max-w-xs">
                                            {{ Str::limit($product->description, 50) }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-outline">
                                        {{ ucfirst(str_replace('_', ' ', $product->type)) }}
                                    </span>
                                </td>
                                <td class="font-semibold">{{ $product->formatted_price }}</td>
                                <td>{{ $product->billing_cycle_text }}</td>
                                <td>{{ $product->setup_time }}</td>
                                <td>
                                    <button 
                                        wire:click="toggleStatus({{ $product->id }})"
                                        class="badge {{ $product->is_active ? 'badge-success' : 'badge-error' }} cursor-pointer"
                                    >
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button 
                                            wire:click="openEditModal({{ $product->id }})"
                                            class="btn btn-sm btn-outline btn-primary"
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button 
                                            wire:click="delete({{ $product->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus produk ini?"
                                            class="btn btn-sm btn-outline btn-error"
                                            title="Hapus"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="text-gray-500">
                                        <i class="fas fa-box-open text-4xl mb-4"></i>
                                        <p>Belum ada produk yang tersedia</p>
                                        <button wire:click="openCreateModal" class="btn btn-primary btn-sm mt-2">
                                            Tambah Produk Pertama
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($products->hasPages())
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-5xl max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $modalTitle }}</h3>
                            <p class="text-sm text-gray-500">{{ $editingProduct ? 'Perbarui informasi produk' : 'Tambahkan produk baru ke katalog' }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="btn btn-sm btn-circle btn-ghost">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="save" class="space-y-6">
                    <!-- Basic Information Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Informasi Dasar</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Nama Produk *</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        wire:model="name"
                                        class="input input-bordered pl-10 focus:input-primary @error('name') input-error @enderror" 
                                        placeholder="Masukkan nama produk"
                                    >
                                </div>
                                @error('name')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Tipe Produk *</span>
                                </label>
                                <select 
                                    wire:model="type"
                                    class="select select-bordered focus:select-primary @error('type') select-error @enderror"
                                >
                                    <option value="website">🌐 Website</option>
                                    <option value="mobile_app">📱 Mobile App</option>
                                    <option value="web_app">💻 Web App</option>
                                    <option value="ecommerce">🛒 E-commerce</option>
                                    <option value="custom">⚙️ Custom</option>
                                </select>
                                @error('type')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Deskripsi Produk</h4>
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Deskripsi Detail *</span>
                            </label>
                            <textarea 
                                wire:model="description"
                                class="textarea textarea-bordered focus:textarea-primary @error('description') textarea-error @enderror" 
                                rows="4"
                                placeholder="Jelaskan detail produk, fitur utama, dan keunggulan yang ditawarkan..."
                            ></textarea>
                            @error('description')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                            <label class="label">
                                <span class="label-text-alt text-gray-500">Deskripsi yang baik akan membantu klien memahami produk Anda</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Pricing & Timeline Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Harga & Timeline</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Harga *</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">Rp</span>
                                    </div>
                                    <input 
                                        type="number" 
                                        wire:model="price"
                                        class="input input-bordered pl-10 focus:input-primary @error('price') input-error @enderror" 
                                        placeholder="0"
                                        step="0.01"
                                        min="0"
                                    >
                                </div>
                                @error('price')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Siklus Tagihan *</span>
                                </label>
                                <select 
                                    wire:model="billing_cycle"
                                    class="select select-bordered focus:select-primary @error('billing_cycle') select-error @enderror"
                                >
                                    <option value="monthly">📅 Bulanan (1 bulan)</option>
                                    <option value="6_months">📅 Semester (6 bulan)</option>
                                    <option value="annually">📅 Tahunan (12 bulan)</option>
                                    <option value="2_years">📅 2 Tahunan (24 bulan)</option>
                                    <option value="3_years">📅 3 Tahunan (36 bulan)</option>
                                </select>
                                @error('billing_cycle')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Waktu Setup *</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        wire:model="setup_time"
                                        class="input input-bordered pl-10 focus:input-primary @error('setup_time') input-error @enderror" 
                                        placeholder="1-2 minggu"
                                    >
                                </div>
                                @error('setup_time')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Settings Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Pengaturan Produk</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Urutan Tampil</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </div>
                                    <input 
                                        type="number" 
                                        wire:model="sort_order"
                                        class="input input-bordered pl-10 focus:input-primary @error('sort_order') input-error @enderror" 
                                        placeholder="0"
                                        min="0"
                                    >
                                </div>
                                @error('sort_order')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Angka lebih kecil akan tampil lebih dulu</span>
                                </label>
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Status Produk</span>
                                </label>
                                <div class="flex items-center space-x-3 mt-2 p-3 bg-white rounded-lg border border-gray-200">
                                    <input 
                                        type="checkbox" 
                                        wire:model="is_active"
                                        class="checkbox checkbox-primary" 
                                    >
                                    <div class="flex-1">
                                        <span class="label-text font-medium">Produk Aktif</span>
                                        <p class="text-xs text-gray-500 mt-1">Produk akan ditampilkan di katalog</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Features Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800">Fitur Produk</h4>
                                <p class="text-sm text-gray-500">Tambahkan fitur-fitur unggulan produk</p>
                            </div>
                        </div>
                        
                        <!-- Add Feature Input -->
                        <div class="flex gap-2 mb-4">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model="newFeature"
                                    wire:keydown.enter.prevent="addFeature"
                                    class="input input-bordered pl-10 focus:input-primary" 
                                    placeholder="Contoh: Responsive Design, SEO Optimized, dll..."
                                >
                            </div>
                            <button 
                                type="button"
                                wire:click="addFeature"
                                class="btn btn-primary"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Tambah
                            </button>
                        </div>
                        
                        <!-- Features List -->
                        @if(count($features) > 0)
                            <div class="space-y-2">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Fitur yang sudah ditambahkan:</h5>
                                @foreach($features as $index => $feature)
                                    <div class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
                                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <span class="flex-1 text-gray-800">{{ $feature }}</span>
                                        <button 
                                            type="button"
                                            wire:click="removeFeature({{ $index }})"
                                            class="btn btn-sm btn-circle btn-ghost text-red-500 hover:bg-red-50"
                                            title="Hapus fitur"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm italic">Belum ada fitur ditambahkan</p>
                                <p class="text-xs text-gray-400 mt-1">Tambahkan fitur untuk menjelaskan keunggulan produk</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Modal Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Fields marked with * are required
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="button" wire:click="closeModal" class="btn btn-outline">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    {{ $editingProduct ? 'Update Produk' : 'Simpan Produk' }}
                                </span>
                                <span wire:loading>
                                    <span class="loading loading-spinner loading-sm mr-2"></span>
                                    {{ $editingProduct ? 'Mengupdate...' : 'Menyimpan...' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
