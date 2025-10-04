<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SupportTicket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $categories = ['technical', 'billing', 'general', 'feature_request'];
        $statuses = ['open', 'in_progress', 'waiting_client', 'resolved', 'closed'];
        
        $subjects = [
            'Website login issue',
            'Payment not processed',
            'Domain configuration problem',
            'Email setup assistance',
            'Website performance issue',
            'Content update request',
            'SSL certificate problem',
            'Database connection error',
            'Mobile responsiveness issue',
            'SEO optimization question'
        ];
        
        return [
            'ticket_number' => 'TKT-' . $this->faker->unique()->numerify('######'),
            'user_id' => User::factory(),
            'subject' => $this->faker->randomElement($subjects),
            'description' => $this->faker->paragraph(3),
            'priority' => $this->faker->randomElement($priorities),
            'category' => $this->faker->randomElement($categories),
            'status' => $this->faker->randomElement($statuses),
            'assigned_to' => null, // Will be assigned later
            'last_reply_at' => null,
            'last_reply_by' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }

    /**
     * Indicate that the ticket is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * Indicate that the ticket is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * Indicate that the ticket is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'closed_at' => null,
        ]);
    }

    /**
     * Indicate that the ticket is closed.
     */
    public function closed(): static
    {
        $resolvedAt = $this->faker->dateTimeBetween('-14 days', '-1 day');
        $closedAt = $this->faker->dateTimeBetween($resolvedAt, 'now');
        
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'resolved_at' => $resolvedAt,
            'closed_at' => $closedAt,
        ]);
    }

    /**
     * Indicate that the ticket has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the ticket has medium priority.
     */
    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'medium',
        ]);
    }

    /**
     * Indicate that the ticket has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'low',
        ]);
    }

    /**
     * Indicate that the ticket is technical.
     */
    public function technical(): static
    {
        return $this->state(fn (array $attributes) => [
            'department' => 'technical',
            'subject' => $this->faker->randomElement([
                'Website login issue',
                'Domain configuration problem',
                'Database connection error',
                'SSL certificate problem',
                'Website performance issue'
            ]),
        ]);
    }

    /**
     * Indicate that the ticket is billing related.
     */
    public function billing(): static
    {
        return $this->state(fn (array $attributes) => [
            'department' => 'billing',
            'subject' => $this->faker->randomElement([
                'Payment not processed',
                'Invoice question',
                'Billing cycle change',
                'Refund request',
                'Payment method update'
            ]),
        ]);
    }

    /**
     * Indicate that the ticket belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the ticket is assigned to a staff member.
     */
    public function assignedTo(User $staff): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $staff->id,
        ]);
    }

    /**
     * Indicate that the ticket has a specific subject.
     */
    public function withSubject(string $subject): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => $subject,
        ]);
    }

    /**
     * Indicate that the ticket has internal notes.
     */
    public function withInternalNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'internal_notes' => $notes,
        ]);
    }
}