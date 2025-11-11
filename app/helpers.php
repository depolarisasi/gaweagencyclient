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

if (!function_exists('terbilang_idr')) {
    /**
     * Konversi angka menjadi teks bahasa Indonesia untuk Rupiah (terbilang)
     * Contoh: 12500 => "dua belas ribu lima ratus"
     *
     * @param float|int $number
     * @return string
     */
    function terbilang_idr($number): string
    {
        $number = (int) round($number);
        if ($number === 0) {
            return 'nol';
        }

        $units = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $spell = function ($n) use (&$spell, $units) {
            if ($n < 12) {
                return $units[$n];
            } elseif ($n < 20) {
                return $units[$n - 10] . ' belas';
            } elseif ($n < 100) {
                $tens = intdiv($n, 10);
                $rest = $n % 10;
                return trim($units[$tens] . ' puluh ' . ($rest ? $units[$rest] : ''));
            } elseif ($n < 200) {
                return trim('seratus ' . ($n > 100 ? $spell($n - 100) : ''));
            } elseif ($n < 1000) {
                $hundreds = intdiv($n, 100);
                $rest = $n % 100;
                return trim($units[$hundreds] . ' ratus ' . ($rest ? $spell($rest) : ''));
            } elseif ($n < 2000) {
                return trim('seribu ' . ($n > 1000 ? $spell($n - 1000) : ''));
            } elseif ($n < 1000000) {
                $thousands = intdiv($n, 1000);
                $rest = $n % 1000;
                return trim($spell($thousands) . ' ribu ' . ($rest ? $spell($rest) : ''));
            } elseif ($n < 1000000000) {
                $millions = intdiv($n, 1000000);
                $rest = $n % 1000000;
                return trim($spell($millions) . ' juta ' . ($rest ? $spell($rest) : ''));
            } elseif ($n < 1000000000000) {
                $billions = intdiv($n, 1000000000);
                $rest = $n % 1000000000;
                return trim($spell($billions) . ' miliar ' . ($rest ? $spell($rest) : ''));
            } elseif ($n < 1000000000000000) {
                $trillions = intdiv($n, 1000000000000);
                $rest = $n % 1000000000000;
                return trim($spell($trillions) . ' triliun ' . ($rest ? $spell($rest) : ''));
            }
            return (string) $n; // fallback jika sangat besar
        };

        return trim($spell($number));
    }
}