<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['website', 'hosting', 'domain', 'maintenance'];
        $billingCycles = ['monthly', 'quarterly', 'semi_annually', 'annually'];
        
        return [
            'name' => $this->faker->words(3, true) . ' Package',
            'description' => $this->faker->paragraph(3),
            'type' => $this->faker->randomElement($types),
            'price' => $this->faker->numberBetween(500000, 10000000), // 500k to 10M IDR
            'billing_cycle' => $this->faker->randomElement($billingCycles),
            'features' => [
                'Responsive Design',
                'SEO Optimized',
                'Contact Form',
                'Gallery Section',
                'Social Media Integration'
            ],
            'setup_time_days' => $this->faker->numberBetween(3, 30),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is a website type.
     */
    public function website(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'website',
            'name' => 'Website Development Package',
            'features' => [
                'Custom Design',
                'Responsive Layout',
                'SEO Optimization',
                'Contact Form',
                'Content Management',
                'Social Media Integration'
            ],
        ]);
    }

    /**
     * Indicate that the product is a hosting type.
     */
    public function hosting(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'hosting',
            'name' => 'Web Hosting Package',
            'features' => [
                'SSD Storage',
                '99.9% Uptime',
                'Free SSL Certificate',
                'Daily Backups',
                '24/7 Support',
                'Email Accounts'
            ],
        ]);
    }

    /**
     * Indicate that the product has a specific price.
     */
    public function withPrice(int $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
        ]);
    }

    /**
     * Indicate that the product has specific features.
     */
    public function withFeatures(array $features): static
    {
        return $this->state(fn (array $attributes) => [
            'features' => $features,
        ]);
    }
}