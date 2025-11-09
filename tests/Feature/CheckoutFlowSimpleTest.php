<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Template;
use App\Models\SubscriptionPlan;
use App\Models\ProductAddon;

class CheckoutFlowSimpleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->template = Template::factory()->create();
        $this->subscriptionPlan = SubscriptionPlan::factory()->create();
        $this->addon = ProductAddon::factory()->create();
    }

    /** @test */
    public function user_can_access_checkout_configuration_page()
    {
        // Debug template
        $this->assertNotNull($this->template);
        $this->assertNotNull($this->template->id);
        
        Session::put('checkout.template_id', $this->template->id);
        Session::put('selected_template_id', $this->template->id);
        
        $response = $this->get('/checkout/configure');
        
        $response->assertStatus(200);
        $response->assertViewIs('checkout.configure');
    }

    /** @test */
    public function user_can_access_personal_info_page_with_template_in_session()
    {
        Session::put('checkout.template_id', $this->template->id);
        Session::put('selected_template_id', $this->template->id);
        
        $response = $this->get('/checkout/personal-info');
        
        $response->assertStatus(200);
        $response->assertViewIs('checkout.personal-info');
    }

    /** @test */
    public function personal_info_page_redirects_without_template()
    {
        $response = $this->get('/checkout/personal-info');
        
        $response->assertRedirect('/checkout');
    }

    /** @test */
    public function user_can_submit_personal_info()
    {
        $personalInfo = [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'company' => 'Test Company',
            // Domain dikumpulkan di langkah domain terpisah
        ];

        $response = $this->withSession([
            'selected_template_id' => $this->template->id,
            'checkout.template_id' => $this->template->id,
            'checkout.subscription_plan_id' => $this->subscriptionPlan->id,
            'checkout.domain' => ['type' => 'new', 'name' => 'example.com']
        ])->post('/checkout/personal-info', $personalInfo);
        
        $response->assertRedirect('/checkout/summary');
        $this->assertNotNull(session('checkout.customer_info'));
    }

    /** @test */
    public function summary_page_requires_complete_data()
    {
        Session::put('checkout.template_id', $this->template->id);
        Session::put('selected_template_id', $this->template->id);
        // Tidak ada subscription_plan atau customer_info → summary harus redirect ke awal
        $response = $this->get('/checkout/summary');
        $response->assertRedirect('/checkout');
    }

    /** @test */
    public function user_can_select_billing_cycle()
    {
        Session::put('checkout.template_id', $this->template->id);
        Session::put('selected_template_id', $this->template->id);
        Session::put('checkout.customer_info', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'company' => 'Test Company'
        ]);
        Session::put('checkout.domain', [
            'type' => 'existing',
            'existing_domain' => 'example.com'
        ]);
        
        $response = $this->post('/checkout/configure', [
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'billing_cycle' => 'monthly'
        ]);
        
        $response->assertRedirect('/checkout/addon');
        $this->assertEquals($this->subscriptionPlan->id, Session::get('checkout.subscription_plan_id'));
        $this->assertEquals('monthly', Session::get('checkout.billing_cycle'));
    }

    /** @test */
    public function addons_page_displays_available_addons()
    {
        Session::put('checkout.template_id', $this->template->id);
        Session::put('selected_template_id', $this->template->id);
        Session::put('checkout.customer_info', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'company' => 'Test Company'
        ]);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.billing_cycle', 'monthly');
        Session::put('checkout.domain', ['type' => 'existing']);
        
        $response = $this->get('/checkout/configure');
        
        $response->assertStatus(200);
        $response->assertViewIs('checkout.configure');
        $response->assertViewHas('addons');
        $response->assertViewHas('subscriptionPlans');
    }

    /** @test */
    public function user_can_select_addons()
    {
        Session::put('checkout.template_id', $this->template->id);
        Session::put('selected_template_id', $this->template->id);
        Session::put('checkout.customer_info', [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'company' => 'Test Company'
        ]);
        Session::put('checkout.subscription_plan_id', $this->subscriptionPlan->id);
        Session::put('checkout.billing_cycle', 'monthly');
        Session::put('checkout.domain', ['type' => 'existing']);
        
        $response = $this->post('/checkout/configure', [
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'billing_cycle' => 'monthly',
            'selected_addons' => [$this->addon->id]
        ]);
        
        $response->assertRedirect('/checkout/summary');
        $this->assertEquals([$this->addon->id], Session::get('checkout.selected_addons'));
    }

    /** @test */
    public function summary_page_displays_order_summary()
    {
        Session::put('checkout', [
            'template_id' => $this->template->id,
            'customer_info' => [
                'full_name' => 'John Doe', 
                'email' => 'john@example.com',
                'phone' => '081234567890',
                'company' => 'Test Company'
            ],
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'billing_cycle' => 'monthly',
            'domain' => ['type' => 'existing', 'existing_domain' => 'example.com'],
            'addons' => [$this->addon->id]
        ]);
        
        $response = $this->get('/checkout/summary');
        
        $response->assertStatus(200);
        $response->assertViewIs('checkout.summary');
        $response->assertViewHas('template');
        $response->assertViewHas('subscriptionPlan');
        $response->assertViewHas('addons');
        $response->assertViewHas('customerInfo');
        $response->assertViewHas('domainInfo');
    }

    /** @test */
    public function checkout_validation_requires_all_fields()
    {
        $response = $this->post('/checkout/personal-info', []);
        
        $response->assertSessionHasErrors(['full_name', 'email', 'phone']);
    }

    /** @test */
    public function checkout_validation_requires_valid_email()
    {
        Session::put('checkout.template_id', $this->template->id);
        Session::put('selected_template_id', $this->template->id);
        
        $response = $this->post('/checkout/personal-info', [
            'full_name' => 'John Doe',
            'email' => 'invalid-email',
            'phone' => '1234567890'
        ]);
        
        $response->assertSessionHasErrors(['email']);
    }
}