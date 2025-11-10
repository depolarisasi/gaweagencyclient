<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class CheckoutCookieHelper
{
    const COOKIE_PREFIX = 'checkout_';
    const COOKIE_DURATION = 60 * 24 * 7; // 7 days in minutes

    /**
     * Store checkout data in cookies
     */
    public static function store(string $key, $value, Response $response = null): void
    {
        $cookieName = self::COOKIE_PREFIX . $key;
        $cookieValue = is_array($value) || is_object($value) ? json_encode($value) : $value;
        
        if ($response) {
            $response->withCookie(cookie($cookieName, $cookieValue, self::COOKIE_DURATION));
        } else {
            Cookie::queue($cookieName, $cookieValue, self::COOKIE_DURATION);
        }
    }

    /**
     * Retrieve checkout data from cookies
     */
    public static function get(string $key, $default = null)
    {
        $cookieName = self::COOKIE_PREFIX . $key;
        $value = Cookie::get($cookieName, $default);
        
        if ($value && $value !== $default) {
            // Try to decode JSON, if it fails return as string
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }
        
        return $default;
    }

    /**
     * Remove specific checkout cookie
     */
    public static function forget(string $key): void
    {
        $cookieName = self::COOKIE_PREFIX . $key;
        Cookie::queue(Cookie::forget($cookieName));
    }

    /**
     * Clear all checkout cookies (except tripay transaction data)
     */
    public static function clearAll(): void
    {
        $keys = [
            'billing_cycle',
            'selected_addons'
            // Note: template_id, subscription_plan_id, customer_info, domain, payment_channel,
            // tripay_transaction and tripay_reference are preserved for billing page
        ];

        foreach ($keys as $key) {
            self::forget($key);
        }
    }

    /**
     * Force clear ALL checkout cookies including payment-related data.
     * Use this when user navigates back from billing and we want a fresh start.
     */
    public static function clearAllForce(): void
    {
        $keys = [
            'template_id',
            'subscription_plan_id',
            'billing_cycle',
            'selected_addons',
            'customer_info',
            'domain',
            'payment_channel',
            'tripay_transaction',
            'tripay_reference'
        ];

        foreach ($keys as $key) {
            self::forget($key);
        }
    }

    /**
     * Store template selection
     */
    public static function storeTemplate(int $templateId): void
    {
        self::store('template_id', $templateId);
    }

    /**
     * Get template ID
     */
    public static function getTemplateId(): ?int
    {
        return self::get('template_id');
    }

    /**
     * Store subscription plan selection
     */
    public static function storeSubscriptionPlan(int $planId, string $billingCycle): void
    {
        self::store('subscription_plan_id', $planId);
        self::store('billing_cycle', $billingCycle);
    }

    /**
     * Get subscription plan ID
     */
    public static function getSubscriptionPlanId(): ?int
    {
        return self::get('subscription_plan_id');
    }

    /**
     * Get billing cycle
     */
    public static function getBillingCycle(): ?string
    {
        return self::get('billing_cycle');
    }

    /**
     * Store selected addons
     */
    public static function storeAddons(array $addonIds): void
    {
        self::store('selected_addons', $addonIds);
    }

    /**
     * Get selected addons
     */
    public static function getAddons(): array
    {
        return self::get('selected_addons', []);
    }

    /**
     * Store customer information
     */
    public static function storeCustomerInfo(array $customerInfo): void
    {
        self::store('customer_info', $customerInfo);
    }

    /**
     * Get customer information
     */
    public static function getCustomerInfo(): ?array
    {
        return self::get('customer_info');
    }

    /**
     * Store domain information
     */
    public static function storeDomain(array $domainInfo): void
    {
        self::store('domain', $domainInfo);
    }

    /**
     * Get domain information
     */
    public static function getDomain(): ?array
    {
        return self::get('domain');
    }

    /**
     * Store payment channel
     */
    public static function storePaymentChannel(string $paymentChannel): void
    {
        self::store('payment_channel', $paymentChannel);
    }

    /**
     * Get payment channel
     */
    public static function getPaymentChannel(): ?string
    {
        return self::get('payment_channel');
    }

    /**
     * Store Tripay transaction
     */
    public static function storeTripayTransaction(array $transaction): void
    {
        self::store('tripay_transaction', $transaction);
        // Handle both possible reference locations
        $reference = $transaction['reference'] ?? $transaction['data']['reference'] ?? null;
        if ($reference) {
            self::store('tripay_reference', $reference);
        }
    }

    /**
     * Get Tripay transaction
     */
    public static function getTripayTransaction(): ?array
    {
        return self::get('tripay_transaction');
    }

    /**
     * Get Tripay reference
     */
    public static function getTripayReference(): ?string
    {
        return self::get('tripay_reference');
    }

    /**
     * Check if all required checkout data exists
     */
    public static function hasCompleteData(): bool
    {
        return self::getTemplateId() && 
               self::getSubscriptionPlanId() && 
               self::getCustomerInfo() && 
               self::getDomain();
    }

    /**
     * Migrate session data to cookies (for backward compatibility)
     */
    public static function migrateFromSession(Request $request): void
    {
        $sessionKeys = [
            'template_id',
            'subscription_plan_id',
            'billing_cycle',
            'selected_addons',
            'customer_info',
            'domain',
            'payment_channel',
            'tripay_transaction',
            'tripay_reference'
        ];

        foreach ($sessionKeys as $key) {
            $sessionValue = $request->session()->get('checkout.' . $key);
            if ($sessionValue !== null) {
                self::store($key, $sessionValue);
            }
        }
    }

    /**
     * Clear checkout data cookies (except payment-related data)
     */
    public static function clearCheckoutData(): void
    {
        $checkoutKeys = [
            'template_id',
            'subscription_plan_id',
            'billing_cycle',
            'selected_addons',
            'customer_info',
            'domain'
        ];

        foreach ($checkoutKeys as $key) {
            self::forget($key);
        }
    }
}