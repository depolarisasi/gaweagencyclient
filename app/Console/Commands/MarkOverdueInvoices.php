<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';
    protected $description = 'Menandai invoice sent yang sudah lewat due_date menjadi overdue (tanpa cancel)';

    public function handle()
    {
        $this->info('Menandai invoice overdue...');
        $count = 0;

        $query = Invoice::where('status', 'sent')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());

        $query->chunkById(100, function ($invoices) use (&$count) {
            foreach ($invoices as $invoice) {
                $invoice->update(['status' => 'overdue']);
                $count++;
            }
        });

        $this->info("Selesai. Ditandai overdue: {$count} invoice.");
        return Command::SUCCESS;
    }
}