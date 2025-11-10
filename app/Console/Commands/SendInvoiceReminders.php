<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use Illuminate\Support\Facades\Notification;
use App\Notifications\InvoiceReminderBeforeDueNotification;
use App\Notifications\InvoiceReminderAfterDueNotification;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Mengirim pengingat invoice H-7/H-1 dan H+3/H+7/H+14';

    public function handle()
    {
        $this->info('Mengirim pengingat invoice...');

        // H-7 dan H-1 untuk status sent
        foreach ([7, 1] as $daysBefore) {
            $countBefore = 0;
            $sentQuery = Invoice::where('status', 'sent')
                ->whereNotNull('due_date')
                ->whereDate('due_date', now()->addDays($daysBefore)->toDateString())
                ->with('user')
                ->orderBy('id');

            $sentQuery->chunkById(100, function ($sentInvoices) use ($daysBefore, &$countBefore) {
                foreach ($sentInvoices as $invoice) {
                    try {
                        Notification::send($invoice->user, new InvoiceReminderBeforeDueNotification($invoice, $daysBefore));
                        $countBefore++;
                    } catch (\Throwable $e) {
                        \Log::warning('Failed to send before-due reminder', [
                            'invoice_id' => $invoice->id,
                            'days_before' => $daysBefore,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            });
            $this->info("Terkirim pengingat H-{$daysBefore}: {$countBefore}");
        }

        // H+3, H+7, H+14 untuk status overdue
        foreach ([3, 7, 14] as $daysAfter) {
            $countAfter = 0;
            $overdueQuery = Invoice::where('status', 'overdue')
                ->whereNotNull('due_date')
                ->whereDate('due_date', now()->subDays($daysAfter)->toDateString())
                ->with('user')
                ->orderBy('id');

            $overdueQuery->chunkById(100, function ($overdueInvoices) use ($daysAfter, &$countAfter) {
                foreach ($overdueInvoices as $invoice) {
                    try {
                        Notification::send($invoice->user, new InvoiceReminderAfterDueNotification($invoice, $daysAfter));
                        $countAfter++;
                    } catch (\Throwable $e) {
                        \Log::warning('Failed to send after-due reminder', [
                            'invoice_id' => $invoice->id,
                            'days_after' => $daysAfter,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            });
            $this->info("Terkirim pengingat H+{$daysAfter}: {$countAfter}");
        }

        $this->info('Selesai mengirim pengingat invoice.');
        return Command::SUCCESS;
    }
}