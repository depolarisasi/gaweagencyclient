<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Detail Invoice #{{ $invoice->id }}</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Status</label>
                <p class="text-gray-800">{{ ucfirst($invoice->status) }}</p>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Metode Pembayaran</label>
                <p class="text-gray-800">{{ strtoupper($invoice->payment_method ?? 'N/A') }}</p>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Tripay Ref</label>
                <p class="text-gray-800">{{ $invoice->tripay_reference ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Jatuh Tempo</label>
                <p class="text-gray-800">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y H:i') }}</p>
            </div>
        </div>

        <hr class="my-4">

        <h2 class="text-xl font-semibold mb-2">Ringkasan Biaya</h2>
        @php
            $order = $invoice->order;
            $subscriptionAmount = $order->subscription_amount ?? 0;
            $domainAmount = $order->domain_amount ?? 0;
            $addons = $order ? $order->orderAddons : collect();
            $addonsAmount = $order->addons_amount ?? ($addons->sum('price'));
            $subtotal = $invoice->amount ?? ($subscriptionAmount + $domainAmount + $addonsAmount);
            $feeCustomer = $invoice->fee_customer ?? ($invoice->tripay_data['fee_customer'] ?? 0);
            $totalBayar = $subtotal + $feeCustomer;
        @endphp

        <div class="space-y-1 text-gray-800">
            <p>Subscription: <span class="font-semibold">Rp {{ number_format($subscriptionAmount, 0, ',', '.') }}</span></p>
            <p>Domain: <span class="font-semibold">Rp {{ number_format($domainAmount, 0, ',', '.') }}</span></p>
            <div>
                <p class="font-semibold">Add-ons:</p>
                @forelse ($addons as $addon)
                    <p class="ml-4">- {{ $addon->addon_details['name'] ?? 'Addon' }}: Rp {{ number_format($addon->price, 0, ',', '.') }}</p>
                @empty
                    <p class="ml-4 text-gray-500">Tidak ada add-on</p>
                @endforelse
                <p class="ml-4">Subtotal Add-ons: <span class="font-semibold">Rp {{ number_format($addonsAmount, 0, ',', '.') }}</span></p>
            </div>
        </div>

        <div class="mt-4">
            <p>Subtotal: <span class="font-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></p>
            <p>Biaya Admin (Tripay): <span class="font-bold">Rp {{ number_format($feeCustomer, 0, ',', '.') }}</span></p>
            <p>Total Bayar: <span class="font-extrabold">Rp {{ number_format($totalBayar, 0, ',', '.') }}</span></p>
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
