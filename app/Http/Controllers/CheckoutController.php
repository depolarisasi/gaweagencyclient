<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Template;
use App\Models\ProductAddon;
use App\Models\SubscriptionPlan;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\User;
use App\Models\Invoice;
use App\Services\TripayService;
use App\Services\CartService;
use App\Helpers\CheckoutCookieHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected $tripayService;
    protected $cartService;

    public function __construct(TripayService $tripayService, CartService $cartService)
    {
        $this->tripayService = $tripayService;
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        // Step 1: Template Selection
        $templates = Template::active()->orderBy('sort_order')->get();
        return view('checkout.step1', compact('templates'));
    }

    public function step1(Request $request)
    {
        // Handle template selection
        $request->validate([
            'template_id' => 'required|exists:templates,id',
        ]);

        // Get or create cart and update template
        $cart = $this->cartService->getOrCreateCart($request);
        $this->cartService->updateTemplate($cart, $request->input('template_id'));

        // Store in session and cookies for backward compatibility
        $templateId = $request->input('template_id');
        $request->session()->put('checkout.template_id', $templateId);
        CheckoutCookieHelper::storeTemplate($templateId);

        return redirect()->route('checkout.configure');
    }

    public function configure(Request $request)
    {
        // Step 2: Configure - Billing cycle/subscription plan
        // Migrate session data to database cart
        $cart = $this->cartService->migrateFromSessionAndCookies($request);
        
        if ($request->isMethod('post')) {
            // Handle POST request - save configuration and redirect to addons
            $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,6_months,annually,2_years,3_years',
        ]);

            // Update cart with subscription plan and billing cycle
            $this->cartService->updateSubscriptionPlan($cart, $request->subscription_plan_id, $request->billing_cycle);

            // Store in session and cookies for backward compatibility
            $request->session()->put('checkout.subscription_plan_id', $request->subscription_plan_id);
            $request->session()->put('checkout.billing_cycle', $request->billing_cycle);
            CheckoutCookieHelper::storeSubscriptionPlan($request->subscription_plan_id, $request->billing_cycle);
            
            return redirect()->route('checkout.addon');
        }
        
        // Handle GET request - display configuration page
        if (!$cart->template_id) {
            return redirect()->route('checkout.index')->withErrors('Template belum dipilih.');
        }

        $template = $cart->template;
        if (!$template) {
            return redirect()->route('checkout.index')->withErrors('Template tidak ditemukan.');
        }
        
        $subscriptionPlans = SubscriptionPlan::active()->get();
        
        return view('checkout.configure', compact('template', 'subscriptionPlans'));
    }

    public function addons(Request $request)
    {
        // Step 3: Addons - Product addon selection (optional)
        
        // Get cart and migrate session data if needed
        $cart = $this->cartService->migrateFromSessionAndCookies($request);
        
        if ($request->isMethod('post')) {
            // Handle POST request - save addons and redirect to domain step
            $request->validate([
                'selected_addons' => 'nullable|array',
                'selected_addons.*' => 'exists:product_addons,id',
            ]);

            $selectedAddons = $request->input('selected_addons', []);
            
            // Update cart with selected addons
            $this->cartService->syncAddons($cart, $selectedAddons);
            
            // Store in cookies and session for backward compatibility
            CheckoutCookieHelper::storeAddons($selectedAddons);
            $request->session()->put('checkout.selected_addons', $selectedAddons);
            
            return redirect()->route('checkout.domain');
        }
        
        // Handle GET request - display addons page
        // Check if template is selected
        if (!$cart->template_id) {
            return redirect()->route('checkout.index')->withErrors('Template belum dipilih.');
        }

        // Check if subscription plan is selected
        if (!$cart->subscription_plan_id) {
            return redirect()->route('checkout.configure')->withErrors('Silakan pilih paket berlangganan terlebih dahulu.');
        }

        // Get selected template and subscription plan from cart
        $template = $cart->template;
        $subscriptionPlan = $cart->subscriptionPlan;
        $billingCycle = $cart->billing_cycle;
        
        if (!$billingCycle) {
            return redirect()->route('checkout.configure')->withErrors('Silakan pilih billing cycle terlebih dahulu.');
        }
        
        // Get available add-ons (former products)
        $addons = ProductAddon::active()->orderBy('sort_order')->get();
        
        return view('checkout.addon', compact('template', 'subscriptionPlan', 'addons', 'billingCycle'));
    }

    public function domain(Request $request)
    {
        // Step 4: Domain - Pilih dan verifikasi domain
        // Get cart and migrate session data if needed
        $cart = $this->cartService->migrateFromSessionAndCookies($request);

        if ($request->isMethod('post')) {
            // Handle POST request - save domain data and redirect to personal info

            // Attempt to get domain data from session first (set by Livewire DomainSelector)
            $sessionDomain = $request->session()->get('checkout.domain');

            // Fallback to hidden inputs if session not set
            $domainType = $request->input('domain_type');
            $domainName = $request->input('domain_name');
            $domainTld = $request->input('domain_tld');
            $domainPrice = $request->input('domain_price');

            if (!$sessionDomain && $domainType && $domainName) {
                $sessionDomain = [
                    'type' => $domainType,
                    'name' => $domainName,
                ];
                if ($domainTld) {
                    $sessionDomain['tld'] = $domainTld;
                }
                if ($domainPrice !== null && $domainPrice !== '') {
                    $sessionDomain['price'] = is_numeric($domainPrice) ? (float) $domainPrice : $domainPrice;
                }
            }

            // Validate domain data presence
            if (empty($sessionDomain) || empty($sessionDomain['type']) || empty($sessionDomain['name'])) {
                return back()->withErrors('Silakan pilih dan verifikasi domain terlebih dahulu.');
            }

            // Normalize keys and update cart
            $domainInfo = [
                'type' => $sessionDomain['type'],
                'name' => $sessionDomain['name'],
            ];
            // Include optional fields if present
            foreach (['tld', 'price', 'own_domain', 'is_available'] as $optKey) {
                if (array_key_exists($optKey, $sessionDomain)) {
                    $domainInfo[$optKey] = $sessionDomain[$optKey];
                }
            }

            $this->cartService->updateDomainData($cart, $domainInfo);

            // Store in cookies for backward compatibility
            CheckoutCookieHelper::storeDomain($domainInfo);

            return redirect()->route('checkout.personal-info')->with('success', 'Domain berhasil disimpan.');
        }

        // Handle GET request - display domain selection page
        // Check if template is selected
        if (!$cart->template_id) {
            return redirect()->route('checkout.index')->withErrors('Template belum dipilih.');
        }

        // Check if subscription plan is selected
        if (!$cart->subscription_plan_id) {
            return redirect()->route('checkout.configure')->withErrors('Silakan pilih paket berlangganan terlebih dahulu.');
        }

        $template = $cart->template;

        return view('checkout.domain', compact('template'));
    }

    public function personalInfo(Request $request)
    {
        // Step 5: Personal Info - Data pengguna
        
        // Get cart and migrate session data if needed
        $cart = $this->cartService->migrateFromSessionAndCookies($request);
        
        if ($request->isMethod('post')) {
            // Handle POST request - save personal info and redirect to summary
            
            // Debug logging
            \Log::info('Personal Info POST data:', [
                'all_data' => $request->all()
            ]);
            
            // Different validation rules for logged-in vs guest users
            if (auth()->check()) {
                // For logged-in users, skip password validation
                $request->validate([
                    // No domain validation here; domain is handled in previous step
                ]);

                $customerInfo = [
                    'full_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone ?? '',
                    'company' => auth()->user()->company ?? '',
                    'user_id' => auth()->user()->id,
                    'is_logged_in' => true,
                ];
            } else {
                // For guest users, require all fields including password
                $request->validate([
                    'full_name' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'phone' => 'required|string|max:20',
                    'password' => 'required|string|min:8|confirmed',
                ]);

                $customerInfo = [
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => $request->password, // Store for user creation
                    'company' => $request->company ?? '',
                    'is_logged_in' => false,
                ];
            }
            
            // Update cart with customer info only (domain handled in previous step)
            $this->cartService->updateCustomerInfo($cart, $customerInfo);

            // Store in cookies for backward compatibility
            CheckoutCookieHelper::storeCustomerInfo($customerInfo);
            
            return redirect()->route('checkout.summary');
        }
        
        // Handle GET request - display personal info page
        // Check if template is selected
        if (!$cart->template_id) {
            return redirect()->route('checkout.index')->withErrors('Template belum dipilih.');
        }

        // Check if subscription plan is selected
        if (!$cart->subscription_plan_id) {
            return redirect()->route('checkout.configure')->withErrors('Silakan pilih paket berlangganan terlebih dahulu.');
        }

        $template = $cart->template;
        
        // Get existing customer info from cart for pre-filling form
        $customerInfo = $cart->configuration['customer_info'] ?? [];
        
        return view('checkout.personal-info', compact('template', 'customerInfo'));
    }

    public function summary(Request $request)
    {
        // Step 5: Order Summary - Display summary and handle payment method selection
        
        // Get cart and migrate session data if needed
        $cart = $this->cartService->migrateFromSessionAndCookies($request);
        
        // Note: POST requests to summary are handled by the submit method according to routes

        // Handle GET request - display summary page
        // Validate all required data exists in cart
        \Log::info('Summary validation check:', [
            'template_id' => $cart->template_id,
            'subscription_plan_id' => $cart->subscription_plan_id,
            'customer_info_empty' => empty($cart->configuration['customer_info']),
            'domain_data_empty' => empty($cart->domain_data),
            'configuration' => $cart->configuration,
            'domain_data' => $cart->domain_data,
        ]);
        
        if (!$cart->template_id || !$cart->subscription_plan_id || 
            empty($cart->configuration['customer_info']) || empty($cart->domain_data)) {
            \Log::error('Cart validation failed in summary');
            return redirect()->route('checkout.index')->withErrors('Data checkout tidak lengkap. Silakan mulai dari awal.');
        }

        // Get cart summary with all calculated totals
        $summary = $this->cartService->getCartSummary($cart);

        // Get available payment channels
        $paymentChannels = $this->tripayService->getPaymentChannels();

        // Extract data for view
        $template = $cart->template;
        $subscriptionPlan = $cart->subscriptionPlan;
        $addons = $cart->addons;
        $customerInfo = $cart->configuration['customer_info'] ?? [];
        $domainInfo = $cart->domain_data ?? [];

        return view('checkout.summary', compact(
            'cart',
            'summary',
            'paymentChannels',
            'template',
            'subscriptionPlan',
            'addons',
            'customerInfo',
            'domainInfo'
        ));
    }

    public function billing(Request $request)
    {
        // Step 6: Payment Information Display (VA/QR/payment guide from TriPay)
        
        Log::info('Billing method called');
        
        // Get cart and migrate session data if needed
        $cart = $this->cartService->migrateFromSessionAndCookies($request);

        // Handle GET request - display Tripay payment information
        // Check if Tripay transaction exists in cookies first, then session
        $tripayTransactionFromCookie = CheckoutCookieHelper::getTripayTransaction();
        $tripayTransactionFromSession = $request->session()->get('checkout.tripay_transaction');
        
        Log::info('Tripay transaction data check', [
            'from_cookie' => $tripayTransactionFromCookie ? 'exists' : 'null',
            'from_session' => $tripayTransactionFromSession ? 'exists' : 'null'
        ]);
        
        $tripayTransaction = $tripayTransactionFromCookie ?? $tripayTransactionFromSession;
        if (!$tripayTransaction) {
            Log::info('No Tripay transaction found, redirecting to checkout.summary');
            return redirect()->route('checkout.summary')->withErrors('Silakan pilih metode pembayaran terlebih dahulu.');
        }
        
        // Normalize tripay transaction data structure for view compatibility
        if (isset($tripayTransaction['data'])) {
            $tripayTransaction = array_merge($tripayTransaction, $tripayTransaction['data']);
        }
        
        Log::info('Tripay transaction found, proceeding with billing page');

        // Get order and invoice information
        $orderId = $request->session()->get('checkout.order_id');
        $order = null;
        $invoice = null;
        
        if ($orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $invoice = Invoice::where('order_id', $order->id)->first();
            }
        }
        
        Log::info('Order and invoice data', [
            'order_id' => $orderId,
            'order_found' => $order ? 'yes' : 'no',
            'invoice_found' => $invoice ? 'yes' : 'no',
            'invoice_number' => $invoice ? $invoice->invoice_number : 'not found'
        ]);

        // Check if all required data exists in cart (only if no Tripay transaction exists)
        // If Tripay transaction exists, the order has been created and cart may be deleted
        if (!$tripayTransaction && (!$cart->template_id || !$cart->subscription_plan_id || 
            empty($cart->configuration['customer_info']) || empty($cart->domain_data))) {
            return redirect()->route('checkout.index')->withErrors('Data checkout tidak lengkap. Silakan mulai dari awal.');
        }

        // Get payment channel details
        $paymentChannel = CheckoutCookieHelper::getPaymentChannel() ?? $request->session()->get('checkout.payment_channel');
        
        // Get payment channel information from Tripay
        $paymentChannels = $this->tripayService->getPaymentChannels();
        $channelDetails = null;
        if ($paymentChannels && $paymentChannels['success']) {
            $channelDetails = collect($paymentChannels['data'] ?? [])->firstWhere('code', $paymentChannel);
        }

        // Get data from order/invoice if available, otherwise from cart
        if ($order && $invoice) {
            // Get data from order/invoice
            $template = $order->template;
            $subscriptionPlan = $order->subscriptionPlan;
            $addons = $order->addons;
            $customerInfo = $order->customer_info;
            $domainInfo = $order->domain_info;
            
            $subscriptionAmount = $order->subscription_amount;
            $addonsAmount = $order->addons_amount;
            $domainAmount = $order->domain_amount ?? 0;
            $totalAmount = $order->total_amount;
        } else {
            // Get cart summary with all calculated totals
            $summary = $this->cartService->getCartSummary($cart);
            
            // Extract data for backward compatibility with view
            $template = $cart->template;
            $subscriptionPlan = $cart->subscriptionPlan;
            $addons = $cart->addons;
            $customerInfo = $cart->configuration['customer_info'];
            $domainInfo = $cart->domain_data;
            
            $subscriptionAmount = $summary['subscription_amount'];
            $addonsAmount = $summary['addons_amount'];
            $domainAmount = $summary['domain_amount'];
            $totalAmount = $summary['total_amount'];
        }
        
        // Add backward compatibility keys for domain info
        $domainInfo['domain_type'] = $domainInfo['type'] ?? 'new';
        $domainInfo['domain_name'] = $domainInfo['name'] ?? '';
        
        // Add customer fee to total amount for display (customer needs to pay this)
        $customerFee = $tripayTransaction['fee_customer'] ?? 0;
        $totalAmountWithFees = $totalAmount + $customerFee;

        // Update the tripayTransaction amount with the calculated total
        if (isset($tripayTransaction['amount'])) {
            $tripayTransaction['amount'] = $totalAmountWithFees;
        }
        
        // Also update the data array if it exists
        if (isset($tripayTransaction['data']['amount'])) {
            $tripayTransaction['data']['amount'] = $totalAmountWithFees;
        }

        // Debug logging
        Log::info('Billing page data', [
            'tripayTransaction' => $tripayTransaction,
            'channelDetails' => $channelDetails,
            'customerInfo' => $customerInfo,
            'domainInfo' => $domainInfo,
            'template' => $template,
            'subscriptionPlan' => $subscriptionPlan,
            'addons' => $addons,
            'subscriptionAmount' => $subscriptionAmount,
            'addonsAmount' => $addonsAmount,
            'domainAmount' => $domainAmount ?? 0,
            'totalAmount' => $totalAmount
        ]);

        return view('checkout.billing', compact(
            'tripayTransaction',
            'channelDetails',
            'customerInfo',
            'template',
            'subscriptionPlan',
            'domainInfo',
            'addons',
            'subscriptionAmount',
            'addonsAmount',
            'domainAmount',
            'totalAmount',
            'totalAmountWithFees',
            'order',
            'invoice'
        ));
    }

    public function submit(Request $request)
    {
        // Step 7: Final Payment Processing
        \Log::info('Checkout submit method called', [
            'payment_channel' => $request->input('payment_channel'),
            'all_data' => $request->all()
        ]);
        
        $request->validate([
            'payment_channel' => 'required|string',
        ]);

        // Get cart and migrate session data if needed
        $cart = $this->cartService->migrateFromSessionAndCookies($request);

        // Store payment channel in both session and cookies
        $request->session()->put('checkout.payment_channel', $request->input('payment_channel'));
        CheckoutCookieHelper::storePaymentChannel($request->input('payment_channel'));

        // Validate all required data exists in cart
        if (!$cart->template_id || !$cart->subscription_plan_id || 
            empty($cart->configuration['customer_info']) || empty($cart->domain_data)) {
            return redirect()->route('checkout.index')->withErrors('Data checkout tidak lengkap. Silakan mulai dari awal.');
        }

        // Get cart summary with all calculated totals
        $summary = $this->cartService->getCartSummary($cart);
        
        // Extract data for order creation
        $template = $cart->template;
        $subscriptionPlan = $cart->subscriptionPlan;
        $addons = $cart->addons;
        $customerInfo = $cart->configuration['customer_info'];
        $domainInfo = $cart->domain_data;
        
        $subscriptionAmount = $summary['subscription_amount'];
        $addonsAmount = $summary['addons_amount'];
        $totalAmount = $summary['total_amount'];

        try {
            \Log::info('Starting transaction for order creation');
            DB::beginTransaction();

            // Create or get user
            $user = Auth::user();
            if (!$user) {
                // For guest checkout, create user with provided password
                $user = User::firstOrCreate(
                    ['email' => $customerInfo['email']],
                    [
                        'name' => $customerInfo['full_name'] ?? $customerInfo['name'] ?? '',
                        'phone' => $customerInfo['phone'],
                        'password' => bcrypt($customerInfo['password'] ?? Str::random(12)), // Use provided password or random fallback
                    ]
                );
            }

            // Prepare domain data for order
            $domainName = '';
            $domainType = '';
            switch ($domainInfo['type']) {
                case 'new':
                    $domainName = $domainInfo['name'] ?? $domainInfo['domain_name'] ?? '';
                    $domainType = 'register_new';
                    break;
                case 'existing':
                    $domainName = $domainInfo['existing'] ?? $domainInfo['name'] ?? $domainInfo['domain_name'] ?? '';
                    $domainType = 'existing';
                    break;
            }

            // Generate unique order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            
            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'template_id' => $cart->template_id,
                'subscription_plan_id' => $cart->subscription_plan_id,
                'order_type' => 'subscription',
                'subscription_amount' => $subscriptionAmount,
                'addons_amount' => $addonsAmount,
                'amount' => $totalAmount,
                'domain_name' => $domainName,
                'domain_type' => $domainType,
                'domain_details' => $domainInfo,
                'billing_cycle' => $subscriptionPlan->billing_cycle,
                'status' => 'pending',
            ]);

            // Create order addons
            foreach ($addons as $addon) {
                OrderAddon::create([
                    'order_id' => $order->id,
                    'product_addon_id' => $addon->id,
                    'price' => $addon->price,
                    'billing_cycle' => $subscriptionPlan->billing_cycle,
                    'quantity' => 1,
                    'addon_details' => $addon->toArray(),
                ]);
            }

            // Create Tripay transaction
            \Log::info('Creating Tripay transaction', ['order_id' => $order->id]);
            $orderCode = 'SUB-' . $order->id . '-' . time();
            $transactionData = [
                'method' => $request->input('payment_channel'),
                'merchant_ref' => $orderCode,
                'amount' => (int) $totalAmount,
                'customer_name' => $customerInfo['full_name'] ?? $customerInfo['name'] ?? '',
                'customer_email' => $customerInfo['email'],
                'customer_phone' => $customerInfo['phone'],
                'order_items' => [
                    [
                        'sku' => $orderCode,
                        'name' => 'Subscription ' . $template->name,
                        'price' => (int) $totalAmount,
                        'quantity' => 1,
                    ]
                ],
                'return_url' => route('checkout.success'),
                'expired_time' => (int) now()->addHours(24)->timestamp,
            ];
            
            $transaction = $this->tripayService->createTransaction($transactionData);

            \Log::info('Tripay transaction result', ['transaction' => $transaction]);

            // Check if transaction creation was successful
            if (!$transaction) {
                throw new \Exception('Gagal membuat transaksi pembayaran. Silakan coba lagi.');
            }

            \Log::info('Updating order with transaction reference', [
                'order_id' => $order->id,
                'tripay_reference' => $transaction['data']['reference'] ?? null,
                'tripay_merchant_ref' => $orderCode
            ]);

            // Update order with transaction reference
            $order->update([
                'tripay_reference' => $transaction['data']['reference'] ?? null,
                'tripay_merchant_ref' => $orderCode,
            ]);

            \Log::info('Order updated successfully');

            // Create invoice for the order
            \Log::info('Creating invoice for order', ['order_id' => $order->id]);
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT),
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'amount' => $order->amount,
                'tax_amount' => 0, // Assuming no tax for now
                'total_amount' => $order->amount,
                'fee_merchant' => $transaction['data']['fee_merchant'] ?? 0,
                'fee_customer' => $transaction['data']['fee_customer'] ?? 0,
                'total_fee' => $transaction['data']['total_fee'] ?? 0,
                'status' => 'sent',
                'due_date' => now()->addDays(7),
                'payment_method' => $request->input('payment_channel'),
                'description' => 'Invoice untuk ' . $order->order_type . ' - ' . ($order->domain_name ?? 'N/A'),
                'tripay_reference' => $transaction['data']['reference'] ?? null,
                'tripay_data' => [
                    'reference' => $transaction['data']['reference'] ?? null,
                    'merchant_ref' => $transaction['data']['merchant_ref'] ?? null,
                    'amount' => $transaction['data']['amount'] ?? null,
                    'status' => $transaction['data']['status'] ?? null,
                    'pay_code' => $transaction['data']['pay_code'] ?? null,
                    'checkout_url' => $transaction['data']['checkout_url'] ?? null,
                    'expired_time' => $transaction['data']['expired_time'] ?? null,
                    'fee_merchant' => $transaction['data']['fee_merchant'] ?? 0,
                    'fee_customer' => $transaction['data']['fee_customer'] ?? 0,
                    'total_fee' => $transaction['data']['total_fee'] ?? 0,
                    'instructions' => $transaction['data']['instructions'] ?? null,
                    'qr_string' => $transaction['data']['qr_string'] ?? null,
                    'qr_url' => $transaction['data']['qr_url'] ?? null,
                    'payment_method' => $transaction['data']['payment_method'] ?? null,
                    'payment_name' => $transaction['data']['payment_name'] ?? null,
                ],
            ]);
            \Log::info('Invoice created successfully', ['invoice_id' => $invoice->id]);

            // Store transaction details in session and cookies
            \Log::info('Storing transaction in session and cookies');
            $request->session()->put('checkout.tripay_transaction', $transaction);
            $request->session()->put('checkout.order_id', $order->id);
            CheckoutCookieHelper::storeTripayTransaction($transaction);

            \Log::info('Committing database transaction');
            DB::commit();

            \Log::info('Clearing checkout session data and cart');
            // Clear cart from database
            $cart->delete();
            
            // Clear checkout session data and cookies (except tripay transaction)
            $request->session()->forget([
                'checkout.template_id',
                'checkout.subscription_plan_id',
                'checkout.customer_info',
                'checkout.domain_info',
                'checkout.selected_addons'
            ]);
            
            // Clear checkout cookies (except tripay transaction)
            CheckoutCookieHelper::clearAll();

            \Log::info('Redirecting to checkout.billing');
            return redirect()->route('checkout.billing')->with('success', 'Pesanan berhasil dibuat! Silakan lanjutkan pembayaran.');
        } catch (\Exception $e) {
            \Log::error('Checkout submit failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::rollback();
            return redirect()->back()->withErrors('Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    public function success()
    {
        return view('checkout.success');
    }

    /**
     * Create test Tripay data for debugging
     */
    public function createTestTripayData()
    {
        // Sample Tripay transaction response structure
        $testTripayData = [
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => [
                'reference' => 'T123456789012345',
                'merchant_ref' => 'SUB-32-1761459282',
                'payment_selection_type' => 'static',
                'payment_method' => 'BRIVA',
                'payment_name' => 'BRI Virtual Account',
                'customer_name' => 'Test User',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
                'callback_url' => '',
                'return_url' => 'http://localhost:8000/checkout/success',
                'amount' => 2900000,
                'fee_merchant' => 4000,
                'fee_customer' => 0,
                'total_fee' => 4000,
                'amount_received' => 2896000,
                'pay_code' => '12345678901234567890',
                'pay_url' => null,
                'checkout_url' => 'https://tripay.co.id/checkout/T123456789012345',
                'status' => 'UNPAID',
                'expired_time' => time() + (24 * 60 * 60), // 24 hours from now
                'order_items' => [
                    [
                        'sku' => 'SUB-32-1761459282',
                        'name' => 'Subscription Artist Portfolio',
                        'price' => 2900000,
                        'quantity' => 1
                    ]
                ],
                'instructions' => [
                    [
                        'title' => 'BRI Virtual Account',
                        'steps' => [
                            'Login ke aplikasi BRI Mobile atau BRI Internet Banking',
                            'Pilih menu Transfer',
                            'Pilih Virtual Account',
                            'Masukkan nomor Virtual Account: 12345678901234567890',
                            'Masukkan nominal pembayaran: Rp 2.900.000',
                            'Ikuti instruksi selanjutnya untuk menyelesaikan pembayaran'
                        ]
                    ]
                ],
                'qr_string' => null,
                'qr_url' => null
            ]
        ];

        // Store test data
        CheckoutCookieHelper::storeTripayTransaction($testTripayData);
        
        // Store other required checkout data
        CheckoutCookieHelper::storeTemplate(1);
        CheckoutCookieHelper::storeSubscriptionPlan(1, 'monthly');
        CheckoutCookieHelper::storeCustomerInfo([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'password' => 'testpassword123'
        ]);
        CheckoutCookieHelper::storeDomain([
            'domain' => 'testdomain.com',
            'type' => 'new'
        ]);
        CheckoutCookieHelper::storePaymentChannel('BRIVA');
        CheckoutCookieHelper::storeAddons([1, 2]);

        return redirect()->route('checkout.billing')->with('success', 'Test data created successfully');
    }

    /**
     * Check Tripay transaction status via API
     */
    public function checkTripayStatus($reference)
    {
        try {
            $tripayResponse = $this->tripayService->getTransactionDetail($reference);
            
            if (!$tripayResponse || !$tripayResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check payment status'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'status' => $tripayResponse['data']['status'],
                'paid_at' => $tripayResponse['data']['paid_at'] ?? null,
                'amount_received' => $tripayResponse['data']['amount_received'] ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error checking Tripay status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status'
            ], 500);
        }
    }
}