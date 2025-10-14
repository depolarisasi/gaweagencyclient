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
    @auth
        <div class="navbar bg-primary text-primary-content">
            <div class="navbar-start">
                <a class="btn btn-ghost text-xl" href="{{ route('dashboard') }}">
                    <i class="fas fa-briefcase mr-2"></i>Gawe Agency
                </a>
                
                <div class="hidden lg:flex">
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
            </div>
            
            <div class="navbar-end">
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost">
                        <i class="fas fa-user mr-1"></i>{{ auth()->user()->name }}
                        <i class="fas fa-chevron-down ml-1"></i>
                    </div>
                    <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                        <li><a href="{{ route('client.profile') }}"><i class="fas fa-user-edit mr-2"></i>Profile</a></li>
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
        </div>
    @endauth
    
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