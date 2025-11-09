<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\CartService;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartNotification extends Component
{
    public $cartCount = 0;
    public $hasAbandonedCart = false;
    public $cartId = null;

    protected $cartService;
    
    protected $listeners = ['cartUpdated' => 'updateCartStatus'];

    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function mount()
    {
        $this->updateCartStatus();
    }

    public function updateCartStatus()
    {
        try {
            $cart = $this->cartService->getOrCreateCart(request());
            
            if ($cart) {
                $this->cartId = $cart->id;
                
                // Check if cart has any data (abandoned cart)
                $this->hasAbandonedCart = $this->isAbandonedCart($cart);
                
                // Count items in cart (template + addons)
                $this->cartCount = 0;
                if ($cart->template_id) {
                    $this->cartCount++;
                }
                $this->cartCount += $cart->cartAddons()->count();
                

            }
        } catch (\Exception $e) {
            \Log::error('Error updating cart status: ' . $e->getMessage());
            $this->cartCount = 0;
            $this->hasAbandonedCart = false;
        }
    }

    private function isAbandonedCart(Cart $cart): bool
    {
        // Cart is considered abandoned if it has:
        // 1. A template selected, OR
        // 2. A subscription plan selected, OR
        // 3. Customer info filled, OR
        // 4. Domain data filled, OR
        // 5. Any addons selected
        return $cart->template_id || 
               $cart->subscription_plan_id || 
               !empty($cart->configuration['customer_info']) ||
               !empty($cart->domain_data) ||
               $cart->cartAddons()->count() > 0;
    }

    public function resumeCart()
    {
        if ($this->cartId) {
            $cart = Cart::find($this->cartId);
            if ($cart) {
                $nextStep = $this->determineNextCheckoutStep($cart);
                return redirect()->route($nextStep);
            }
        }
        return redirect()->route('checkout.index');
    }

    /**
     * Determine the next checkout step based on cart progress
     */
    private function determineNextCheckoutStep(Cart $cart): string
    {
        // Step 1: Template selection - if no template, start from beginning
        if (!$cart->template_id) {
            return 'checkout.index';
        }

        // Step 2: Configure subscription plan - if no subscription plan, go to configure
        if (!$cart->subscription_plan_id) {
            return 'checkout.configure';
        }

        // Step 3: Configure - jika info personal atau domain belum lengkap, arahkan ke configure
        $customerInfo = $cart->configuration['customer_info'] ?? null;
        if (empty($customerInfo) || empty($cart->domain_data)) {
            return 'checkout.configure';
        }

        // Step 4: Personal info - if customer info or domain data is incomplete, go to personal-info
        if (empty($customerInfo['full_name']) || empty($customerInfo['email']) || 
            empty($cart->domain_data['domain_name']) || empty($cart->domain_data['domain_type'])) {
            return 'checkout.personal-info';
        }

        // Step 5: Summary - if all previous steps are complete, go to summary
        return 'checkout.summary';
    }

    public function refreshCart()
    {
        $this->updateCartStatus();
    }

    public function render()
    {
        return view('livewire.cart-notification');
    }
}