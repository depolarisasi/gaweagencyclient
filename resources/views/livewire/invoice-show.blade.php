<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Detail Invoice #{{ $invoice->id }}</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
            <p class="text-gray-800">{{ ucfirst($invoice->status) }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah:</label>
            <p class="text-gray-800">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Jatuh Tempo:</label>
            <p class="text-gray-800">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y H:i') }}</p>
        </div>

        @if ($invoice->status == 'unpaid' && $paymentUrl)
            <div class="mt-6">
                <a href="{{ $paymentUrl }}" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Lanjutkan ke Pembayaran
                </a>
            </div>
        @elseif ($invoice->status == 'paid')
            <div class="mt-6 text-green-600 font-bold">
                Invoice ini sudah dibayar.
            </div>
        @else
            <div class="mt-6 text-red-600 font-bold">
                Invoice ini tidak dapat dibayar atau telah kedaluwarsa.
            </div>
        @endif
    </div>
</div>
