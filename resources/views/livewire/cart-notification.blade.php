<div>
    <button wire:click="resumeCart" class="btn btn-ghost btn-sm relative">
        Cart
        
        @if($hasAbandonedCart)
            <!-- Red notification indicator -->
            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                !
            </div>
        @elseif($cartCount > 0)
            <!-- Regular cart count -->
            <div class="absolute -top-2 -right-2 bg-primary text-primary-content text-xs rounded-full h-5 w-5 flex items-center justify-center">
                !
            </div>
        @endif
    </button>
</div>