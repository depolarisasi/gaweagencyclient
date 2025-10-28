<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Models\User;
use App\Models\Order;
use App\Models\SubscriptionPlan;
use App\Models\Template;
use App\Livewire\SubscriptionManager;

class SubscriptionSimpleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->template = Template::factory()->create();
        $this->subscriptionPlan = SubscriptionPlan::factory()->create([
            'price' => 100.00
        ]);
        $this->higherPlan = SubscriptionPlan::factory()->create([
            'price' => 200.00
        ]);
    }

    /** @test */
    public function subscription_manager_component_renders()
    {
        $this->actingAs($this->user);
        
        Livewire::test(SubscriptionManager::class)
            ->assertStatus(200)
            ->assertSee('Langganan Saya');
    }

    /** @test */
    public function user_can_view_subscriptions()
    {
        $user = User::factory()->create();
        $template = Template::factory()->create(['name' => 'Test Template']);
        $subscriptionPlan = SubscriptionPlan::factory()->create(['name' => 'Test Plan']);
        
        $this->actingAs($user);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'subscription_plan_id' => $subscriptionPlan->id,
            'status' => 'active',
            'order_type' => 'subscription'
        ]);
        
        Livewire::test(SubscriptionManager::class)
            ->assertSee($template->name)
            ->assertSee($subscriptionPlan->name)
            ->assertSee('Aktif');
    }

    /** @test */
    public function user_can_open_upgrade_modal()
    {
        $user = User::factory()->create();
        $template = Template::factory()->create();
        $subscriptionPlan = SubscriptionPlan::factory()->create();
        
        $this->actingAs($user);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'subscription_plan_id' => $subscriptionPlan->id,
            'status' => 'active'
        ]);
        
        Livewire::test(SubscriptionManager::class)
            ->call('openUpgradeModal', $order->id)
            ->assertSet('showUpgradeModal', true)
            ->assertSet('selectedOrder.id', $order->id);
    }

    /** @test */
    public function user_can_close_upgrade_modal()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        Livewire::test(SubscriptionManager::class)
            ->set('showUpgradeModal', true)
            ->call('closeUpgradeModal')
            ->assertSet('showUpgradeModal', false);
    }

    /** @test */
    public function upgrade_requires_higher_priced_plan()
    {
        $user = User::factory()->create();
        $template = Template::factory()->create();
        $lowerPlan = SubscriptionPlan::factory()->create(['price' => 100]);
        $higherPlan = SubscriptionPlan::factory()->create(['price' => 200]);
        
        $this->actingAs($user);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'subscription_plan_id' => $higherPlan->id,
            'status' => 'active'
        ]);
        
        Livewire::test(SubscriptionManager::class)
            ->set('selectedOrder', $order)
            ->set('selectedPlanId', $lowerPlan->id) // Lower priced plan
            ->call('upgradeSubscription')
            ->assertHasErrors(['selectedPlanId']);
    }

    /** @test */
    public function user_can_cancel_subscription()
    {
        $user = User::factory()->create();
        $template = Template::factory()->create();
        $subscriptionPlan = SubscriptionPlan::factory()->create();
        
        $this->actingAs($user);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'subscription_plan_id' => $subscriptionPlan->id,
            'status' => 'active'
        ]);
        
        Livewire::test(SubscriptionManager::class)
            ->call('cancelSubscription', $order->id);
        
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /** @test */
    public function user_can_renew_expired_subscription()
    {
        $user = User::factory()->create();
        $template = Template::factory()->create();
        $subscriptionPlan = SubscriptionPlan::factory()->create();
        
        $this->actingAs($user);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'subscription_plan_id' => $subscriptionPlan->id,
            'status' => 'expired',
            'domain_name' => 'test.com',
            'domain_type' => 'register_new',
        ]);
        
        $initialOrderCount = Order::count();
        
        Livewire::test(SubscriptionManager::class)
            ->call('renewSubscription', $order->id);
        
        // Should create a new renewal order
        $this->assertEquals($initialOrderCount + 1, Order::count());
        
        // Check that a new order was created (renewal functionality)
        $renewalOrder = Order::where('user_id', $user->id)
            ->where('subscription_plan_id', $subscriptionPlan->id)
            ->where('status', 'pending')
            ->latest()
            ->first();
        $this->assertNotNull($renewalOrder);
        $this->assertEquals('pending', $renewalOrder->status);
        $this->assertEquals('subscription', $renewalOrder->order_type); // Will be 'renewal' when migration is added
    }

    /** @test */
    public function status_badge_returns_correct_classes()
    {
        $this->actingAs($this->user);
        
        $component = Livewire::test(SubscriptionManager::class);
        
        // Test different status badge classes
        $this->assertEquals('bg-green-100 text-green-800', $component->instance()->getStatusBadgeClass('active'));
        $this->assertEquals('bg-yellow-100 text-yellow-800', $component->instance()->getStatusBadgeClass('pending'));
        $this->assertEquals('bg-gray-100 text-gray-800', $component->instance()->getStatusBadgeClass('cancelled'));
        $this->assertEquals('bg-red-100 text-red-800', $component->instance()->getStatusBadgeClass('expired'));
    }

    /** @test */
    public function status_text_returns_correct_labels()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(SubscriptionManager::class);

        // Test different status text labels (Indonesian)
        $this->assertEquals('Aktif', $component->instance()->getStatusText('active'));
        $this->assertEquals('Menunggu Pembayaran', $component->instance()->getStatusText('pending'));
        $this->assertEquals('Dibatalkan', $component->instance()->getStatusText('cancelled'));
        $this->assertEquals('Kedaluwarsa', $component->instance()->getStatusText('expired'));
    }

    /** @test */
    public function user_cannot_manage_other_users_subscriptions()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $template = Template::factory()->create();
        $subscriptionPlan = SubscriptionPlan::factory()->create();
        
        $this->actingAs($user);
        
        $otherOrder = Order::factory()->create([
            'user_id' => $otherUser->id,
            'template_id' => $template->id,
            'subscription_plan_id' => $subscriptionPlan->id,
            'status' => 'active'
        ]);
        
        // This should throw ModelNotFoundException because user cannot access other user's orders
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        Livewire::test(SubscriptionManager::class)
            ->call('cancelSubscription', $otherOrder->id);
    }
}