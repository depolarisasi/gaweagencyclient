@extends('layouts.app')

@section('title', 'Edit Invoice - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg border-r border-gray-200">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Admin Panel</h3>
                        <p class="text-xs text-gray-500">Management Center</p>
                    </div>
                </div>
                
                <nav class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                        <i class="fas fa-chart-line w-5"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Management</p>
                        
                        <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-users w-5"></i>
                            <span>User Management</span>
                        </a>
                        
                        <a href="{{ route('admin.products') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-box w-5"></i>
                            <span>Products</span>
                        </a>
                        
                        <a href="{{ route('admin.projects.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-project-diagram w-5"></i>
                            <span>Projects</span>
                        </a>
                        
                        <a href="{{ route('admin.invoices.index') }}" class="flex items-center space-x-3 px-4 py-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
                            <i class="fas fa-file-invoice w-5"></i>
                            <span>Invoices</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Support</p>
                        
                        <a href="{{ route('admin.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-ticket-alt w-5"></i>
                            <span>Support Tickets</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Settings</p>
                        
                        <a href="{{ route('admin.templates.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-palette w-5"></i>
                            <span>Templates</span>
                        </a>
                        
                        <a href="{{ route('admin.settings') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-credit-card w-5"></i>
                            <span>Payment Settings</span>
                        </a>
                    </div>
                    
                    <div class="pt-6 border-t border-gray-200">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-red-50 hover:text-red-600 rounded-lg transition-colors w-full text-left">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Invoices
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Invoice</h1>
                        <p class="text-gray-600 mt-1">Update invoice information and status</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="badge {{ $invoice->status === 'paid' ? 'badge-success' : ($invoice->status === 'sent' ? 'badge-warning' : 'badge-error') }}">
                        {{ ucfirst($invoice->status) }}
                    </div>
                    <span class="text-lg font-bold text-gray-900">{{ $invoice->invoice_number }}</span>
                </div>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <h3 class="font-bold">Please fix the following errors:</h3>
                        <ul class="list-disc list-inside mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            
            <!-- Edit Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <form action="{{ route('admin.invoices.update', $invoice->id) }}" method="POST" class="space-y-6 p-8">
                    @csrf
                    @method('PUT')
                    
                    <!-- Invoice Information Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-file-invoice text-blue-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Invoice Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Invoice Number</span>
                                </label>
                                <input type="text" value="{{ $invoice->invoice_number }}" 
                                       class="input input-bordered bg-gray-100" readonly>
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Invoice number cannot be changed</span>
                                </label>
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Client</span>
                                </label>
                                <input type="text" value="{{ $invoice->user->name }} ({{ $invoice->user->email }})" 
                                       class="input input-bordered bg-gray-100" readonly>
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Client cannot be changed</span>
                                </label>
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Invoice Status *</span>
                                </label>
                                <select name="status" class="select select-bordered focus:select-primary @error('status') select-error @enderror" required>
                                    <option value="draft" {{ old('status', $invoice->status) === 'draft' ? 'selected' : '' }}>📝 Draft - Not sent yet</option>
                                    <option value="sent" {{ old('status', $invoice->status) === 'sent' ? 'selected' : '' }}>📧 Sent - Waiting for payment</option>
                                    <option value="paid" {{ old('status', $invoice->status) === 'paid' ? 'selected' : '' }}>✅ Paid - Payment received</option>
                                    <option value="overdue" {{ old('status', $invoice->status) === 'overdue' ? 'selected' : '' }}>⏰ Overdue - Payment late</option>
                                    <option value="cancelled" {{ old('status', $invoice->status) === 'cancelled' ? 'selected' : '' }}>❌ Cancelled - Invoice void</option>
                                </select>
                                @error('status')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Due Date *</span>
                                </label>
                                <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" 
                                       class="input input-bordered focus:input-primary @error('due_date') input-error @enderror" required>
                                @error('due_date')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-control mt-4">
                            <label class="label">
                                <span class="label-text font-medium text-gray-700">Description</span>
                            </label>
                            <textarea name="description" class="textarea textarea-bordered focus:textarea-primary @error('description') textarea-error @enderror" 
                                      rows="3" placeholder="Invoice description or notes">{{ old('description', $invoice->description) }}</textarea>
                            @error('description')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Amount Information Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-dollar-sign text-green-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Amount Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Subtotal Amount *</span>
                                </label>
                                <input type="number" name="amount" value="{{ old('amount', $invoice->amount) }}" 
                                       class="input input-bordered focus:input-primary @error('amount') input-error @enderror" 
                                       step="0.01" min="0" placeholder="0.00" required>
                                @error('amount')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Tax Amount</span>
                                </label>
                                <input type="number" name="tax_amount" value="{{ old('tax_amount', $invoice->tax_amount) }}" 
                                       class="input input-bordered focus:input-primary @error('tax_amount') input-error @enderror" 
                                       step="0.01" min="0" placeholder="0.00">
                                @error('tax_amount')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Total Amount *</span>
                                </label>
                                <input type="number" name="total_amount" value="{{ old('total_amount', $invoice->total_amount) }}" 
                                       class="input input-bordered focus:input-primary @error('total_amount') input-error @enderror" 
                                       step="0.01" min="0" placeholder="0.00" required>
                                @error('total_amount')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <h3 class="font-bold">Amount Calculation</h3>
                                <div class="text-sm mt-1">
                                    Total Amount should equal Subtotal Amount + Tax Amount
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Information Section -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-credit-card text-purple-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800">Payment Information</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Payment Method</span>
                                </label>
                                <input type="text" name="payment_method" value="{{ old('payment_method', $invoice->payment_method) }}" 
                                       class="input input-bordered focus:input-primary @error('payment_method') input-error @enderror" 
                                       placeholder="e.g., Bank Transfer, Credit Card">
                                @error('payment_method')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Payment Date</span>
                                </label>
                                <input type="date" name="paid_date" value="{{ old('paid_date', $invoice->paid_date?->format('Y-m-d')) }}" 
                                       class="input input-bordered focus:input-primary @error('paid_date') input-error @enderror">
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Leave empty if not paid yet</span>
                                </label>
                                @error('paid_date')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Fields marked with * are required
                        </div>
                        
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Update Invoice
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection