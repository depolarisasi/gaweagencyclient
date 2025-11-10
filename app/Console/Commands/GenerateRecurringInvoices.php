<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate recurring invoices for subscription renewals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to generate recurring invoices...');
        
        // Find orders that need renewal invoices
        // Check orders where next_due_date is within the next 14 days
        // Note: idempotensi dicek per-order saat loop; hindari filter created_at yang bisa salah-positif
        $upcomingRenewalsQuery = Order::where('status', 'active')
            ->where('order_type', 'subscription')
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<=', now()->addDays(14))
            ->with(['user', 'product', 'project'])
            ->orderBy('id');

        $generatedCount = 0;

        $upcomingRenewalsQuery->chunkById(100, function ($orders) use (&$generatedCount) {
            foreach ($orders as $order) {
                try {
                    // Skip if project is not active
                    if ($order->project && $order->project->status !== 'active') {
                        $this->info("Skipping order ID: {$order->id} - Project not active (status: {$order->project->status})");
                        continue;
                    }
                
                // Calculate next billing period
                $nextDueDate = $this->calculateNextDueDate($order->billing_cycle, $order->next_due_date);

                // Idempoten: hindari duplikasi invoice pada periode yang sama
                $periodStart = Carbon::parse($order->next_due_date)->toDateString();
                $existingSamePeriod = Invoice::where('order_id', $order->id)
                    ->whereDate('billing_period_start', $periodStart)
                    ->whereIn('status', ['sent', 'paid', 'overdue', 'cancelled'])
                    ->exists();
                if ($existingSamePeriod) {
                    $this->info("Skipping order ID: {$order->id} - invoice for period {$periodStart} already exists");
                    continue;
                }
                
                // Calculate tax (PPN 11%)
                $taxAmount = round($order->amount * 0.11, 2);
                $totalAmount = round($order->amount + $taxAmount, 2);
                
                // Create renewal invoice
                $invoice = Invoice::create([
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . uniqid(),
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'amount' => $order->amount,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'status' => 'sent',
                    // Jatuh tempo sama dengan awal periode berikut (next_due_date saat ini)
                    'due_date' => Carbon::parse($order->next_due_date),
                    'is_renewal' => true,
                    'billing_period_start' => Carbon::parse($order->next_due_date),
                    'billing_period_end' => $nextDueDate,
                ]);
                
                // Kirim notifikasi invoice baru (Invoice Generated)
                try {
                    \Notification::send($order->user, new \App\Notifications\InvoiceGeneratedNotification($invoice));
                } catch (\Throwable $notifyErr) {
                    Log::warning('Failed to send InvoiceGeneratedNotification', [
                        'invoice_id' => $invoice->id,
                        'error' => $notifyErr->getMessage(),
                    ]);
                }
                
                $generatedCount++;
                
                Log::info('Recurring invoice generated', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $totalAmount,
                    'due_date' => $invoice->due_date,
                    'billing_period' => [
                        'start' => $invoice->billing_period_start,
                        'end' => $invoice->billing_period_end
                    ]
                ]);
                
                $this->info("Generated renewal invoice: {$invoice->invoice_number} for order: {$order->order_number} (Amount: Rp " . number_format($totalAmount, 0, ',', '.') . ")");
                
            } catch (\Exception $e) {
                $this->error("Failed to generate renewal invoice for order ID: {$order->id} - {$e->getMessage()}");
                
                Log::error('Failed to generate recurring invoice', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
            }
        });
        
        $this->info("Completed! Generated {$generatedCount} recurring invoices.");
        
        return Command::SUCCESS;
    }
    
    /**
     * Calculate next due date based on billing cycle
     */
    private function calculateNextDueDate($billingCycle, $currentDueDate)
    {
        $date = Carbon::parse($currentDueDate);
        
        return match($billingCycle) {
            'monthly' => $date->addMonth(),
            'quarterly' => $date->addMonths(3),
            'semi_annually' => $date->addMonths(6),
            'annually' => $date->addYear(),
            default => $date->addMonth(),
        };
    }
}