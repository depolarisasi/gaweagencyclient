<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderAddon;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class GenerateRecurringAddonInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-recurring-addons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate combined recurring invoices for add-ons per order';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to generate recurring addon invoices...');

        // Ambil semua add-on aktif yang akan jatuh tempo dalam 14 hari
        $dueAddonsQuery = OrderAddon::where('status', 'active')
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', now()->addDays(14)->toDateString())
            ->with(['order', 'productAddon'])
            ->orderBy('order_id')
            ->orderBy('next_due_date');

        $generatedCount = 0;

        // Kelompokkan per order_id dan due_date
        $groups = [];
        $dueAddonsQuery->chunkById(200, function ($addons) use (&$groups) {
            foreach ($addons as $addon) {
                $key = $addon->order_id . '|' . Carbon::parse($addon->next_due_date)->toDateString();
                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'order_id' => $addon->order_id,
                        'due_date' => Carbon::parse($addon->next_due_date)->toDateString(),
                        'items' => [],
                    ];
                }
                $groups[$key]['items'][] = $addon;
            }
        });

        foreach ($groups as $groupKey => $group) {
            try {
                $order = Order::find($group['order_id']);
                if (!$order) {
                    $this->warn("Skipping group {$groupKey} - order not found");
                    continue;
                }

                // Idempoten: cek invoice gabungan add-ons untuk order+due_date
                $existing = Invoice::where('order_id', $order->id)
                    ->where('is_renewal', true)
                    ->where('renewal_type', 'addons')
                    ->whereDate('due_date', $group['due_date'])
                    ->whereIn('status', ['sent', 'paid', 'overdue', 'cancelled'])
                    ->exists();
                if ($existing) {
                    $this->info("Skip order {$order->id} - invoice addons for due {$group['due_date']} already exists");
                    continue;
                }

                // Bangun line items
                $lineItems = [];
                $subtotal = 0.0;
                foreach ($group['items'] as $addon) {
                    $name = $addon->productAddon->name ?? ($addon->addon_details['name'] ?? 'Addon');
                    $amount = (float) $addon->price * (int) ($addon->quantity ?? 1);
                    $subtotal += $amount;
                    $lineItems[] = [
                        'order_addon_id' => $addon->id,
                        'product_addon_id' => $addon->product_addon_id,
                        'description' => $name,
                        'amount' => $amount,
                        'quantity' => (int) ($addon->quantity ?? 1),
                        'billing_type' => 'recurring',
                        'billing_cycle' => $addon->billing_cycle,
                    ];
                }

                // Pajak 11%
                $taxAmount = round($subtotal * 0.11, 2);
                $totalAmount = round($subtotal + $taxAmount, 2);

                // Buat invoice gabungan untuk add-ons
                $invoice = Invoice::create([
                    'invoice_number' => 'INV-ADD-' . date('Ymd') . '-' . uniqid(),
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'amount' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'status' => 'sent',
                    'due_date' => Carbon::parse($group['due_date']),
                    'is_renewal' => true,
                    'renewal_type' => 'addons',
                    // Periode tidak seragam antar add-on, biarkan null
                    'billing_period_start' => null,
                    'billing_period_end' => null,
                    'items_snapshot' => $lineItems,
                ]);

                // Simpan invoice_items satu baris per add-on
                foreach ($lineItems as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'order_id' => $order->id,
                        'order_addon_id' => $item['order_addon_id'],
                        'product_addon_id' => $item['product_addon_id'],
                        'item_type' => 'addon',
                        'description' => $item['description'],
                        'amount' => $item['amount'],
                        'quantity' => $item['quantity'],
                        'billing_type' => $item['billing_type'],
                        'billing_cycle' => $item['billing_cycle'],
                    ]);
                }

                // Notifikasi ke user
                try {
                    Notification::send($order->user, new \App\Notifications\InvoiceGeneratedNotification($invoice));
                } catch (\Throwable $notifyErr) {
                    Log::warning('Failed to send InvoiceGeneratedNotification (addons)', [
                        'invoice_id' => $invoice->id,
                        'error' => $notifyErr->getMessage(),
                    ]);
                }

                $generatedCount++;

                Log::info('Recurring addons invoice generated', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $totalAmount,
                    'due_date' => $invoice->due_date,
                    'items_count' => count($lineItems),
                ]);

                $this->info("Generated addons invoice: {$invoice->invoice_number} for order: {$order->order_number} (Items: " . count($lineItems) . ")");
            } catch (\Exception $e) {
                $this->error("Failed to generate addons invoice for group {$groupKey} - {$e->getMessage()}");
                Log::error('Failed to generate recurring addons invoice', [
                    'group' => $group,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Completed! Generated {$generatedCount} recurring addon invoices.");
        return Command::SUCCESS;
    }
}