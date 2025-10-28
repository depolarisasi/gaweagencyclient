<?php

namespace Database\Seeders;

use App\Models\ProductAddon;
use Illuminate\Database\Seeder;

class ProductAddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = [
            [
                'name' => 'SSL Certificate',
                'description' => 'Sertifikat SSL untuk keamanan website Anda',
                'price' => 150000,
                'billing_type' => 'recurring',
                'billing_cycle' => 'annually',
                'is_active' => true,
                'sort_order' => 1,
                'category' => 'security',
            ],
            [
                'name' => 'Premium Support',
                'description' => 'Dukungan prioritas 24/7 dari tim ahli kami',
                'price' => 200000,
                'billing_type' => 'recurring',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'sort_order' => 2,
                'category' => 'support',
            ],
            [
                'name' => 'Website Backup',
                'description' => 'Backup otomatis harian untuk website Anda',
                'price' => 100000,
                'billing_type' => 'recurring',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'sort_order' => 3,
                'category' => 'backup',
            ],
            [
                'name' => 'SEO Optimization',
                'description' => 'Optimasi SEO untuk meningkatkan ranking website',
                'price' => 500000,
                'billing_type' => 'one_time',
                'billing_cycle' => null,
                'is_active' => true,
                'sort_order' => 4,
                'category' => 'marketing',
            ],
            [
                'name' => 'Google Analytics Setup',
                'description' => 'Setup dan konfigurasi Google Analytics',
                'price' => 250000,
                'billing_type' => 'one_time',
                'billing_cycle' => null,
                'is_active' => true,
                'sort_order' => 5,
                'category' => 'analytics',
            ],
            [
                'name' => 'Content Management Training',
                'description' => 'Pelatihan penggunaan CMS untuk tim Anda',
                'price' => 750000,
                'billing_type' => 'one_time',
                'billing_cycle' => null,
                'is_active' => true,
                'sort_order' => 6,
                'category' => 'training',
            ],
            [
                'name' => 'Email Marketing Setup',
                'description' => 'Setup sistem email marketing terintegrasi',
                'price' => 300000,
                'billing_type' => 'one_time',
                'billing_cycle' => null,
                'is_active' => true,
                'sort_order' => 7,
                'category' => 'marketing',
            ],
            [
                'name' => 'Performance Monitoring',
                'description' => 'Monitoring performa website real-time',
                'price' => 150000,
                'billing_type' => 'recurring',
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'sort_order' => 8,
                'category' => 'monitoring',
            ],
        ];

        foreach ($addons as $addon) {
            ProductAddon::create($addon);
        }
    }
}