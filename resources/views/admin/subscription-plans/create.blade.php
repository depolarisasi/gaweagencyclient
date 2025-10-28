<x-layouts.admin>
    @section('title', 'Tambah Paket Langganan')

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Paket Langganan</h1>
                <p class="text-gray-600">Buat paket langganan baru dengan konfigurasi harga dan siklus pembayaran</p>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow">
            <form method="POST" action="{{ route('admin.subscription-plans.store') }}" class="p-6 space-y-6">
                @csrf
                
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Paket <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" 
                               class="input input-bordered w-full @error('name') input-error @enderror" 
                               placeholder="Contoh: Basic Plan, Premium Plan">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Harga <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" name="price" value="{{ old('price') }}" 
                                   class="input input-bordered w-full pl-12 @error('price') input-error @enderror" 
                                   placeholder="100000" min="0" step="1000">
                        </div>
                        @error('price')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Billing Cycle -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Siklus Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <select name="billing_cycle" class="select select-bordered w-full @error('billing_cycle') select-error @enderror">
                            <option value="">Pilih Siklus Pembayaran</option>
                            <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="quarterly" {{ old('billing_cycle') === 'quarterly' ? 'selected' : '' }}>Triwulan (3 Bulan)</option>
                            <option value="semi_annual" {{ old('billing_cycle') === 'semi_annual' ? 'selected' : '' }}>Semi Annual (6 Bulan)</option>
                            <option value="annual" {{ old('billing_cycle') === 'annual' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                        @error('billing_cycle')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Durasi (Bulan) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="cycle_months" value="{{ old('cycle_months') }}" 
                               class="input input-bordered w-full @error('cycle_months') input-error @enderror" 
                               placeholder="1" min="1" max="12">
                        <p class="text-sm text-gray-500 mt-1">Durasi dalam bulan untuk siklus pembayaran ini</p>
                        @error('cycle_months')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" rows="3" 
                              class="textarea textarea-bordered w-full @error('description') textarea-error @enderror" 
                              placeholder="Deskripsi singkat tentang paket ini...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Features -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fitur-fitur</label>
                    <div id="features-container" class="space-y-2">
                        @if(old('features'))
                            @foreach(old('features') as $index => $feature)
                                <div class="flex items-center space-x-2 feature-item">
                                    <input type="text" name="features[]" value="{{ $feature }}" 
                                           class="input input-bordered flex-1" 
                                           placeholder="Contoh: 10 Template Premium">
                                    <button type="button" class="btn btn-outline btn-error btn-sm remove-feature">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center space-x-2 feature-item">
                                <input type="text" name="features[]" 
                                       class="input input-bordered flex-1" 
                                       placeholder="Contoh: 10 Template Premium">
                                <button type="button" class="btn btn-outline btn-error btn-sm remove-feature">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                    <button type="button" id="add-feature" class="btn btn-outline btn-sm mt-2">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Fitur
                    </button>
                    @error('features')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Settings -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Diskon (%)</label>
                        <input type="number" name="discount_percentage" value="{{ old('discount_percentage', 0) }}" 
                               class="input input-bordered w-full @error('discount_percentage') input-error @enderror" 
                               placeholder="0" min="0" max="100" step="0.01">
                        @error('discount_percentage')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Urutan Tampil</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" 
                               class="input input-bordered w-full @error('sort_order') input-error @enderror" 
                               placeholder="0" min="0">
                        <p class="text-sm text-gray-500 mt-1">Semakin kecil, semakin atas</p>
                        @error('sort_order')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <span class="label-text">Paket Aktif</span>
                                <input type="checkbox" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }} 
                                       class="checkbox checkbox-primary">
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <span class="label-text">Paket Popular</span>
                                <input type="checkbox" name="is_popular" value="1" 
                                       {{ old('is_popular') ? 'checked' : '' }} 
                                       class="checkbox checkbox-warning">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Paket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const featuresContainer = document.getElementById('features-container');
            const addFeatureBtn = document.getElementById('add-feature');

            // Add feature
            addFeatureBtn.addEventListener('click', function() {
                const featureItem = document.createElement('div');
                featureItem.className = 'flex items-center space-x-2 feature-item';
                featureItem.innerHTML = `
                    <input type="text" name="features[]" 
                           class="input input-bordered flex-1" 
                           placeholder="Contoh: 10 Template Premium">
                    <button type="button" class="btn btn-outline btn-error btn-sm remove-feature">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                featuresContainer.appendChild(featureItem);
            });

            // Remove feature
            featuresContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-feature')) {
                    const featureItems = featuresContainer.querySelectorAll('.feature-item');
                    if (featureItems.length > 1) {
                        e.target.closest('.feature-item').remove();
                    }
                }
            });

            // Auto-fill cycle_months based on billing_cycle
            const billingCycleSelect = document.querySelector('select[name="billing_cycle"]');
            const cycleMonthsInput = document.querySelector('input[name="cycle_months"]');

            billingCycleSelect.addEventListener('change', function() {
                const cycleMap = {
                    'monthly': 1,
                    'quarterly': 3,
                    'semi_annual': 6,
                    'annual': 12
                };
                
                if (cycleMap[this.value]) {
                    cycleMonthsInput.value = cycleMap[this.value];
                }
            });
        });
    </script>
</x-layouts.admin>