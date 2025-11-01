  <!-- Navigation Bar -->
    <nav class="navbar sticky top-0 z-50 bg-base-100 border-b border-base-300">
        <div class="navbar-start">
            <a href="{{ url('/') }}">
            <img src="{{ asset('images/gawe-logo.png') }}" alt="Gawe Agency" class="h-8 ml-5">
            </a>
        </div>
        
        <div class="navbar-end">
            <!-- Cart Notification -->
            @livewire('cart-notification')
            
            <!-- User Dropdown -->
            @guest
                <!-- Guest Navigation: [cart][register][login] -->
                <div class="flex items-center space-x-2">
                  
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm ">
                         Login
                    </a>
                      
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                        Buat Website Sekarang
                    </a>
                </div>
            @else
                
                    
                    @if(auth()->user()->role === 'client')
                        <a href="{{ route('client.dashboard') }}" class="btn btn-sm btn-ghost">
                           Client Area
                        </a>
                    @elseif(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-ghost">
                           Admin Panel
                        </a>
                    @elseif(auth()->user()->role === 'staff')
                        <a href="{{ route('staff.dashboard') }}" class="btn btn-sm btn-ghost">
                           Staff Panel
                        </a>
                    @endif
                    
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-sm btn-ghost">
                            <i class="fas fa-user mr-1"></i>{{ auth()->user()->name }}
                            <i class="fas fa-chevron-down ml-1"></i>
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                            @if(auth()->user()->role === 'client')
                                <li><a href="{{ route('client.profile') }}"> Profile</a></li>
                            @endif 
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full ">
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            @endguest
        </div>
    </nav>