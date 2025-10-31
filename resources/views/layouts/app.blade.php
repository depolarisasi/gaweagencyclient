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
    @include('components.client-navbar')
    
    <!-- Main Content -->
    <main>
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