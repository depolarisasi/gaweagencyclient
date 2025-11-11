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
use App\Notifications\OrderCreatedWithInvoiceNotification;
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

    /**
     * Hitung next_due_date untuk add-on berdasarkan subscription plan.
     */
    private function calculateAddonNextDueDate(?\App\Models\SubscriptionPlan $plan, $baseDate)
    {
        // Sederhanakan: semua add-on ditagihkan per bulan
        $date = \Carbon\Carbon::parse($baseDate);
        return $date->copy()->addMonth()->toDateString();
    }

    public function index(Request $request)
    {
        // Mulai selalu dari langkah Domain sesuai flow baru
        return redirect()->route('checkout.domain');
    }

    public function template(Request $request)
    {
        // Halaman pemilihan template (langkah 2 dalam flow baru)
        $cart = $this->cartService->migrateFromSessionAndCookies($request);

        // Guard: pastikan domain sudah dipilih sebelumnya
        if (empty($cart->domain_data)) {
            return redirect()->route('checkout.domain')->withErrors('Silakan pilih dan verifikasi domain terlebih dahulu.');
        }

        $templates = Template::active()->orderBy('sort_order')->get();
        $selectedTemplateId = $cart->template_id
            ?: (CheckoutCookieHelper::getTemplateId() ?? $request->query('id') ?? $request->session()->get('checkout.template_id'));
        return view('checkout.template', compact('templates', 'selectedTemplateId'));
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
        
        // Guard sesuai flow baru: domain & info pelanggan harus sudah ada, dan template dipilih
        if (!$request->isMethod('post')) {
            if (empty($cart->domain_data)) {
                return redirect()->route('checkout.domain')->withErrors('Silakan pilih domain terlebih dahulu.');
            }
            if (!$cart->template_id) {
                return redirect()->route('checkout.template')->withErrors('Silakan pilih template terlebih dahulu.');
            }
            if (empty($cart->configuration['customer_info'])) {
                return redirect()->route('checkout.personal-info')->withErrors('Lengkapi data personal terlebih dahulu.');
            }
        }
        
        if ($request->isMethod('post')) {
            // Handle POST request - save configuration and selected add-ons, then redirect to summary
            $request->validate([
                'subscription_plan_id' => 'required|exists:subscription_plans,id',
                'billing_cycle' => 'required|in:monthly,6_months,annually,2_years,3_years',
                'selected_addons' => 'array',
                'selected_addons.*' => 'exists:product_addons,id',
            ]);

            // Update cart with subscription plan and billing cycle
            $this->cartService->updateSubscriptionPlan($cart, $request->subscription_plan_id, $request->billing_cycle);

            // Sync selected add-ons (optional)
            $selectedAddons = $request->input('selected_addons', []);
            if (!empty($selectedAddons)) {
                $this->cartService->syncAddons($cart, $selectedAddons);
            } else {
                // Clear addons if none selected
                $cart->clearAddons();
                $cart->calculateTotals();
                $cart->save();
            }

            // Store in session and cookies for backward compatibility
            $request->session()->put('checkout.subscription_plan_id', $request->subscription_plan_id);
            $request->session()->put('checkout.billing_cycle', $request->billing_cycle);
            $request->session()->put('checkout.selected_addons', $selectedAddons);
            CheckoutCookieHelper::storeSubscriptionPlan($request->subscription_plan_id, $request->billing_cycle);
            CheckoutCookieHelper::storeAddons($selectedAddons);

            return redirect()->route('checkout.summary');
        }
        
        // Handle GET request - display configuration page
        $template = $cart->template;
        if (!$template) {
            return redirect()->route('checkout.template')->withErrors('Template tidak ditemukan.');
        }
        
        $subscriptionPlans = SubscriptionPlan::active()->get();
        
        // Load available add-ons and pre-selected addon IDs from cart (for single-page flow)
        $addons = ProductAddon::active()->orderBy('sort_order')->get();
        $selectedAddonIds = $cart->addons->pluck('id')->toArray();

        return view('checkout.configure', compact('template', 'subscriptionPlans', 'addons', 'selectedAddonIds'));
    }

    public function addons(Request $request)
    {
        // Step 3: Addons - Product addon selection (optional)
        
        // Get cart and migrate session data if needed
        $cart = $this->cartService->migrateFromSessionAndCookies($request);
        
        // Guard: domain dan template harus ada
        if (!$request->isMethod('post')) {
            if (empty($cart->domain_data)) {
                return redirect()->route('checkout.domain')->withErrors('Silakan pilih domain terlebih dahulu.');
            }
            if (!$cart->template_id) {
                return redirect()->route('checkout.template')->withErrors('Silakan pilih template terlebih dahulu.');
            }
        }
        
        if ($request->isMethod('post')) {
            // Kompatibilitas lama: proses add-ons lalu arahkan ke ringkasan
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
            
            return redirect()->route('checkout.summary');
        }
        
        // Handle GET request - alihkan ke halaman configure (UI add-ons sudah digabung)
        return redirect()->route('checkout.configure');
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

            // Setelah domain disimpan, arahkan ke langkah pemilihan template (Step 2)
            return redirect()->route('checkout.template')->with('success', 'Domain berhasil disimpan. Silakan pilih template.');
        }

        // Handle GET request - display domain selection page (tanpa guard template/plans)
        $template = $cart->template; // bisa null, view menangani kondisi ini
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
            
            // Setelah info personal, lanjutkan ke konfigurasi paket
            return redirect()->route('checkout.configure');
        }
        
        // Handle GET request - display personal info page
        // Guard sesuai flow: domain dan template harus ada, paket belum wajib
        if (empty($cart->domain_data)) {
            return redirect()->route('checkout.domain')->withErrors('Silakan pilih domain terlebih dahulu.');
        }
        if (!$cart->template_id) {
            return redirect()->route('checkout.template')->withErrors('Silakan pilih template terlebih dahulu.');
        }

        $template = $cart->template;
        
        // Prefill otomatis untuk user yang sudah login bila belum ada customer_info di cart
        if (auth()->check() && empty($cart->configuration['customer_info'])) {
            $user = auth()->user();
            $autoInfo = [
                'full_name' => $user->name ?? ($user->full_name ?? ''),
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'company' => $user->company ?? '',
                'user_id' => $user->id ?? null,
                'is_logged_in' => true,
            ];

            $this->cartService->updateCustomerInfo($cart, $autoInfo);
            CheckoutCookieHelper::storeCustomerInfo($autoInfo);
            $customerInfo = $autoInfo;
        } else {
            // Get existing customer info from cart for pre-filling form
            $customerInfo = $cart->configuration['customer_info'] ?? [];
        }
        
        return view('checkout.personal-info', compact('template', 'customerInfo'));
    }

    public function summary(Request $request)
    {
        // Step 5: Order Summary - Display summary and handle payment method selection
        // Server-side guard: jika state billing sudah terbentuk, paksa mulai dari awal
        $tripayRefCookie = \App\Helpers\CheckoutCookieHelper::getTripayReference();
        $tripayRefSession = $request->session()->get('checkout.tripay_reference');
        $tripayTxCookie = \App\Helpers\CheckoutCookieHelper::getTripayTransaction();
        $tripayTxSession = $request->session()->get('checkout.tripay_transaction');

        if ($tripayRefCookie || $tripayRefSession || $tripayTxCookie || $tripayTxSession) {
            \Log::info('Summary guard triggered: billing state detected, redirecting to checkout.index', [
                'tripay_reference_cookie' => (bool) $tripayRefCookie,
                'tripay_reference_session' => (bool) $tripayRefSession,
                'tripay_transaction_cookie' => (bool) $tripayTxCookie,
                'tripay_transaction_session' => (bool) $tripayTxSession,
            ]);
            return redirect()->route('checkout.index')->withErrors('Anda sudah berada di tahap pembayaran. Silakan mulai dari awal.');
        }
        
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

        // Fallback: hitung subtotal add-ons dari relasi jika belum terisi atau nol
        if ((!isset($addonsAmount) || (float) $addonsAmount <= 0) && isset($addons)) {
            $calculatedAddonsAmount = 0.0;
            foreach ($addons as $addon) {
                $calculatedAddonsAmount += (float) ($addon->pivot->price ?? $addon->price ?? 0);
            }
            if ($calculatedAddonsAmount > 0) {
                $addonsAmount = $calculatedAddonsAmount;
            }
        }
        
        // Normalisasi nama dan tipe domain untuk kompatibilitas tampilan
        // Nama domain: fallback ke domain_name → name → domain
        $domainInfo['domain_name'] = $domainInfo['domain_name']
            ?? $domainInfo['name']
            ?? ($domainInfo['domain'] ?? '');

        // Tipe domain: normalisasi nilai agar konsisten di view
        $rawDomainType = $domainInfo['domain_type']
            ?? $domainInfo['type']
            ?? ($order ? $order->domain_type : null);

        $normalizedDomainType = match ($rawDomainType) {
            'register_new' => 'new',
            'new' => 'new',
            'existing' => 'existing',
            'transfer' => 'transfer',
            default => 'unknown',
        };

        $domainInfo['domain_type'] = $normalizedDomainType;
        $domainInfo['type'] = $normalizedDomainType;
        
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
        $domainAmount = $summary['domain_amount'];
        $totalAmount = $summary['total_amount'];

        // Normalisasi add-ons: gunakan harga pivot (harga aktual di keranjang)
        $calculatedAddonsAmount = 0.0;
        foreach ($addons as $addon) {
            $calculatedAddonsAmount += (float) ($addon->pivot->price ?? $addon->price ?? 0);
        }
        if ($calculatedAddonsAmount > 0) {
            $addonsAmount = $calculatedAddonsAmount;
        }
        // Pastikan total sama dengan komponen-komponen
        $totalAmount = (float) $subscriptionAmount + (float) $domainAmount + (float) $addonsAmount;

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
                // Normalisasi: hanya add-on berulang yang memiliki billing_cycle 'monthly'
                $isRecurring = ($addon->billing_type === 'recurring');

                OrderAddon::create([
                    'order_id' => $order->id,
                    'product_addon_id' => $addon->id,
                    // Gunakan harga pivot jika tersedia agar konsisten dengan keranjang
                    'price' => $addon->pivot->price ?? $addon->price,
                    'billing_cycle' => $isRecurring ? 'monthly' : null,
                    'quantity' => 1,
                    'addon_details' => $addon->toArray(),
                    // Status awal pending sampai invoice dibayar
                    'status' => 'pending',
                    // Inisialisasi tanggal mulai agar tidak null; next_due_date hanya untuk recurring
                    'started_at' => now(),
                    'next_due_date' => $isRecurring ? $this->calculateAddonNextDueDate($subscriptionPlan, now()) : null,
                ]);
            }

            // Create Tripay transaction
            \Log::info('Creating Tripay transaction', ['order_id' => $order->id]);
            $orderCode = 'SUB-' . $order->id . '-' . time();
            // Bangun itemisasi untuk Tripay agar jelas breakdown biaya
            $orderItems = [];
            $orderItems[] = [
                'sku' => $orderCode . '-SUB',
                'name' => 'Subscription ' . $template->name,
                'price' => (int) $subscriptionAmount,
                'quantity' => 1,
            ];
            if ($domainAmount > 0) {
                $orderItems[] = [
                    'sku' => $orderCode . '-DOM',
                    'name' => 'Domain Registration',
                    'price' => (int) $domainAmount,
                    'quantity' => 1,
                ];
            }
            foreach ($addons as $addon) {
                $orderItems[] = [
                    'sku' => $orderCode . '-ADD-' . $addon->id,
                    'name' => $addon->name,
                    // Gunakan harga pivot jika tersedia agar konsisten dengan keranjang
                    'price' => (int) ($addon->pivot->price ?? $addon->price ?? 0),
                    'quantity' => 1,
                ];
            }

            // Sanity check: pastikan jumlah item sama dengan amount
            $orderItemsSum = array_reduce($orderItems, function ($carry, $item) {
                return $carry + ((int) $item['price'] * (int) ($item['quantity'] ?? 1));
            }, 0);
            if ($orderItemsSum !== (int) $totalAmount) {
                \Log::warning('Normalizing amount: order_items sum != amount', [
                    'order_items_sum' => $orderItemsSum,
                    'amount_before' => (int) $totalAmount,
                ]);
                $totalAmount = (float) $orderItemsSum;
                // Sinkronkan juga nilai order agar konsisten (sebelum membuat invoice)
                $order->update(['amount' => (int) $totalAmount]);
            }

            $transactionData = [
                'method' => $request->input('payment_channel'),
                'merchant_ref' => $orderCode,
                'amount' => (int) $totalAmount,
                'customer_name' => $customerInfo['full_name'] ?? $customerInfo['name'] ?? '',
                'customer_email' => $customerInfo['email'],
                'customer_phone' => $customerInfo['phone'] ?? '08123456789',
                'order_items' => $orderItems,
                'return_url' => route('checkout.success'),
                'callback_url' => route('payment.callback'),
                'expired_time' => (int) now()->addHours(24)->timestamp,
            ];
            
            $transaction = $this->tripayService->createTransaction($transactionData);

            \Log::info('Tripay transaction result', ['transaction' => $transaction]);

            // Check if transaction creation was successful
            if (!$transaction || (isset($transaction['success']) && !$transaction['success'])) {
                $message = is_array($transaction)
                    ? ($transaction['message'] ?? 'Gagal membuat transaksi pembayaran. Silakan coba lagi.')
                    : 'Gagal membuat transaksi pembayaran. Silakan coba lagi.';
                throw new \Exception($message);
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

            // Send order created + invoice notification to client
            try {
                if ($user) {
                    $user->notify(new OrderCreatedWithInvoiceNotification($order, $invoice));
                    \Log::info('OrderCreatedWithInvoiceNotification sent', [
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'invoice_id' => $invoice->id
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to send OrderCreatedWithInvoiceNotification', [
                    'user_id' => $user->id ?? null,
                    'order_id' => $order->id ?? null,
                    'invoice_id' => $invoice->id ?? null,
                    'message' => $e->getMessage()
                ]);
            }

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
     * Reset seluruh data checkout (session, cookies, cart) untuk memulai dari awal.
     * Dipanggil saat user mencoba kembali dari halaman billing.
     */
    public function reset(Request $request)
    {
        \Log::info('Checkout reset triggered');

        try {
            // Hapus semua cart terkait user/session tanpa membuat cart baru.
            if (\Illuminate\Support\Facades\Auth::check()) {
                $userId = \Illuminate\Support\Facades\Auth::id();
                \App\Models\Cart::forUser($userId)->delete();
            } else {
                $sessionId = $request->session()->getId();
                \App\Models\Cart::forSession($sessionId)->delete();
            }

            // Bersihkan seluruh data session checkout termasuk payment
            $request->session()->forget([
                'checkout.template_id',
                'checkout.subscription_plan_id',
                'checkout.billing_cycle',
                'checkout.customer_info',
                'checkout.domain',
                'checkout.selected_addons',
                'checkout.payment_channel',
                'checkout.tripay_transaction',
                'checkout.tripay_reference',
                'checkout.order_id',
            ]);

            // Hapus semua cookies checkout termasuk payment
            \App\Helpers\CheckoutCookieHelper::clearAllForce();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Log::error('Checkout reset failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Reset gagal'], 500);
        }
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

            // Sinkronkan status invoice bila ada dan status final
            try {
                $status = $tripayResponse['data']['status'] ?? null;
                $finalStatuses = ['PAID', 'EXPIRED', 'FAILED', 'REFUND'];
                if ($status && in_array($status, $finalStatuses)) {
                    // Temukan invoice berdasarkan reference
                    $invoice = Invoice::where('tripay_reference', $reference)->first();
                    if ($invoice) {
                        $statusMapping = config('tripay.status_mapping');
                        $newStatus = $statusMapping[$status] ?? 'pending';

                        $updateData = [
                            'status' => $newStatus,
                            'tripay_data' => $tripayResponse['data'],
                        ];

                        if ($status === 'PAID') {
                            $updateData['paid_date'] = now();
                        }

                        $invoice->update($updateData);

                        // Aktifkan order & project bila pembayaran sukses
                        if ($status === 'PAID') {
                            try {
                                // Aktifkan order jika masih pending
                                $order = \App\Models\Order::find($invoice->order_id);
                                if ($order && $order->status === 'pending') {
                                    $order->status = 'active';
                                    $order->activated_at = now();
                                    if (!$order->next_due_date) {
                                        $order->next_due_date = $order->calculateNextDueDate();
                                    }
                                    $order->save();
                                }

                                $project = \App\Models\Project::where('order_id', $invoice->order_id)->first();
                                if ($project && $project->status === 'pending') {
                                    $project->update([
                                        'status' => 'active',
                                        'start_date' => now(),
                                    ]);
                                } elseif (!$project && isset($order)) {
                                    // Buat project otomatis bila belum ada
                                    $baseName = '';
                                    if ($order->domain_name) {
                                        $baseName = 'Website for ' . $order->domain_name;
                                    } elseif ($order->template) {
                                        $baseName = $order->template->name . ' Project';
                                    } elseif ($order->product) {
                                        $baseName = $order->product->name . ' Project';
                                    } else {
                                        $baseName = 'Project';
                                    }
                                    if ($order->user) {
                                        $baseName .= ' for ' . $order->user->name;
                                    }

                                    $websiteUrl = $order->domain_name ? ('https://' . $order->domain_name) : null;

                                    \App\Models\Project::create([
                                        'project_name' => $baseName,
                                        'user_id' => $order->user_id,
                                        'order_id' => $order->id,
                                        'template_id' => $order->template_id,
                                        'status' => 'active',
                                        'website_url' => $websiteUrl,
                                        'start_date' => now(),
                                    ]);
                                }
                                // Kirim notifikasi sukses pembayaran
                                if ($invoice->user) {
                                    $invoice->user->notify(new \App\Notifications\PaymentSuccessful($invoice));
                                }
                            } catch (\Throwable $e) {
                                \Log::warning('Failed to activate project or send notification (polling)', [
                                    'invoice_id' => $invoice->id,
                                    'message' => $e->getMessage(),
                                ]);
                            }
                        }

                        \Log::info('Invoice status synchronized via polling', [
                            'invoice_id' => $invoice->id,
                            'reference' => $reference,
                            'tripay_status' => $status,
                            'new_status' => $newStatus,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('checkTripayStatus sync failed', [
                    'reference' => $reference,
                    'message' => $e->getMessage(),
                ]);
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