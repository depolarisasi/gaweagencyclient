<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Override configuration values from DB settings if available
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::query()->get()->pluck('value', 'key');

                // Company info (optional, used in views/email)
                if (isset($settings['company_name'])) {
                    config(['app.company_name' => $settings['company_name']]);
                }
                if (isset($settings['company_email'])) {
                    config(['app.company_email' => $settings['company_email']]);
                }
                if (isset($settings['company_phone'])) {
                    config(['app.company_phone' => $settings['company_phone']]);
                }

                // Tripay configuration
                $tripayOverrides = [];
                if (isset($settings['tripay_merchant_code'])) {
                    $tripayOverrides['tripay.merchant_code'] = $settings['tripay_merchant_code'];
                }
                if (isset($settings['tripay_api_key'])) {
                    $tripayOverrides['tripay.api_key'] = $settings['tripay_api_key'];
                }
                if (isset($settings['tripay_private_key'])) {
                    $tripayOverrides['tripay.private_key'] = $settings['tripay_private_key'];
                }
                if (isset($settings['tripay_mode'])) {
                    $tripayOverrides['tripay.sandbox'] = $settings['tripay_mode'] === 'sandbox';
                }
                if (isset($settings['tripay_base_url'])) {
                    $tripayOverrides['tripay.base_url'] = $settings['tripay_base_url'];
                }
                if (!empty($tripayOverrides)) {
                    config($tripayOverrides);
                }

                // Mail configuration (SMTP)
                $mailOverrides = [];
                if (isset($settings['mail_host'])) {
                    $mailOverrides['mail.mailers.smtp.host'] = $settings['mail_host'];
                }
                if (isset($settings['mail_port'])) {
                    $mailOverrides['mail.mailers.smtp.port'] = (int) $settings['mail_port'];
                }
                if (isset($settings['mail_username'])) {
                    $mailOverrides['mail.mailers.smtp.username'] = $settings['mail_username'];
                }
                if (isset($settings['mail_password'])) {
                    $mailOverrides['mail.mailers.smtp.password'] = $settings['mail_password'];
                }
                if (isset($settings['mail_encryption'])) {
                    $mailOverrides['mail.mailers.smtp.encryption'] = $settings['mail_encryption'];
                }
                if (isset($settings['mail_from_name'])) {
                    $mailOverrides['mail.from.name'] = $settings['mail_from_name'];
                }
                if (isset($settings['mail_from_address'])) {
                    $mailOverrides['mail.from.address'] = $settings['mail_from_address'];
                }
                if (!empty($mailOverrides)) {
                    config($mailOverrides);
                }
            }
        } catch (\Throwable $e) {
            // Avoid breaking app during early migrations
            \Log::warning('Settings override failed', ['message' => $e->getMessage()]);
        }
    }
}
