<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrderAddon;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CancelOverdueAddons extends Command
{
    protected $signature = 'addons:cancel-overdue';
    protected $description = 'Auto-cancel add-ons when renewal invoices are overdue by 14+ days';

    public function handle()
    {
        $this->info('Starting overdue add-ons cancellation...');

        $threshold = Carbon::now()->subDays(14)->toDateString();

        $overdueInvoicesQuery = Invoice::where('is_renewal', true)
            ->where('renewal_type', 'addons')
            ->where('status', 'overdue')
            ->whereDate('due_date', '<', $threshold)
            ->orderBy('id');

        $cancelledCount = 0;

        $overdueInvoicesQuery->chunkById(100, function ($invoices) use (&$cancelledCount) {
            foreach ($invoices as $invoice) {
                try {
                    $items = InvoiceItem::where('invoice_id', $invoice->id)
                        ->where('item_type', 'addon')
                        ->get();

                    foreach ($items as $item) {
                        if (!$item->order_addon_id) {
                            continue;
                        }
                        $addon = OrderAddon::find($item->order_addon_id);
                        if (!$addon) {
                            continue;
                        }
                        if ($addon->status === 'cancelled') {
                            continue;
                        }
                        $addon->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                        ]);
                        $cancelledCount++;
                    }

                    // Update invoice menjadi cancelled setelah semua add-ons dicancel
                    $invoice->update(['status' => 'cancelled']);

                    Log::info('Cancelled overdue addons for invoice', [
                        'invoice_id' => $invoice->id,
                        'due_date' => $invoice->due_date,
                        'order_id' => $invoice->order_id,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to cancel overdue addons', [
                        'invoice_id' => $invoice->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Completed! Cancelled {$cancelledCount} overdue add-ons.");
        return Command::SUCCESS;
    }
}