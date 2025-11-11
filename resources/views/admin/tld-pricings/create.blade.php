@extends('layouts.app')

@section('title', 'Tambah TLD Pricing - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        @include('layouts.sidebar')

        <div class="flex-1 p-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Tambah TLD</h1>
                <p class="text-gray-600">Masukkan ekstensi TLD dan harganya</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('admin.tld-pricings.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">TLD</label>
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-500">.</span>
                            <input type="text" name="tld" value="{{ old('tld') }}" placeholder="com, net, co.id" class="input input-bordered w-full" />
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Tanpa titik awalan. Contoh: <strong>com</strong> atau <strong>co.id</strong></p>
                        @error('tld')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga</label>
                        <input type="number" name="price" value="{{ old('price') }}" min="0" step="1" placeholder="150000" class="input input-bordered w-full" />
                        @error('price')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" class="checkbox" {{ old('is_active', true) ? 'checked' : '' }} />
                        <span class="ml-2 text-sm text-gray-700">Aktif</span>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.tld-pricings.index') }}" class="btn btn-ghost">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection