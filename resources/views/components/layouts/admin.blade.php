<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - Gawe Agency')</title>
    
    <!-- Tailwind CSS + DaisyUI -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @stack('styles')
    @livewireStyles
</head>
<body>
    <div class="min-h-screen bg-gray-50">
        <div class="flex">
            <!-- Sidebar -->
            <div class="w-64 bg-white shadow-lg border-r border-gray-200">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-crown text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Admin Panel</h3>
                            <p class="text-xs text-gray-500">Management Center</p>
                        </div>
                    </div>
                    
                    <nav class="space-y-2">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                            <i class="fas fa-chart-line w-5"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                        
                        <div class="pt-4">
                            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Management</p>
                            
                            <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                                <i class="fas fa-users w-5"></i>
                                <span>User Management</span>
                            </a>
                            
                            <a href="{{ route('admin.products') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('admin.products') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                                <i class="fas fa-box w-5"></i>
                                <span>Products</span>
                            </a>
                            
                            <a href="{{ route('admin.projects.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('admin.projects.*') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                                <i class="fas fa-project-diagram w-5"></i>
                                <span>Projects</span>
                            </a>
                            
                            <a href="{{ route('admin.invoices.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('admin.invoices.*') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                                <i class="fas fa-file-invoice w-5"></i>
                                <span>Invoices</span>
                            </a>
                        </div>
                        
                        <div class="pt-4">
                            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Support</p>
                            
                            <a href="{{ route('admin.tickets.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('admin.tickets.*') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                                <i class="fas fa-ticket-alt w-5"></i>
                                <span>Support Tickets</span>
                            </a>
                        </div>
                        
                        <div class="pt-4">
                            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Settings</p>
                            
                            <a href="{{ route('admin.templates.index') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('admin.templates.*') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                                <i class="fas fa-palette w-5"></i>
                                <span>Templates</span>
                            </a>
                            
                            <a href="{{ route('admin.settings') }}" class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('admin.settings') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                                <i class="fas fa-credit-card w-5"></i>
                                <span>Payment Settings</span>
                            </a>
                        </div>
                        
                        <div class="pt-6 border-t border-gray-200">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-red-50 hover:text-red-600 rounded-lg transition-colors w-full text-left">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="flex-1 p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
    
    @livewireScripts
    @stack('scripts')
</body>
</html>