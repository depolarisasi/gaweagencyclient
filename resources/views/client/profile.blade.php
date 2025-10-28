@extends('layouts.client')

@section('title', 'My Profile')

@section('content')
    <div class="p-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-user text-blue-600 mr-3"></i>
                        My Profile
                    </h1>
                    <p class="text-gray-600">Manage your account information and settings</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Information -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-header bg-gray-50 px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Profile Information</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('client.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium text-gray-700">Full Name</span>
                                    </label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                           class="input input-bordered w-full @error('name') input-error @enderror" 
                                           required>
                                    @error('name')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium text-gray-700">Email Address</span>
                                    </label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                           class="input input-bordered w-full @error('email') input-error @enderror" 
                                           required>
                                    @error('email')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium text-gray-700">Phone Number</span>
                                    </label>
                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                                           class="input input-bordered w-full @error('phone') input-error @enderror">
                                    @error('phone')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>

                                <!-- Company -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium text-gray-700">Company</span>
                                    </label>
                                    <input type="text" name="company" value="{{ old('company', $user->company) }}" 
                                           class="input input-bordered w-full @error('company') input-error @enderror">
                                    @error('company')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="form-control mt-6">
                                <label class="label">
                                    <span class="label-text font-medium text-gray-700">Address</span>
                                </label>
                                <textarea name="address" rows="3" 
                                          class="textarea textarea-bordered w-full @error('address') textarea-error @enderror">{{ old('address', $user->address) }}</textarea>
                                @error('address')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-8">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card bg-base-100 shadow-lg mt-8">
                    <div class="card-header bg-gray-50 px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Change Password</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('client.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Current Password -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium text-gray-700">Current Password</span>
                                    </label>
                                    <input type="password" name="current_password" 
                                           class="input input-bordered w-full @error('current_password') input-error @enderror" 
                                           required>
                                    @error('current_password')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>

                                <!-- New Password -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium text-gray-700">New Password</span>
                                    </label>
                                    <input type="password" name="password" 
                                           class="input input-bordered w-full @error('password') input-error @enderror" 
                                           required>
                                    @error('password')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium text-gray-700">Confirm New Password</span>
                                    </label>
                                    <input type="password" name="password_confirmation" 
                                           class="input input-bordered w-full" 
                                           required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-8">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key mr-2"></i>
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Profile Summary -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-header bg-gray-50 px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Account Summary</h3>
                    </div>
                    <div class="card-body">
                        <!-- Profile Avatar -->
                        <div class="text-center mb-6">
                            <div class="avatar">
                                <div class="w-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                                    @else
                                        <div class="bg-primary text-primary-content flex items-center justify-center text-2xl font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <h4 class="text-xl font-semibold text-gray-800 mt-4">{{ $user->name }}</h4>
                            <p class="text-gray-600">{{ $user->email }}</p>
                        </div>

                        <!-- Account Stats -->
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2 border-b">
                                <span class="text-gray-600">Member Since</span>
                                <span class="font-medium text-gray-800">{{ $user->created_at->format('M Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b">
                                <span class="text-gray-600">Account Status</span>
                                <span class="badge badge-success">Active</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b">
                                <span class="text-gray-600">Total Projects</span>
                                <span class="font-medium text-gray-800">{{ $user->projects->count() }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Total Orders</span>
                                <span class="font-medium text-gray-800">{{ $user->orders->count() ?? 0 }}</span>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-8 space-y-3">
                            <a href="{{ route('client.projects.index') }}" class="btn btn-outline btn-primary w-full">
                                <i class="fas fa-project-diagram mr-2"></i>
                                View Projects
                            </a>
                            <a href="{{ route('client.invoices.index') }}" class="btn btn-outline btn-success w-full">
                                <i class="fas fa-file-invoice mr-2"></i>
                                View Invoices
                            </a>
                            <a href="{{ route('client.tickets.index') }}" class="btn btn-outline btn-info w-full">
                                <i class="fas fa-ticket-alt mr-2"></i>
                                Support Tickets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection