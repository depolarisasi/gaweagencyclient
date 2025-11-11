@extends('layouts.app')

@section('title', 'Order #' . $order->order_number . ' - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
    @include('layouts.sidebar')
      
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
                        <p class="text-gray-600 mt-1">Order details and management</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit mr-2"></i>Edit Order
                    </a>
                    @if($order->status === 'pending')
                        <form method="POST" action="{{ route('admin.orders.activate', $order) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to activate this order?')">
                                <i class="fas fa-play mr-2"></i>Activate
                            </button>
                        </form>
                    @elseif($order->status === 'active')
                        <form method="POST" action="{{ route('admin.orders.suspend', $order) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to suspend this order?')">
                                <i class="fas fa-pause mr-2"></i>Suspend
                            </button>
                        </form>
                    @elseif($order->status === 'suspended')
                        <form method="POST" action="{{ route('admin.orders.activate', $order) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to reactivate this order?')">
                                <i class="fas fa-play mr-2"></i>Reactivate
                            </button>
                        </form>
                    @endif
                    @if(in_array($order->status, ['pending', 'active', 'suspended']))
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-error btn-sm" onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.')">
                                <i class="fas fa-ban mr-2"></i>Cancel
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Order Status & Quick Info -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Status</p>
                            @php
                                $statusColors = [
                                    'pending' => 'badge-warning',
                                    'active' => 'badge-success',
                                    'suspended' => 'badge-error',
                                    'cancelled' => 'badge-neutral',
                                    'completed' => 'badge-info'
                                ];
                                $statusIcons = [
                                    'pending' => 'fas fa-clock',
                                    'active' => 'fas fa-check-circle',
                                    'suspended' => 'fas fa-pause-circle',
                                    'cancelled' => 'fas fa-ban',
                                    'completed' => 'fas fa-flag-checkered'
                                ];
                            @endphp
                            <div class="badge {{ $statusColors[$order->status] ?? 'badge-neutral' }} badge-lg mt-2">
                                <i class="{{ $statusIcons[$order->status] ?? 'fas fa-question' }} mr-2"></i>
                                {{ ucfirst($order->status) }}
                            </div>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Amount Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Amount</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatIDR($order->total_amount) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-green-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Order Type Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Order Type</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1 capitalize">{{ $order->order_type ?? 'N/A' }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            @if($order->order_type === 'product')
                                <i class="fas fa-box text-purple-600"></i>
                            @elseif($order->order_type === 'subscription')
                                <i class="fas fa-sync text-purple-600"></i>
                            @elseif($order->order_type === 'domain')
                                <i class="fas fa-globe text-purple-600"></i>
                            @else
                                <i class="fas fa-cog text-purple-600"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Created Date Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Created</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">{{ $order->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar text-orange-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Order Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Customer Information -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Customer Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Customer Name</p>
                                <p class="text-gray-900 font-medium">{{ $order->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Email</p>
                                <p class="text-gray-900">{{ $order->user->email }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Role</p>
                                <span class="badge badge-outline">{{ ucfirst($order->user->role) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Orders</p>
                                <p class="text-gray-900 font-medium">{{ $order->user->orders->count() }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('admin.users.show', $order->user) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-external-link-alt mr-2"></i>View Customer Profile
                            </a>
                        </div>
                    </div>

                    <!-- Product & Service Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-green-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Product & Service Details</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($order->product)
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Product</p>
                                    <p class="text-gray-900 font-medium">{{ $order->product->name }}</p>
                                    <p class="text-sm text-gray-500">{{ formatIDR($order->product->price) }}</p>
                                </div>
                            @endif
                            
                            @if($order->subscriptionPlan)
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Subscription Plan</p>
                                    <p class="text-gray-900 font-medium">{{ $order->subscriptionPlan->name }}</p>
                                    <p class="text-sm text-gray-500">{{ formatIDR($order->subscriptionPlan->price) }}/{{ $order->subscriptionPlan->billing_cycle }}</p>
                                </div>
                            @endif
                            
                            @if($order->template)
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Template</p>
                                    <p class="text-gray-900 font-medium">{{ $order->template->name }}</p>
                                </div>
                            @endif
                            
                            @if($order->billing_cycle)
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Billing Cycle</p>
                                    <p class="text-gray-900 font-medium capitalize">{{ str_replace('_', ' ', $order->billing_cycle) }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Domain Information -->
                    @if($order->domain_name || $order->domain_type)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-globe text-orange-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">Domain Information</h4>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($order->domain_name)
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Domain Name</p>
                                        <p class="text-gray-900 font-medium">{{ $order->domain_name }}</p>
                                    </div>
                                @endif
                                
                                @if($order->domain_type)
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Domain Type</p>
                                        <span class="badge badge-outline capitalize">{{ $order->domain_type }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Order Addons -->
                    @if($order->orderAddons && $order->orderAddons->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-plus-circle text-indigo-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">Order Addons</h4>
                            </div>
                            
                            <div class="space-y-3">
                                @foreach($order->orderAddons as $orderAddon)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $orderAddon->addon_details['name'] ?? ($orderAddon->productAddon->name ?? 'Addon') }}</p>
                                            @php($desc = $orderAddon->addon_details['description'] ?? ($orderAddon->productAddon->description ?? null))
                                            @if(!empty($desc))
                                                <p class="text-sm text-gray-600">{{ $desc }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-gray-900">{{ formatIDR($orderAddon->price ?? 0) }}</p>
                                            <p class="text-sm text-gray-500">/{{ $orderAddon->billing_cycle_label }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if($order->notes)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-sticky-note text-yellow-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-800">Notes</h4>
                            </div>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Pricing Breakdown -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calculator text-purple-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Pricing Breakdown</h4>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium">{{ formatIDR(($order->subscription_amount ?? 0) + ($order->addons_amount ?? 0)) }}</span>
                            </div>
                            
                            @if($order->subscription_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subscription Amount</span>
                                    <span class="font-medium">{{ formatIDR($order->subscription_amount) }}</span>
                                </div>
                            @endif
                            
                            @if($order->addons_amount > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Addons Amount</span>
                                    <span class="font-medium">{{ formatIDR($order->addons_amount) }}</span>
                                </div>
                            @endif
                            
                            @if($order->setup_fee > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Setup Fee</span>
                                    <span class="font-medium">{{ formatIDR($order->setup_fee) }}</span>
                                </div>
                            @endif
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between">
                                    <span class="font-semibold text-gray-900">Total</span>
                                    <span class="font-bold text-lg text-gray-900">{{ formatIDR($order->total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Timeline -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-red-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Order Timeline</h4>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                <div>
                                    <p class="font-medium text-gray-900">Order Created</p>
                                    <p class="text-sm text-gray-600">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                            
                            @if($order->updated_at != $order->created_at)
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                    <div>
                                        <p class="font-medium text-gray-900">Last Updated</p>
                                        <p class="text-sm text-gray-600">{{ $order->updated_at->format('M d, Y \a\t g:i A') }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($order->next_due_date)
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-orange-500 rounded-full mt-2"></div>
                                    <div>
                                        <p class="font-medium text-gray-900">Next Due Date</p>
                                        <p class="text-sm text-gray-600">{{ $order->next_due_date->format('M d, Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->next_due_date->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Related Records -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-link text-gray-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Related Records</h4>
                        </div>
                        
                        <div class="space-y-3">
                            @if($order->invoice)
                                <a href="{{ route('admin.invoices.show', $order->invoice) }}" class="flex items-center justify-between p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-file-invoice text-blue-600"></i>
                                        <span class="font-medium text-blue-900">Invoice #{{ $order->invoice->invoice_number }}</span>
                                    </div>
                                    <i class="fas fa-external-link-alt text-blue-600"></i>
                                </a>
                            @endif
                            
                            @if($order->project)
                                <a href="{{ route('admin.projects.show', $order->project) }}" class="flex items-center justify-between p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-project-diagram text-green-600"></i>
                                        <span class="font-medium text-green-900">Project: {{ $order->project->name }}</span>
                                    </div>
                                    <i class="fas fa-external-link-alt text-green-600"></i>
                                </a>
                            @endif
                            
                            @if(!$order->invoice && !$order->project)
                                <p class="text-gray-500 text-sm italic">No related records found</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection