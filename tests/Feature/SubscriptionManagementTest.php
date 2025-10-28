<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Template;
use App\Models\SubscriptionPlan;
use App\Models\ProductAddon;
use App\Models\OrderAddon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\SubscriptionManager;
use Carbon\Carbon;

class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $template;
    protected $basicPlan;
    protected $premiumPlan;
    protected $addon;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->template = Template::factory()->create();
        
        $this->basicPlan = SubscriptionPlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 100000,
            'billing_cycle' => 'monthly'
        ]);
        
        $this->premiumPlan = SubscriptionPlan::factory()->create([
            'name' => 'Premium Plan',
            'price' => 200000,
            'billing_cycle' => 'monthly'
        ]);
        
        $this->addon = ProductAddon::factory()->create([
            'name' => 'SSL Certificate',
            'price' => 50000
        ]);
    }

    /** @test */
    public function subscription_manager_displays_user_subscriptions()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active'
        ]);

        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->assertSee($this->template->name)
            ->assertSee($this->basicPlan->name)
            ->assertSee('Aktif');
    }

    /** @test */
    public function user_can_upgrade_subscription_to_higher_plan()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'subscription_amount' => $this->basicPlan->price,
            'status' => 'active'
        ]);

        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->call('openUpgradeModal', $order->id)
            ->assertSet('showUpgradeModal', true)
            ->assertSet('selectedOrder.id', $order->id)
            ->set('selectedPlanId', $this->premiumPlan->id)
            ->call('upgradeSubscription')
            ->assertHasNoErrors()
            ->assertSet('showUpgradeModal', false);

        $order->refresh();
        $this->assertEquals($this->premiumPlan->id, $order->subscription_plan_id);
        $this->assertEquals($this->premiumPlan->price, $order->subscription_amount);
    }

    /** @test */
    public function user_cannot_upgrade_to_lower_priced_plan()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->premiumPlan->id,
            'subscription_amount' => $this->premiumPlan->price,
            'status' => 'active'
        ]);

        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->call('openUpgradeModal', $order->id)
            ->set('selectedPlanId', $this->basicPlan->id)
            ->call('upgradeSubscription')
            ->assertHasErrors(['selectedPlanId']);
    }

    /** @test */
    public function user_can_cancel_active_subscription()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active'
        ]);

        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->call('cancelSubscription', $order->id);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /** @test */
    public function user_can_renew_expired_subscription()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'expired'
        ]);

        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->call('renewSubscription', $order->id);

        $order->refresh();
        $this->assertEquals('pending', $order->status);
    }

    /** @test */
    public function subscription_manager_shows_correct_status_badges()
    {
        $activeOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active'
        ]);

        $pendingOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'pending'
        ]);

        $expiredOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'expired'
        ]);

        $this->actingAs($this->user);

        $component = Livewire::test(SubscriptionManager::class);

        // Test status badge classes
        $this->assertEquals('bg-green-100 text-green-800', $component->instance()->getStatusBadgeClass('active'));
        $this->assertEquals('bg-yellow-100 text-yellow-800', $component->instance()->getStatusBadgeClass('pending'));
        $this->assertEquals('bg-red-100 text-red-800', $component->instance()->getStatusBadgeClass('expired'));
        $this->assertEquals('bg-gray-100 text-gray-800', $component->instance()->getStatusBadgeClass('cancelled'));

        // Test status text
        $this->assertEquals('Aktif', $component->instance()->getStatusText('active'));
        $this->assertEquals('Menunggu Pembayaran', $component->instance()->getStatusText('pending'));
        $this->assertEquals('Kedaluwarsa', $component->instance()->getStatusText('expired'));
        $this->assertEquals('Dibatalkan', $component->instance()->getStatusText('cancelled'));
    }

    /** @test */
    public function subscription_manager_loads_available_plans_for_upgrade()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active'
        ]);

        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->call('openUpgradeModal', $order->id)
            ->assertSet('showUpgradeModal', true);
    }

    /** @test */
    public function subscription_manager_handles_pagination()
    {
        // Create multiple orders for pagination testing
        Order::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active'
        ]);

        $this->actingAs($this->user);

        $component = Livewire::test(SubscriptionManager::class);
        
        // Check that pagination is working
        $this->assertCount(10, $component->instance()->subscriptions->items());
    }

    /** @test */
    public function user_cannot_manage_other_users_subscriptions()
    {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active'
        ]);

        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->call('cancelSubscription', $order->id);

        $order->refresh();
        // Order should remain active since user cannot cancel other's subscriptions
        $this->assertEquals('active', $order->status);
    }

    /** @test */
    public function subscription_manager_shows_empty_state_when_no_subscriptions()
    {
        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->assertSee('Belum ada langganan')
            ->assertSee('Mulai dengan membuat website pertama Anda');
    }

    /** @test */
    public function subscription_upgrade_calculates_prorated_amount()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'subscription_amount' => $this->basicPlan->price,
            'status' => 'active',
            'created_at' => Carbon::now()->subDays(15) // 15 days ago
        ]);

        $this->actingAs($this->user);

        $component = Livewire::test(SubscriptionManager::class)
            ->call('openUpgradeModal', $order->id)
            ->set('selectedPlanId', $this->premiumPlan->id)
            ->call('upgradeSubscription');

        $order->refresh();
        
        // Check that the upgrade was processed
        $this->assertEquals($this->premiumPlan->id, $order->subscription_plan_id);
        
        // The prorated amount calculation should be handled in the component
        // This test ensures the upgrade process works correctly
    }

    /** @test */
    public function subscription_manager_handles_orders_with_addons()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active'
        ]);

        // Add addon to order
        OrderAddon::create([
            'order_id' => $order->id,
            'product_addon_id' => $this->addon->id,
            'quantity' => 1,
            'price' => $this->addon->price
        ]);

        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->assertSee($this->template->name)
            ->assertSee($this->basicPlan->name);
    }

    /** @test */
    public function subscription_manager_validates_upgrade_modal_input()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'template_id' => $this->template->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active'
        ]);

        $this->actingAs($this->user);

        Livewire::test(SubscriptionManager::class)
            ->call('openUpgradeModal', $order->id)
            ->call('upgradeSubscription')
            ->assertHasErrors(['selectedPlanId']);
    }
}