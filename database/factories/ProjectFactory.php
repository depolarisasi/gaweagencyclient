<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'in_progress', 'review', 'completed', 'on_hold', 'cancelled'];
        
        return [
            'project_name' => 'Website for ' . $this->faker->company,
            'user_id' => User::factory(),
            'order_id' => Order::factory(),
            'status' => $this->faker->randomElement($statuses),
            'assigned_to' => null, // Will be assigned later
            'description' => $this->faker->paragraph(2),
            'requirements' => json_encode([
                'features' => $this->faker->words(3),
                'pages' => $this->faker->numberBetween(5, 20)
            ]),
            'deliverables' => json_encode([
                'website' => true,
                'documentation' => true,
                'training' => false
            ]),
            'start_date' => $this->faker->optional(0.4)->dateTimeBetween('-30 days', 'now'),
            'due_date' => $this->faker->optional(0.6)->dateTimeBetween('now', '+60 days'),
            'completed_date' => null,
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'notes' => $this->faker->optional()->paragraph,
            'files' => json_encode([]),
        ];
    }

    /**
     * Indicate that the project is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the project is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'started_at' => $this->faker->dateTimeBetween('-15 days', 'now'),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        $startedAt = $this->faker->dateTimeBetween('-30 days', '-5 days');
        $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');
        
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'website_url' => $this->faker->url,
            'admin_url' => $this->faker->url . '/admin',
            'admin_username' => 'admin',
            'admin_password' => 'secure123',
        ]);
    }

    /**
     * Indicate that the project is on hold.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on_hold',
            'started_at' => $this->faker->dateTimeBetween('-20 days', '-5 days'),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the project is assigned to a staff member.
     */
    public function assignedTo(User $staff): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $staff->id,
        ]);
    }

    /**
     * Indicate that the project belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the project is linked to a specific order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
        ]);
    }

    /**
     * Indicate that the project uses a specific template.
     */
    public function withTemplate(Template $template): static
    {
        return $this->state(fn (array $attributes) => [
            'template_id' => $template->id,
        ]);
    }

    /**
     * Indicate that the project has website access details.
     */
    public function withWebsiteAccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'website_url' => $this->faker->url,
            'admin_url' => $this->faker->url . '/admin',
            'admin_username' => 'admin',
            'admin_password' => 'secure123',
        ]);
    }

    /**
     * Indicate that the project has a specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'project_name' => $name,
        ]);
    }
}