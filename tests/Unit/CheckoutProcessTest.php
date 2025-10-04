<?php

namespace Tests\Unit;

use App\Livewire\CheckoutProcess;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Carbon\Carbon;
use Tests\TestCase;
use ReflectionClass;

class CheckoutProcessTest extends TestCase
{
    use RefreshDatabase;
    
    protected $product;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->product = Product::create([
            'name' => 'Website Basic',
            'description' => 'Basic website package',
            'type' => 'website',
            'price' => 1000000,
            'billing_cycle' => 'monthly',
            'setup_time_days' => 14,
            'features' => ['Responsive Design', 'SEO Optimized'],
            'is_active' => true,
        ]);
    }

public function test_component_can_be_instantiated_with_product()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $this->assertEquals($this->product->id, $component->get('product')->id);
        $this->assertEquals('monthly', $component->get('billing_cycle'));
    }

public function test_validation_rules_work_correctly()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $component->call('submitOrder')
            ->assertHasErrors([
                'name' => 'required',
                'email' => 'required', 
                'password' => 'required',
                'phone' => 'required'
            ]);
    }

public function test_email_validation_works_correctly()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $component->set('email', 'invalid-email')
            ->call('submitOrder')
            ->assertHasErrors(['email' => 'email']);
    }

public function test_password_confirmation_validation_works()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $component->set('password', 'password123')
            ->set('password_confirmation', 'different')
            ->call('submitOrder')
            ->assertHasErrors(['password' => 'confirmed']);
    }

public function test_email_uniqueness_validation_works()
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'phone' => '081234567890',
            'role' => 'client'
        ]);
        
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $component->set('email', 'test@example.com')
            ->call('submitOrder')
            ->assertHasErrors(['email' => 'unique']);
    }

public function test_calculate_next_due_date_works_for_monthly_billing()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        // Use reflection to access private method
        $reflection = new ReflectionClass($component->instance());
        $method = $reflection->getMethod('calculateNextDueDate');
        $method->setAccessible(true);
        
        $component->set('billing_cycle', 'monthly');
        
        $result = $method->invoke($component->instance());
        $expected = now()->addMonth();
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }

public function test_calculate_next_due_date_works_for_quarterly_billing()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $reflection = new ReflectionClass($component->instance());
        $method = $reflection->getMethod('calculateNextDueDate');
        $method->setAccessible(true);
        
        $component->set('billing_cycle', 'quarterly');
        
        $result = $method->invoke($component->instance());
        $expected = now()->addMonths(3);
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }

public function test_calculate_next_due_date_works_for_semi_annually_billing()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $reflection = new ReflectionClass($component->instance());
        $method = $reflection->getMethod('calculateNextDueDate');
        $method->setAccessible(true);
        
        $component->set('billing_cycle', 'semi_annually');
        
        $result = $method->invoke($component->instance());
        $expected = now()->addMonths(6);
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }

public function test_calculate_next_due_date_works_for_annually_billing()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $reflection = new ReflectionClass($component->instance());
        $method = $reflection->getMethod('calculateNextDueDate');
        $method->setAccessible(true);
        
        $component->set('billing_cycle', 'annually');
        
        $result = $method->invoke($component->instance());
        $expected = now()->addYear();
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }

public function test_calculate_next_due_date_defaults_to_monthly_for_invalid_billing_cycle()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $reflection = new ReflectionClass($component->instance());
        $method = $reflection->getMethod('calculateNextDueDate');
        $method->setAccessible(true);
        
        $component->set('billing_cycle', 'invalid_cycle');
        
        $result = $method->invoke($component->instance());
        $expected = now()->addMonth();
        
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }

public function test_tax_calculation_is_correct()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $component->set([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'billing_cycle' => 'monthly'
        ]);
        
        $component->call('submitOrder');
        
        // Check if invoice was created with correct tax calculation
        $this->assertDatabaseHas('invoices', [
            'amount' => 1000000,
            'tax_amount' => 110000, // 11% of 1000000
            'total_amount' => 1110000 // 1000000 + 110000
        ]);
    }

public function test_component_properties_are_properly_initialized()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $this->assertEquals('', $component->get('name'));
        $this->assertEquals('', $component->get('email'));
        $this->assertEquals('', $component->get('password'));
        $this->assertEquals('', $component->get('password_confirmation'));
        $this->assertEquals('', $component->get('phone'));
        $this->assertEquals('', $component->get('company'));
        $this->assertEquals('monthly', $component->get('billing_cycle'));
    }

public function test_validation_messages_are_in_indonesian()
    {
        $component = Livewire::test(CheckoutProcess::class, ['product' => $this->product->id]);
        
        $component->call('submitOrder');
        
        $errors = $component->instance()->getErrorBag();
        
        $this->assertEquals('Nama lengkap wajib diisi.', $errors->get('name')[0]);
        $this->assertEquals('Email wajib diisi.', $errors->get('email')[0]);
        $this->assertEquals('Password wajib diisi.', $errors->get('password')[0]);
        $this->assertEquals('Nomor telepon wajib diisi.', $errors->get('phone')[0]);
    }
}