@extends('layouts.app')

@section('title', 'Edit Product - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
    @include('layouts.sidebar')

        <div class="flex-1 p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
                    <p class="text-gray-600 mt-1">Update product details</p>
                </div>
                <div class="flex space-x-2">
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('admin.products.update', $product->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @csrf
                    @method('PUT')

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Name</legend>
                        <input type="text" name="name" class="input input-bordered w-full focus:input-primary" value="{{ old('name', $product->name) }}" required>
                        @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Type</legend>
                        <select name="type" class="select select-bordered w-full focus:select-primary" required>
                            @foreach(['website','hosting','domain','maintenance'] as $type)
                                <option value="{{ $type }}" {{ old('type', $product->type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Billing Cycle</legend>
                        <select name="billing_cycle" class="select select-bordered w-full focus:select-primary" required>
                            @foreach(['monthly','quarterly','semi_annually','annually'] as $cycle)
                                <option value="{{ $cycle }}" {{ old('billing_cycle', $product->billing_cycle) == $cycle ? 'selected' : '' }}>{{ str_replace('_',' ',ucfirst($cycle)) }}</option>
                            @endforeach
                        </select>
                        @error('billing_cycle')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Price</legend>
                        <input type="number" name="price" step="0.01" min="0" class="input input-bordered w-full focus:input-primary" value="{{ old('price', $product->price) }}" required>
                        @error('price')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Setup Time (days)</legend>
                        <input type="number" name="setup_time_days" min="0" class="input input-bordered w-full focus:input-primary" value="{{ old('setup_time_days', $product->setup_time_days) }}">
                        @error('setup_time_days')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Sort Order</legend>
                        <input type="number" name="sort_order" min="0" class="input input-bordered w-full focus:input-primary" value="{{ old('sort_order', $product->sort_order) }}">
                        @error('sort_order')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Status</legend>
                        <select name="is_active" class="select select-bordered w-full focus:select-primary" required>
                            <option value="1" {{ old('is_active', $product->is_active ? 1 : 0) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $product->is_active ? 1 : 0) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('is_active')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="md:col-span-2 border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Description</legend>
                        <textarea name="description" rows="4" class="textarea textarea-bordered w-full focus:textarea-primary" required>{{ old('description', $product->description) }}</textarea>
                        @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="md:col-span-2 border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Features (one per line)</legend>
                        <textarea name="features" rows="4" class="textarea textarea-bordered w-full focus:textarea-primary">{{ old('features', is_array($product->features) ? implode("\n", $product->features) : '') }}</textarea>
                        @error('features')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <div class="md:col-span-2 flex justify-end space-x-2">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Update</button>
                    </div>
                </form>
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