<?php

if (!function_exists('formatIDR')) {
    /**
     * Format amount to Indonesian Rupiah (IDR) format
     * 
     * @param float|int $amount
     * @return string
     */
    function formatIDR($amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format amount with custom currency symbol
     * 
     * @param float|int $amount
     * @param string $symbol
     * @param int $decimals
     * @return string
     */
    function formatCurrency($amount, string $symbol = 'Rp', int $decimals = 0): string
    {
        return $symbol . ' ' . number_format($amount, $decimals, ',', '.');
    }
}