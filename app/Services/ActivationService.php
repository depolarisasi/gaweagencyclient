<?php

namespace App\Services;

use App\Models\Invoice;

class ActivationService
{
    /**
     * Aktifkan Order dan Project berdasarkan invoice yang sudah dibayar.
     * Optional: kirim notifikasi PaymentSuccessful.
     */
    public function activateOrderAndProjectFromInvoice(Invoice $invoice, bool $sendNotification = false): void
    {
        try {
            $invoice->load(['order', 'order.user', 'order.product', 'order.subscriptionPlan']);
            $order = $invoice->order;

            if ($order) {
                // Aktivasi order
                if ($order->status !== 'active') {
                    $order->status = 'active';
                    $order->activated_at = now();
                    // Konsistensi next_due_date: gunakan billing_period_end bila tersedia, fallback hitung
                    $order->next_due_date = $invoice->billing_period_end ?? $order->calculateNextDueDate();
                    $order->save();
                }

                // Aktivasi/kreasi project
                $project = \App\Models\Project::where('order_id', $order->id)->first();
                if ($project) {
                    if ($project->status === 'pending') {
                        $project->update([
                            'status' => 'active',
                            'start_date' => now(),
                        ]);
                    }
                } else {
                    $baseName = $order->product?->name ?? $order->subscriptionPlan?->name ?? 'Website';
                    $name = $order->domain_name ? ('Website ' . $order->domain_name) : ('Project ' . $baseName);
                    $websiteUrl = $order->domain_name ? ('https://' . $order->domain_name) : null;

                    \App\Models\Project::create([
                        'project_name' => $name,
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'status' => 'active',
                        'start_date' => now(),
                        'template_id' => $order->template_id,
                        'website_url' => $websiteUrl,
                        'description' => 'Project otomatis setelah pembayaran invoice #' . ($invoice->invoice_number ?? $invoice->id),
                    ]);
                }
            }

            if ($sendNotification) {
                $invoice->loadMissing('user');
                if ($invoice->user) {
                    $invoice->user->notify(new \App\Notifications\PaymentSuccessful($invoice));
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Aktivasi order/project dari invoice gagal', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}