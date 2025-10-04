<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        
        $amount = $this->faker->numberBetween(1000000, 10000000); // 1M to 10M IDR
        $taxAmount = $amount * 0.1; // 10% tax
        $totalAmount = $amount + $taxAmount;
        
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return [
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => $this->faker->randomElement($statuses),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'paid_date' => null,
            'payment_method' => null,
            'description' => $this->faker->optional()->paragraph,
        ];
    }

    /**
     * Indicate that the invoice is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'paid_date' => null,
            'payment_method' => null,
        ]);
    }

    /**
     * Indicate that the invoice is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'paid_date' => null,
            'payment_method' => null,
        ]);
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        $paidDate = $this->faker->dateTimeBetween('-30 days', 'now');
        
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_date' => $paidDate,
            'payment_method' => $this->faker->randomElement(['Bank Transfer', 'Credit Card', 'PayPal', 'Cash']),
        ]);
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'paid_date' => null,
            'payment_method' => null,
        ]);
    }

    /**
     * Indicate that the invoice is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'paid_date' => null,
            'payment_method' => null,
        ]);
    }

    /**
     * Indicate that the invoice belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the invoice is linked to a specific order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
        ]);
    }

    /**
     * Indicate that the invoice has a specific amount.
     */
    public function withAmount(int $amount): static
    {
        $taxAmount = $amount * 0.1;
        $totalAmount = $amount + $taxAmount;
        
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Indicate that the invoice has a specific due date.
     */
    public function dueIn(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->addDays($days),
        ]);
    }

    /**
     * Indicate that the invoice was paid with a specific method.
     */
    public function paidWith(string $method): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'payment_method' => $method,
        ]);
    }

    /**
     * Indicate that the invoice has a specific invoice number.
     */
    public function withInvoiceNumber(string $number): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_number' => $number,
        ]);
    }
}