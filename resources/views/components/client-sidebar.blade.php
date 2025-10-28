<!-- Client Sidebar Component -->
<div class="w-64 bg-white shadow-xl border-r border-gray-200">
    <div class="p-6">
        <div class="flex items-center space-x-3 mb-8">
            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-lg flex items-center justify-center">
                <i class="fas fa-user text-white text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Client Portal</h3>
                <p class="text-xs text-gray-500">Your Projects Hub</p>
            </div>
        </div>
        
        <nav class="space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('client.dashboard') }}" 
               class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('client.dashboard') ? 'bg-green-50 text-green-700 border border-green-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                <i class="fas fa-home w-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <!-- My Services Section -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">My Services</p>
                
                <!-- My Projects -->
                <a href="{{ route('client.projects.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('client.projects.*') ? 'bg-green-50 text-green-700 border border-green-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                    <i class="fas fa-project-diagram w-5"></i>
                    <span>My Projects</span>
                </a>
                
                <!-- My Orders -->
                <a href="{{ route('client.orders') }}" 
                   class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('client.orders') ? 'bg-green-50 text-green-700 border border-green-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>My Orders</span>
                </a>
                
                <!-- Invoices & Billing -->
                <a href="{{ route('client.invoices.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('client.invoices.*') ? 'bg-green-50 text-green-700 border border-green-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                    <i class="fas fa-file-invoice w-5"></i>
                    <span>Invoices & Billing</span>
                </a>
            </div>
            
            <!-- Explore Section -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Explore</p>
                
                <!-- Browse Services -->
                <a href="{{ route('client.products') }}" 
                   class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('client.products') ? 'bg-green-50 text-green-700 border border-green-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                    <i class="fas fa-store w-5"></i>
                    <span>Browse Services</span>
                </a>
            </div>
            
            <!-- Support Section -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Support</p>
                
                <!-- Support Tickets -->
                <a href="{{ route('client.tickets.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('client.tickets.*') ? 'bg-green-50 text-green-700 border border-green-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                    <i class="fas fa-life-ring w-5"></i>
                    <span>Support Tickets</span>
                </a>
            </div>

            <!-- Account Section -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Account</p>
                
                <!-- Profile -->
                <a href="{{ route('client.profile') }}" 
                   class="flex items-center space-x-3 px-4 py-3 {{ request()->routeIs('client.profile') ? 'bg-green-50 text-green-700 border border-green-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                    <i class="fas fa-user-cog w-5"></i>
                    <span>Profile Settings</span>
                </a>
            </div>
        </nav>
    </div>
</div>