<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Hero Section -->
    <div class="hero min-h-[60vh] bg-gradient-to-r from-primary to-secondary">
        <div class="hero-content text-center text-primary-content">
            <div class="max-w-4xl">
                <h1 class="text-5xl font-bold mb-6">Template Website Profesional</h1>
                <p class="text-xl mb-8 opacity-90">
                    Pilih template website yang sesuai dengan kebutuhan bisnis Anda. 
                    Semua template sudah termasuk hosting, domain, dan maintenance profesional.
                </p>
                <div class="stats stats-horizontal shadow bg-base-100 text-base-content">
                    <div class="stat">
                        <div class="stat-title">Template Tersedia</div>
                        <div class="stat-value text-primary">{{ $templates->count() }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Setup Time</div>
                        <div class="stat-value text-secondary">1-3 Hari</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Support</div>
                        <div class="stat-value text-accent">24/7</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Selection Section -->
    <div class="container mx-auto px-4 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Pilih Template Website Anda</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Setiap template dirancang khusus untuk memberikan pengalaman terbaik bagi pengunjung website Anda.
            </p>
        </div>

        @if($templates->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($templates as $template)
                    <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                        @if($template->thumbnail_url)
                            <figure class="h-48 overflow-hidden">
                                <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" 
                                     class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                            </figure>
                        @endif
                        
                        <div class="card-body">
                            <div class="flex justify-between items-start mb-2">
                                <h2 class="card-title text-xl font-bold text-primary">{{ $template->name }}</h2>
                                <div class="badge {{ $template->category_badge_class }}">{{ $template->category_text }}</div>
                            </div>
                            
                            <p class="text-gray-600 mb-4">{{ $template->description }}</p>
                            
                            @if($template->features)
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-800 mb-2">Fitur Template:</h4>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($template->features, 0, 3) as $feature)
                                            <div class="badge badge-outline badge-sm">{{ $feature }}</div>
                                        @endforeach
                                        @if(count($template->features) > 3)
                                            <div class="badge badge-outline badge-sm">+{{ count($template->features) - 3 }} lainnya</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                            <div class="card-actions justify-between items-center mt-4">
                                <div class="flex space-x-2 flex-1">
                                    @if($template->demo_url)
                                        <a href="{{ $template->demo_url }}" target="_blank" 
                                           class="btn btn-outline btn-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Preview
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route('templates.show', $template->id) }}" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Details
                                    </a>
                                </div>
                                
                                <button 
                                    wire:click="selectTemplate({{ $template->id }})"
                                    class="btn btn-primary btn-sm"
                                >
                                    <i class="fas fa-rocket mr-2"></i>
                                    Pilih Template
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-6xl text-gray-300 mb-4">
                    <i class="fas fa-palette"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Template Sedang Disiapkan</h3>
                <p class="text-gray-500">Template website profesional akan segera tersedia. Silakan kembali lagi nanti.</p>
            </div>
        @endif
    </div>

    <!-- Pricing Section -->
    @if($products->count() > 0)
        <div class="bg-base-200 py-16">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Paket Berlangganan</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Pilih paket yang sesuai dengan kebutuhan dan budget Anda. Semua paket sudah termasuk hosting dan maintenance.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min($products->count(), 4) }} gap-6">
                    @foreach($products as $product)
                        <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <div class="card-body text-center">
                                <h3 class="card-title justify-center text-lg font-bold text-primary mb-2">{{ $product->name }}</h3>
                                <div class="text-3xl font-bold text-gray-800 mb-2">{{ $product->formatted_price }}</div>
                                <div class="text-sm text-gray-500 mb-4">{{ $product->billing_cycle_text }}</div>
                                
                                <p class="text-gray-600 mb-4">{{ $product->description }}</p>
                                
                                @if($product->features)
                                    <ul class="text-sm text-gray-600 space-y-1 mb-4">
                                        @foreach($product->features as $feature)
                                            <li class="flex items-center">
                                                <i class="fas fa-check text-success mr-2"></i>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                
                                @if($product->setup_time_days)
                                    <div class="badge badge-secondary mb-4">Setup {{ $product->setup_time_days }} hari</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-primary to-secondary py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-primary-content mb-4">Siap Memulai Website Profesional Anda?</h2>
            <p class="text-xl text-primary-content opacity-90 mb-8 max-w-2xl mx-auto">
                Bergabunglah dengan ratusan bisnis yang telah mempercayakan website mereka kepada kami.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#templates" class="btn btn-accent btn-lg">
                    <i class="fas fa-rocket mr-2"></i>
                    Pilih Template Sekarang
                </a>
                <a href="#pricing" class="btn btn-outline btn-lg text-primary-content border-primary-content hover:bg-primary-content hover:text-primary">
                    <i class="fas fa-info-circle mr-2"></i>
                    Lihat Paket Harga
                </a>
            </div>
        </div>
    </div>
</div>
