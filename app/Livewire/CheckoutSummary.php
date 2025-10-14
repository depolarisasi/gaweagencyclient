<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Models\Order;
use App\Models\Project;
use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewProjectNotification;

class CheckoutSummary extends Component
{
    public $cart;
    public $template;
    public $product;
    public $totalPrice;

    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $domain;
    public $isRegistered = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'domain' => 'required|string|max:255|unique:projects,domain',
    ];

    public function mount()
    {
        $this->cart = Session::get('cart');

        if (!$this->cart || !isset($this->cart['template_id']) || !isset($this->cart['product_id'])) {
            return redirect('/'); // Kembali jika keranjang kosong atau tidak lengkap
        }

        // Periksa masa berlaku keranjang (7 hari)
        $cartTimestamp = $this->cart['created_at'] ?? 0;
        if (now()->timestamp - $cartTimestamp > (7 * 24 * 60 * 60)) { // 7 hari dalam detik
            Session::forget('cart');
            return redirect()->route('welcome')->with('error', 'Keranjang Anda telah kedaluwarsa.');
        }

        $this->template = Template::findOrFail($this->cart['template_id']);
        $this->product = Product::findOrFail($this->cart['product_id']);

        $this->totalPrice = $this->template->price + $this->product->price; // Tambahkan logika add-on nanti

        if (Auth::check()) {
            $this->isRegistered = true;
            $this->name = Auth::user()->name;
            $this->email = Auth::user()->email;
        }
    }

    public function registerAndCheckout()
    {
        if (!$this->isRegistered) {
            $this->validate();

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            Auth::login($user);
        }

        $this->checkout();
    }

    public function checkout()
    {
        // Logika untuk membuat pesanan dan invoice akan ditambahkan di sini
        $order = Order::create([
            'user_id' => Auth::id(),
            'template_id' => $this->template->id,
            'product_id' => $this->product->id,
            'total_price' => $this->totalPrice,
            'status' => 'pending',
        ]);

        Invoice::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'amount' => $this->totalPrice,
            'due_date' => Carbon::now()->addDays(7),
            'status' => 'unpaid',
        ]);

        $project = Project::create([
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'template_id' => $this->template->id,
            'domain' => $this->domain,
            'status' => 'pending',
        ]);

        // Get admin users and send notification
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new NewProjectNotification($project));

        Session::forget('cart');
        Session::flash('success', 'Pesanan Anda telah berhasil dibuat! Silakan lanjutkan ke pembayaran.');
        return redirect('/'); // Ganti dengan rute pembayaran atau sukses
    }

    public function render()
    {
        return view('livewire.checkout-summary');
    }
}
