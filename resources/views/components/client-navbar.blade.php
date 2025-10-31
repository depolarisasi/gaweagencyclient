  <!-- Navigation Bar -->
    <nav class="navbar sticky top-0 z-50 bg-base-100">
        <div class="navbar-start">
            <a class="btn btn-ghost text-xl" href="{{ url('/') }}">
                 Gawe
            </a>
        </div>
        
        <div class="navbar-end">
            <!-- Cart Notification -->
            @livewire('cart-notification')
            
            <!-- User Dropdown -->
            @guest
                <!-- Guest Navigation: [cart][register][login] -->
                <div class="flex items-center space-x-2">
                  
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">
                         Login
                    </a>
                      
                    <a href="{{ route('register') }}" class="btn btn-neutral btn-sm">
                        Buat Website Sekarang
                    </a>
                </div>
            @else
                
                    
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
                                <li><a href="{{ route('client.profile') }}" class="text-base-content"><i class="fas fa-user-edit mr-2"></i>Profile</a></li>
                            @endif
                            <li><hr class="my-2"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full text-left text-base-content">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            @endguest
        </div>
    </nav>