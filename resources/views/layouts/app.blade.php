<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gawe Agency')</title>
    
    <!-- Tailwind CSS + DaisyUI -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        @yield('styles')
    @livewireStyles
</head>
<body class="bg-base-100 text-base-content">
    <!-- Navigation -->
    <div class="navbar bg-primary text-primary-content">
        <div class="navbar-start">
            <a class="btn btn-ghost text-xl" href="{{ url('/') }}">
                <i class="fas fa-briefcase mr-2"></i>Gawe Agency
            </a>
        </div>
        
        <div class="navbar-end">
            @guest
                <!-- Guest Navigation: [cart][register][login] -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('checkout.index') }}" class="btn btn-ghost">
                        <i class="fas fa-shopping-cart mr-1"></i>Cart
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-ghost">
                        <i class="fas fa-user-plus mr-1"></i>Register
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-ghost">
                        <i class="fas fa-sign-in-alt mr-1"></i>Login
                    </a>
                </div>
            @else
                <!-- Authenticated Navigation: [cart][client area][user profile dropdown] -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('checkout.index') }}" class="btn btn-ghost">
                        <i class="fas fa-shopping-cart mr-1"></i>Cart
                    </a>
                    
                    @if(auth()->user()->role === 'client')
                        <a href="{{ route('client.dashboard') }}" class="btn btn-ghost">
                            <i class="fas fa-tachometer-alt mr-1"></i>Client Area
                        </a>
                    @elseif(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
                            <i class="fas fa-crown mr-1"></i>Admin Panel
                        </a>
                    @elseif(auth()->user()->role === 'staff')
                        <a href="{{ route('staff.dashboard') }}" class="btn btn-ghost">
                            <i class="fas fa-users mr-1"></i>Staff Panel
                        </a>
                    @endif
                    
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost">
                            <i class="fas fa-user mr-1"></i>{{ auth()->user()->name }}
                            <i class="fas fa-chevron-down ml-1"></i>
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                            @if(auth()->user()->role === 'client')
                                <li><a href="{{ route('client.profile') }}"><i class="fas fa-user-edit mr-2"></i>Profile</a></li>
                            @endif
                            <li><hr class="my-2"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full text-left">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            @endguest
        </div>
    </div>
    
    <!-- Main Content -->
    <main class="@guest py-5 @else py-4 @endguest">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="footer footer-center bg-base-200 text-base-content p-4 mt-auto">
        <div>
            <p>&copy; {{ date('Y') }} Gawe Agency. All rights reserved.</p>
        </div>
    </footer>
 
    @yield('scripts')
    @livewireScripts
            {{-- Global SweetAlert include --}}  
</body>
</html>