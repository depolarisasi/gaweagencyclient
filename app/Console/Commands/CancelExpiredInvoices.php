<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CancelExpiredInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel invoices that are expired (not paid within 7 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to cancel expired invoices...');
        
        // Find invoices that are pending and past due date
        $expiredInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->get();
            
        $cancelledCount = 0;
        
        foreach ($expiredInvoices as $invoice) {
            try {
                // Cancel the invoice
                $invoice->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Auto-cancelled: Payment not received within 7 days'
                ]);
                
                // Cancel related project if it's still pending
                $project = Project::where('order_id', $invoice->order_id)
                    ->where('status', 'pending')
                    ->first();
                    
                if ($project) {
                    $project->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Invoice expired and not paid'
                    ]);
                    
                    $this->info("Cancelled project ID: {$project->id} for expired invoice ID: {$invoice->id}");
                }
                
                $cancelledCount++;
                
                Log::info('Invoice auto-cancelled due to expiration', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'due_date' => $invoice->due_date,
                    'user_id' => $invoice->user_id
                ]);
                
                $this->info("Cancelled invoice: {$invoice->invoice_number} (ID: {$invoice->id})");
                
            } catch (\Exception $e) {
                $this->error("Failed to cancel invoice ID: {$invoice->id} - {$e->getMessage()}");
                
                Log::error('Failed to auto-cancel expired invoice', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("Completed! Cancelled {$cancelledCount} expired invoices.");
        
        return Command::SUCCESS;
    }
}