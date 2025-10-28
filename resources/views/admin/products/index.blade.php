@extends('layouts.app')

@section('title', 'Product Management - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
    @include('layouts.sidebar')
      
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Product Management</h1>
                    <p class="text-gray-600 mt-1">Manage products and offerings</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-2"></i>Add Product
                    </a>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or description..." class="input input-bordered w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="type" class="select select-bordered w-full">
                            <option value="">All Types</option>
                            @foreach(['website','hosting','domain','maintenance'] as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Products Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left font-semibold text-gray-900">Product</th>
                                <th class="text-left font-semibold text-gray-900">Type</th>
                                <th class="text-left font-semibold text-gray-900">Price</th>
                                <th class="text-left font-semibold text-gray-900">Status</th>
                                <th class="text-left font-semibold text-gray-900">Created</th>
                                <th class="text-center font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-lg flex items-center justify-center">
                                            <span class="text-white font-semibold text-sm">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                                            <p class="text-sm text-gray-500">{{ Str::limit($product->description, 60) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-box mr-1"></i>{{ ucfirst($product->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-pause-circle mr-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm text-gray-900">{{ $product->created_at->format('M d, Y') }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @php
                                            $hasOrders = $product->orders()->exists();
                                            $orderCount = $hasOrders ? $product->orders()->count() : 0;
                                        @endphp
                                        
                                        @if($hasOrders)
                                            <!-- Dropdown for products with orders -->
                                            <div class="dropdown dropdown-end">
                                                <label tabindex="0" class="btn btn-sm btn-error">
                                                    <i class="fas fa-trash"></i>
                                                    <i class="fas fa-chevron-down ml-1"></i>
                                                </label>
                                                <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-64">
                                                    <li class="menu-title">
                                                        <span class="text-warning">⚠️ Product has {{ $orderCount }} order(s)</span>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-orange-600">
                                                                <i class="fas fa-pause mr-2"></i>Deactivate Only
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('admin.products.force-destroy', $product->id) }}" method="POST" class="force-delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600">
                                                                <i class="fas fa-exclamation-triangle mr-2"></i>Force Delete (+ Orders)
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        @else
                                            <!-- Regular delete for products without orders -->
                                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline-block delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-error" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <i class="fas fa-boxes text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500 text-lg">No products found</p>
                                    <p class="text-gray-400">Try adjusting your search criteria</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($products->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $products->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
}</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Regular delete confirmation
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Anda yakin?',
                text: 'Product akan dideaktivasi atau dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Force delete confirmation
    const forceDeleteForms = document.querySelectorAll('.force-delete-form');
    forceDeleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'PERINGATAN!',
                html: '<p class="text-red-600 font-bold">Ini akan menghapus PERMANEN:</p><ul class="text-left mt-2"><li>• Product ini</li><li>• SEMUA order yang terkait</li><li>• Data tidak dapat dikembalikan!</li></ul>',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus PERMANEN!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn-error',
                    cancelButton: 'btn-ghost'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection