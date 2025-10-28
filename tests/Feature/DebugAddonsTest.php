<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Template;
use App\Models\SubscriptionPlan;
use App\Models\ProductAddon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class DebugAddonsTest extends TestCase
{
    use RefreshDatabase;

    protected $template;
    protected $subscriptionPlan;
    protected $addon;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->template = Template::factory()->create([
            'name' => 'Test Template',
            'is_active' => true
        ]);

        $this->subscriptionPlan = SubscriptionPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 50000,
            'is_active' => true
        ]);

        $this->addon = ProductAddon::factory()->create([
            'name' => 'SSL Certificate',
            'price' => 25000,
            'is_active' => true
        ]);
    }

    /** @test */
    public function debug_addons_display_on_billing_page()
    {
        // Set up session data
        Session::put('checkout.template_id', $this->template->id);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.selected_addons', [$this->addon->id]);
        Session::put('checkout.customer_info', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890'
        ]);
        Session::put('checkout.domain', [
            'type' => 'existing',
            'name' => 'existing-domain.com'
        ]);
        Session::put('checkout.tripay_transaction', [
            'reference' => 'T123456789',
            'amount' => 75000,
            'pay_code' => '88810123456789',
            'status' => 'UNPAID',
            'payment_method' => 'BRIVA',
            'checkout_url' => 'https://tripay.co.id/checkout/T123456789'
        ]);

        $response = $this->get('/checkout/billing');

        // Debug: Print the response content
        echo "\n=== RESPONSE CONTENT ===\n";
        echo $response->getContent();
        echo "\n=== END RESPONSE ===\n";

        $response->assertStatus(200);
        $response->assertSee('SSL Certificate');
        $response->assertSee('Rp 25.000');
    }
}