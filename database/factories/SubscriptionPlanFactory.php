<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        // Konsisten dengan enum subscription_plans: monthly, quarterly, semi_annual, annual
        $billingCycle = $this->faker->randomElement(['monthly', 'quarterly', 'semi_annual', 'annual']);
        $cycleMonths = match($billingCycle) {
            'monthly' => 1,
            'quarterly' => 3,
            'semi_annual' => 6,
            'annual' => 12,
        };

        return [
            'name' => $this->faker->words(2, true) . ' Plan',
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'billing_cycle' => $billingCycle,
            'cycle_months' => $cycleMonths,
            'discount_percentage' => $this->faker->randomFloat(2, 0, 20),
            'features' => json_encode([
                'Storage: ' . $this->faker->numberBetween(1, 100) . 'GB',
                'Bandwidth: ' . $this->faker->numberBetween(10, 1000) . 'GB',
                'Email Accounts: ' . $this->faker->numberBetween(1, 50),
                'Databases: ' . $this->faker->numberBetween(1, 10)
            ]),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_popular' => $this->faker->boolean(20), // 20% chance of being popular
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'monthly',
            'cycle_months' => 1,
        ]);
    }

    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'annual',
            'cycle_months' => 12,
        ]);
    }

    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'quarterly',
            'cycle_months' => 3,
        ]);
    }

    public function semiAnnual(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'semi_annual',
            'cycle_months' => 6,
        ]);
    }
}