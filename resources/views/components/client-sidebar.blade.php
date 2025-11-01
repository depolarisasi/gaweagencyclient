<!-- Client Sidebar Component -->
<div class="w-64 bg-base-100 shadow-sm border-r border-base-300">
    <div class="p-6">
        <div class="flex items-center space-x-3 mb-8">
            <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                <i class="fas fa-user text-white text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">{{ auth()->user()->name }}</h3> 
            </div>
        </div>
        
        <nav class="space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('client.dashboard') }}" 
               class="flex items-center space-x-3 px-4 py-1 {{ request()->routeIs('client.dashboard') ? 'bg-base-200 text-grey-600 border border-base-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                
                <span class="font-medium">📊 Dashboard</span>
            </a>
            
            <!-- My Services Section --> 
                
                <!-- My Projects -->
                <a href="{{ route('client.projects.index') }}" 
                   class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('client.projects.*') ? 'bg-base-200 text-grey-600 border border-base-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                  
                    <span>🗂️ My Projects</span>
                </a>
                
                <!-- My Orders -->
                <a href="{{ route('client.orders') }}" 
                   class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('client.orders') ? 'bg-base-200 text-grey-600 border border-base-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                   
                    <span>🔄 My Orders</span>
                </a>
                
                <!-- Invoices & Billing -->
                <a href="{{ route('client.invoices.index') }}" 
                   class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('client.invoices.*') ? 'bg-base-200 text-grey-600 border border-base-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                     
                    <span>🧾 Invoices & Billing</span>
                </a> 
             
                <!-- Browse Services -->
                <a href="{{ route('client.products') }}" 
                   class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('client.products') ? 'bg-base-200 text-grey-600 border border-base-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                   
                    <span>🛒 Browse Services</span>
                </a> 
            
            <!-- Support Section -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Support</p>
                
                <!-- Support Tickets -->
                <a href="{{ route('client.tickets.index') }}" 
                   class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('client.tickets.*') ? 'bg-base-200 text-grey-600 border border-base-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                   
                    <span>💬 Support Tickets</span>
                </a>
            </div>

            <!-- Account Section -->
            <div class="pt-4">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Account</p>
                
                <!-- Profile -->
                <a href="{{ route('client.profile') }}" 
                   class="flex items-center space-x-3 px-4 py-2 {{ request()->routeIs('client.profile') ? 'bg-base-200 text-grey-600 border border-base-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-lg transition-colors">
                   
                    <span>👤 Profile Settings</span>
                </a>
            </div>
        </nav>
    </div>
</div>