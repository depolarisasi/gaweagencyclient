<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TldPricing;

class TldPricingSeeder extends Seeder
{
    /**
     * Seed initial TLD pricing data.
     */
    public function run(): void
    {
        $seed = [
            ['tld' => 'com', 'price' => 150000, 'is_active' => true],
            ['tld' => 'net', 'price' => 130000, 'is_active' => true],
            ['tld' => 'org', 'price' => 140000, 'is_active' => true],
            ['tld' => 'info', 'price' => 120000, 'is_active' => true],
            ['tld' => 'biz', 'price' => 120000, 'is_active' => true],
            ['tld' => 'id', 'price' => 250000, 'is_active' => true],
            ['tld' => 'co.id', 'price' => 200000, 'is_active' => true],
            ['tld' => 'biz.id', 'price' => 160000, 'is_active' => true],
            ['tld' => 'web.id', 'price' => 150000, 'is_active' => true],
            ['tld' => 'my.id', 'price' => 100000, 'is_active' => true],
            ['tld' => 'co.uk', 'price' => 200000, 'is_active' => true],
            ['tld' => 'com.au', 'price' => 220000, 'is_active' => true],
        ];

        foreach ($seed as $row) {
            TldPricing::updateOrCreate(
                ['tld' => $row['tld']],
                ['price' => $row['price'], 'is_active' => $row['is_active']]
            );
        }
    }
}