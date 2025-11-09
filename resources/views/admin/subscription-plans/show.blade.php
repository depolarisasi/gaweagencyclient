@extends('layouts.app')

@section('title', 'Subscription Plans Management - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="flex">
        @include('layouts.sidebar')
        <div class="flex-1 p-8">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $subscriptionPlan->name }}</h1>
                    <p class="text-gray-600">Detail paket langganan</p>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <a href="{{ route('admin.subscription-plans.edit', $subscriptionPlan) }}" class="btn btn-warning">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <form method="POST" action="{{ route('admin.subscription-plans.toggle-status', $subscriptionPlan) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn {{ $subscriptionPlan->is_active ? 'btn-error' : 'btn-success' }}">
                        <i class="fas fa-{{ $subscriptionPlan->is_active ? 'pause' : 'play' }} mr-2"></i>
                        {{ $subscriptionPlan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Plan Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nama Paket</label>
                            <p class="text-lg font-medium text-gray-900">{{ $subscriptionPlan->name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Harga</label>
                            <p class="text-lg font-medium text-gray-900">
                                Rp {{ number_format($subscriptionPlan->price, 0, ',', '.') }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Siklus Pembayaran</label>
                            <p class="text-lg font-medium text-gray-900">
                                {{ $subscriptionPlan->billing_cycle_label }}
                                <span class="text-sm text-gray-600">({{ $subscriptionPlan->cycle_months }} bulan)</span>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Urutan Tampil</label>
                            <p class="text-lg font-medium text-gray-900">{{ $subscriptionPlan->sort_order }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Diskon</label>
                            <p class="text-lg font-medium text-gray-900">
                                @if((float)($subscriptionPlan->discount_percentage ?? 0) > 0)
                                    {{ rtrim(rtrim(number_format($subscriptionPlan->discount_percentage, 2, ',', '.'), '0'), ',') }}%
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($subscriptionPlan->description)
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-500">Deskripsi</label>
                            <p class="text-gray-900 mt-1">{{ $subscriptionPlan->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Features Card -->
                @if($subscriptionPlan->features && count($subscriptionPlan->features) > 0)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Fitur-fitur</h2>
                        <div class="space-y-2">
                            @foreach($subscriptionPlan->features as $feature)
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-check text-green-500"></i>
                                    <span class="text-gray-900">{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Statistics Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $subscriptionPlan->orders()->count() }}</div>
                            <div class="text-sm text-blue-600">Total Pesanan</div>
                        </div>
                        
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">
                                {{ $subscriptionPlan->orders()->where('status', 'completed')->count() }}
                            </div>
                            <div class="text-sm text-green-600">Pesanan Selesai</div>
                        </div>
                        
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">
                                Rp {{ number_format($subscriptionPlan->orders()->where('status', 'completed')->sum('total_amount'), 0, ',', '.') }}
                            </div>
                            <div class="text-sm text-yellow-600">Total Revenue</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status & Actions -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Status</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Status Aktif</span>
                            <span class="badge {{ $subscriptionPlan->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $subscriptionPlan->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Paket Popular</span>
                            <span class="badge {{ $subscriptionPlan->is_popular ? 'badge-warning' : 'badge-outline' }}">
                                {{ $subscriptionPlan->is_popular ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Dibuat</span>
                            <span class="text-sm text-gray-900">
                                {{ $subscriptionPlan->created_at->format('d M Y') }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Terakhir Update</span>
                            <span class="text-sm text-gray-900">
                                {{ $subscriptionPlan->updated_at->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h2>
                    <div class="space-y-2">
                        <a href="{{ route('admin.subscription-plans.edit', $subscriptionPlan) }}" 
                           class="btn btn-outline btn-warning w-full">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Paket
                        </a>
                        
                        <form method="POST" action="{{ route('admin.subscription-plans.toggle-status', $subscriptionPlan) }}" class="w-full">
                            @csrf
                            <button type="submit" class="btn btn-outline {{ $subscriptionPlan->is_active ? 'btn-error' : 'btn-success' }} w-full">
                                <i class="fas fa-{{ $subscriptionPlan->is_active ? 'pause' : 'play' }} mr-2"></i>
                                {{ $subscriptionPlan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.subscription-plans.destroy', $subscriptionPlan) }}" 
                              class="w-full" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-error w-full">
                                <i class="fas fa-trash mr-2"></i>
                                Hapus Paket
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Preview Paket</h2>
                    <div class="border border-gray-200 rounded-lg p-4 {{ $subscriptionPlan->is_popular ? 'ring-2 ring-yellow-400' : '' }}">
                        @if($subscriptionPlan->is_popular)
                            <div class="text-center mb-2">
                                <span class="badge badge-warning">Popular</span>
                            </div>
                        @endif
                        
                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $subscriptionPlan->name }}</h3>
                            @if($subscriptionPlan->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $subscriptionPlan->description }}</p>
                            @endif
                            
                            <div class="mt-4">
                                <div class="text-2xl font-bold text-gray-900">
                                    Rp {{ number_format($subscriptionPlan->price, 0, ',', '.') }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    per {{ str_replace('_', ' ', $subscriptionPlan->billing_cycle) }}
                                </div>
                            </div>
                            
                            @if($subscriptionPlan->features && count($subscriptionPlan->features) > 0)
                                <div class="mt-4 text-left">
                                    @foreach(array_slice($subscriptionPlan->features, 0, 3) as $feature)
                                        <div class="flex items-center space-x-2 text-sm">
                                            <i class="fas fa-check text-green-500"></i>
                                            <span>{{ $feature }}</span>
                                        </div>
                                    @endforeach
                                    @if(count($subscriptionPlan->features) > 3)
                                        <div class="text-sm text-gray-500 mt-1">
                                            +{{ count($subscriptionPlan->features) - 3 }} fitur lainnya
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
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