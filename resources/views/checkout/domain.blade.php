@extends('layouts.app')

@section('title', 'Checkout - Domain')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    <!-- Langkah 1: Domain (aktif) -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                        <span class="ml-2 text-sm font-medium text-blue-600">Domain</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>

                    <!-- Langkah 2: Template -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">2</div>
                        <span class="ml-2 text-sm text-gray-500">Template</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>

                    <!-- Langkah 3: Info Personal -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">3</div>
                        <span class="ml-2 text-sm text-gray-500">Info Personal</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>

                    <!-- Langkah 4: Paket & Add-ons -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">4</div>
                        <span class="ml-2 text-sm text-gray-500">Paket & Add-ons</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>

                    <!-- Langkah 5: Ringkasan -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">5</div>
                        <span class="ml-2 text-sm text-gray-500">Ringkasan</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>

                    <!-- Langkah 6: Pembayaran -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">6</div>
                        <span class="ml-2 text-sm text-gray-500">Pembayaran</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                <h1 class="text-2xl font-bold text-gray-900">Pilih Domain</h1>
                <p class="text-gray-600 mt-1">Pilih dan verifikasi domain untuk website Anda</p>
            </div>

            <form action="{{ route('checkout.domain.post') }}" method="POST" class="p-6 space-y-8">
                @csrf

                <!-- Alert: Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-error">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.938 19h14.124c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.206 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Hint: Domain Guidance -->
                <div class="alert alert-info">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18a9 9 0 110-18 9 9 0 010 18z"></path>
                    </svg>
                    <div>
                        <div class="font-medium">Tips pemilihan domain</div>
                        <div class="text-sm">Jika sudah punya domain, pilih opsi "Punya domain sendiri". Jika belum, ketik nama domain, pilih ekstensi (TLD), lalu klik cek ketersediaan. Harga domain ditampilkan di bawah pilihan TLD dan telah termasuk dalam paket layanan.</div>
                    </div>
                </div>

                <!-- Template Info -->
                @if($template)
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Template Terpilih</h3>
                    <div class="flex items-center space-x-4">
                        @if($template->thumbnail_url)
                            <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" class="w-16 h-16 object-cover rounded-lg">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $template->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $template->description }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Domain Selection -->
                <div class="space-y-6 p-6">
                    <h3 class="text-lg font-medium text-gray-900">Pilihan Domain</h3>
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        @livewire('domain-selector')
                    </div>

                    <!-- Hidden inputs for domain data -->
                    @php
                        $domainData = session('checkout.domain', []);
                        $domainType = $domainData['type'] ?? '';
                        $domainName = $domainData['name'] ?? '';
                        $debugData = json_encode([
                            'sessionData' => $domainData,
                            'domainType' => $domainType,
                            'domainName' => $domainName,
                        ]);
                    @endphp

                    <input type="hidden" name="domain_type" value="{{ $domainType }}" id="domain_type_input">
                    <input type="hidden" name="domain_name" value="{{ $domainName }}" id="domain_name_input">
                    @php
                        $domainTld = $domainData['tld'] ?? '';
                        $domainPrice = $domainData['price'] ?? '';
                    @endphp
                    <input type="hidden" name="domain_tld" value="{{ $domainTld }}" id="domain_tld_input">
                    <input type="hidden" name="domain_price" value="{{ is_numeric($domainPrice) ? $domainPrice : '' }}" id="domain_price_input">

                    <div class="flex justify-end pt-6">
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Lanjutkan ke Template
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Session Toasts -->
            @if(session('success'))
                <div class="toast toast-top toast-end">
                    <div class="alert alert-success">
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="toast toast-top toast-end">
                    <div class="alert alert-error">
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Define early so Livewire JS calls won't fail
window.updateHiddenInputs = function(data) {
    const domainTypeInput = document.getElementById('domain_type_input');
    const domainNameInput = document.getElementById('domain_name_input');
    const domainTldInput = document.getElementById('domain_tld_input');
    const domainPriceInput = document.getElementById('domain_price_input');
    if (domainTypeInput && domainNameInput && data) {
        domainTypeInput.value = data.type || '';
        domainNameInput.value = data.name || '';
        if (domainTldInput) {
            domainTldInput.value = data.tld || '';
        }
        if (domainPriceInput) {
            const priceVal = (data.price !== undefined && data.price !== null) ? data.price : '';
            domainPriceInput.value = priceVal;
        }
        console.log('Hidden inputs updated:', data);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Domain step loaded. Session data:', {!! $debugData !!});

    function ensureHiddenInputsFilled() {
        const domainTypeInput = document.getElementById('domain_type_input');
        const domainNameInput = document.getElementById('domain_name_input');

        // If already filled, skip
        if (domainTypeInput && domainTypeInput.value && domainNameInput && domainNameInput.value && domainNameInput.value.trim() !== '') {
            return;
        }

        // Try to infer from visible controls
        const domainTypeRadio = document.querySelector('input[wire\\:model\\.live="domainType"]:checked');
        const domainNameField = document.querySelector('input[wire\\:model\\.live\\.debounce\\.500ms="domainName"]');
        const selectedTldRadio = document.querySelector('input[wire\\:model\\.live="selectedTld"]:checked');

        const inferredType = domainTypeRadio ? domainTypeRadio.value : (window.lastDomainUpdateData ? window.lastDomainUpdateData.type : '');
        const namePart = domainNameField ? domainNameField.value.trim() : '';
        const tldPart = selectedTldRadio ? selectedTldRadio.value : '';
        const inferredName = window.lastDomainUpdateData && window.lastDomainUpdateData.name
            ? window.lastDomainUpdateData.name
            : (namePart ? (tldPart ? (namePart + '.' + tldPart) : namePart) : '');

        if (domainTypeInput) domainTypeInput.value = inferredType || domainTypeInput.value || '';
        if (domainNameInput) domainNameInput.value = inferredName || domainNameInput.value || '';
    }

    function isDomainDataValid() {
        const domainTypeInput = document.getElementById('domain_type_input');
        const domainNameInput = document.getElementById('domain_name_input');
        // Try to fill missing values from visible controls/session first
        ensureHiddenInputsFilled();
        // Minimal validasi: type & name wajib ada
        return domainTypeInput && domainNameInput && domainTypeInput.value && domainNameInput.value && domainNameInput.value.trim() !== '';
    }

    document.addEventListener('livewire:init', () => {
        if (window.Livewire) {
            window.Livewire.on('domainUpdated', (data) => {
                console.log('Livewire domainUpdated event received:', data);
                window.lastDomainUpdateData = data;
                window.updateHiddenInputs(data);
            });
        }
    });

    window.addEventListener('domainUpdated', function(event) {
        console.log('Browser domainUpdated event received:', event.detail);
        window.lastDomainUpdateData = event.detail;
        window.updateHiddenInputs(event.detail);

        const dn = (event.detail && event.detail.name) ? event.detail.name : null;
        if (dn) {
            showToast(`Domain diperbarui: ${dn}`, 'info');
        }
    });

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Ensure hidden inputs are synchronized before validation
            ensureHiddenInputsFilled();
            if (!isDomainDataValid()) {
                // Inline alert fallback
                showToast('Silakan pilih dan verifikasi domain terlebih dahulu.', 'error');
                e.preventDefault();
                return false;
            }
            console.log('Domain data valid, submitting...');
            return true;
        });
    }

    // Small toast helper (DaisyUI)
    function showToast(message, type = 'info') {
        const container = document.createElement('div');
        container.className = 'toast toast-top toast-end';
        const alert = document.createElement('div');
        alert.className = `alert ${type === 'success' ? 'alert-success' : type === 'error' ? 'alert-error' : 'alert-info'}`;
        const span = document.createElement('span');
        span.textContent = message;
        alert.appendChild(span);
        container.appendChild(alert);
        document.body.appendChild(container);
        setTimeout(() => {
            container.remove();
        }, 2500);
    }
});
</script>
@endsection