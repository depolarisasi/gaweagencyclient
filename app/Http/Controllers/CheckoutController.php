<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Template;
use App\Models\ProductAddon;
use App\Services\TripayService;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        $this->tripayService = $tripayService;
    }

    public function index(Request $request)
    {
        // Logic to display the first step of the checkout process
        // This might involve selecting a template or product
        $templates = Template::all();
        $products = Product::active()->orderBy('sort_order')->get();
        return view('checkout.step1', compact('templates', 'products'));
    }

    public function step1(Request $request)
    {
        // Handle template and product selection
        // Validate input and store in session
        $request->validate([
            'template_id' => 'nullable|exists:templates,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $request->session()->put('checkout.template_id', $request->input('template_id'));
        $request->session()->put('checkout.product_id', $request->input('product_id'));

        return redirect()->route('checkout.step2');
    }

    public function step2(Request $request)
    {
        // Display add-ons selection
        $selectedProduct = Product::find($request->session()->get('checkout.product_id'));
        if (!$selectedProduct) {
            return redirect()->route('checkout.index')->withErrors('Produk belum dipilih.');
        }
        $addons = ProductAddon::active()->orderBy('sort_order')->get();
        return view('checkout.step2', compact('selectedProduct', 'addons'));
    }

    public function step3(Request $request)
    {
        // Handle add-ons selection and display customer info form
        $request->validate([
            'addons' => 'nullable|array',
            'addons.*' => 'exists:product_addons,id',
        ]);
        $request->session()->put('checkout.selected_addons', $request->input('addons', []));
        return view('checkout.step3');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Store customer information in session
        $request->session()->put('checkout.customer_info', [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ]);

        // Retrieve all checkout data from session
        $templateId = $request->session()->get('checkout.template_id');
        $productId = $request->session()->get('checkout.product_id');
        $selectedAddons = $request->session()->get('checkout.selected_addons', []);
        $customerInfo = $request->session()->get('checkout.customer_info');

        $product = Product::find($productId);
        $addons = ProductAddon::whereIn('id', $selectedAddons)->get();

        $amount = $product->price;
        foreach ($addons as $addon) {
            $amount += $addon->price;
        }

        // For now, let's assume a default payment channel. Later, we'll add a step for selection.
        $paymentChannel = 'BRIVA'; // Example: BRI Virtual Account

        try {
            $transaction = $this->tripayService->createTransaction(
                $amount,
                'ORDER-' . uniqid(), // Generate a unique order code
                $customerInfo['name'],
                $customerInfo['email'],
                $customerInfo['phone'],
                $paymentChannel
            );

            // Store transaction details in session or database if needed
            $request->session()->put('checkout.tripay_transaction', $transaction);

            // Redirect to a payment instruction page or directly to Tripay if applicable
            return redirect()->route('checkout.success')->with('success', 'Pesanan berhasil dibuat! Silakan lanjutkan pembayaran.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Gagal membuat transaksi pembayaran: ' . $e->getMessage());
        }
    }

    public function success()
    {
        return view('checkout.success');
    }
}