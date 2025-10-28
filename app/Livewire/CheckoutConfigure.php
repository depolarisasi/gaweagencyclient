<?php

namespace App\Livewire;

use App\Models\SubscriptionPlan;
use App\Models\Template;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class CheckoutConfigure extends Component
{
    public $template;
    public $subscriptionPlans;
    public $selectedTemplateId;
    public $selectedSubscriptionPlanId;
    public $selectedBillingCycle;

    public function mount()
    {
        $this->selectedTemplateId = Session::get('checkout.template_id');

        if (!$this->selectedTemplateId) {
            return redirect('/'); // Kembali ke halaman utama jika tidak ada template yang dipilih
        }

        $this->template = Template::findOrFail($this->selectedTemplateId);
        $this->subscriptionPlans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function updatedSelectedSubscriptionPlanId()
    {
        if ($this->selectedSubscriptionPlanId) {
            $plan = SubscriptionPlan::find($this->selectedSubscriptionPlanId);
            if ($plan) {
                $this->selectedBillingCycle = $plan->billing_cycle;
            }
        }
    }

    public function configureProduct()
    {
        if (!$this->selectedSubscriptionPlanId) {
            $this->addError('selectedSubscriptionPlanId', 'Silakan pilih paket berlangganan terlebih dahulu.');
            return;
        }

        // Simpan konfigurasi ke session
        Session::put('checkout.subscription_plan_id', $this->selectedSubscriptionPlanId);
        Session::put('checkout.billing_cycle', $this->selectedBillingCycle);
        Session::put('checkout.template_id', $this->selectedTemplateId);

        return redirect()->route('checkout.addons.show'); // Arahkan ke halaman add-ons
    }

    public function render()
    {
        return view('livewire.checkout-configure');
    }
}
