<!DOCTYPE html>
@php
    $theme = request()->cookie('theme') ?? session('theme') ?? 'light';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Client Dashboard') - {{ config('app.name', 'Gawe') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon"> 
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Compiled CSS with custom theme -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     
    
    @livewireStyles
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <!-- Navigation Bar -->
    @include('components.client-navbar')

    <!-- Main Content -->
    <div class="min-h-screen bg-base-100">
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