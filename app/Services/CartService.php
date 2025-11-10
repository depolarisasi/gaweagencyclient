<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartAddon;
use App\Models\Template;
use App\Models\SubscriptionPlan;
use App\Models\ProductAddon;
use App\Helpers\CheckoutCookieHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get or create cart for current session/user
     */
    public function getOrCreateCart(Request $request): Cart
    {
        if (Auth::check()) {
            $cart = Cart::findOrCreateForUser(Auth::id());
            \Log::info('Cart for authenticated user:', ['user_id' => Auth::id(), 'cart_id' => $cart->id]);
            return $cart;
        } else {
            $sessionId = $request->session()->getId();
            $cart = Cart::findOrCreateForSession($sessionId);
            // Penting: Jangan mengambil keranjang tamu lain sebagai fallback.
            // Untuk guest baru, selalu gunakan keranjang kosong untuk mencegah salah alur checkout.
            \Log::info('Cart for session:', ['session_id' => $sessionId, 'cart_id' => $cart->id, 'cart_exists' => $cart->wasRecentlyCreated ? 'new' : 'existing']);
            return $cart;
        }
    }

    /**
     * Migrate session/cookie data to database cart
     */
    public function migrateFromSessionAndCookies(Request $request): Cart
    {
        $cart = $this->getOrCreateCart($request);

        // Migrate from cookies first (priority), then session
        $templateId = CheckoutCookieHelper::getTemplateId() ?? $request->session()->get('checkout.template_id');
        $subscriptionPlanId = CheckoutCookieHelper::getSubscriptionPlanId() ?? $request->session()->get('checkout.subscription_plan_id');
        $billingCycle = CheckoutCookieHelper::getBillingCycle() ?? $request->session()->get('checkout.billing_cycle');
        $customerInfo = CheckoutCookieHelper::getCustomerInfo() ?? $request->session()->get('checkout.customer_info');
        $domainInfo = CheckoutCookieHelper::getDomain() ?? $request->session()->get('checkout.domain');
        
        \Log::info('Migration data sources:', [
            'cart_id' => $cart->id,
            'current_cart_config' => $cart->configuration,
            'current_cart_domain' => $cart->domain_data,
            'cookie_customer_info' => CheckoutCookieHelper::getCustomerInfo(),
            'session_customer_info' => $request->session()->get('checkout.customer_info'),
            'cookie_domain' => CheckoutCookieHelper::getDomain(),
            'session_domain' => $request->session()->get('checkout.domain'),
            'final_customer_info' => $customerInfo,
            'final_domain_info' => $domainInfo,
        ]);
        
        // Get addons from both sources
        $sessionAddons = $request->session()->get('checkout.selected_addons', []);
        $cookieAddons = CheckoutCookieHelper::getAddons();
        $selectedAddons = !empty($sessionAddons) ? $sessionAddons : $cookieAddons;

        // Track if any changes were made
        $hasChanges = false;

        // Update cart with migrated data (only if not already set)
        if ($templateId && !$cart->template_id) {
            $cart->template_id = $templateId;
            $hasChanges = true;
        }

        if ($subscriptionPlanId && !$cart->subscription_plan_id) {
            $cart->subscription_plan_id = $subscriptionPlanId;
            
            // Set template_amount based on subscription plan price
            $subscriptionPlan = SubscriptionPlan::find($subscriptionPlanId);
            if ($subscriptionPlan) {
                $cart->template_amount = $subscriptionPlan->discounted_price ?? ($subscriptionPlan->price ?? 0);
            }
            
            $hasChanges = true;
        }

        if ($billingCycle && !$cart->billing_cycle) {
            $cart->billing_cycle = $billingCycle;
            $hasChanges = true;
        }

        // Auto-populate customer_info for authenticated users if not present
        if (empty($customerInfo) && Auth::check()) {
            $user = Auth::user();
            $customerInfo = [
                'full_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'company' => $user->company ?? '',
                'user_id' => $user->id,
                'is_logged_in' => true,
            ];
            // Clear one-time flag if set
            if (Session::get('user_just_logged_in')) {
                Session::forget('user_just_logged_in');
            }
        }

        if ($customerInfo && empty($cart->configuration['customer_info'])) {
            $cart->configuration = array_merge($cart->configuration ?? [], [
                'customer_info' => $customerInfo
            ]);
            $hasChanges = true;
        }

        if ($domainInfo && empty($cart->domain_data)) {
            $cart->domain_data = $domainInfo;
            $hasChanges = true;
        }

        // Always update expiration, but only save if there were other changes or expiration needs updating
        $needsExpirationUpdate = !$cart->expires_at || $cart->expires_at->lt(now()->addDays(6));
        if ($needsExpirationUpdate) {
            $cart->expires_at = now()->addDays(7);
            $hasChanges = true;
        }

        // Only save if there were actual changes
        if ($hasChanges) {
            $cart->save();
        }

        // Migrate addons
        if (!empty($selectedAddons)) {
            $this->syncAddons($cart, $selectedAddons);
        }

        return $cart;
    }

    /**
     * Sync addons with cart
     */
    public function syncAddons(Cart $cart, array $addonIds): void
    {
        // Clear existing addons
        $cart->clearAddons();

        // Add new addons
        foreach ($addonIds as $addonId) {
            $addon = ProductAddon::find($addonId);
            if ($addon) {
                $cart->addAddon($addon);
            }
        }

        // Recalculate totals
        $cart->calculateTotals();
        $cart->save();
    }

    /**
     * Update cart template
     */
    public function updateTemplate(Cart $cart, int $templateId): Cart
    {
        $template = Template::findOrFail($templateId);
        $cart->template_id = $templateId;
        $cart->calculateTotals();
        $cart->save();

        return $cart;
    }

    /**
     * Update cart subscription plan
     */
    public function updateSubscriptionPlan(Cart $cart, int $subscriptionPlanId, string $billingCycle): Cart
    {
        $subscriptionPlan = SubscriptionPlan::findOrFail($subscriptionPlanId);
        $cart->subscription_plan_id = $subscriptionPlanId;
        $cart->billing_cycle = $billingCycle;
        
        // Set template_amount based on subscription plan discounted price
        $cart->template_amount = $subscriptionPlan->discounted_price ?? ($subscriptionPlan->price ?? 0);
        
        $cart->calculateTotals();
        $cart->save();

        return $cart;
    }

    /**
     * Update cart customer info
     */
    public function updateCustomerInfo(Cart $cart, array $customerInfo): Cart
    {
        $cart->configuration = array_merge($cart->configuration ?? [], [
            'customer_info' => $customerInfo
        ]);
        $cart->save();

        return $cart;
    }

    /**
     * Update cart domain data
     */
    public function updateDomainData(Cart $cart, array $domainData): Cart
    {
        $cart->domain_data = $domainData;
        $cart->calculateTotals();
        $cart->save();

        return $cart;
    }

    /**
     * Add addon to cart
     */
    public function addAddon(Cart $cart, int $addonId): Cart
    {
        $addon = ProductAddon::findOrFail($addonId);
        $cart->addAddon($addon);
        $cart->calculateTotals();
        $cart->save();

        return $cart;
    }

    /**
     * Remove addon from cart
     */
    public function removeAddon(Cart $cart, int $addonId): Cart
    {
        $addon = ProductAddon::findOrFail($addonId);
        $cart->removeAddon($addon);
        $cart->calculateTotals();
        $cart->save();

        return $cart;
    }

    /**
     * Clear expired carts
     */
    public function clearExpiredCarts(): int
    {
        return Cart::where('expires_at', '<', now())->delete();
    }

    /**
     * Get cart summary for display
     */
    public function getCartSummary(Cart $cart): array
    {
        $template = $cart->template;
        $subscriptionPlan = $cart->subscriptionPlan;
        $addons = $cart->addons;

        // Calculate subscription amount based on subscription plan and billing cycle (apply discount)
        $subscriptionAmount = 0;
        if ($subscriptionPlan) {
            $subscriptionAmount = $subscriptionPlan->discounted_price ?? $subscriptionPlan->price;
        }

        // Calculate domain amount (only for new domain registrations)
        $domainAmount = 0;
        try {
            $domainData = $cart->domain_data ?? [];
            $domainType = $domainData['type'] ?? $domainData['domain_type'] ?? null;
            if ($domainType === 'new') {
                // Prefer explicit price if available
                if (isset($domainData['price']) && is_numeric($domainData['price'])) {
                    $domainAmount = (float) $domainData['price'];
                } else {
                    // Derive price from TLD
                    $tld = $domainData['tld'] ?? null;
                    $domainName = $domainData['name'] ?? $domainData['domain_name'] ?? '';
                    if (!$tld && $domainName) {
                        // Extract TLD from domain name (supports multi-level e.g. co.id)
                        $parts = explode('.', $domainName);
                        if (count($parts) > 1) {
                            // Handle multi-part TLDs (e.g., co.id)
                            $tld = implode('.', array_slice($parts, 1));
                        }
                    }

                    // Get price mapping from DomainService
                    $domainService = app(\App\Services\DomainService::class);
                    $prices = $domainService->getDomainPrices();
                    $domainAmount = $tld && isset($prices[$tld]) ? (float) $prices[$tld] : 150000.0;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to calculate domain amount in CartService::getCartSummary', [
                'error' => $e->getMessage(),
            ]);
            $domainAmount = 0;
        }

        // Recalculate cart financials including domain amount
        $templateAmount = $cart->template_amount ?? 0;
        $addonsAmount = $cart->addons_amount ?? 0;
        $subtotal = $templateAmount + $addonsAmount + $domainAmount;
        // Jangan menambahkan biaya platform 3% pada ringkasan.
        // Tripay akan menambahkan fee_customer saat transaksi dibuat.
        $customerFee = 0.0;
        $totalAmount = $subtotal;

        return [
            'cart' => $cart,
            'template' => $template,
            'subscription_plan' => $subscriptionPlan,
            'addons' => $addons,
            'customer_info' => $cart->configuration['customer_info'] ?? null,
            'domain_data' => $cart->domain_data,
            'subscription_amount' => $subscriptionAmount,
            'addons_amount' => $addonsAmount,
            'domain_amount' => $domainAmount,
            'template_amount' => $templateAmount,
            'subtotal' => $subtotal,
            'customer_fee' => $customerFee,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Check if cart has all required data for checkout
     */
    public function isCartComplete(Cart $cart): bool
    {
        return $cart->template_id && 
               $cart->subscription_plan_id && 
               $cart->billing_cycle &&
               isset($cart->configuration['customer_info']) &&
               $cart->domain_data;
    }

    /**
     * Clear session and cookie data after migration
     */
    public function clearSessionAndCookieData(Request $request): void
    {
        // Clear session data
        $request->session()->forget([
            'checkout.template_id',
            'checkout.subscription_plan_id',
            'checkout.billing_cycle',
            'checkout.customer_info',
            'checkout.domain',
            'checkout.selected_addons'
        ]);

        // Clear cookie data (except payment-related data)
        CheckoutCookieHelper::clearCheckoutData();
    }
}