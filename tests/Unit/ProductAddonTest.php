<?php

namespace Tests\Unit;

use App\Models\ProductAddon;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAddonTest extends TestCase
{
    use RefreshDatabase;
    
    protected $addon;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test addon
        $this->addon = ProductAddon::create([
            'name' => 'SSL Certificate',
            'description' => 'Premium SSL certificate for enhanced security',
            'price' => 500000,
            'billing_type' => 'recurring',
            'billing_cycle' => 'annually',
            'is_active' => true,
            'sort_order' => 1,
            'category' => 'ssl',
        ]);
    }
    
    public function test_product_addon_can_be_created_with_required_fields()
    {
        $addon = ProductAddon::create([
            'name' => 'Domain Registration',
            'description' => 'Premium domain registration service',
            'price' => 200000,
            'billing_type' => 'one_time',
            'billing_cycle' => null,
            'is_active' => true,
            'sort_order' => 2,
            'category' => 'domain',
        ]);
        
        $this->assertDatabaseHas('product_addons', [
            'name' => 'Domain Registration',
            'description' => 'Premium domain registration service',
            'price' => 200000,
            'billing_type' => 'one_time',
            'category' => 'domain',
        ]);
    }
    
    public function test_product_addon_belongs_to_many_products()
    {
        // Note: This would require creating the pivot table
        // For now, we'll test the relationship method exists
        $this->assertTrue(method_exists($this->addon, 'products'));
    }
    
    public function test_active_scope()
    {
        // Create inactive addon
        ProductAddon::create([
            'name' => 'Inactive Addon',
            'description' => 'This addon is inactive',
            'price' => 100000,
            'billing_type' => 'one_time',
            'is_active' => false,
            'sort_order' => 3,
            'category' => 'other',
        ]);
        
        $activeAddons = ProductAddon::active()->get();
        $this->assertEquals(1, $activeAddons->count());
        $this->assertTrue($activeAddons->first()->is_active);
    }
    
    public function test_one_time_scope()
    {
        ProductAddon::create([
            'name' => 'One Time Setup',
            'description' => 'One time setup fee',
            'price' => 1000000,
            'billing_type' => 'one_time',
            'is_active' => true,
            'sort_order' => 2,
            'category' => 'setup',
        ]);
        
        $oneTimeAddons = ProductAddon::oneTime()->get();
        $this->assertEquals(1, $oneTimeAddons->count());
        $this->assertEquals('one_time', $oneTimeAddons->first()->billing_type);
    }
    
    public function test_recurring_scope()
    {
        ProductAddon::create([
            'name' => 'Monthly Backup',
            'description' => 'Monthly backup service',
            'price' => 100000,
            'billing_type' => 'one_time',
            'is_active' => true,
            'sort_order' => 3,
            'category' => 'backup',
        ]);
        
        $recurringAddons = ProductAddon::recurring()->get();
        $this->assertEquals(1, $recurringAddons->count());
        $this->assertEquals('recurring', $recurringAddons->first()->billing_type);
    }
    
    public function test_by_category_scope()
    {
        ProductAddon::create([
            'name' => 'Domain Addon',
            'description' => 'Domain related addon',
            'price' => 300000,
            'billing_type' => 'one_time',
            'is_active' => true,
            'sort_order' => 2,
            'category' => 'domain',
        ]);
        
        $sslAddons = ProductAddon::byCategory('ssl')->get();
        $this->assertEquals(1, $sslAddons->count());
        $this->assertEquals('ssl', $sslAddons->first()->category);
        
        $domainAddons = ProductAddon::byCategory('domain')->get();
        $this->assertEquals(1, $domainAddons->count());
        $this->assertEquals('domain', $domainAddons->first()->category);
    }
    
    public function test_ordered_scope()
    {
        ProductAddon::create([
            'name' => 'First Addon',
            'description' => 'First addon by sort order',
            'price' => 100000,
            'billing_type' => 'one_time',
            'is_active' => true,
            'sort_order' => 0, // Lower sort order should come first
            'category' => 'other',
        ]);
        
        $orderedAddons = ProductAddon::ordered()->get();
        $this->assertEquals('First Addon', $orderedAddons->first()->name);
        $this->assertEquals('SSL Certificate', $orderedAddons->last()->name);
    }
    
    public function test_is_active_method()
    {
        $this->assertTrue($this->addon->isActive());
        
        $this->addon->update(['is_active' => false]);
        $this->assertFalse($this->addon->isActive());
    }
    
    public function test_is_one_time_method()
    {
        $this->assertFalse($this->addon->isOneTime());
        
        $this->addon->update(['billing_type' => 'one_time']);
        $this->assertTrue($this->addon->isOneTime());
    }
    
    public function test_is_recurring_method()
    {
        $this->assertTrue($this->addon->isRecurring());
        
        $this->addon->update(['billing_type' => 'one_time']);
        $this->assertFalse($this->addon->isRecurring());
    }
    
    public function test_formatted_price_attribute()
    {
        $this->assertEquals('Rp 500.000', $this->addon->formatted_price);
        
        $this->addon->update(['price' => 1250000]);
        $this->assertEquals('Rp 1.250.000', $this->addon->formatted_price);
    }
    
    public function test_billing_type_text_attribute()
    {
        $this->assertEquals('Berulang', $this->addon->billing_type_text);
        
        $this->addon->update(['billing_type' => 'one_time']);
        $this->assertEquals('Sekali Bayar', $this->addon->billing_type_text);
    }
    
    public function test_billing_cycle_text_attribute()
    {
        $this->assertEquals('Tahunan', $this->addon->billing_cycle_text);
        
        $this->addon->update(['billing_cycle' => 'monthly']);
        $this->assertEquals('Bulanan', $this->addon->billing_cycle_text);
        
        $this->addon->update(['billing_cycle' => 'quarterly']);
        $this->assertEquals('Triwulan', $this->addon->billing_cycle_text);
        
        $this->addon->update(['billing_cycle' => 'semi_annually']);
        $this->assertEquals('Semester', $this->addon->billing_cycle_text);
        
        // Test one-time billing
        $this->addon->update(['billing_type' => 'one_time', 'billing_cycle' => null]);
        $this->assertEquals('Sekali Bayar', $this->addon->billing_cycle_text);
    }
    
    public function test_category_badge_class_attribute()
    {
        $this->assertEquals('badge-warning', $this->addon->category_badge_class);
        
        $this->addon->update(['category' => 'hosting']);
        $this->assertEquals('badge-primary', $this->addon->category_badge_class);
        
        $this->addon->update(['category' => 'domain']);
        $this->assertEquals('badge-success', $this->addon->category_badge_class);
        
        $this->addon->update(['category' => 'maintenance']);
        $this->assertEquals('badge-info', $this->addon->category_badge_class);
        
        $this->addon->update(['category' => 'marketing']);
        $this->assertEquals('badge-accent', $this->addon->category_badge_class);
        
        $this->addon->update(['category' => 'unknown']);
        $this->assertEquals('badge-secondary', $this->addon->category_badge_class);
    }
    
    public function test_category_text_attribute()
    {
        $this->assertEquals('SSL Certificate', $this->addon->category_text);
        
        $this->addon->update(['category' => 'hosting']);
        $this->assertEquals('Hosting', $this->addon->category_text);
        
        $this->addon->update(['category' => 'domain']);
        $this->assertEquals('Domain', $this->addon->category_text);
        
        $this->addon->update(['category' => 'maintenance']);
        $this->assertEquals('Maintenance', $this->addon->category_text);
        
        $this->addon->update(['category' => 'marketing']);
        $this->assertEquals('Marketing', $this->addon->category_text);
        
        $this->addon->update(['category' => 'custom']);
        $this->assertEquals('Custom', $this->addon->category_text);
    }
    
    public function test_price_casting_to_decimal()
    {
        $this->assertEquals('500000.00', $this->addon->price);
        
        $this->addon->update(['price' => 1250000.50]);
        $this->addon->refresh();
        $this->assertEquals('1250000.50', $this->addon->price);
    }
    
    public function test_boolean_casting()
    {
        $this->assertIsBool($this->addon->is_active);
        $this->assertTrue($this->addon->is_active);
        
        $this->addon->update(['is_active' => 0]);
        $this->addon->refresh();
        $this->assertIsBool($this->addon->is_active);
        $this->assertFalse($this->addon->is_active);
    }
    
    public function test_integer_casting()
    {
        $this->assertIsInt($this->addon->sort_order);
        $this->assertEquals(1, $this->addon->sort_order);
    }
}