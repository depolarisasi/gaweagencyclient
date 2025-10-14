<div>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Ringkasan Pesanan</h1>

        @if (session()->has('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2">Detail Pesanan</h2>
            @if ($template)
                <p><strong>Template:</strong> {{ $template->name }}</p>
            @endif
            @if ($product)
                <p><strong>Siklus Penagihan:</strong> {{ $product->name }}</p>
            @endif
            <p class="text-lg font-bold mt-4">Total: Rp{{ number_format($totalPrice, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-2">Informasi Pribadi</h2>
            <form wire:submit.prevent="registerAndCheckout">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap:</label>
                    <input type="text" wire:model.live="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('name') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                    <input type="email" wire:model.live="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('email') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="domain" class="block text-gray-700 text-sm font-bold mb-2">Nama Domain:</label>
                    <input type="text" wire:model.live="domain" id="domain" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('domain') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>

                @if (!$isRegistered)
                    <h3 class="text-lg font-semibold mb-2">Daftar Akun Baru</h3>
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                        <input type="password" wire:model.live="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('password') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password:</label>
                        <input type="password" wire:model.live="password_confirmation" id="password_confirmation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                @endif

                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Lanjutkan ke Pembayaran
                </button>
            </form>
        </div>
    </div>
</div>
