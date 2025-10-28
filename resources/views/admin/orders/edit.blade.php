@extends('layouts.app')

@section('title', 'Edit Order #' . $order->order_number . ' - Admin')

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
                        <h1 class="text-3xl font-bold text-gray-900">Edit Order #{{ $order->order_number }}</h1>
                        <p class="text-gray-600 mt-1">Modify order details and settings</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-eye mr-2"></i>View Details
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

            <!-- Order Status Badge -->
            <div class="mb-6">
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
                <div class="badge {{ $statusColors[$order->status] ?? 'badge-neutral' }} badge-lg">
                    <i class="{{ $statusIcons[$order->status] ?? 'fas fa-question' }} mr-2"></i>
                    {{ ucfirst($order->status) }}
                </div>
            </div>

            <!-- Order Form -->
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Customer Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800">Customer Information</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Customer *</span>
                            </label>
                            <select name="user_id" class="select select-bordered focus:select-primary @error('user_id') select-error @enderror" required>
                                <option value="">Choose customer</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ (old('user_id', $order->user_id) == $user->id) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }}) - {{ ucfirst($user->role) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Order Type *</span>
                            </label>
                            <select name="order_type" class="select select-bordered focus:select-primary @error('order_type') select-error @enderror" required>
                                <option value="">Choose order type</option>
                                <option value="product" {{ old('order_type', $order->order_type) === 'product' ? 'selected' : '' }}>🛍️ Product - Physical or digital product</option>
                                <option value="subscription" {{ old('order_type', $order->order_type) === 'subscription' ? 'selected' : '' }}>🔄 Subscription - Recurring service</option>
                                <option value="domain" {{ old('order_type', $order->order_type) === 'domain' ? 'selected' : '' }}>🌐 Domain - Domain registration/transfer</option>
                                <option value="custom" {{ old('order_type', $order->order_type) === 'custom' ? 'selected' : '' }}>⚙️ Custom - Custom service</option>
                            </select>
                            @error('order_type')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
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
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Product</span>
                            </label>
                            <select name="product_id" class="select select-bordered focus:select-primary @error('product_id') select-error @enderror">
                                <option value="">Choose product (optional)</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id', $order->product_id) == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} - {{ formatIDR($product->price) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Subscription Plan</span>
                            </label>
                            <select name="subscription_plan_id" class="select select-bordered focus:select-primary @error('subscription_plan_id') select-error @enderror">
                                <option value="">Choose subscription plan (optional)</option>
                                @foreach($subscriptionPlans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('subscription_plan_id', $order->subscription_plan_id) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} - {{ formatIDR($plan->price) }}/{{ $plan->billing_cycle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subscription_plan_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Template</span>
                            </label>
                            <select name="template_id" class="select select-bordered focus:select-primary @error('template_id') select-error @enderror">
                                <option value="">Choose template (optional)</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('template_id', $order->template_id) == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Billing Cycle</span>
                            </label>
                            <select name="billing_cycle" class="select select-bordered focus:select-primary @error('billing_cycle') select-error @enderror">
                                <option value="">Choose billing cycle (optional)</option>
                                <option value="monthly" {{ old('billing_cycle', $order->billing_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('billing_cycle', $order->billing_cycle) === 'quarterly' ? 'selected' : '' }}>Quarterly (3 months)</option>
                                <option value="semi_annually" {{ old('billing_cycle', $order->billing_cycle) === 'semi_annually' ? 'selected' : '' }}>Semi-Annually (6 months)</option>
                                <option value="annually" {{ old('billing_cycle', $order->billing_cycle) === 'annually' ? 'selected' : '' }}>Annually (12 months)</option>
                                <option value="biennially" {{ old('billing_cycle', $order->billing_cycle) === 'biennially' ? 'selected' : '' }}>Biennially (24 months)</option>
                                <option value="triennially" {{ old('billing_cycle', $order->billing_cycle) === 'triennially' ? 'selected' : '' }}>Triennially (36 months)</option>
                            </select>
                            @error('billing_cycle')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-purple-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800">Pricing Information</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Order Amount *</span>
                            </label>
                            <input type="number" name="amount" value="{{ old('amount', $order->amount) }}" step="0.01" min="0" placeholder="0.00" class="input input-bordered focus:input-primary @error('amount') input-error @enderror" required>
                            @error('amount')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Subscription Amount</span>
                            </label>
                            <input type="number" name="subscription_amount" value="{{ old('subscription_amount', $order->subscription_amount) }}" step="0.01" min="0" placeholder="0.00" class="input input-bordered focus:input-primary @error('subscription_amount') input-error @enderror">
                            @error('subscription_amount')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Addons Amount</span>
                            </label>
                            <input type="number" name="addons_amount" value="{{ old('addons_amount', $order->addons_amount) }}" step="0.01" min="0" placeholder="0.00" class="input input-bordered focus:input-primary @error('addons_amount') input-error @enderror">
                            @error('addons_amount')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Setup Fee</span>
                            </label>
                            <input type="number" name="setup_fee" value="{{ old('setup_fee', $order->setup_fee) }}" step="0.01" min="0" placeholder="0.00" class="input input-bordered focus:input-primary @error('setup_fee') input-error @enderror">
                            @error('setup_fee')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Domain Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-globe text-orange-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800">Domain Information</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Domain Name</span>
                            </label>
                            <input type="text" name="domain_name" value="{{ old('domain_name', $order->domain_name) }}" placeholder="example.com" class="input input-bordered focus:input-primary @error('domain_name') input-error @enderror">
                            @error('domain_name')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Domain Type</span>
                            </label>
                            <select name="domain_type" class="select select-bordered focus:select-primary @error('domain_type') select-error @enderror">
                                <option value="">Choose domain type (optional)</option>
                                <option value="new" {{ old('domain_type', $order->domain_type) === 'new' ? 'selected' : '' }}>🆕 New Registration</option>
                                <option value="transfer" {{ old('domain_type', $order->domain_type) === 'transfer' ? 'selected' : '' }}>🔄 Transfer</option>
                                <option value="existing" {{ old('domain_type', $order->domain_type) === 'existing' ? 'selected' : '' }}>✅ Existing Domain</option>
                            </select>
                            @error('domain_type')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Order Settings -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-cog text-red-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-800">Order Settings</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Order Status *</span>
                            </label>
                            <select name="status" class="select select-bordered focus:select-primary @error('status') select-error @enderror" required>
                                <option value="pending" {{ old('status', $order->status) === 'pending' ? 'selected' : '' }}>⏳ Pending - Waiting for processing</option>
                                <option value="active" {{ old('status', $order->status) === 'active' ? 'selected' : '' }}>✅ Active - Order is active</option>
                                <option value="suspended" {{ old('status', $order->status) === 'suspended' ? 'selected' : '' }}>⏸️ Suspended - Temporarily disabled</option>
                                <option value="cancelled" {{ old('status', $order->status) === 'cancelled' ? 'selected' : '' }}>❌ Cancelled - Order cancelled</option>
                                <option value="completed" {{ old('status', $order->status) === 'completed' ? 'selected' : '' }}>🏁 Completed - Order completed</option>
                            </select>
                            @error('status')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Next Due Date</span>
                            </label>
                            <input type="date" name="next_due_date" value="{{ old('next_due_date', $order->next_due_date ? $order->next_due_date->format('Y-m-d') : '') }}" class="input input-bordered focus:input-primary @error('next_due_date') input-error @enderror">
                            @error('next_due_date')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Notes</span>
                            </label>
                            <textarea name="notes" rows="3" placeholder="Additional notes about this order..." class="textarea textarea-bordered focus:textarea-primary @error('notes') textarea-error @enderror">{{ old('notes', $order->notes) }}</textarea>
                            @error('notes')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Update Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection