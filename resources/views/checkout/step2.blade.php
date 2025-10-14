<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Checkout - Langkah 2: Pilih Add-on') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Produk Terpilih: {{ $selectedProduct->name }} - Rp{{ number_format($selectedProduct->price, 0, ',', '.') }}</h3>

                    <form method="POST" action="{{ route('checkout.step3') }}">
                        @csrf

                        <!-- Add-on Selection -->
                        <div class="mb-4">
                            <x-label :value="__('Pilih Add-on (Opsional)')" />
                            @forelse($addons as $addon)
                                <div class="flex items-center mt-2">
                                    <input type="checkbox" name="addons[]" id="addon_{{ $addon->id }}" value="{{ $addon->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label for="addon_{{ $addon->id }}" class="ml-2 text-sm text-gray-600">{{ $addon->name }} - Rp{{ number_format($addon->price, 0, ',', '.') }}</label>
                                </div>
                            @empty
                                <p class="text-sm text-gray-600">Tidak ada add-on tersedia.</p>
                            @endforelse
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <a href="{{ route('checkout.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Kembali') }}
                            </a>
                            <x-button class="ml-4">
                                {{ __('Lanjutkan') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>