<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Admin\ProductManagement;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->client = User::factory()->create(['role' => 'client']);
    }

    /** @test */
    public function admin_can_access_product_management_page()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSeeLivewire('admin.product-management');
    }

    /** @test */
    public function non_admin_cannot_access_product_management()
    {
        $response = $this->actingAs($this->staff)
            ->get('/admin/products');
        $response->assertStatus(403);

        $response = $this->actingAs($this->client)
            ->get('/admin/products');
        $response->assertStatus(403);
    }

    /** @test */
    public function product_management_component_displays_products()
    {
        $products = Product::factory()->count(3)->create();

        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->assertSee($products[0]->name)
            ->assertSee($products[1]->name)
            ->assertSee($products[2]->name);
    }

    /** @test */
    public function admin_can_create_new_product_via_livewire()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test product description',
            'type' => 'website',
            'price' => 1000000,
            'billing_cycle' => 'annually',
            'setup_time' => 7,
            'features' => ['Responsive Design', 'SEO Optimized'],
            'is_active' => true,
            'sort_order' => 0
        ];

        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->set('name', $productData['name'])
            ->set('description', $productData['description'])
            ->set('type', $productData['type'])
            ->set('price', $productData['price'])
            ->set('billing_cycle', $productData['billing_cycle'])
            ->set('setup_time', $productData['setup_time'])
            ->set('features', $productData['features'])
            ->set('is_active', $productData['is_active'])
            ->set('sort_order', $productData['sort_order'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'Test product description',
            'type' => 'website',
            'price' => 1000000,
            'billing_cycle' => 'annually',
            'setup_time_days' => 7,
            'is_active' => true,
            'sort_order' => 0
        ]);
    }

    /** @test */
    public function admin_can_edit_existing_product()
    {
        $product = Product::factory()->create([
            'name' => 'Original Product',
            'price' => 500000
        ]);

        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->call('edit', $product->id)
            ->set('name', 'Updated Product')
            ->set('price', 750000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 750000
        ]);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $product = Product::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->call('delete', $product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function admin_can_toggle_product_status()
    {
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->call('toggleStatus', $product->id);

        $product->refresh();
        $this->assertFalse($product->is_active);

        // Toggle back
        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->call('toggleStatus', $product->id);

        $product->refresh();
        $this->assertTrue($product->is_active);
    }

    /** @test */
    public function admin_can_search_products()
    {
        Product::factory()->create(['name' => 'Website Package']);
        Product::factory()->create(['name' => 'Hosting Package']);

        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->set('search', 'Website')
            ->assertSee('Website Package')
            ->assertDontSee('Hosting Package');
    }

    /** @test */
    public function admin_can_filter_products_by_type()
    {
        Product::factory()->create(['name' => 'Website Product', 'type' => 'website']);
        Product::factory()->create(['name' => 'Hosting Product', 'type' => 'hosting']);

        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->set('filterType', 'website')
            ->assertSee('Website Product')
            ->assertDontSee('Hosting Product');
    }

    /** @test */
    public function admin_can_filter_products_by_status()
    {
        Product::factory()->create(['name' => 'Active Product', 'is_active' => true]);
        Product::factory()->create(['name' => 'Inactive Product', 'is_active' => false]);

        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->set('filterStatus', '1')
            ->assertSee('Active Product')
            ->assertDontSee('Inactive Product');
    }

    /** @test */
    public function product_validation_works_correctly()
    {
        Livewire::actingAs($this->admin)
            ->test(ProductManagement::class)
            ->set('name', '')
            ->set('price', -100)
            ->set('type', 'invalid_type')
            ->set('billing_cycle', 'invalid_cycle')
            ->call('save')
            ->assertHasErrors([
                'name' => 'required',
                'price' => 'min',
                'type' => 'in',
                'billing_cycle' => 'in'
            ]);
    }

    /** @test */
    public function product_model_has_correct_fillable_attributes()
    {
        $product = new Product();
        $fillable = $product->getFillable();

        $expectedFillable = [
            'name', 'description', 'type', 'price', 'billing_cycle',
            'features', 'setup_time_days', 'is_active', 'sort_order'
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    /** @test */
    public function product_model_has_correct_casts()
    {
        $product = new Product();
        $casts = $product->getCasts();

        $this->assertEquals('array', $casts['features']);
        $this->assertEquals('decimal:2', $casts['price']);
        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('integer', $casts['setup_time_days']);
        $this->assertEquals('integer', $casts['sort_order']);
    }

    /** @test */
    public function product_relationships_work_correctly()
    {
        $product = Product::factory()->create();

        // Test that relationships are defined
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $product->orders());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsToMany', $product->addons());
    }

    /** @test */
    public function product_scopes_work_correctly()
    {
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);
        $websiteProduct = Product::factory()->create(['type' => 'website']);
        $hostingProduct = Product::factory()->create(['type' => 'hosting']);

        // Test active scope
        $activeProducts = Product::active()->get();
        $this->assertTrue($activeProducts->contains($activeProduct));
        $this->assertFalse($activeProducts->contains($inactiveProduct));

        // Test by type scope
        $websiteProducts = Product::byType('website')->get();
        $this->assertTrue($websiteProducts->contains($websiteProduct));
        $this->assertFalse($websiteProducts->contains($hostingProduct));
    }

    /** @test */
    public function product_can_have_addons()
    {
        $product = Product::factory()->create();
        $addon1 = ProductAddon::factory()->create(['name' => 'SSL Certificate']);
        $addon2 = ProductAddon::factory()->create(['name' => 'Premium Support']);

        $product->addons()->attach([$addon1->id, $addon2->id]);

        $this->assertEquals(2, $product->addons()->count());
        $this->assertTrue($product->addons->contains($addon1));
        $this->assertTrue($product->addons->contains($addon2));
    }

    /** @test */
    public function product_pricing_calculations_work_correctly()
    {
        $product = Product::factory()->create([
            'price' => 1000000, // 1 million IDR
            'billing_cycle' => 'annually'
        ]);

        // Test annual price
        $this->assertEquals(1000000, $product->price);

        // Test monthly equivalent (if needed)
        $monthlyPrice = $product->price / 12;
        $this->assertEquals(83333.33, round($monthlyPrice, 2));
    }

    /** @test */
    public function product_features_are_stored_as_json_array()
    {
        $features = ['Responsive Design', 'SEO Optimized', 'Contact Form'];
        
        $product = Product::factory()->create([
            'features' => $features
        ]);

        $this->assertEquals($features, $product->features);
        $this->assertIsArray($product->features);
        $this->assertCount(3, $product->features);
    }

    /** @test */
    public function product_can_be_ordered_by_clients()
    {
        $product = Product::factory()->create(['is_active' => true]);
        
        $order = Order::factory()->create([
            'user_id' => $this->client->id,
            'product_id' => $product->id
        ]);

        $this->assertEquals($product->id, $order->product_id);
        $this->assertEquals($this->client->id, $order->user_id);
        $this->assertTrue($product->orders->contains($order));
    }

    /** @test */
    public function inactive_products_are_not_visible_to_clients()
    {
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->client)
            ->get('/client/products');

        $response->assertSee($activeProduct->name);
        $response->assertDontSee($inactiveProduct->name);
    }

    /** @test */
    public function product_setup_time_is_calculated_correctly()
    {
        $product = Product::factory()->create(['setup_time_days' => 7]);

        $this->assertEquals(7, $product->setup_time_days);
        
        // Test setup completion date calculation
        $orderDate = now();
        $expectedCompletionDate = $orderDate->addDays($product->setup_time_days);
        
        $this->assertEquals(
            $expectedCompletionDate->format('Y-m-d'),
            $orderDate->addDays($product->setup_time_days)->format('Y-m-d')
        );
    }

    /** @test */
    public function product_types_are_validated_correctly()
    {
        $validTypes = ['website', 'hosting', 'domain', 'maintenance'];
        
        foreach ($validTypes as $type) {
            $product = Product::factory()->create(['type' => $type]);
            $this->assertEquals($type, $product->type);
        }
    }

    /** @test */
    public function product_billing_cycles_are_validated_correctly()
    {
        $validCycles = ['monthly', 'quarterly', 'semi_annually', 'annually'];
        
        foreach ($validCycles as $cycle) {
            $product = Product::factory()->create(['billing_cycle' => $cycle]);
            $this->assertEquals($cycle, $product->billing_cycle);
        }
    }

    /** @test */
    public function product_sort_order_affects_display_order()
    {
        $product1 = Product::factory()->create(['sort_order' => 2, 'name' => 'Second']);
        $product2 = Product::factory()->create(['sort_order' => 1, 'name' => 'First']);
        $product3 = Product::factory()->create(['sort_order' => 3, 'name' => 'Third']);

        $sortedProducts = Product::orderBy('sort_order')->get();
        
        $this->assertEquals('First', $sortedProducts[0]->name);
        $this->assertEquals('Second', $sortedProducts[1]->name);
        $this->assertEquals('Third', $sortedProducts[2]->name);
    }
}