@extends('components.layouts.app')

@section('title', $template->name . ' - Template Detail')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary to-secondary text-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl font-bold mb-4">{{ $template->name }}</h1>
                <p class="text-xl opacity-90 mb-8">{{ $template->description }}</p>
                
                <div class="flex flex-wrap justify-center gap-2 mb-8">
                    <span class="badge badge-lg {{ $template->category_badge_class }} text-white">
                        {{ $template->category_text }}
                    </span>
                    @if($template->is_active)
                        <span class="badge badge-lg badge-success text-white">Available</span>
                    @endif
                </div>
                
                <div class="flex flex-wrap justify-center gap-4">
                    @if($template->demo_url)
                        <a href="{{ $template->demo_url }}" target="_blank" class="btn btn-outline btn-lg text-white border-white hover:bg-white hover:text-primary">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Live Demo
                        </a>
                    @endif
                    <a href="{{ route('checkout.template', $template->id) }}" class="btn btn-accent btn-lg">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Order Now
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Template Preview -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
                    @if($template->thumbnail_url)
                        <div class="aspect-video bg-gray-100">
                            <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" 
                                 class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <div class="text-center text-gray-500">
                                <i class="fas fa-image text-6xl mb-4"></i>
                                <p class="text-lg">Preview Coming Soon</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Features Section -->
                @if($template->features && count($template->features) > 0)
                <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Template Features</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($template->features as $feature)
                            <div class="flex items-center space-x-3">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check text-green-600 text-sm"></i>
                                </div>
                                <span class="text-gray-700">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Description Section -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">About This Template</h2>
                    <div class="prose max-w-none text-gray-600">
                        <p>{{ $template->description }}</p>
                        
                        <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">Perfect For:</h3>
                        <ul class="list-disc list-inside space-y-2">
                            @switch($template->category)
                                @case('business')
                                    <li>Corporate websites</li>
                                    <li>Professional services</li>
                                    <li>Consulting firms</li>
                                    <li>Business portfolios</li>
                                    @break
                                @case('ecommerce')
                                    <li>Online stores</li>
                                    <li>Product catalogs</li>
                                    <li>Shopping websites</li>
                                    <li>Retail businesses</li>
                                    @break
                                @case('portfolio')
                                    <li>Creative professionals</li>
                                    <li>Artists and designers</li>
                                    <li>Photographers</li>
                                    <li>Personal portfolios</li>
                                    @break
                                @default
                                    <li>Various business needs</li>
                                    <li>Professional websites</li>
                                    <li>Modern web presence</li>
                            @endswitch
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Pricing Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-8 sticky top-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Choose Your Package</h3>
                    
                    @if($products->count() > 0)
                        <div class="space-y-4">
                            @foreach($products as $product)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-primary hover:shadow-md transition-all">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-800">{{ $product->name }}</h4>
                                            <p class="text-sm text-gray-600">{{ $product->description }}</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold text-primary">
                                                Rp {{ number_format($product->price, 0, ',', '.') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                /{{ ucfirst($product->billing_cycle) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($product->features && count($product->features) > 0)
                                        <div class="mb-4">
                                            <ul class="text-sm text-gray-600 space-y-1">
                                                @foreach(array_slice($product->features, 0, 3) as $feature)
                                                    <li class="flex items-center">
                                                        <i class="fas fa-check text-green-500 text-xs mr-2"></i>
                                                        {{ $feature }}
                                                    </li>
                                                @endforeach
                                                @if(count($product->features) > 3)
                                                    <li class="text-gray-500">+ {{ count($product->features) - 3 }} more features</li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                    
                                    <a href="{{ route('checkout.template', $template->id) }}?product={{ $product->id }}" 
                                       class="btn btn-primary btn-block btn-sm">
                                        Select Package
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-box-open text-4xl mb-4"></i>
                            <p>No packages available</p>
                        </div>
                    @endif
                    
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-4">Need help choosing?</p>
                            <a href="#" class="btn btn-outline btn-sm">
                                <i class="fas fa-comments mr-2"></i>
                                Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Templates -->
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">Other Templates</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach(\App\Models\Template::where('is_active', true)->where('id', '!=', $template->id)->take(3)->get() as $relatedTemplate)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                        @if($relatedTemplate->thumbnail_url)
                            <div class="aspect-video bg-gray-100">
                                <img src="{{ $relatedTemplate->thumbnail_url }}" alt="{{ $relatedTemplate->name }}" 
                                     class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-3xl"></i>
                            </div>
                        @endif
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-lg font-semibold text-gray-800">{{ $relatedTemplate->name }}</h3>
                                <span class="badge {{ $relatedTemplate->category_badge_class }}">
                                    {{ $relatedTemplate->category_text }}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $relatedTemplate->description }}</p>
                            
                            <div class="flex space-x-2">
                                <a href="{{ route('templates.show', $relatedTemplate->id) }}" 
                                   class="btn btn-outline btn-sm flex-1">
                                    View Details
                                </a>
                                <a href="{{ route('checkout.template', $relatedTemplate->id) }}" 
                                   class="btn btn-primary btn-sm flex-1">
                                    Order
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection