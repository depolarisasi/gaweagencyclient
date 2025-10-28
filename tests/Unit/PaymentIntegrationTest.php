<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Order;
use App\Models\Project;
use App\Models\Product;
use App\Http\Controllers\PaymentController;
use App\Services\TripayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $order;
    protected $invoice;
    protected $project;
    protected $paymentController;
    protected $tripayServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create([
            'role' => 'client',
            'email' => 'test@example.com'
        ]);
        
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 500000,
            'is_active' => true
        ]);
        
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'amount' => 500000,
            'status' => 'pending'
        ]);
        
        $this->invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'amount' => 500000,
            'tax_amount' => 55000,
            'total_amount' => 555000,
            'status' => 'sent',
            'due_date' => now()->addDays(7)
        ]);
        
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'status' => 'pending'
        ]);
        
        // Mock TripayService
        $this->tripayServiceMock = Mockery::mock(TripayService::class);
        $this->app->instance(TripayService::class, $this->tripayServiceMock);
        
        $this->paymentController = new PaymentController($this->tripayServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_shows_payment_page_for_valid_invoice()
    {
        $this->tripayServiceMock
            ->shouldReceive('getPaymentChannels')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    [
                        'code' => 'BRIVA',
                        'name' => 'BRI Virtual Account',
                        'group' => 'Virtual Account',
                        'active' => true,
                        'total_fee' => ['flat' => 4000, 'percent' => 0]
                    ]
                ]
            ]);

        $response = $this->paymentController->showPayment($this->invoice);
        
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertEquals('client.payment.show', $response->getName());
        $this->assertArrayHasKey('invoice', $response->getData());
        $this->assertArrayHasKey('paymentChannels', $response->getData());
    }

    /** @test */
    public function it_creates_payment_transaction_successfully()
    {
        // Update invoice status to pending for payment creation
        $this->invoice->update(['status' => 'pending']);
        
        $this->tripayServiceMock
            ->shouldReceive('formatTransactionData')
            ->once()
            ->with(Mockery::type(Invoice::class), 'BRIVA', Mockery::type('array'))
            ->andReturn([
                'method' => 'BRIVA',
                'merchant_ref' => 'TEST-REF-123',
                'amount' => 555000,
                'customer_name' => $this->user->name,
                'customer_email' => $this->user->email,
                'customer_phone' => $this->user->phone,
            ]);
            
        $this->tripayServiceMock
            ->shouldReceive('createTransaction')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn([
                'success' => true,
                'data' => [
                    'reference' => 'TEST123456',
                    'checkout_url' => 'https://tripay.co.id/checkout/TEST123456',
                    'pay_code' => '1234567890',
                    'expired_time' => time() + 3600,
                    'qr_url' => 'https://tripay.co.id/qr/TEST123456'
                ]
            ]);

        $request = Request::create('/payment', 'POST', [
            'payment_method' => 'BRIVA'
        ]);

        $response = $this->paymentController->createPayment($request, $this->invoice);
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertEquals('TEST123456', $responseData['data']['reference']);
        
        // Check if invoice was updated
        $this->invoice->refresh();
        $this->assertEquals('BRIVA', $this->invoice->payment_method);
        $this->assertEquals('TEST123456', $this->invoice->payment_reference);
    }

    /** @test */
    public function it_handles_payment_callback_successfully()
    {
        // Set up invoice with payment reference
        $this->invoice->update([
            'payment_reference' => 'TEST123456',
            'payment_method' => 'BRIVA'
        ]);

        $this->tripayServiceMock
            ->shouldReceive('validateCallbackSignature')
            ->once()
            ->andReturn(true);

        $callbackData = [
            'reference' => 'TEST123456',
            'status' => 'PAID',
            'total_amount' => 555000,
            'paid_amount' => 555000,
            'paid_at' => now()->timestamp
        ];

        $request = Request::create('/payment/callback', 'POST', $callbackData);
        $request->headers->set('X-Callback-Signature', 'valid_signature');

        $response = $this->paymentController->handleCallback($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        // Check if invoice status was updated
        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);
        $this->assertNotNull($this->invoice->paid_at);
        
        // Check if project was activated
        $this->project->refresh();
        $this->assertEquals('active', $this->project->status);
    }

    /** @test */
    public function it_rejects_callback_without_signature()
    {
        $request = Request::create('/payment/callback', 'POST', [
            'reference' => 'TEST123456',
            'status' => 'PAID'
        ]);

        $response = $this->paymentController->handleCallback($request);
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    /** @test */
    public function it_rejects_callback_with_invalid_signature()
    {
        $this->tripayServiceMock
            ->shouldReceive('validateCallbackSignature')
            ->once()
            ->andReturn(false);

        $request = Request::create('/payment/callback', 'POST', [
            'reference' => 'TEST123456',
            'status' => 'PAID'
        ]);
        $request->headers->set('X-Callback-Signature', 'invalid_signature');

        $response = $this->paymentController->handleCallback($request);
        
        $this->assertEquals(401, $response->getStatusCode());
    }

    /** @test */
    public function it_handles_callback_for_non_existent_invoice()
    {
        $this->tripayServiceMock
            ->shouldReceive('validateCallbackSignature')
            ->once()
            ->andReturn(true);

        $request = Request::create('/payment/callback', 'POST', [
            'reference' => 'NONEXISTENT123',
            'status' => 'PAID'
        ]);
        $request->headers->set('X-Callback-Signature', 'valid_signature');

        $response = $this->paymentController->handleCallback($request);
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    /** @test */
    public function it_checks_payment_status_correctly()
    {
        $this->invoice->update([
            'payment_reference' => 'TEST123456'
        ]);

        $this->tripayServiceMock
            ->shouldReceive('getTransactionDetail')
            ->once()
            ->with('TEST123456')
            ->andReturn([
                'success' => true,
                'data' => [
                    'status' => 'PAID',
                    'paid_at' => now()->timestamp,
                    'amount_received' => 555000
                ]
            ]);

        $response = $this->paymentController->checkPaymentStatus($this->invoice);
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertTrue($responseData['success']);
        $this->assertEquals('PAID', $responseData['data']['status']);
    }

    /** @test */
    public function it_handles_failed_payment_status_check()
    {
        $this->invoice->update([
            'payment_reference' => 'TEST123456'
        ]);

        $this->tripayServiceMock
            ->shouldReceive('getTransactionDetail')
            ->once()
            ->with('TEST123456')
            ->andReturn([
                'success' => false,
                'message' => 'Transaction not found'
            ]);

        $response = $this->paymentController->checkPaymentStatus($this->invoice);
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertFalse($responseData['success']);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /** @test */
    public function it_handles_payment_status_check_for_invoice_without_reference()
    {
        $response = $this->paymentController->checkPaymentStatus($this->invoice);
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertFalse($responseData['success']);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /** @test */
    public function it_updates_invoice_status_correctly_for_different_payment_statuses()
    {
        $this->invoice->update([
            'payment_reference' => 'TEST123456'
        ]);

        // Test FAILED status
        $reflection = new \ReflectionClass($this->paymentController);
        $method = $reflection->getMethod('updateInvoiceStatus');
        $method->setAccessible(true);

        $callbackData = [
            'status' => 'FAILED',
            'reference' => 'TEST123456'
        ];

        $method->invoke($this->paymentController, $this->invoice, $callbackData);
        
        $this->invoice->refresh();
        $this->assertEquals('cancelled', $this->invoice->status);
    }

    /** @test */
    public function it_activates_project_only_for_pending_projects()
    {
        // Set project to active status
        $this->project->update(['status' => 'active']);
        
        $reflection = new \ReflectionClass($this->paymentController);
        $method = $reflection->getMethod('activateProject');
        $method->setAccessible(true);

        $originalUpdatedAt = $this->project->updated_at;
        
        $method->invoke($this->paymentController, $this->invoice);
        
        $this->project->refresh();
        // Should remain active and not be updated
        $this->assertEquals('active', $this->project->status);
        $this->assertEquals($originalUpdatedAt, $this->project->updated_at);
    }

    /** @test */
    public function it_handles_database_transaction_rollback_on_error()
    {
        $this->tripayServiceMock
            ->shouldReceive('createTransaction')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $request = Request::create('/payment', 'POST', [
            'payment_method' => 'BRIVA'
        ]);

        $response = $this->paymentController->createPayment($request, $this->invoice);
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertFalse($responseData['success']);
        $this->assertEquals(500, $response->getStatusCode());
        
        // Invoice should not be updated
        $this->invoice->refresh();
        $this->assertNull($this->invoice->payment_reference);
    }
}