<?php

namespace Database\Factories;

use App\Models\TicketReply;
use App\Models\User;
use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketReply>
 */
class TicketReplyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TicketReply::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publicReplies = [
            'Thank you for contacting us. We will look into this issue and get back to you shortly.',
            'I understand your concern. Let me check this for you and provide a solution.',
            'This issue has been resolved. Please check and let us know if you need further assistance.',
            'We have updated your account settings. The changes should be visible now.',
            'I have forwarded your request to our technical team. They will contact you within 24 hours.',
            'The problem you reported has been fixed. Please clear your browser cache and try again.',
            'Your payment has been processed successfully. You should receive a confirmation email shortly.',
            'I have reset your password. Please check your email for the new login credentials.'
        ];
        
        $internalNotes = [
            'Customer seems frustrated. Need to prioritize this issue.',
            'This is a recurring problem. Need to investigate the root cause.',
            'Escalating to senior developer for complex technical issue.',
            'Customer is on premium plan. Ensure quick resolution.',
            'Similar issue reported by 3 other customers this week.',
            'Requires database access to fix. Scheduling maintenance window.',
            'Customer has been very patient. Provide extra support if needed.',
            'Issue is related to recent server update. Rolling back changes.'
        ];
        
        $isInternal = $this->faker->boolean(20); // 20% chance of being internal
        
        return [
            'ticket_id' => SupportTicket::factory(),
            'user_id' => User::factory(),
            'message' => $isInternal 
                ? $this->faker->randomElement($internalNotes)
                : $this->faker->randomElement($publicReplies),
            'is_internal' => $isInternal,
        ];
    }

    /**
     * Indicate that the reply is public (visible to client).
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => false,
            'message' => $this->faker->randomElement([
                'Thank you for contacting us. We will look into this issue.',
                'I understand your concern. Let me check this for you.',
                'This issue has been resolved. Please check and let us know if you need further assistance.',
                'We have updated your account settings. The changes should be visible now.',
                'Your request has been processed successfully.'
            ]),
        ]);
    }

    /**
     * Indicate that the reply is internal (staff only).
     */
    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => true,
            'message' => $this->faker->randomElement([
                'Customer seems frustrated. Need to prioritize this issue.',
                'This is a recurring problem. Need to investigate the root cause.',
                'Escalating to senior developer for complex technical issue.',
                'Customer is on premium plan. Ensure quick resolution.',
                'Requires database access to fix. Scheduling maintenance window.'
            ]),
        ]);
    }

    /**
     * Indicate that the reply belongs to a specific ticket.
     */
    public function forTicket(SupportTicket $ticket): static
    {
        return $this->state(fn (array $attributes) => [
            'ticket_id' => $ticket->id,
        ]);
    }

    /**
     * Indicate that the reply is from a specific user.
     */
    public function fromUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the reply has a specific message.
     */
    public function withMessage(string $message): static
    {
        return $this->state(fn (array $attributes) => [
            'message' => $message,
        ]);
    }

    /**
     * Indicate that the reply is from staff.
     */
    public function fromStaff(): static
    {
        return $this->state(fn (array $attributes) => [
            'message' => $this->faker->randomElement([
                'Thank you for your patience. I have reviewed your case and here is the solution.',
                'I have escalated this issue to our technical team for immediate attention.',
                'The issue has been identified and we are working on a fix. ETA is 2 hours.',
                'Your account has been updated with the requested changes.',
                'I have processed your refund. It should appear in your account within 3-5 business days.'
            ]),
        ]);
    }

    /**
     * Indicate that the reply is from client.
     */
    public function fromClient(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => false,
            'message' => $this->faker->randomElement([
                'Thank you for the quick response. The issue is now resolved.',
                'I tried the suggested solution but the problem persists. Can you help further?',
                'The fix worked perfectly. I appreciate your excellent support.',
                'I need more clarification on the steps you provided.',
                'This is urgent. Please prioritize my request as it affects my business.'
            ]),
        ]);
    }
}