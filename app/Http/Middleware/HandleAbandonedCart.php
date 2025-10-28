<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class HandleAbandonedCart
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Only handle for authenticated users or checkout routes
        if (Auth::check() || $request->routeIs('checkout.*')) {
            try {
                // Get or create cart for current session/user
                $cart = $this->cartService->getOrCreateCart($request);
                
                // If user just logged in, merge any session cart with user cart
                if (Auth::check() && $request->session()->has('just_logged_in')) {
                    $this->mergeSessionCartWithUserCart($request, $cart);
                    $request->session()->forget('just_logged_in');
                }
                
                // Share cart data with views
                view()->share('currentCart', $cart);
                
            } catch (\Exception $e) {
                \Log::error('Error in HandleAbandonedCart middleware: ' . $e->getMessage());
            }
        }

        return $next($request);
    }

    private function mergeSessionCartWithUserCart(Request $request, Cart $userCart)
    {
        try {
            // Look for any session-based cart data
            $sessionId = $request->session()->getId();
            $sessionCart = Cart::forSession($sessionId)->notExpired()->first();
            
            if ($sessionCart && $sessionCart->id !== $userCart->id) {
                // Merge session cart data into user cart if user cart is empty
                if (!$userCart->template_id && $sessionCart->template_id) {
                    $userCart->template_id = $sessionCart->template_id;
                }
                
                if (!$userCart->subscription_plan_id && $sessionCart->subscription_plan_id) {
                    $userCart->subscription_plan_id = $sessionCart->subscription_plan_id;
                    $userCart->billing_cycle = $sessionCart->billing_cycle;
                    $userCart->template_amount = $sessionCart->template_amount;
                }
                
                if (empty($userCart->configuration) && !empty($sessionCart->configuration)) {
                    $userCart->configuration = $sessionCart->configuration;
                }
                
                if (empty($userCart->domain_data) && !empty($sessionCart->domain_data)) {
                    $userCart->domain_data = $sessionCart->domain_data;
                    $userCart->domain_amount = $sessionCart->domain_amount;
                }
                
                // Merge addons
                if ($sessionCart->cartAddons()->count() > 0 && $userCart->cartAddons()->count() === 0) {
                    foreach ($sessionCart->cartAddons as $addon) {
                        $userCart->cartAddons()->create([
                            'product_addon_id' => $addon->product_addon_id,
                            'price' => $addon->price
                        ]);
                    }
                }
                
                // Recalculate totals and save
                $userCart->calculateTotals();
                $userCart->save();
                
                // Delete the session cart
                $sessionCart->delete();
                
                \Log::info('Merged session cart with user cart', [
                    'user_id' => Auth::id(),
                    'session_cart_id' => $sessionCart->id,
                    'user_cart_id' => $userCart->id
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error merging session cart with user cart: ' . $e->getMessage());
        }
    }
}