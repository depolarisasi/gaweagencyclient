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
    @include('components.client-navbar')
    
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