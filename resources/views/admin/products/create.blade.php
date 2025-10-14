@extends('layouts.app')

@section('title', 'Create Product - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
    @include('layouts.sidebar')

        <div class="flex-1 p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Create Product</h1>
                    <p class="text-gray-600 mt-1">Add a new product</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('admin.products.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @csrf

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Name</legend>
                        <input type="text" name="name" class="input input-bordered w-full focus:input-primary" value="{{ old('name') }}" required>
                        @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Type</legend>
                        <select name="type" class="select select-bordered w-full focus:select-primary" required>
                            @foreach(['website','hosting','domain','maintenance'] as $type)
                                <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Billing Cycle</legend>
                        <select name="billing_cycle" class="select select-bordered w-full focus:select-primary" required>
                            @foreach(['monthly','quarterly','semi_annually','annually'] as $cycle)
                                <option value="{{ $cycle }}" {{ old('billing_cycle') == $cycle ? 'selected' : '' }}>{{ str_replace('_',' ',ucfirst($cycle)) }}</option>
                            @endforeach
                        </select>
                        @error('billing_cycle')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Price</legend>
                        <input type="number" name="price" step="0.01" min="0" class="input input-bordered w-full focus:input-primary" value="{{ old('price') }}" required>
                        @error('price')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Setup Time (days)</legend>
                        <input type="number" name="setup_time_days" min="0" class="input input-bordered w-full focus:input-primary" value="{{ old('setup_time_days', 7) }}">
                        @error('setup_time_days')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Sort Order</legend>
                        <input type="number" name="sort_order" min="0" class="input input-bordered w-full focus:input-primary" value="{{ old('sort_order', 0) }}">
                        @error('sort_order')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Status</legend>
                        <select name="is_active" class="select select-bordered w-full focus:select-primary" required>
                            <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('is_active')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="md:col-span-2 border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Description</legend>
                        <textarea name="description" rows="4" class="textarea textarea-bordered w-full focus:textarea-primary" required>{{ old('description') }}</textarea>
                        @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset class="md:col-span-2 border border-gray-200 rounded-lg p-4">
                        <legend class="text-sm font-semibold text-gray-700 px-2">Features (one per line)</legend>
                        <textarea name="features" rows="4" class="textarea textarea-bordered w-full focus:textarea-primary">{{ old('features') }}</textarea>
                        @error('features')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    <div class="md:col-span-2 flex justify-end space-x-2">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection