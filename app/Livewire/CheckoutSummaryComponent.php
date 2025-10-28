<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Template;
use App\Models\SubscriptionPlan;
use App\Models\ProductAddon;

class CheckoutSummaryComponent extends Component
{
    public $template;
    public $subscriptionPlan;
    public $addons;
    public $customerInfo;
    public $domainInfo;
    public $subscriptionAmount;
    public $addonsAmount;
    public $totalAmount;
    public $paymentChannels;
    public $selectedPaymentChannel;

    public function mount($template, $subscriptionPlan, $addons, $customerInfo, $domainInfo)
    {
        $this->template = $template;
        $this->subscriptionPlan = $subscriptionPlan;
        $this->addons = $addons;
        $this->customerInfo = $customerInfo;
        $this->domainInfo = $domainInfo;
        
        $this->calculateAmounts();
        $this->loadPaymentChannels();
    }

    public function calculateAmounts()
    {
        $this->subscriptionAmount = $this->subscriptionPlan->price;
        $this->addonsAmount = $this->addons->sum('price');
        $domainPrice = $this->domainInfo['price'] ?? 0;
        $this->totalAmount = $this->subscriptionAmount + $this->addonsAmount + $domainPrice;
    }

    public function loadPaymentChannels()
    {
        try {
            $tripayService = app(\App\Services\TripayService::class);
            $response = $tripayService->getPaymentChannels();
            
            if ($response && $response['success']) {
                $this->paymentChannels = $response['data'];
            } else {
                // Fallback to working image URLs if API fails
                $this->paymentChannels = [
                    [
                        'code' => 'BRIVA',
                        'name' => 'BRI Virtual Account',
                        'type' => 'Virtual Account',
                        'fee_merchant' => ['flat' => 4000, 'percent' => 0],
                        'fee_customer' => ['flat' => 0, 'percent' => 0],
                        'total_fee' => ['flat' => 4000, 'percent' => 0],
                        'icon_url' => 'https://payment.tripay.co.id/images/payment_method/bri.png'
                    ],
                    [
                        'code' => 'BCAVA',
                        'name' => 'BCA Virtual Account',
                        'type' => 'Virtual Account',
                        'fee_merchant' => ['flat' => 4000, 'percent' => 0],
                        'fee_customer' => ['flat' => 0, 'percent' => 0],
                        'total_fee' => ['flat' => 4000, 'percent' => 0],
                        'icon_url' => 'https://payment.tripay.co.id/images/payment_method/bca.png'
                    ],
                    [
                        'code' => 'MANDIRIVA',
                        'name' => 'Mandiri Virtual Account',
                        'type' => 'Virtual Account',
                        'fee_merchant' => ['flat' => 4000, 'percent' => 0],
                        'fee_customer' => ['flat' => 0, 'percent' => 0],
                        'total_fee' => ['flat' => 4000, 'percent' => 0],
                        'icon_url' => 'https://payment.tripay.co.id/images/payment_method/mandiri.png'
                    ],
                    [
                        'code' => 'QRIS',
                        'name' => 'QRIS',
                        'type' => 'E-Wallet',
                        'fee_merchant' => ['flat' => 0, 'percent' => 0.7],
                        'fee_customer' => ['flat' => 0, 'percent' => 0],
                        'total_fee' => ['flat' => 0, 'percent' => 0.7],
                        'icon_url' => 'https://payment.tripay.co.id/images/payment_method/qris.png'
                    ],
                ];
            }
        } catch (\Exception $e) {
            // Fallback to working image URLs if there's an exception
            $this->paymentChannels = [
                [
                    'code' => 'BRIVA',
                    'name' => 'BRI Virtual Account',
                    'type' => 'Virtual Account',
                    'fee_merchant' => 4000,
                    'fee_customer' => 0,
                    'total_fee' => 4000,
                    'icon_url' => 'https://payment.tripay.co.id/images/payment_method/bri.png'
                ],
                [
                    'code' => 'BCAVA',
                    'name' => 'BCA Virtual Account',
                    'type' => 'Virtual Account',
                    'fee_merchant' => 4000,
                    'fee_customer' => 0,
                    'total_fee' => 4000,
                    'icon_url' => 'https://payment.tripay.co.id/images/payment_method/bca.png'
                ],
                [
                    'code' => 'MANDIRIVA',
                    'name' => 'Mandiri Virtual Account',
                    'type' => 'Virtual Account',
                    'fee_merchant' => 4000,
                    'fee_customer' => 0,
                    'total_fee' => 4000,
                    'icon_url' => 'https://payment.tripay.co.id/images/payment_method/mandiri.png'
                ],
                [
                    'code' => 'QRIS',
                    'name' => 'QRIS',
                    'type' => 'E-Wallet',
                    'fee_merchant' => 0.7,
                    'fee_customer' => 0,
                    'total_fee' => 0.7,
                    'icon_url' => 'https://payment.tripay.co.id/images/payment_method/qris.png'
                ],
            ];
        }
    }

    public function selectPaymentChannel($channelCode)
    {
        \Log::info('Payment channel selected: ' . $channelCode);
        $this->selectedPaymentChannel = $channelCode;
        
        // Dispatch multiple events to ensure compatibility
        $this->dispatch('payment-channel-selected', $channelCode);
        $this->dispatch('paymentChannelSelected', $channelCode);
        
        // Also dispatch browser event
        $this->js("
            window.dispatchEvent(new CustomEvent('payment-channel-selected', {
                detail: ['$channelCode']
            }));
            
            // Also trigger change event on hidden input if it exists
            const hiddenInput = document.getElementById('payment_channel');
            if (hiddenInput) {
                hiddenInput.value = '$channelCode';
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            
            // Enable submit button directly
            const submitButton = document.getElementById('submit-button');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
                submitButton.classList.add('bg-blue-600', 'hover:bg-blue-700', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-blue-500');
            }
        ");
    }

    public function getDomainDisplayProperty()
    {
        // Normalize domainInfo structure for backward compatibility
        $type = $this->domainInfo['type'] ?? $this->domainInfo['domain_type'] ?? 'unknown';
        
        switch ($type) {
            case 'new':
                return $this->domainInfo['name'] ?? $this->domainInfo['domain_name'] ?? 'Domain baru';
            case 'existing':
                return $this->domainInfo['existing'] ?? $this->domainInfo['domain_name'] ?? 'Domain existing';
            case 'subdomain':
                return ($this->domainInfo['subdomain'] ?? 'subdomain') . '.gaweagency.com';
            default:
                return 'Domain tidak diketahui';
        }
    }

    public function render()
    {
        return view('livewire.checkout-summary-component');
    }
}