@extends('layouts.app')

@section('title', 'TLD Pricing Management - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">TLD Pricing</h1>
                    <p class="text-gray-600 mt-1">Kelola harga ekstensi domain (TLD)</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.tld-pricings.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-2"></i>Tambah TLD
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.tld-pricings.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cari TLD</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="mis. .com, .co.id" class="input input-bordered w-full">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="btn btn-outline">Filter</button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TLD</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktif</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tlds as $tld)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">.{{ $tld->tld }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($tld->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($tld->is_active)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Aktif</span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.tld-pricings.edit', $tld) }}" class="btn btn-sm">Edit</a>
                                    <form action="{{ route('admin.tld-pricings.destroy', $tld) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Hapus TLD ini?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data TLD</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $tlds->withQueryString()->links() }}</div>
        </div>
    </div>
</div>
@endsection