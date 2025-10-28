<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Paket Bulanan',
                'description' => 'Paket berlangganan bulanan dengan fleksibilitas tinggi',
                'price' => 500000,
                'billing_cycle' => 'monthly',
                'cycle_months' => 1,
                'discount_percentage' => 0,
                'features' => [
                    'Website profesional dengan template premium',
                    'Hosting gratis selama berlangganan',
                    'SSL Certificate gratis',
                    'Support 24/7 via WhatsApp',
                    'Update konten unlimited',
                    'Backup otomatis harian',
                ],
                'is_active' => true,
                'sort_order' => 1,
                'is_popular' => false,
            ],
            [
                'name' => 'Paket 6 Bulan',
                'description' => 'Paket berlangganan 6 bulan dengan diskon menarik',
                'price' => 2700000, // 10% discount
                'billing_cycle' => '6_months',
                'cycle_months' => 6,
                'discount_percentage' => 10,
                'features' => [
                    'Website profesional dengan template premium',
                    'Hosting gratis selama berlangganan',
                    'SSL Certificate gratis',
                    'Support 24/7 via WhatsApp',
                    'Update konten unlimited',
                    'Backup otomatis harian',
                    'SEO optimization dasar',
                ],
                'is_active' => true,
                'sort_order' => 2,
                'is_popular' => false,
            ],
            [
                'name' => 'Paket Tahunan',
                'description' => 'Paket berlangganan tahunan terpopuler dengan nilai terbaik',
                'price' => 5000000, // 17% discount
                'billing_cycle' => 'annually',
                'cycle_months' => 12,
                'discount_percentage' => 17,
                'features' => [
                    'Website profesional dengan template premium',
                    'Hosting gratis selama berlangganan',
                    'SSL Certificate gratis',
                    'Support 24/7 via WhatsApp',
                    'Update konten unlimited',
                    'Backup otomatis harian',
                    'SEO optimization lengkap',
                    'Google Analytics setup',
                    'Domain gratis (.com/.id)',
                ],
                'is_active' => true,
                'sort_order' => 3,
                'is_popular' => true,
            ],
            [
                'name' => 'Paket 2 Tahun',
                'description' => 'Paket berlangganan 2 tahun dengan penghematan maksimal',
                'price' => 9000000, // 25% discount
                'billing_cycle' => '2_years',
                'cycle_months' => 24,
                'discount_percentage' => 25,
                'features' => [
                    'Website profesional dengan template premium',
                    'Hosting gratis selama berlangganan',
                    'SSL Certificate gratis',
                    'Support 24/7 via WhatsApp',
                    'Update konten unlimited',
                    'Backup otomatis harian',
                    'SEO optimization lengkap',
                    'Google Analytics setup',
                    'Domain gratis (.com/.id)',
                    'Social media integration',
                    'E-commerce ready',
                ],
                'is_active' => true,
                'sort_order' => 4,
                'is_popular' => false,
            ],
            [
                'name' => 'Paket 3 Tahun',
                'description' => 'Paket berlangganan 3 tahun untuk komitmen jangka panjang',
                'price' => 12000000, // 33% discount
                'billing_cycle' => '3_years',
                'cycle_months' => 36,
                'discount_percentage' => 33,
                'features' => [
                    'Website profesional dengan template premium',
                    'Hosting gratis selama berlangganan',
                    'SSL Certificate gratis',
                    'Support 24/7 via WhatsApp',
                    'Update konten unlimited',
                    'Backup otomatis harian',
                    'SEO optimization lengkap',
                    'Google Analytics setup',
                    'Domain gratis (.com/.id)',
                    'Social media integration',
                    'E-commerce ready',
                    'Priority support',
                    'Custom development (minor)',
                ],
                'is_active' => true,
                'sort_order' => 5,
                'is_popular' => false,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}