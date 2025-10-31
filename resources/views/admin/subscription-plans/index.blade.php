@extends('layouts.app')

@section('title', 'Subscription Plans Management - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Subscription Plans</h1>
                    <p class="text-gray-600 mt-1">Kelola paket langganan dan siklus pembayaran</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.subscription-plans.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-2"></i>Tambah Paket Baru
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.subscription-plans.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cari Nama Paket</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Masukkan nama paket..." class="input input-bordered w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="select select-bordered w-full">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Siklus Pembayaran</label>
                        <select name="billing_cycle" class="select select-bordered w-full">
                            <option value="">Semua Siklus</option>
                            <option value="monthly" {{ request('billing_cycle') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="quarterly" {{ request('billing_cycle') === 'quarterly' ? 'selected' : '' }}>Triwulan</option>
                            <option value="semi_annual" {{ request('billing_cycle') === 'semi_annual' ? 'selected' : '' }}>6 Bulan</option>
                            <option value="annual" {{ request('billing_cycle') === 'annual' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline">
                            <i class="fas fa-times mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Subscription Plans Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left font-semibold text-gray-900">Nama Paket</th>
                                <th class="text-left font-semibold text-gray-900">Harga</th>
                                <th class="text-left font-semibold text-gray-900">Siklus</th>
                                <th class="text-left font-semibold text-gray-900">Diskon</th>
                                <th class="text-left font-semibold text-gray-900">Status</th>
                                <th class="text-left font-semibold text-gray-900">Popular</th>
                                <th class="text-left font-semibold text-gray-900">Urutan</th>
                                <th class="text-center font-semibold text-gray-900">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptionPlans as $plan)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $plan->name }}</div>
                                            @if($plan->description)
                                                <div class="text-sm text-gray-500">{{ Str::limit($plan->description, 50) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium text-gray-900">Rp {{ number_format($plan->price, 0, ',', '.') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline">{{ ucfirst(str_replace('_', ' ', $plan->billing_cycle)) }} ({{ $plan->cycle_months }} bulan)</span>
                                    </td>
                                    <td>
                                        @if($plan->discount_percentage > 0)
                                            <span class="badge badge-success">{{ $plan->discount_percentage }}%</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.subscription-plans.toggle-status', $plan) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="badge {{ $plan->is_active ? 'badge-success' : 'badge-error' }} cursor-pointer">{{ $plan->is_active ? 'Aktif' : 'Tidak Aktif' }}</button>
                                        </form>
                                    </td>
                                    <td>
                                        @if($plan->is_popular)
                                            <span class="badge badge-warning">Popular</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-sm text-gray-600">{{ $plan->sort_order }}</span>
                                    </td>
                                    <td>
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('admin.subscription-plans.show', $plan) }}" class="btn btn-sm btn-outline btn-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.subscription-plans.edit', $plan) }}" class="btn btn-sm btn-outline btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.subscription-plans.destroy', $plan) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline btn-error" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-12">
                                        <i class="fas fa-credit-card text-gray-300 text-4xl mb-4"></i>
                                        <p class="text-gray-500 text-lg">Belum ada paket langganan.</p>
                                        <a href="{{ route('admin.subscription-plans.create') }}" class="btn btn-primary mt-4">Tambah Paket Pertama</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($subscriptionPlans->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $subscriptionPlans->links() }}
                </div>
                @endif
            </div>

            @if(session('success'))
                <div class="toast toast-top toast-end">
                    <div class="alert alert-success">
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="toast toast-top toast-end">
                    <div class="alert alert-error">
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection