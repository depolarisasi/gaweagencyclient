@extends('layouts.client')

@section('title', 'My Orders')

@section('content')
    <div class="p-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-shopping-cart text-blue-600 mr-3"></i>
                        My Orders
                    </h1>
                    <p class="text-gray-600">Track your orders and purchase history</p>
                </div>
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    New Order
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $orders->total() }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Completed Orders</p>
                            <p class="text-2xl font-bold text-green-600">{{ $orders->where('status', 'completed')->count() }}</p>
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
                            <p class="text-sm text-gray-600">Pending Orders</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $orders->where('status', 'pending')->count() }}</p>
                        </div>
                        <div class="p-3 bg-orange-100 rounded-full">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-header bg-gray-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Order History</h3>
            </div>
            <div class="card-body p-0">
                @if($orders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left font-semibold text-gray-700">Order ID</th>
                                    <th class="text-left font-semibold text-gray-700">Product</th>
                                    <th class="text-left font-semibold text-gray-700">Amount</th>
                                    <th class="text-left font-semibold text-gray-700">Status</th>
                                    <th class="text-left font-semibold text-gray-700">Date</th>
                                    <th class="text-left font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr class="hover:bg-gray-50">
                                        <td class="font-medium text-gray-800">#{{ $order->id }}</td>
                                        <td>
                                            <div class="flex items-center space-x-3">
                                                <div>
                                                    <div class="font-medium text-gray-800">
                                                        {{ $order->product->name ?? ($order->subscriptionPlan->name ?? 'N/A') }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        @if($order->template)
                                                            Template: {{ $order->template->name }}
                                                        @elseif($order->product)
                                                            {{ $order->product->description }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-semibold text-gray-800">
                                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            @php
                                                $statusClasses = [
                                                    'pending' => 'badge-warning',
                                                    'processing' => 'badge-info',
                                                    'completed' => 'badge-success',
                                                    'cancelled' => 'badge-error'
                                                ];
                                                $statusClass = $statusClasses[$order->status] ?? 'badge-neutral';
                                            @endphp
                                            <span class="badge {{ $statusClass }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-gray-600">
                                            {{ $order->created_at->format('M d, Y') }}
                                        </td>
                                        <td>
                                            <div class="flex space-x-2">
                                                @if($order->invoice)
                                                    <a href="{{ route('client.invoices.show', $order->invoice) }}" 
                                                       class="btn btn-sm btn-outline btn-primary">
                                                        <i class="fas fa-file-invoice mr-1"></i>
                                                        Invoice
                                                    </a>
                                                @endif
                                                @if($order->project)
                                                    <a href="{{ route('client.projects.show', $order->project) }}" 
                                                       class="btn btn-sm btn-outline btn-success">
                                                        <i class="fas fa-project-diagram mr-1"></i>
                                                        Project
                                                    </a>
                                                @endif
                                                <a href="{{ route('client.orders.show', $order) }}" 
                                                   class="btn btn-sm btn-outline btn-info">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    Details
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($orders->hasPages())
                        <div class="px-6 py-4 border-t bg-gray-50">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="mb-4">
                            <i class="fas fa-shopping-cart text-gray-300 text-6xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Orders Yet</h3>
                        <p class="text-gray-500 mb-6">You haven't placed any orders yet. Start by browsing our templates!</p>
                        <a href="{{ route('templates.index') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            Browse Templates
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection