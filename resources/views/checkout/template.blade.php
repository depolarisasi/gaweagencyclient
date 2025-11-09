@extends('layouts.app')

@section('title', 'Pilih Template - Checkout')

@section('content')
<div class="min-h-screen bg-base-300 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    <!-- Langkah 1: Domain (selesai) -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-2 text-sm text-gray-600 font-medium">Domain</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Langkah 2: Template (aktif) -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            2
                        </div>
                        <span class="ml-2 text-sm text-blue-600 font-medium">Template</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Langkah 3: Info Personal -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                            3
                        </div>
                        <span class="ml-2 text-sm text-gray-500">Info Personal</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Langkah 4: Paket & Add-ons -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                            4
                        </div>
                        <span class="ml-2 text-sm text-gray-500">Paket & Add-ons</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Langkah 5: Ringkasan -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center text-sm font-medium">
                            5
                        </div>
                        <span class="ml-2 text-sm text-gray-500">Ringkasan</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
                    
                    <!-- Langkah 6: Pembayaran -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center text-sm font-medium">
                            6
                        </div>
                        <span class="ml-2 text-sm text-gray-500">Pembayaran</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-8">
                    <form method="POST" action="{{ route('checkout.template.post') }}" id="templateForm">
                        @csrf
                        <input type="hidden" name="template_id" id="selected_template_id" value="{{ old('template_id', $selectedTemplateId ?? null) }}">

                        <!-- Template Selection Cards -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pilih Template</h3>
                            <p class="text-sm text-gray-600 mb-6">Pilih salah satu template di bawah ini untuk website Anda.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Template Options -->
                                @foreach($templates as $template)
                                <div class="template-card border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors {{ (old('template_id', $selectedTemplateId ?? null) == $template->id) ? 'border-blue-500 bg-blue-50' : '' }}" 
                                     data-template-id="{{ $template->id }}">
                                    <div class="flex flex-col">
                                        <!-- Template Thumbnail -->
                                        <div class="w-full h-32 bg-gray-100 rounded-lg overflow-hidden mb-3">
                                            @if($template->thumbnail_url)
                                                <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Template Info -->
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 mb-1">{{ $template->name }}</h4>
                                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($template->description, 80) }}</p>
                                            
                                            @if($template->category)
                                                <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full">{{ $template->category }}</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Demo Link -->
                                        @if($template->demo_url)
                                            <div class="mt-3">
                                                <a href="{{ $template->demo_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    Lihat Demo →
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            @error('template_id')
                                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <a href="{{ route('checkout.domain') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Kembali ke Domain
                            </a>
                            <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                {{ __('Lanjutkan') }}
                                <svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const templateCards = document.querySelectorAll('.template-card');
        const hiddenInput = document.getElementById('selected_template_id');
        
        templateCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove selection from all cards
                templateCards.forEach(c => {
                    c.classList.remove('border-blue-500', 'bg-blue-50');
                    c.classList.add('border-gray-200');
                });
                
                // Add selection to clicked card
                this.classList.remove('border-gray-200');
                this.classList.add('border-blue-500', 'bg-blue-50');
                
                // Update hidden input
                hiddenInput.value = this.dataset.templateId;
            });
        });
    });
</script>
@endsection