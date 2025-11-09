<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\SubscriptionPlan;

class AdminSubscriptionPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function store_maps_cycle_months_from_billing_cycle()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin);

        $payload = [
            'name' => 'Quarter Plan',
            'price' => 100000,
            'billing_cycle' => 'quarterly',
            // sengaja kirim nilai yang salah, controller harus override
            'cycle_months' => 1,
            'description' => 'Test plan',
        ];

        $response = $this->post(route('admin.subscription-plans.store'), $payload);

        $response->assertRedirect(route('admin.subscription-plans.index'));

        $plan = SubscriptionPlan::where('name', 'Quarter Plan')->first();
        $this->assertNotNull($plan, 'Plan tidak berhasil dibuat');
        $this->assertSame('quarterly', $plan->billing_cycle);
        $this->assertSame(3, (int) $plan->cycle_months, 'cycle_months harus 3 untuk quarterly');
    }

    /** @test */
    public function update_maps_cycle_months_from_billing_cycle()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin);

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Monthly Plan',
            'price' => 50000,
            'billing_cycle' => 'monthly',
            'cycle_months' => 1,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Semi Annual Plan',
            'price' => 60000,
            'billing_cycle' => 'semi_annual',
            // sengaja salah, controller harus set ke 6
            'cycle_months' => 12,
            'description' => 'Updated plan',
            'is_active' => true,
        ];

        $response = $this->put(route('admin.subscription-plans.update', $plan), $payload);

        $response->assertRedirect(route('admin.subscription-plans.index'));

        $plan->refresh();
        $this->assertSame('semi_annual', $plan->billing_cycle);
        $this->assertSame(6, (int) $plan->cycle_months, 'cycle_months harus 6 untuk semi_annual');
    }
}