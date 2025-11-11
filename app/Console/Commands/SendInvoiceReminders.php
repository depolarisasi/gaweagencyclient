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
            $failBefore = 0;
            $sentQuery = Invoice::where('status', 'sent')
                ->whereNotNull('due_date')
                ->whereDate('due_date', now()->addDays($daysBefore)->toDateString())
                ->with('user')
                ->orderBy('id');

            $sentQuery->chunkById(100, function ($sentInvoices) use ($daysBefore, &$countBefore, &$failBefore) {
                foreach ($sentInvoices as $invoice) {
                    $key = $daysBefore === 7 ? 'before_7' : 'before_1';
                    $reminders = is_array($invoice->reminders ?? null) ? $invoice->reminders : [];

                    // Idempoten: skip jika sudah diingatkan
                    if (isset($reminders[$key])) {
                        continue;
                    }

                    try {
                        Notification::send($invoice->user, new InvoiceReminderBeforeDueNotification($invoice, $daysBefore));
                        $reminders[$key] = now()->toDateTimeString();
                        $invoice->reminders = $reminders;
                        $invoice->save();
                        $countBefore++;
                    } catch (\Throwable $e) {
                        $failBefore++;
                        \Log::warning('Failed to send before-due reminder', [
                            'invoice_id' => $invoice->id,
                            'days_before' => $daysBefore,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            });
            $this->info("Terkirim pengingat H-{$daysBefore}: {$countBefore}, gagal: {$failBefore}");
        }

        // H+3, H+7, H+14 untuk status overdue
        foreach ([3, 7, 14] as $daysAfter) {
            $countAfter = 0;
            $failAfter = 0;
            $overdueQuery = Invoice::where('status', 'overdue')
                ->whereNotNull('due_date')
                ->whereDate('due_date', now()->subDays($daysAfter)->toDateString())
                ->with('user')
                ->orderBy('id');

            $overdueQuery->chunkById(100, function ($overdueInvoices) use ($daysAfter, &$countAfter, &$failAfter) {
                foreach ($overdueInvoices as $invoice) {
                    $key = match($daysAfter) {
                        3 => 'after_3',
                        7 => 'after_7',
                        14 => 'after_14',
                        default => 'after_'.$daysAfter,
                    };
                    $reminders = is_array($invoice->reminders ?? null) ? $invoice->reminders : [];

                    // Idempoten: skip jika sudah diingatkan
                    if (isset($reminders[$key])) {
                        continue;
                    }

                    try {
                        Notification::send($invoice->user, new InvoiceReminderAfterDueNotification($invoice, $daysAfter));
                        $reminders[$key] = now()->toDateTimeString();
                        $invoice->reminders = $reminders;
                        $invoice->save();
                        $countAfter++;
                    } catch (\Throwable $e) {
                        $failAfter++;
                        \Log::warning('Failed to send after-due reminder', [
                            'invoice_id' => $invoice->id,
                            'days_after' => $daysAfter,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            });
            $this->info("Terkirim pengingat H+{$daysAfter}: {$countAfter}, gagal: {$failAfter}");
        }

        $this->info('Selesai mengirim pengingat invoice.');
        return Command::SUCCESS;
    }
}