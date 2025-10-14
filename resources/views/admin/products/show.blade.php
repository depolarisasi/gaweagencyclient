@extends('layouts.app')

@section('title', 'Product Details - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
    @include('layouts.sidebar')

        <div class="flex-1 p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Product Details</h1>
                    <p class="text-gray-600 mt-1">View product information</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <form action="{{ route('admin.products.toggle-status', $product->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline">
                            <i class="fas fa-toggle-on mr-2"></i>{{ $product->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Overview</h2>
                    <div class="space-y-3">
                        <p><span class="text-gray-500">Name:</span> <span class="text-gray-900 font-medium">{{ $product->name }}</span></p>
                        <p><span class="text-gray-500">Type:</span> <span class="text-gray-900 font-medium">{{ ucfirst($product->type) }}</span></p>
                        <p><span class="text-gray-500">Billing Cycle:</span> <span class="text-gray-900 font-medium">{{ str_replace('_',' ',ucfirst($product->billing_cycle)) }}</span></p>
                        <p><span class="text-gray-500">Price:</span> <span class="text-gray-900 font-medium">Rp {{ number_format($product->price, 0, ',', '.') }}</span></p>
                        <p><span class="text-gray-500">Setup Time:</span> <span class="text-gray-900 font-medium">{{ $product->setup_time_days }} days</span></p>
                        <p><span class="text-gray-500">Sort Order:</span> <span class="text-gray-900 font-medium">{{ $product->sort_order }}</span></p>
                        <p><span class="text-gray-500">Status:</span>
                            @if($product->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-pause-circle mr-1"></i>Inactive
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Features</h2>
                    @if(is_array($product->features) && count($product->features))
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            @foreach($product->features as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500">No features listed.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Anda yakin?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
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