<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;

class TestPaymentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find or create a test user
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Find or create a test order
        $order = Order::first();
        if (!$order) {
            $order = Order::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        // Create test invoices with payment data
        $invoice1 = Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 500000,
            'tax_amount' => 0,
            'total_amount' => 500000,
            'status' => 'sent',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'payment_method' => 'BRIVA',
            'tripay_reference' => 'T123456789001',
            'tripay_merchant_ref' => 'INV-TEST-001',
            'payment_code' => '88810123456789001',
            'payment_expired_at' => now()->addHours(24),
            'tripay_data' => json_encode([
                'reference' => 'T123456789001',
                'merchant_ref' => 'INV-TEST-001',
                'payment_selection_type' => 'static',
                'payment_method' => 'BRIVA',
                'payment_name' => 'BRI Virtual Account',
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => '081234567890',
                'callback_url' => 'https://example.com/callback',
                 'return_url' => 'https://example.com/return',
                'amount' => 500000,
                'fee_merchant' => 4000,
                'fee_customer' => 0,
                'total_fee' => 4000,
                'amount_received' => 496000,
                'pay_code' => '88810123456789001',
                'pay_url' => null,
                'checkout_url' => null,
                'status' => 'UNPAID',
                'expired_time' => now()->addHours(24)->timestamp,
                'order_items' => [
                    [
                        'sku' => 'WEBSITE-BASIC',
                        'name' => 'Website Basic Package',
                        'price' => 500000,
                        'quantity' => 1,
                        'product_url' => null,
                        'image_url' => null
                    ]
                ],
                'instructions' => [
                    [
                        'title' => 'BRI Virtual Account',
                        'steps' => [
                            'Login ke aplikasi BRI Mobile atau BRImo',
                            'Pilih menu Transfer',
                            'Pilih Virtual Account',
                            'Masukkan nomor Virtual Account: 88810123456789001',
                            'Masukkan nominal: Rp 500.000',
                            'Konfirmasi pembayaran'
                        ]
                    ]
                ],
                'qr_string' => null,
                'qr_url' => null
            ])
        ]);

        $invoice2 = Invoice::create([
            'invoice_number' => 'INV-TEST-002',
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 750000,
            'tax_amount' => 0,
            'total_amount' => 750000,
            'status' => 'sent',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'payment_method' => 'QRIS',
            'tripay_reference' => 'T123456789002',
            'tripay_merchant_ref' => 'INV-TEST-002',
            'payment_code' => null,
            'payment_expired_at' => now()->addMinutes(30),
            'tripay_data' => json_encode([
                'reference' => 'T123456789002',
                'merchant_ref' => 'INV-TEST-002',
                'payment_selection_type' => 'static',
                'payment_method' => 'QRIS',
                'payment_name' => 'QRIS',
                'customer_name' => $user->name,
                 'customer_email' => $user->email,
                 'customer_phone' => '081234567890',
                 'callback_url' => 'https://example.com/callback',
                 'return_url' => 'https://example.com/return',
                'amount' => 750000,
                'fee_merchant' => 5500,
                'fee_customer' => 0,
                'total_fee' => 5500,
                'amount_received' => 744500,
                'pay_code' => null,
                'pay_url' => null,
                'checkout_url' => null,
                'status' => 'UNPAID',
                'expired_time' => now()->addMinutes(30)->timestamp,
                'order_items' => [
                    [
                        'sku' => 'WEBSITE-PREMIUM',
                        'name' => 'Website Premium Package',
                        'price' => 750000,
                        'quantity' => 1,
                        'product_url' => null,
                        'image_url' => null
                    ]
                ],
                'instructions' => [
                    [
                        'title' => 'QRIS',
                        'steps' => [
                            'Buka aplikasi mobile banking atau e-wallet',
                            'Pilih menu Scan QR atau QRIS',
                            'Scan QR Code yang tersedia',
                            'Periksa detail pembayaran',
                            'Konfirmasi pembayaran'
                        ]
                    ]
                ],
                'qr_string' => '00020101021226670016COM.NOBUBANK.WWW01189360050300000898240214545455000000000303UME51440014ID.CO.QRIS.WWW0215ID20232959382950303UME5204481253033605802ID5909Test User6007Jakarta61051234562070703A0163044B5A',
                'qr_url' => 'https://tripay.co.id/qr-image?d=00020101021226670016COM.NOBUBANK.WWW01189360050300000898240214545455000000000303UME51440014ID.CO.QRIS.WWW0215ID20232959382950303UME5204481253033605802ID5909Test User6007Jakarta61051234562070703A0163044B5A'
            ])
        ]);

        $this->command->info('Test payment data created successfully!');
        $this->command->info('VA Invoice ID: ' . $invoice1->id . ' - Virtual Account: 88810123456789001');
        $this->command->info('QRIS Invoice ID: ' . $invoice2->id . ' - QRIS Payment');
    }
}
