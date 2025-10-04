<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Website Development Products
        Product::create([
            'name' => 'Website Company Profile',
            'description' => 'Website company profile profesional dengan desain modern dan responsif',
            'type' => 'website',
            'price' => 2500000.00,
            'billing_cycle' => 'annually',
            'features' => [
                'Desain responsif',
                'Halaman About, Services, Contact',
                'SEO Basic',
                'SSL Certificate',
                'Hosting 1 tahun',
                'Domain .com/.id',
                'Admin panel sederhana'
            ],
            'setup_time_days' => 14,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'name' => 'Website E-Commerce',
            'description' => 'Toko online lengkap dengan sistem pembayaran dan manajemen produk',
            'type' => 'website',
            'price' => 5000000.00,
            'billing_cycle' => 'annually',
            'features' => [
                'Katalog produk unlimited',
                'Sistem pembayaran online',
                'Manajemen inventory',
                'Dashboard admin lengkap',
                'Integrasi kurir',
                'SEO Optimized',
                'Mobile responsive',
                'Hosting 1 tahun',
                'SSL Certificate'
            ],
            'setup_time_days' => 30,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Product::create([
            'name' => 'Website Custom Application',
            'description' => 'Aplikasi web custom sesuai kebutuhan bisnis spesifik',
            'type' => 'website',
            'price' => 10000000.00,
             'billing_cycle' => 'annually',
            'features' => [
                'Analisis kebutuhan mendalam',
                'Custom database design',
                'User management system',
                'API integration',
                'Advanced security',
                'Performance optimization',
                'Training & documentation',
                'Maintenance 3 bulan',
                'Hosting 1 tahun'
            ],
            'setup_time_days' => 60,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Digital Marketing Products
        Product::create([
            'name' => 'SEO Optimization',
            'description' => 'Optimasi SEO untuk meningkatkan ranking website di search engine',
            'type' => 'website',
            'price' => 1500000.00,
            'billing_cycle' => 'monthly',
            'features' => [
                'Keyword research',
                'On-page optimization',
                'Technical SEO audit',
                'Content optimization',
                'Backlink building',
                'Monthly reporting',
                'Google Analytics setup',
                'Search Console setup'
            ],
            'setup_time_days' => 7,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        Product::create([
            'name' => 'Social Media Management',
             'description' => 'Pengelolaan media sosial profesional untuk meningkatkan brand awareness',
             'type' => 'website',
            'price' => 2000000.00,
            'billing_cycle' => 'monthly',
            'features' => [
                'Content planning & creation',
                'Daily posting schedule',
                'Community management',
                'Paid ads management',
                'Analytics & reporting',
                'Hashtag research',
                'Competitor analysis',
                'Brand monitoring'
            ],
            'setup_time_days' => 3,
            'is_active' => true,
            'sort_order' => 5,
        ]);

        // Hosting & Maintenance Products
        Product::create([
            'name' => 'Web Hosting Premium',
            'description' => 'Hosting premium dengan performa tinggi dan support 24/7',
            'type' => 'hosting',
            'price' => 500000.00,
            'billing_cycle' => 'annually',
            'features' => [
                'SSD Storage 10GB',
                'Bandwidth unlimited',
                'SSL Certificate',
                'Daily backup',
                'Email accounts unlimited',
                'cPanel access',
                'Support 24/7',
                '99.9% uptime guarantee'
            ],
            'setup_time_days' => 1,
            'is_active' => true,
            'sort_order' => 6,
        ]);

        Product::create([
            'name' => 'Website Maintenance',
            'description' => 'Layanan maintenance rutin untuk menjaga performa dan keamanan website',
            'type' => 'maintenance',
            'price' => 300000.00,
            'billing_cycle' => 'monthly',
            'features' => [
                'Security monitoring',
                'Regular updates',
                'Performance optimization',
                'Backup management',
                'Bug fixes',
                'Content updates',
                'Monthly reports',
                'Priority support'
            ],
            'setup_time_days' => 1,
            'is_active' => true,
            'sort_order' => 7,
        ]);

        // Consultation Product
        Product::create([
            'name' => 'Digital Strategy Consultation',
            'description' => 'Konsultasi strategi digital untuk mengoptimalkan bisnis online',
            'type' => 'website',
            'price' => 1000000.00,
             'billing_cycle' => 'annually',
            'features' => [
                'Business analysis',
                'Digital audit',
                'Strategy roadmap',
                'Competitor analysis',
                'Technology recommendations',
                'Implementation plan',
                'Follow-up session',
                'Written report'
            ],
            'setup_time_days' => 7,
            'is_active' => true,
            'sort_order' => 8,
        ]);
    }
}
