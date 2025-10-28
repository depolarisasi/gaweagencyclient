<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Client Dashboard') - {{ config('app.name', 'Gawe Agency') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Compiled CSS with custom theme -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @livewireStyles
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation Bar -->
    <nav class="navbar bg-primary text-primary-content">
        <div class="navbar-start">
            <a class="btn btn-ghost text-xl" href="{{ url('/') }}">
                <i class="fas fa-briefcase mr-2"></i>Gawe Agency
            </a>
        </div>
        
        <div class="navbar-end">
            <!-- Cart Notification -->
            @livewire('cart-notification')
            
            <!-- User Dropdown -->
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost flex items-center space-x-2">
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <span class="hidden md:block font-medium">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
                
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                    <li>
                        <a href="{{ route('client.profile') }}" class="text-gray-700">
                            <i class="fas fa-user-cog mr-2"></i>Profile Settings
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-700 w-full text-left">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <div class="flex">
            <!-- Sidebar -->
            @include('components.client-sidebar')
            
            <!-- Page Content -->
            <div class="flex-1 p-8">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error!',
                html: '@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach',
                timer: 5000,
                showConfirmButton: false
            });
        </script>
    @endif

    @livewireScripts
    @stack('scripts')
</body>
</html>