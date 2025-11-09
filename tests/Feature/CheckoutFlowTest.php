<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Template;
use App\Models\SubscriptionPlan;
use App\Models\ProductAddon;
use App\Models\Order;
use App\Livewire\DomainSelector;
use App\Livewire\CheckoutSummaryComponent;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $template;
    protected $subscriptionPlan;
    protected $addon;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createTestData()
    {
        $this->user = User::factory()->create();
        $this->template = Template::factory()->create(['is_active' => true]);
        $this->subscriptionPlan = SubscriptionPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 100000,
            'billing_cycle' => 'monthly'
        ]);
        $this->addon = ProductAddon::factory()->create([
            'name' => 'SSL Certificate',
            'price' => 50000
        ]);
    }

    /** @test */
    public function user_can_access_checkout_configure_page()
    {
        $this->createTestData();
        
        $response = $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id,
        ])->get(route('checkout.configure'));

        $response->assertStatus(200);
        $response->assertViewIs('checkout.configure-page');
    }

    /** @test */
    public function user_can_select_template_and_proceed_to_configure()
    {
        $this->createTestData();
        
        $response = $this->post(route('checkout.step1'), [
            'template_id' => $this->template->id
        ]);

        $response->assertRedirect(route('checkout.configure'));
        $response->assertSessionHas('checkout.template_id', $this->template->id);
    }

    /** @test */
    public function user_cannot_proceed_without_selecting_template()
    {
        $response = $this->post(route('checkout.step1'), []);

        $response->assertSessionHasErrors(['template_id']);
    }

    /** @test */
    public function user_can_access_personal_info_page_with_session_data()
    {
        $this->createTestData();
        
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id
        ]);

        $response = $this->get(route('checkout.personal-info'));
        
        $response->assertStatus(200);
        $response->assertViewIs('checkout.personal-info');
    }

    /** @test */
    public function domain_selector_component_can_check_domain_availability()
    {
        Livewire::test(DomainSelector::class)
            ->set('domainType', 'new')
            ->set('domainName', 'example')
            ->call('checkDomainAvailability')
            ->assertSet('isChecking', false);
    }

    /** @test */
    public function domain_selector_validates_domain_format()
    {
        Livewire::test(DomainSelector::class)
            ->set('domainType', 'new')
            ->set('domainName', 'invalid-domain')
            ->call('checkDomainAvailability');
    }

    /** @test */
    public function user_can_submit_personal_info_and_proceed_to_billing_cycle()
    {
        $this->createTestData();
        
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id,
            'checkout.domain' => [
                'type' => 'new',
                'name' => 'example.com',
                'price' => 150000
            ]
        ]);

        $response = $this->post(route('checkout.personal-info'), [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'company' => 'Test Company',
            'domain_name' => 'example.com',
            'domain_type' => 'new'
        ]);

        $response->assertRedirect(route('checkout.summary'));
        $response->assertSessionHas('checkout.customer_info.full_name', 'John Doe');
        $response->assertSessionHas('checkout.customer_info.email', 'john@example.com');
    }

    /** @test */
    public function user_cannot_proceed_without_domain_selection()
    {
        $this->createTestData();
        
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id
        ]);

        $response = $this->post(route('checkout.personal-info'), [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'domain_name' => 'myexistingdomain.com',
            'domain_type' => 'existing'
        ]);

        $response->assertRedirect()
                ->assertSessionHasErrors();
    }

    /** @test */
    public function user_can_access_addons_page()
    {
        $this->createTestData();
        
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id,
            'checkout.subscription_plan_id' => $this->subscriptionPlan->id,
            'checkout.customer_info' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '08123456789'
            ]
        ]);

        $response = $this->get(route('checkout.configure'));
        
        $response->assertStatus(200);
        $response->assertViewIs('checkout.configure');
    }

    /** @test */
    public function user_can_select_addons_and_proceed_to_domain()
    {
        $this->createTestData();
        
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id,
            'checkout.subscription_plan_id' => $this->subscriptionPlan->id,
            'checkout.customer_info' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '08123456789'
            ]
        ]);

        $response = $this->post(route('checkout.configure.post'), [
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'billing_cycle' => 'monthly',
            'selected_addons' => [$this->addon->id]
        ]);

        $response->assertRedirect(route('checkout.summary'));
        $response->assertSessionHas('checkout.selected_addons', [$this->addon->id]);
    }

    /** @test */
    public function checkout_summary_component_calculates_amounts_correctly()
    {
        $this->createTestData();
        
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id,
            'checkout.subscription_plan_id' => $this->subscriptionPlan->id,
            'checkout.customer_info' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ],
            'checkout.domain' => [
                'type' => 'new',
                'name' => 'example.com',
                'price' => 150000
            ],
            'checkout.selected_addons' => [$this->addon->id]
        ]);

        $customerInfo = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'company' => null
        ];
        
        $domainInfo = [
            'type' => 'new',
            'name' => 'example.com',
            'price' => 150000
        ];
        
        $addons = collect([$this->addon]);

        Livewire::test(CheckoutSummaryComponent::class, [
            'template' => $this->template,
            'subscriptionPlan' => $this->subscriptionPlan,
            'addons' => $addons,
            'customerInfo' => $customerInfo,
            'domainInfo' => $domainInfo
        ])
            ->assertSet('subscriptionAmount', $this->subscriptionPlan->price)
            ->assertSet('addonsAmount', $this->addon->price)
            ->assertSet('totalAmount', $this->subscriptionPlan->price + $this->addon->price + 150000);
    }

    /** @test */
    public function user_can_complete_checkout_and_create_order()
    {
        $this->createTestData();
        
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'checkout.subscription_plan_id' => $this->subscriptionPlan->id,
            'checkout.billing_cycle' => 'monthly',
            'checkout.customer_info' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '08123456789'
            ],
            'checkout.domain' => [
                'type' => 'new',
                'name' => 'example.com',
                'price' => 150000
            ],
            'checkout.selected_addons' => [$this->addon->id]
        ]);

        $response = $this->post(route('checkout.submit'), [
            'payment_channel' => 'BRIVA'
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->subscriptionPlan->id,
            'domain_name' => 'example.com',
            'domain_type' => 'register_new',
            'status' => 'pending'
        ]);

        $order = Order::where('template_id', $this->template->id)
                     ->where('subscription_plan_id', $this->subscriptionPlan->id)
                     ->first();
        $this->assertNotNull($order);
        $this->assertEquals($this->subscriptionPlan->price + $this->addon->price + 150000, $order->amount);
    }

    /** @test */
    public function checkout_redirects_to_index_if_no_session_data()
    {
        $response = $this->get(route('checkout.personal-info'));
        
        $response->assertRedirect(route('checkout.index'));
    }

    /** @test */
    public function checkout_validates_required_fields_at_each_step()
    {
        $this->createTestData();
        
        // Test personal info validation
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id
        ]);

        $response = $this->post(route('checkout.personal-info'), []);
        
        $response->assertSessionHasErrors(['full_name', 'email', 'phone']);
    }

    /** @test */
    public function checkout_handles_different_domain_types()
    {
        $this->createTestData();
        
        // Existing domain type
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id,
            'checkout.subscription_plan_id' => $this->subscriptionPlan->id,
            'checkout.domain' => [
                'type' => 'existing',
                'name' => 'myexistingdomain.com'
            ]
        ]);

        $response = $this->post(route('checkout.domain'));
        $response->assertRedirect(route('checkout.personal-info'));

        // Subdomain type
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id,
            'checkout.subscription_plan_id' => $this->subscriptionPlan->id,
            'checkout.domain' => [
                'type' => 'subdomain',
                'name' => 'mysite'
            ]
        ]);

        $response = $this->post(route('checkout.domain'));
        $response->assertRedirect(route('checkout.personal-info'));
    }

    /** @test */
    public function checkout_creates_user_if_not_exists()
    {
        // Create only template and subscription plan, not user
        $this->template = Template::factory()->create(['is_active' => true]);
        $this->subscriptionPlan = SubscriptionPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 100000,
            'billing_cycle' => 'monthly'
        ]);
        
        $this->withSession([
            'checkout.template_id' => $this->template->id,
            'selected_template_id' => $this->template->id,
            'checkout.subscription_plan_id' => $this->subscriptionPlan->id,
            'checkout.billing_cycle' => 'monthly',
            'checkout.customer_info' => [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'phone' => '08123456789'
            ],
            'checkout.domain' => [
                'type' => 'new',
                'name' => 'newsite.com',
                'price' => 150000
            ],
            'checkout.selected_addons' => []
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@example.com'
        ]);

        $response = $this->post(route('checkout.submit'), [
            'payment_channel' => 'BRIVA'
        ]);

        $response->assertRedirect(route('checkout.billing'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User'
        ]);
    }
}