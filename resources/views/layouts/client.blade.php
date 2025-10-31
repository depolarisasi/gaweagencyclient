<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Client Dashboard') - {{ config('app.name', 'Gawe') }}</title>

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
    @include('components.client-navbar')

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