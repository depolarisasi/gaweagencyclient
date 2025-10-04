@extends('layouts.app')

@section('title', 'Invoice Management - Admin')

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
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-box w-5"></i>
                            <span>Products</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
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
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-ticket-alt w-5"></i>
                            <span>Support Tickets</span>
                        </a>
                    </div>
                    
                    <div class="pt-4">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Settings</p>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-palette w-5"></i>
                            <span>Templates</span>
                        </a>
                        
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <i class="fas fa-credit-card w-5"></i>
                            <span>Payment Settings</span>
                        </a>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Invoice Management</h1>
                    <p class="text-gray-600 mt-1">Manage invoices and track payments</p>
                </div>
                <div class="flex space-x-3">
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-download mr-2"></i>Export Invoices
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                        <i class="fas fa-plus mr-2"></i>Create Invoice
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Invoices</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $invoices->total() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Paid This Month</p>
                            <p class="text-3xl font-bold text-gray-900">Rp {{ number_format(\App\Models\Invoice::where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('total_amount'), 0, ',', '.') }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-money-bill text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Pending Payment</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Invoice::where('status', 'sent')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Overdue</p>
                            <p class="text-3xl font-bold text-gray-900">{{ \App\Models\Invoice::where('status', 'overdue')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.invoices.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice number or client..." class="input input-bordered w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="input input-bordered w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="input input-bordered w-full">
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Invoices Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left font-semibold text-gray-900">Invoice</th>
                                <th class="text-left font-semibold text-gray-900">Client</th>
                                <th class="text-left font-semibold text-gray-900">Amount</th>
                                <th class="text-left font-semibold text-gray-900">Status</th>
                                <th class="text-left font-semibold text-gray-900">Due Date</th>
                                <th class="text-left font-semibold text-gray-900">Payment</th>
                                <th class="text-center font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td>
                                    <div>
                                        <p class="font-semibold text-gray-900">#{{ $invoice->invoice_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $invoice->created_at->format('M d, Y') }}</p>
                                        @if($invoice->order)
                                            <p class="text-xs text-gray-400">Order #{{ $invoice->order->id }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                            <span class="text-white font-semibold text-xs">{{ strtoupper(substr($invoice->user->name, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $invoice->user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $invoice->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <p class="font-semibold text-gray-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                                        @if($invoice->tax_amount > 0)
                                            <p class="text-sm text-gray-500">Tax: Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($invoice->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Paid
                                        </span>
                                    @elseif($invoice->status === 'sent')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Pending
                                        </span>
                                    @elseif($invoice->status === 'overdue')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Overdue
                                        </span>
                                    @elseif($invoice->status === 'draft')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-edit mr-1"></i>Draft
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-times mr-1"></i>Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <p class="text-sm text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</p>
                                        @if($invoice->status !== 'paid' && $invoice->due_date < now())
                                            <p class="text-xs text-red-600">{{ abs($invoice->days_until_due) }} days overdue</p>
                                        @elseif($invoice->status !== 'paid')
                                            <p class="text-xs text-gray-500">{{ $invoice->days_until_due }} days left</p>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($invoice->paid_at)
                                        <div>
                                            <p class="text-sm text-green-600 font-medium">Paid</p>
                                            <p class="text-xs text-gray-500">{{ $invoice->paid_at->format('M d, Y') }}</p>
                                            @if($invoice->payment_method)
                                                <p class="text-xs text-gray-400">{{ $invoice->payment_method }}</p>
                                            @endif
                                        </div>
                                    @elseif($invoice->tripay_checkout_url)
                                        <a href="{{ $invoice->tripay_checkout_url }}" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                            <i class="fas fa-external-link-alt mr-1"></i>Pay Now
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-sm">No payment link</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="viewInvoice({{ $invoice->id }})" class="btn btn-sm btn-outline" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($invoice->status === 'draft')
                                        <button onclick="editInvoice({{ $invoice->id }})" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endif
                                        @if($invoice->status !== 'paid')
                                        <button onclick="sendInvoice({{ $invoice->id }})" class="btn btn-sm btn-success" title="Send">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                        @endif
                                        <div class="dropdown dropdown-end">
                                            <button tabindex="0" class="btn btn-sm btn-ghost">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                                <li><a onclick="downloadInvoice({{ $invoice->id }})"><i class="fas fa-download mr-2"></i>Download PDF</a></li>
                                                @if($invoice->status !== 'paid')
                                                <li><a onclick="markAsPaid({{ $invoice->id }})"><i class="fas fa-check mr-2"></i>Mark as Paid</a></li>
                                                @endif
                                                @if($invoice->status !== 'cancelled')
                                                <li><a onclick="cancelInvoice({{ $invoice->id }})"><i class="fas fa-times mr-2"></i>Cancel</a></li>
                                                @endif
                                                <li><a onclick="deleteInvoice({{ $invoice->id }})" class="text-red-600"><i class="fas fa-trash mr-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <i class="fas fa-file-invoice text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500 text-lg">No invoices found</p>
                                    <p class="text-gray-400">Create your first invoice to get started</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($invoices->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $invoices->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Invoice Modal -->
<div id="invoiceModal" class="modal">
    <div class="modal-box w-11/12 max-w-4xl">
        <h3 class="font-bold text-lg mb-4" id="modalTitle">Create New Invoice</h3>
        
        <form id="invoiceForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client Selection -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Client *</span>
                    </label>
                    <select name="user_id" class="select select-bordered" required>
                        <option value="">Select Client</option>
                        @foreach(\App\Models\User::where('role', 'client')->where('status', 'active')->get() as $client)
                            <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Due Date -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Due Date *</span>
                    </label>
                    <input type="date" name="due_date" class="input input-bordered" required value="{{ now()->addDays(7)->format('Y-m-d') }}">
                </div>
                
                <!-- Amount -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Amount *</span>
                    </label>
                    <input type="number" name="amount" class="input input-bordered" step="0.01" min="0" required placeholder="0.00">
                </div>
                
                <!-- Tax Amount -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Tax Amount</span>
                    </label>
                    <input type="number" name="tax_amount" class="input input-bordered" step="0.01" min="0" placeholder="0.00">
                </div>
                
                <!-- Notes -->
                <div class="form-control md:col-span-2">
                    <label class="label">
                        <span class="label-text font-medium">Notes</span>
                    </label>
                    <textarea name="notes" class="textarea textarea-bordered" rows="3" placeholder="Additional notes for this invoice..."></textarea>
                </div>
            </div>
            
            <div class="modal-action">
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>Create Invoice
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create New Invoice';
    document.getElementById('invoiceForm').reset();
    document.getElementById('invoiceModal').classList.add('modal-open');
}

function closeModal() {
    document.getElementById('invoiceModal').classList.remove('modal-open');
}

function viewInvoice(invoiceId) {
    window.location.href = `/admin/invoices/${invoiceId}`;
}

function editInvoice(invoiceId) {
    window.location.href = `/admin/invoices/${invoiceId}/edit`;
}

function sendInvoice(invoiceId) {
    if (confirm('Send this invoice to the client?')) {
        fetch(`/admin/invoices/${invoiceId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error sending invoice');
            }
        });
    }
}

function markAsPaid(invoiceId) {
    if (confirm('Mark this invoice as paid?')) {
        fetch(`/admin/invoices/${invoiceId}/mark-paid`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error marking invoice as paid');
            }
        });
    }
}

function cancelInvoice(invoiceId) {
    if (confirm('Cancel this invoice?')) {
        fetch(`/admin/invoices/${invoiceId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error cancelling invoice');
            }
        });
    }
}

function deleteInvoice(invoiceId) {
    if (confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
        fetch(`/admin/invoices/${invoiceId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting invoice');
            }
        });
    }
}

function downloadInvoice(invoiceId) {
    window.open(`/admin/invoices/${invoiceId}/download`, '_blank');
}

// Handle form submission
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/invoices', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            location.reload();
        } else {
            alert('Error creating invoice');
        }
    });
});
</script>
@endsection