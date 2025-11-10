<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SuspendOverdueProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:suspend-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Suspend projects with overdue renewal invoices (not paid within 14 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to suspend projects with overdue renewal invoices...');
        
        // Find renewal invoices that are overdue by 14 days
        $overdueDate = now()->subDays(14);
        
        $overdueQuery = Invoice::where('status', 'overdue')
            ->where('due_date', '<', $overdueDate)
            ->where('is_renewal', true)
            ->whereHas('order', function ($q) {
                $q->where('order_type', 'subscription');
            })
            ->with(['order.project', 'order'])
            ->orderBy('id');

        $suspendedCount = 0;

        $overdueQuery->chunkById(100, function ($overdueInvoices) use (&$suspendedCount) {
            foreach ($overdueInvoices as $invoice) {
                try {
                    $order = $invoice->order;
                    $project = $order->project ?? null;
                
                if (!$project) {
                    // Jika tidak ada project, tetap suspend order dan batalkan invoice
                    if ($order) {
                        $order->update([
                            'status' => 'suspended',
                            'suspended_at' => now(),
                        ]);
                    }

                    $invoice->update(['status' => 'cancelled']);

                    $this->warn("No project found for invoice ID: {$invoice->id}. Order suspended and invoice cancelled.");
                    $suspendedCount++;
                    continue;
                }
                
                // Only suspend active projects
                if ($project->status !== 'active') {
                    $this->info("Project ID: {$project->id} is not active (status: {$project->status}), skipping...");
                    continue;
                }
                
                // Suspend the project
                $project->update([
                    'status' => 'suspended',
                    'suspended_at' => now(),
                    'suspension_reason' => 'Renewal invoice overdue by 14+ days',
                    'overdue_invoice_id' => $invoice->id
                ]);

                // Suspend the order as well (standar: suspended)
                if ($order) {
                    $order->update([
                        'status' => 'suspended',
                        'suspended_at' => now(),
                    ]);
                }
                
                // Cancel the overdue invoice (single source of truth H+14)
                $invoice->update([
                    'status' => 'cancelled',
                ]);
                
                $suspendedCount++;
                
                Log::info('Project/order suspended due to overdue renewal invoice', [
                    'project_id' => $project->id,
                    'order_id' => $order?->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'due_date' => $invoice->due_date,
                    'days_overdue' => $invoice->due_date->diffInDays(now()),
                    'user_id' => $invoice->user_id
                ]);
                
                $this->info("Suspended project: {$project->project_name} (ID: {$project->id}) - Invoice: {$invoice->invoice_number}");
                
            } catch (\Exception $e) {
                $this->error("Failed to suspend project for invoice ID: {$invoice->id} - {$e->getMessage()}");
                
                Log::error('Failed to suspend project for overdue renewal invoice', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
            }
        });
        
        $this->info("Completed! Suspended {$suspendedCount} projects with overdue renewal invoices.");
        
        return Command::SUCCESS;
    }
}