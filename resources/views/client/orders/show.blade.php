@extends('layouts.client')

@section('title', 'Order Details')

@section('content')
    <div class="p-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-shopping-cart text-blue-600 mr-2"></i>
                        Order #{{ $order->id }}
                    </h1>
                    <p class="text-gray-600">{{ $order->product->name ?? ($order->subscriptionPlan->name ?? 'N/A') }}</p>
                </div>
                <div>
                    @php
                        $statusClasses = [
                            'pending' => 'badge-warning',
                            'processing' => 'badge-info',
                            'active' => 'badge-success',
                            'completed' => 'badge-success',
                            'suspended' => 'badge-error',
                            'cancelled' => 'badge-error'
                        ];
                        $statusClass = $statusClasses[$order->status] ?? 'badge-neutral';
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold text-gray-800">Ringkasan</h3>
                    <div class="text-sm text-gray-600 mt-2 space-y-1">
                        <div>Created: {{ $order->created_at->format('d M Y') }}</div>
                        @if($order->next_due_date)
                            <div>Next Due: {{ optional($order->next_due_date)->format('d M Y') }}</div>
                        @endif
                        @if($order->subscriptionPlan)
                            <div>Billing: {{ $order->subscriptionPlan->billing_cycle_label ?? $order->billing_cycle }}</div>
                        @else
                            <div>Billing: {{ $order->billing_cycle ?? '-' }}</div>
                        @endif
                        @php
                            $latestInvoice = $order->invoice ?? null;
                        @endphp
                        @if($latestInvoice && $latestInvoice->billing_period_start && $latestInvoice->billing_period_end)
                            <div>Periode: {{ $latestInvoice->billing_period_start->format('d M Y') }} - {{ $latestInvoice->billing_period_end->format('d M Y') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            @if($order->subscriptionPlan)
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold text-gray-800">Paket Subscription</h3>
                    <div class="text-sm text-gray-600 mt-2 space-y-1">
                        <div class="font-medium text-gray-800">{{ $order->subscriptionPlan->name }}</div>
                        <div>Harga: {{ 'Rp ' . number_format($order->subscriptionPlan->price ?? 0, 0, ',', '.') }}</div>
                        <div>Siklus: {{ $order->subscriptionPlan->billing_cycle_label ?? ucfirst($order->subscriptionPlan->billing_cycle ?? '-') }}</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold text-gray-800">Template</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $order->template->name ?? '-' }}</p>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold text-gray-800">Domain</h3>
                    <p class="text-sm text-gray-600 mt-2">{{ $order->domain_name ?? '-' }}</p>
                    <p class="text-sm text-gray-500">Type: {{ $order->domain_type ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Add-ons -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-header bg-gray-50 px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Add-ons</h3>
                    <p class="text-sm text-gray-500">Kelola status add-on dan pembatalan periode akhir</p>
                </div>
            </div>
            <div class="card-body p-0">
                @if($order->orderAddons->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left">Nama</th>
                                    <th class="text-left">Harga</th>
                                    <th class="text-left">Qty</th>
                                    <th class="text-left">Billing</th>
                                    <th class="text-left">Status</th>
                                    <th class="text-left">Periode</th>
                                    <th class="text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderAddons as $addon)
                                    @php
                                        $statusClasses = [
                                            'pending' => 'badge-warning',
                                            'active' => 'badge-success',
                                            'cancelled' => 'badge-error'
                                        ];
                                        $statusClass = $statusClasses[$addon->status] ?? 'badge-neutral';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td>
                                            <div class="font-medium text-gray-800">{{ $addon->productAddon->name ?? 'Add-on' }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $addon->id }}</div>
                                        </td>
                                        <td class="text-gray-800">{{ $addon->formatted_price }}</td>
                                        <td class="text-gray-800">{{ $addon->quantity }}</td>
                                        <td class="text-gray-800">{{ $addon->billing_cycle_label }}</td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">{{ ucfirst($addon->status ?? 'active') }}</span>
                                            @if($addon->cancel_at_period_end)
                                                <span class="badge badge-outline ml-2">Cancel at period end</span>
                                                @if($addon->next_due_date)
                                                    <div class="text-xs text-red-500 mt-1">Akan dibatalkan pada: {{ optional($addon->next_due_date)->format('d M Y') }}</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-gray-600">
                                            @if($addon->started_at)
                                                <div>Start: {{ optional($addon->started_at)->format('d M Y') }}</div>
                                            @endif
                                            @if($addon->next_due_date)
                                                <div>Next Due: {{ optional($addon->next_due_date)->format('d M Y') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if(($addon->status !== 'cancelled') && !$addon->cancel_at_period_end)
                                                <form method="POST" action="{{ route('client.orders.addons.cancel', [$order, $addon]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline btn-error" onclick="return handleCancelAddon(this)">
                                                        <i class="fas fa-ban mr-1"></i>
                                                        Cancel at end of term
                                                    </button>
                                                </form>
                                            @elseif(($addon->status !== 'cancelled') && $addon->cancel_at_period_end)
                                                <form method="POST" action="{{ route('client.orders.addons.uncancel', [$order, $addon]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline" onclick="return handleUncancelAddon(this)">
                                                        <i class="fas fa-undo mr-1"></i>
                                                        Undo cancel at period end
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-500">Tidak ada aksi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-600">Order ini belum memiliki add-ons.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Invoice Terkait -->
        <div class="card bg-base-100 shadow-lg mt-8">
            <div class="card-header bg-gray-50 px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Invoice Terkait</h3>
                    <p class="text-sm text-gray-500">Daftar invoice untuk order ini</p>
                </div>
            </div>
            <div class="card-body p-0">
                @if($order->invoices && $order->invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left">Invoice</th>
                                    <th class="text-left">Status</th>
                                    <th class="text-left">Jumlah</th>
                                    <th class="text-left">Jatuh Tempo</th>
                                    <th class="text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->invoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td>
                                            <div class="font-medium text-gray-800">{{ $invoice->invoice_number ?? ('INV-' . $invoice->id) }}</div>
                                            <div class="text-xs text-gray-500">Dibuat: {{ optional($invoice->created_at)->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            <span class="badge">{{ ucfirst($invoice->status) }}</span>
                                        </td>
                                        <td class="text-gray-800">{{ 'Rp ' . number_format($invoice->total_amount ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-gray-600">{{ optional($invoice->due_date)->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('client.invoices.show', $invoice) }}" class="btn btn-sm btn-outline">
                                                <i class="fas fa-eye mr-1"></i> Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-600">Belum ada invoice untuk order ini.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Back -->
        <div class="mt-6">
            <a href="{{ route('client.orders') }}" class="btn btn-ghost">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Orders
            </a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function handleCancelAddon(buttonEl) {
        try {
            const form = buttonEl.closest('form');
            if (!form) return true;
            if (window.Swal) {
                Swal.fire({
                    title: 'Batalkan di akhir periode?',
                    text: 'Add-on akan tetap aktif hingga Next Due, lalu dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, tandai batal',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) { buttonEl.disabled = true; form.submit(); }
                });
                return false;
            }
            return confirm('Add-on akan dibatalkan di akhir periode. Lanjutkan?');
        } catch (e) { return true; }
    }

    function handleUncancelAddon(buttonEl) {
        try {
            const form = buttonEl.closest('form');
            if (!form) return true;
            if (window.Swal) {
                Swal.fire({
                    title: 'Batalkan penandaan batal?',
                    text: 'Add-on akan tetap aktif dan diperpanjang normal.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) { buttonEl.disabled = true; form.submit(); }
                });
                return false;
            }
            return confirm('Hapus penandaan batal di akhir periode?');
        } catch (e) { return true; }
    }
</script>
@endpush