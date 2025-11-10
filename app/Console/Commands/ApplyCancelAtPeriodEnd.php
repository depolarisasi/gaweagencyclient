<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderAddon;
use Illuminate\Support\Facades\Log;

class ApplyCancelAtPeriodEnd extends Command
{
    protected $signature = 'addons:apply-cancel-at-period-end';
    protected $description = 'Apply client-requested cancel for add-ons at end of current period';

    public function handle()
    {
        $this->info('Applying cancel-at-period-end for eligible add-ons...');

        $query = OrderAddon::where('cancel_at_period_end', true)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', now()->toDateString())
            ->orderBy('id');

        $updatedCount = 0;

        $query->chunkById(200, function ($addons) use (&$updatedCount) {
            foreach ($addons as $addon) {
                try {
                    $addon->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ]);
                    $updatedCount++;
                    Log::info('Addon cancelled at period end', [
                        'order_addon_id' => $addon->id,
                        'order_id' => $addon->order_id,
                        'next_due_date' => $addon->next_due_date,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to apply cancel-at-period-end', [
                        'order_addon_id' => $addon->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Completed! Applied cancel for {$updatedCount} add-ons.");
        return Command::SUCCESS;
    }
}