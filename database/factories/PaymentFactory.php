<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'completed', 'failed', 'expired', 'cancelled'];
        $paymentMethods = ['BRIVA', 'BCAVA', 'BNIVA', 'MANDIRIVA', 'PERMATAVA', 'ALFAMART', 'INDOMARET'];
        
        $amount = $this->faker->numberBetween(100000, 10000000); // 100k to 10M IDR
        
        return [
            'invoice_id' => Invoice::factory(),
            'user_id' => User::factory(),
            'reference' => 'T' . $this->faker->unique()->numerify('#########'),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'amount' => $amount,
            'status' => $this->faker->randomElement($statuses),
            'expired_at' => $this->faker->dateTimeBetween('now', '+24 hours'),
            'paid_at' => null,
            'callback_data' => null,
        ];
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'callback_data' => [
                'reference' => $attributes['reference'] ?? 'T' . $this->faker->numerify('#########'),
                'status' => 'PAID',
                'paid_amount' => $attributes['amount'] ?? $this->faker->numberBetween(100000, 10000000),
                'paid_at' => now()->timestamp
            ],
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'paid_at' => null,
            'callback_data' => [
                'reference' => $attributes['reference'] ?? 'T' . $this->faker->numerify('#########'),
                'status' => 'FAILED',
                'note' => 'Payment failed due to insufficient funds'
            ],
        ]);
    }

    /**
     * Indicate that the payment is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expired_at' => $this->faker->dateTimeBetween('-24 hours', '-1 hour'),
            'paid_at' => null,
            'callback_data' => [
                'reference' => $attributes['reference'] ?? 'T' . $this->faker->numerify('#########'),
                'status' => 'EXPIRED',
                'note' => 'Payment expired'
            ],
        ]);
    }

    /**
     * Indicate that the payment belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the payment is for a specific invoice.
     */
    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => $invoice->id,
            'user_id' => $invoice->user_id,
            'amount' => $invoice->total_amount,
        ]);
    }

    /**
     * Indicate that the payment uses a specific method.
     */
    public function withMethod(string $method): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => $method,
        ]);
    }

    /**
     * Indicate that the payment has a specific reference.
     */
    public function withReference(string $reference): static
    {
        return $this->state(fn (array $attributes) => [
            'reference' => $reference,
        ]);
    }

    /**
     * Indicate that the payment has a specific amount.
     */
    public function withAmount(int $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Indicate that the payment uses BRI Virtual Account.
     */
    public function briva(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'BRIVA',
        ]);
    }

    /**
     * Indicate that the payment uses BCA Virtual Account.
     */
    public function bcava(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'BCAVA',
        ]);
    }

    /**
     * Indicate that the payment uses Alfamart.
     */
    public function alfamart(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'ALFAMART',
        ]);
    }
}