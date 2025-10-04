<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Create a test product so the homepage can load
        Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'type' => 'website',
            'price' => 1000000.00,
            'billing_cycle' => 'monthly',
            'setup_time_days' => 14,
            'features' => ['Feature 1', 'Feature 2'],
            'is_active' => true,
            'sort_order' => 1
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
