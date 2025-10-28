@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-file-invoice text-green-600 mr-3"></i>
                        Invoice {{ $invoice->invoice_number }}
                    </h1>
                    <p class="text-gray-600">Invoice details and payment information</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('client.invoices.index') }}" class="btn btn-outline">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Invoices
                    </a>
                    @if($invoice->status === 'pending')
                        <a href="{{ route('client.invoices.payment', $invoice) }}" class="btn btn-success">
                            <i class="fas fa-credit-card mr-2"></i>
                            Pay Now
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Invoice Details -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <!-- Invoice Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">{{ $invoice->invoice_number }}</h2>
                                <p class="text-gray-600">Invoice Date: {{ $invoice->created_at->format('d M Y') }}</p>
                                <p class="text-gray-600">Due Date: {{ $invoice->due_date->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                @if($invoice->status === 'pending')
                                    <div class="badge badge-warning badge-lg">Pending Payment</div>
                                @elseif($invoice->status === 'paid')
                                    <div class="badge badge-success badge-lg">Paid</div>
                                @elseif($invoice->status === 'cancelled')
                                    <div class="badge badge-error badge-lg">Cancelled</div>
                                @endif
                                
                                @if($invoice->is_renewal)
                                    <div class="badge badge-info mt-2">Renewal Invoice</div>
                                @endif
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Bill To:</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="font-medium text-gray-800">{{ $invoice->user->name }}</p>
                                <p class="text-gray-600">{{ $invoice->user->email }}</p>
                                @if($invoice->user->phone)
                                    <p class="text-gray-600">{{ $invoice->user->phone }}</p>
                                @endif
                                @if($invoice->user->company)
                                    <p class="text-gray-600">{{ $invoice->user->company }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Invoice Items:</h3>
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Billing Period</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($invoice->order)
                                            <tr>
                                                <td>
                                                    <div class="font-medium">{{ $invoice->order->product->name ?? 'Service' }}</div>
                                                    @if($invoice->order->order_details && isset($invoice->order->order_details['template']))
                                                        <div class="text-sm text-gray-600">
                                                            Template: {{ $invoice->order->order_details['template']['name'] }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($invoice->billing_period_start && $invoice->billing_period_end)
                                                        {{ $invoice->billing_period_start->format('d M Y') }} - {{ $invoice->billing_period_end->format('d M Y') }}
                                                    @else
                                                        {{ ucfirst($invoice->order->billing_cycle) }}
                                                    @endif
                                                </td>
                                                <td class="text-right font-medium">
                                                    Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                            
                                            @if($invoice->order->order_details && isset($invoice->order->order_details['addons']))
                                                @foreach($invoice->order->order_details['addons'] as $addon)
                                                    <tr>
                                                        <td>
                                                            <div class="font-medium">{{ $addon['name'] }}</div>
                                                            <div class="text-sm text-gray-600">{{ $addon['billing_type'] }}</div>
                                                        </td>
                                                        <td>-</td>
                                                        <td class="text-right font-medium">
                                                            Rp {{ number_format($addon['price'], 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @else
                                            <tr>
                                                <td>Service</td>
                                                <td>-</td>
                                                <td class="text-right font-medium">
                                                    Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Invoice Total -->
                        <div class="border-t pt-4">
                            <div class="flex justify-end">
                                <div class="w-64">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-gray-600">Tax (PPN 11%):</span>
                                        <span class="font-medium">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                                    </div>
                                    
                                    <!-- Tripay Fee Information -->
                                    @if($invoice->fee_merchant && $invoice->fee_merchant > 0)
                                        <div class="flex justify-between mb-2">
                                            <span class="text-gray-600">Biaya Admin (Merchant):</span>
                                            <span class="font-medium">Rp {{ number_format($invoice->fee_merchant, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($invoice->fee_customer && $invoice->fee_customer > 0)
                                        <div class="flex justify-between mb-2">
                                            <span class="text-gray-600">Biaya Admin (Customer):</span>
                                            <span class="font-medium">Rp {{ number_format($invoice->fee_customer, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($invoice->total_fee && $invoice->total_fee > 0)
                                        <div class="flex justify-between mb-2">
                                            <span class="text-orange-600 font-medium">Total Biaya Admin:</span>
                                            <span class="font-medium text-orange-600">Rp {{ number_format($invoice->total_fee, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                    
                                    <div class="border-t pt-2">
                                        <div class="flex justify-between">
                                            <span class="text-lg font-bold text-gray-800">Total:</span>
                                            <span class="text-lg font-bold text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-lg font-bold mb-4">
                            <i class="fas fa-credit-card text-green-600"></i>
                            Payment Information
                        </h3>

                        @if($invoice->status === 'pending')
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <h4 class="font-bold">Payment Required</h4>
                                    <div class="text-sm">
                                        @if($invoice->due_date < now())
                                            This invoice is overdue. Please pay immediately.
                                        @else
                                            Due: {{ $invoice->due_date->format('d M Y') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ route('client.invoices.payment', $invoice) }}" class="btn btn-success btn-block mb-4">
                                <i class="fas fa-credit-card mr-2"></i>
                                Pay Now
                            </a>
                        @elseif($invoice->status === 'paid')
                            <div class="alert alert-success mb-4">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <h4 class="font-bold">Payment Received</h4>
                                    <div class="text-sm">
                                        Paid on: {{ $invoice->paid_date ? $invoice->paid_date->format('d M Y H:i') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Payment Details -->
                        @if($invoice->payment_method)
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-600">Payment Method:</span>
                                    <div class="font-medium">{{ strtoupper($invoice->payment_method) }}</div>
                                </div>
                                
                                @if($invoice->payment_reference)
                                    <div>
                                        <span class="text-sm text-gray-600">Reference:</span>
                                        <div class="font-medium text-xs">{{ $invoice->payment_reference }}</div>
                                    </div>
                                @endif
                                
                                @if($invoice->payment_amount)
                                    <div>
                                        <span class="text-sm text-gray-600">Amount Paid:</span>
                                        <div class="font-medium">Rp {{ number_format($invoice->payment_amount, 0, ',', '.') }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Invoice Actions -->
                        <div class="divider"></div>
                        <div class="space-y-2">
                            <button onclick="window.print()" class="btn btn-outline btn-block">
                                <i class="fas fa-print mr-2"></i>
                                Print Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh payment status for pending invoices
@if($invoice->status === 'pending')
setInterval(function() {
    fetch('{{ route("client.invoices.payment.status", $invoice) }}')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.status === 'PAID') {
                location.reload();
            }
        })
        .catch(error => console.log('Status check failed:', error));
}, 30000); // Check every 30 seconds
@endif
</script>
@endpush