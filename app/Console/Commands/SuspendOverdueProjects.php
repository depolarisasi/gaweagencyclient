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
        
        $overdueInvoices = Invoice::where('status', 'pending')
            ->where('due_date', '<', $overdueDate)
            ->whereHas('order', function($query) {
                // This is a renewal invoice (not the first invoice)
                $query->where('is_renewal', true);
            })
            ->with(['order.project'])
            ->get();
            
        $suspendedCount = 0;
        
        foreach ($overdueInvoices as $invoice) {
            try {
                $project = $invoice->order->project ?? null;
                
                if (!$project) {
                    $this->warn("No project found for invoice ID: {$invoice->id}");
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
                
                // Cancel the overdue invoice
                $invoice->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Auto-cancelled: Renewal payment overdue by 14+ days, project suspended'
                ]);
                
                $suspendedCount++;
                
                Log::info('Project suspended due to overdue renewal invoice', [
                    'project_id' => $project->id,
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
        
        $this->info("Completed! Suspended {$suspendedCount} projects with overdue renewal invoices.");
        
        return Command::SUCCESS;
    }
}