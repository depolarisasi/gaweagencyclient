<div>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Konfigurasi Produk</h1>

        @if ($template)
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-2">Template Terpilih: {{ $template->name }}</h2>
                <p>{{ $template->description }}</p>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2">Pilih Siklus Penagihan</h2>
            <div class="mb-4">
                <label for="billing_cycle" class="block text-gray-700 text-sm font-bold mb-2">Siklus Penagihan:</label>
                <select wire:model.live="selectedProductId" id="billing_cycle" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Pilih Siklus Penagihan</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} - Rp{{ number_format($product->price, 0, ',', '.') }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Bagian untuk Add-on (jika ada) --}}
            {{-- <h2 class="text-xl font-semibold mb-2">Pilih Add-on</h2>
            @foreach ($addons as $addon)
                <div class="flex items-center mb-2">
                    <input type="checkbox" wire:model="selectedAddons" value="{{ $addon->id }}" id="addon-{{ $addon->id }}" class="mr-2">
                    <label for="addon-{{ $addon->id }}">{{ $addon->name }} - Rp{{ number_format($addon->price, 0, ',', '.') }}</label>
                </div>
            @endforeach --}}
        </div>

        <button wire:click="configureProduct" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Lanjutkan ke Checkout
        </button>
    </div>
</div>
