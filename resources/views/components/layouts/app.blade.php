<!DOCTYPE html>
<html lang="en"  data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gawe Agency Client')</title>
    
    <!-- Tailwind CSS + DaisyUI -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    @auth
    <div class="navbar bg-primary text-primary-content">
        <div class="navbar-start">
            <div class="dropdown">
                <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"></path>
                    </svg>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                    @if(auth()->user()->role === 'admin')
                        <li><a href="{{ route('admin.dashboard') }}" class="text-base-content">Dashboard</a></li>
                    @elseif(auth()->user()->role === 'staff')
                        <li><a href="{{ route('staff.dashboard') }}" class="text-base-content">Dashboard</a></li>
                    @elseif(auth()->user()->role === 'client')
                        <li><a href="{{ route('client.dashboard') }}" class="text-base-content">Dashboard</a></li>
                    @endif
                </ul>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost text-xl">
                <i class="fas fa-briefcase mr-2"></i>Gawe Agency
            </a>
        </div>
        
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1">
                @if(auth()->user()->role === 'admin')
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                @elseif(auth()->user()->role === 'staff')
                    <li><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                @elseif(auth()->user()->role === 'client')
                    <li><a href="{{ route('client.dashboard') }}">Dashboard</a></li>
                @endif
            </ul>
        </div>
        
        <div class="navbar-end">
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost">
                    <i class="fas fa-user mr-1"></i>{{ auth()->user()->name }}
                    <i class="fas fa-chevron-down ml-1"></i>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                    <li><a href="{{ route('client.profile') }}" class="text-base-content"><i class="fas fa-user-edit mr-2"></i>Profile</a></li>
                    <li><hr class="my-2"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left text-base-content">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endauth
    
    <!-- Main Content -->
    <main class="@guest min-h-screen @else min-h-[calc(100vh-4rem)] @endguest">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="footer footer-center p-4 bg-base-100 text-base-content">
        <div>
            <p>Copyright &copy; {{ date('Y') }} Gawe Agency. All rights reserved.</p>
        </div>
    </footer>
    
    @stack('scripts')
</body>
</html>