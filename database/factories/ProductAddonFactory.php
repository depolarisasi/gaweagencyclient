<?php

namespace Database\Factories;

use App\Models\ProductAddon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductAddonFactory extends Factory
{
    protected $model = ProductAddon::class;

    public function definition(): array
    {
        $billingType = $this->faker->randomElement(['one_time', 'recurring']);
        $billingCycle = $billingType === 'recurring' 
            ? $this->faker->randomElement(['monthly', 'quarterly', 'semi_annually', 'annually'])
            : null;

        return [
            'name' => $this->faker->words(2, true) . ' Addon',
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'billing_type' => $billingType,
            'billing_cycle' => $billingCycle,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 10),
            'category' => $this->faker->randomElement(['hosting', 'domain', 'ssl', 'maintenance', 'marketing', 'general']),
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
        ]);
    }

    public function annually(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'annually',
        ]);
    }
}