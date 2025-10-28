@extends('layouts.client')

@section('title', 'My Invoices')

@section('content')
    <div class="p-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-file-invoice-dollar text-green-600 mr-3"></i>
                        My Invoices
                    </h1>
                    <p class="text-gray-600">Manage your invoices and payments</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Invoices</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $invoices->total() }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pending Payment</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $pendingCount }}</p>
                        </div>
                        <div class="p-3 bg-orange-100 rounded-full">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Paid Invoices</p>
                            <p class="text-2xl font-bold text-green-600">{{ $paidCount }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $totalAmount }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h2 class="card-title text-xl font-bold">
                        <i class="fas fa-list text-green-600"></i>
                        Invoice List
                    </h2>
                </div>

                @if($invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Date</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    <tr class="hover">
                                        <td>
                                            <div class="font-medium text-gray-800">
                                                {{ $invoice->invoice_number }}
                                            </div>
                                            @if($invoice->is_renewal)
                                                <div class="badge badge-info badge-sm mt-1">Renewal</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-sm text-gray-600">
                                                {{ $invoice->created_at->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-sm text-gray-600">
                                                {{ $invoice->due_date->format('d M Y') }}
                                            </div>
                                            @if($invoice->status === 'pending' && $invoice->due_date < now())
                                                <div class="badge badge-error badge-sm mt-1">Overdue</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-medium text-gray-800">
                                                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($invoice->status === 'pending')
                                                <div class="badge badge-warning">Pending</div>
                                            @elseif($invoice->status === 'paid')
                                                <div class="badge badge-success">Paid</div>
                                            @elseif($invoice->status === 'cancelled')
                                                <div class="badge badge-error">Cancelled</div>
                                            @else
                                                <div class="badge badge-ghost">{{ ucfirst($invoice->status) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a href="{{ route('client.invoices.show', $invoice) }}" 
                                                   class="btn btn-sm btn-outline btn-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($invoice->status === 'pending')
                                                    <a href="{{ route('client.invoices.payment', $invoice) }}" 
                                                       class="btn btn-sm btn-success" title="Pay Now">
                                                        <i class="fas fa-credit-card"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($invoices->hasPages())
                        <div class="flex justify-center mt-6">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="text-6xl text-gray-300 mb-4">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Invoices Yet</h3>
                        <p class="text-gray-500 mb-6">You don't have any invoices at the moment.</p>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            Order New Service
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection