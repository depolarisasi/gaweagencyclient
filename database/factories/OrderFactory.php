<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'active', 'suspended', 'cancelled', 'completed'];
        $billingCycles = ['monthly', 'quarterly', 'semi_annually', 'annually'];
        
        $amount = $this->faker->numberBetween(1000000, 10000000); // 1M to 10M IDR
        $setupFee = $this->faker->numberBetween(0, 500000); // 0 to 500K IDR
        
        return [
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . $this->faker->unique()->randomNumber(5),
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'amount' => $amount,
            'setup_fee' => $setupFee,
            'billing_cycle' => $this->faker->randomElement($billingCycles),
            'status' => $this->faker->randomElement($statuses),
            'next_due_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'order_details' => [
                'product_name' => $this->faker->words(3, true),
                'billing_cycle' => $this->faker->randomElement($billingCycles),
                'features' => $this->faker->words(5)
            ],
            'notes' => $this->faker->optional()->paragraph,
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the order has a specific total amount.
     */
    public function withAmount(int $amount): static
    {
        $taxAmount = $amount * 0.1;
        $subtotal = $amount - $taxAmount;
        
        return $this->state(fn (array $attributes) => [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $amount,
        ]);
    }

    /**
     * Indicate that the order belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the order is for a specific product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }


}