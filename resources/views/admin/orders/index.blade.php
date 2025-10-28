@extends('layouts.app')

@section('title', 'Order Management - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
    @include('layouts.sidebar')
      
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Order Management</h1>
                    <p class="text-gray-600 mt-1">Manage customer orders and subscriptions</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-2"></i>Add Order
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Orders</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $orders->total() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Active Orders</p>
                            <p class="text-3xl font-bold text-green-600">{{ $orders->where('status', 'active')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Pending Orders</p>
                            <p class="text-3xl font-bold text-yellow-600">{{ $orders->where('status', 'pending')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Monthly Revenue</p>
                            <p class="text-3xl font-bold text-purple-600">
                                {{ formatIDR($orders->where('created_at', '>=', now()->startOfMonth())->sum('amount')) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Order number, domain, customer..." class="input input-bordered w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Order Type</label>
                        <select name="order_type" class="select select-bordered w-full">
                            <option value="">All Types</option>
                            <option value="product" {{ request('order_type') === 'product' ? 'selected' : '' }}>Product</option>
                            <option value="subscription" {{ request('order_type') === 'subscription' ? 'selected' : '' }}>Subscription</option>
                            <option value="domain" {{ request('order_type') === 'domain' ? 'selected' : '' }}>Domain</option>
                            <option value="custom" {{ request('order_type') === 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="input input-bordered w-full">
                    </div>
                    
                    <div class="flex items-end">
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <div class="flex space-x-2">
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input input-bordered flex-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Orders Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left font-semibold text-gray-700">Order Details</th>
                                <th class="text-left font-semibold text-gray-700">Customer</th>
                                <th class="text-left font-semibold text-gray-700">Product/Service</th>
                                <th class="text-left font-semibold text-gray-700">Amount</th>
                                <th class="text-left font-semibold text-gray-700">Status</th>
                                <th class="text-left font-semibold text-gray-700">Date</th>
                                <th class="text-center font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $order->order_number }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ ucfirst($order->order_type) }}
                                            @if($order->domain_name)
                                                • {{ $order->domain_name }}
                                            @endif
                                        </div>
                                        @if($order->billing_cycle)
                                            <div class="text-xs text-blue-600">{{ ucfirst(str_replace('_', ' ', $order->billing_cycle)) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $order->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->user->email }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        @if($order->product)
                                            <div class="font-medium text-gray-900">{{ $order->product->name }}</div>
                                        @endif
                                        @if($order->subscriptionPlan)
                                            <div class="text-sm text-gray-500">{{ $order->subscriptionPlan->name }}</div>
                                        @endif
                                        @if($order->template)
                                            <div class="text-xs text-purple-600">{{ $order->template->name }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ formatIDR($order->amount) }}</div>
                                        @if($order->subscription_amount)
                                            <div class="text-sm text-gray-500">
                                                Recurring: {{ formatIDR($order->subscription_amount) }}
                                            </div>
                                        @endif
                                        @if($order->setup_fee)
                                            <div class="text-xs text-orange-600">
                                                Setup: {{ formatIDR($order->setup_fee) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ 
                                        $order->status === 'active' ? 'badge-success' : 
                                        ($order->status === 'pending' ? 'badge-warning' : 
                                        ($order->status === 'suspended' ? 'badge-error' : 
                                        ($order->status === 'completed' ? 'badge-info' : 'badge-ghost'))) 
                                    }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    @if($order->next_due_date)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Due: {{ $order->next_due_date->format('M d, Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost btn-xs" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-ghost btn-xs" title="Edit Order">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- Quick Actions -->
                                        @if($order->status === 'pending')
                                            <form method="POST" action="{{ route('admin.orders.activate', $order) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-xs" title="Activate Order">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                        @elseif($order->status === 'active')
                                            <form method="POST" action="{{ route('admin.orders.suspend', $order) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-warning btn-xs" title="Suspend Order">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this order?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-error btn-xs" title="Delete Order">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-4"></i>
                                        <p class="text-gray-500 text-lg">No orders found</p>
                                        <p class="text-gray-400 text-sm">Try adjusting your search criteria</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection