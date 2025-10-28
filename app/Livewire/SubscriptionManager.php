<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Auth;

class SubscriptionManager extends Component
{
    use WithPagination;

    public $showUpgradeModal = false;
    public $selectedOrder;
    public $availablePlans;
    public $selectedPlanId;

    protected $listeners = ['refreshSubscriptions' => '$refresh'];

    public function mount()
    {
        $this->loadAvailablePlans();
    }

    public function loadAvailablePlans()
    {
        $this->availablePlans = SubscriptionPlan::active()
            ->orderBy('price', 'asc')
            ->get();
    }

    public function openUpgradeModal($orderId)
    {
        $this->selectedOrder = Order::with(['subscriptionPlan', 'template'])
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);
        
        $this->showUpgradeModal = true;
    }

    public function closeUpgradeModal()
    {
        $this->showUpgradeModal = false;
        $this->selectedOrder = null;
        $this->selectedPlanId = null;
    }

    public function upgradeSubscription()
    {
        $this->validate([
            'selectedPlanId' => 'required|exists:subscription_plans,id'
        ]);

        $newPlan = SubscriptionPlan::findOrFail($this->selectedPlanId);
        
        // Check if the new plan is actually an upgrade
        if ($newPlan->price <= $this->selectedOrder->subscriptionPlan->price) {
            $this->addError('selectedPlanId', 'Silakan pilih paket yang lebih tinggi dari paket saat ini.');
            return;
        }

        try {
            // Create upgrade order
            $upgradeOrder = Order::create([
                'user_id' => Auth::id(),
                'template_id' => $this->selectedOrder->template_id,
                'subscription_plan_id' => $this->selectedPlanId,
                'order_type' => 'upgrade',
                'subscription_amount' => $newPlan->price,
                'addons_amount' => 0,
                'amount' => $newPlan->price - $this->selectedOrder->subscriptionPlan->price, // Prorated amount
                'domain_name' => $this->selectedOrder->domain_name,
                'domain_type' => $this->selectedOrder->domain_type,
                'domain_details' => $this->selectedOrder->domain_details,
                'billing_cycle' => $newPlan->billing_cycle,
                'status' => 'pending',
                'customer_name' => $this->selectedOrder->customer_name,
                'customer_email' => $this->selectedOrder->customer_email,
                'customer_phone' => $this->selectedOrder->customer_phone,
                'parent_order_id' => $this->selectedOrder->id,
            ]);

            $this->closeUpgradeModal();
            $this->dispatch('subscription-upgrade-created', $upgradeOrder->id);
            session()->flash('success', 'Permintaan upgrade berhasil dibuat. Silakan lakukan pembayaran untuk mengaktifkan paket baru.');
            
        } catch (\Exception $e) {
            $this->addError('upgrade', 'Terjadi kesalahan saat membuat upgrade. Silakan coba lagi.');
        }
    }

    public function cancelSubscription($orderId)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'active')
            ->findOrFail($orderId);

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        session()->flash('success', 'Langganan berhasil dibatalkan.');
        $this->dispatch('refreshSubscriptions');
    }

    public function renewSubscription($orderId)
    {
        $order = Order::with('subscriptionPlan')
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);

        try {
            // Create renewal order
            $renewalOrder = Order::create([
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . rand(10000, 99999),
                'user_id' => Auth::id(),
                'product_id' => $order->product_id,
                'template_id' => $order->template_id,
                'subscription_plan_id' => $order->subscription_plan_id,
                'order_type' => 'subscription', // Use subscription for now until renewal type is added
                'subscription_amount' => $order->subscriptionPlan->price,
                'addons_amount' => 0,
                'amount' => $order->subscriptionPlan->price,
                'setup_fee' => 0,
                'domain_name' => $order->domain_name,
                'domain_type' => $order->domain_type,
                'domain_details' => $order->domain_details,
                'billing_cycle' => $order->subscriptionPlan->billing_cycle,
                'status' => 'pending',
                'order_details' => [
                    'product_name' => $order->subscriptionPlan->name,
                    'billing_cycle' => $order->subscriptionPlan->billing_cycle,
                    'renewal' => true
                ],
                // 'parent_order_id' => $order->id, // TODO: Add this field in migration
            ]);

            $this->dispatch('subscription-renewal-created', $renewalOrder->id);
            session()->flash('success', 'Permintaan perpanjangan berhasil dibuat. Silakan lakukan pembayaran untuk memperpanjang langganan.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat membuat perpanjangan. Silakan coba lagi.');
        }
    }

    public function getStatusBadgeClass($status)
    {
        return match($status) {
            'active' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'expired' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusText($status)
    {
        return match($status) {
            'active' => 'Aktif',
            'pending' => 'Menunggu Pembayaran',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($status)
        };
    }

    public function render()
    {
        $subscriptions = Order::with(['subscriptionPlan', 'template'])
            ->where('user_id', Auth::id())
            ->where('order_type', 'subscription')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.subscription-manager', compact('subscriptions'));
    }
}