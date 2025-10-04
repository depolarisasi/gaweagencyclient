<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Template>
 */
class TemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Template::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['business', 'ecommerce', 'portfolio', 'blog', 'landing'];
        $templateNames = [
            'business' => ['Corporate Pro', 'Business Elite', 'Professional Suite', 'Executive Theme'],
            'ecommerce' => ['Shop Master', 'Commerce Plus', 'Store Pro', 'Market Elite'],
            'portfolio' => ['Creative Showcase', 'Artist Portfolio', 'Designer Pro', 'Gallery Master'],
            'blog' => ['Blog Master', 'Content Pro', 'Writer Theme', 'News Elite'],
            'landing' => ['Landing Pro', 'Convert Master', 'Sales Page', 'Lead Gen']
        ];
        
        $category = $this->faker->randomElement($categories);
        $name = $this->faker->randomElement($templateNames[$category]);
        
        return [
            'name' => $name,
            'description' => $this->faker->paragraph(2),
            'category' => $category,
            'demo_url' => 'https://demo.example.com/' . strtolower(str_replace(' ', '-', $name)),
            'thumbnail_url' => 'https://example.com/thumbnails/' . strtolower(str_replace(' ', '-', $name)) . '.jpg',
            'features' => $this->getFeaturesByCategory($category),
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Get features based on template category.
     */
    private function getFeaturesByCategory(string $category): array
    {
        $baseFeatures = ['Responsive Design', 'SEO Optimized', 'Fast Loading'];
        
        $categoryFeatures = [
            'business' => ['Contact Form', 'Team Section', 'Services Page', 'Testimonials'],
            'ecommerce' => ['Product Catalog', 'Shopping Cart', 'Payment Integration', 'Inventory Management'],
            'portfolio' => ['Gallery Section', 'Project Showcase', 'About Page', 'Contact Form'],
            'blog' => ['Blog Layout', 'Comment System', 'Archive Pages', 'Search Function'],
            'landing' => ['Call to Action', 'Lead Forms', 'Conversion Tracking', 'A/B Testing']
        ];
        
        return array_merge($baseFeatures, $categoryFeatures[$category] ?? []);
    }

    /**
     * Indicate that the template is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the template is a business type.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'business',
            'name' => 'Business Pro Template',
            'features' => $this->getFeaturesByCategory('business'),
        ]);
    }

    /**
     * Indicate that the template is an ecommerce type.
     */
    public function ecommerce(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'ecommerce',
            'name' => 'E-commerce Pro Template',
            'features' => $this->getFeaturesByCategory('ecommerce'),
        ]);
    }

    /**
     * Indicate that the template is a portfolio type.
     */
    public function portfolio(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'portfolio',
            'name' => 'Portfolio Pro Template',
            'features' => $this->getFeaturesByCategory('portfolio'),
        ]);
    }

    /**
     * Indicate that the template has specific features.
     */
    public function withFeatures(array $features): static
    {
        return $this->state(fn (array $attributes) => [
            'features' => $features,
        ]);
    }

    /**
     * Indicate that the template has a specific sort order.
     */
    public function withSortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }
}