<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelUnpaidInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:cancel-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel unpaid invoices older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = Carbon::now()->subDays(7);

        $cancelledCount = Invoice::where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->update(['status' => 'cancelled']);

        $this->info("$cancelledCount unpaid invoices cancelled.");
    }
}
