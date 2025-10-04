@extends('layouts.app')

@section('title', 'Register - Gawe Agency')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/10 to-secondary/10 py-8">
    <div class="w-full max-w-2xl p-4">
        <div class="card bg-base-100 shadow-2xl border border-base-300">
            <div class="card-body p-8">
                <!-- Logo/Brand -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 mx-auto mb-4 bg-success rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-success-content" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-base-content mb-2">Create Account</h1>
                    <p class="text-base-content/60">Join us and start your journey</p>
                </div>
                @if($errors->any())
                    <div class="alert alert-error mb-6">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
                    
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-control">
                            <label class="label" for="name">
                                <span class="label-text font-medium">Full Name *</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       class="input input-bordered pl-10 w-full @error('name') input-error @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       placeholder="Enter your full name"
                                       required 
                                       autofocus>
                            </div>
                            @error('name')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label" for="email">
                                <span class="label-text font-medium">Email Address *</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                </div>
                                <input type="email" 
                                       class="input input-bordered pl-10 w-full @error('email') input-error @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="Enter your email"
                                       required>
                            </div>
                            @error('email')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                        
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-control">
                            <label class="label" for="password">
                                <span class="label-text font-medium">Password *</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input type="password" 
                                       class="input input-bordered pl-10 w-full @error('password') input-error @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Create a password"
                                       required>
                            </div>
                            @error('password')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label" for="password_confirmation">
                                <span class="label-text font-medium">Confirm Password *</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input type="password" 
                                       class="input input-bordered pl-10 w-full" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Confirm your password"
                                       required>
                            </div>
                        </div>
                    </div>
                        
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-control">
                            <label class="label" for="phone">
                                <span class="label-text font-medium">Phone Number</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       class="input input-bordered pl-10 w-full @error('phone') input-error @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}"
                                       placeholder="Enter your phone number">
                            </div>
                            @error('phone')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                        
                        <div class="form-control">
                            <label class="label" for="company_name">
                                <span class="label-text font-medium">Company Name</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       class="input input-bordered pl-10 w-full @error('company_name') input-error @enderror" 
                                       id="company_name" 
                                       name="company_name" 
                                       value="{{ old('company_name') }}"
                                       placeholder="Enter your company name">
                            </div>
                            @error('company_name')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>
                        
                    <div class="alert alert-info">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm">Your account will be activated by administrator after registration.</span>
                    </div>
                    
                    <div class="form-control mt-8">
                        <button type="submit" class="btn btn-success btn-lg w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Create Account
                        </button>
                    </div>
                </form>
                
                <div class="divider text-base-content/60">or</div>
                
                <div class="text-center">
                    <p class="text-base-content/60 mb-4">Already have an account?</p>
                    <a href="{{ route('login') }}" class="btn btn-outline btn-wide">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Sign In
                    </a>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection