<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format amount to Indonesian Rupiah (IDR) format
     * 
     * @param float|int $amount
     * @return string
     */
    public static function formatIDR($amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
    
    /**
     * Format amount with custom currency symbol
     * 
     * @param float|int $amount
     * @param string $symbol
     * @param int $decimals
     * @return string
     */
    public static function format($amount, string $symbol = 'Rp', int $decimals = 0): string
    {
        return $symbol . ' ' . number_format($amount, $decimals, ',', '.');
    }
}