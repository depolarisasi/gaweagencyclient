<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomainService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.domain.api_url', 'https://api.domain-provider.com');
        $this->apiKey = config('services.domain.api_key');
    }

    /**
     * Check domain availability
     */
    public function checkAvailability(string $domain): array
    {
        try {
            // For now, simulate domain availability check
            // In production, integrate with actual domain registrar API
            $isAvailable = $this->simulateDomainCheck($domain);
            
            return [
                'available' => $isAvailable,
                'domain' => $domain,
                'price' => $this->getDomainPrice($domain),
                'suggestions' => $isAvailable ? [] : $this->getDomainSuggestions($domain)
            ];
        } catch (\Exception $e) {
            Log::error('Domain availability check failed: ' . $e->getMessage());
            return [
                'available' => false,
                'domain' => $domain,
                'price' => 0,
                'suggestions' => [],
                'error' => 'Gagal mengecek ketersediaan domain'
            ];
        }
    }

    /**
     * Get domain price based on TLD
     */
    public function getDomainPrice(string $domain): float
    {
        $tld = $this->extractTld($domain);
        
        $prices = [
            'com' => 150000,
            'net' => 160000,
            'org' => 160000,
            'id' => 200000,
            'co.id' => 180000,
            'biz.id' => 190000,
            'web.id' => 190000,
            'my.id' => 190000,
        ];

        return $prices[$tld] ?? 150000; // Default price
    }

    /**
     * Get domain suggestions if domain is not available
     */
    public function getDomainSuggestions(string $domain): array
    {
        $domainName = $this->extractDomainName($domain);
        $suggestions = [];

        $tlds = ['com', 'net', 'org', 'id', 'co.id', 'biz.id', 'web.id', 'my.id'];
        
        foreach ($tlds as $tld) {
            $suggestion = $domainName . '.' . $tld;
            if ($suggestion !== $domain && $this->simulateDomainCheck($suggestion)) {
                $suggestions[] = [
                    'domain' => $suggestion,
                    'price' => $this->getDomainPrice($suggestion),
                    'available' => true
                ];
            }
        }

        // Add some creative suggestions
        $prefixes = ['my', 'get', 'the'];
        $suffixes = ['online', 'web', 'site'];
        
        foreach ($prefixes as $prefix) {
            $suggestion = $prefix . $domainName . '.com';
            if ($this->simulateDomainCheck($suggestion)) {
                $suggestions[] = [
                    'domain' => $suggestion,
                    'price' => $this->getDomainPrice($suggestion),
                    'available' => true
                ];
            }
        }

        foreach ($suffixes as $suffix) {
            $suggestion = $domainName . $suffix . '.com';
            if ($this->simulateDomainCheck($suggestion)) {
                $suggestions[] = [
                    'domain' => $suggestion,
                    'price' => $this->getDomainPrice($suggestion),
                    'available' => true
                ];
            }
        }

        return array_slice($suggestions, 0, 5); // Return max 5 suggestions
    }

    /**
     * Register domain (placeholder for actual implementation)
     */
    public function registerDomain(string $domain, array $contactInfo): array
    {
        try {
            // In production, integrate with actual domain registrar API
            Log::info('Domain registration requested', [
                'domain' => $domain,
                'contact' => $contactInfo
            ]);

            return [
                'success' => true,
                'domain' => $domain,
                'registration_id' => 'REG-' . time() . '-' . rand(1000, 9999),
                'expires_at' => now()->addYear(),
                'message' => 'Domain berhasil didaftarkan'
            ];
        } catch (\Exception $e) {
            Log::error('Domain registration failed: ' . $e->getMessage());
            return [
                'success' => false,
                'domain' => $domain,
                'error' => 'Gagal mendaftarkan domain: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate domain format
     */
    public function validateDomain(string $domain): bool
    {
        // Basic domain validation
        return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}$/', $domain);
    }

    /**
     * Extract TLD from domain
     */
    protected function extractTld(string $domain): string
    {
        $parts = explode('.', $domain);
        if (count($parts) >= 3 && in_array(end($parts), ['id'])) {
            // Handle .co.id, .web.id, etc.
            return $parts[count($parts) - 2] . '.' . end($parts);
        }
        return end($parts);
    }

    /**
     * Extract domain name without TLD
     */
    protected function extractDomainName(string $domain): string
    {
        $tld = $this->extractTld($domain);
        return str_replace('.' . $tld, '', $domain);
    }

    /**
     * Simulate domain availability check
     * In production, replace with actual API call
     */
    protected function simulateDomainCheck(string $domain): bool
    {
        // Simulate some domains as taken
        $takenDomains = [
            'google.com',
            'facebook.com',
            'instagram.com',
            'twitter.com',
            'youtube.com',
            'amazon.com',
            'microsoft.com',
            'apple.com'
        ];

        // Check if domain is in taken list
        if (in_array(strtolower($domain), $takenDomains)) {
            return false;
        }

        // Simulate random availability (80% available)
        return rand(1, 10) <= 8;
    }

    /**
     * Validate domain format and TLD
     */
    public function isValidDomain(string $domain): bool
    {
        // Basic domain format validation
        if (!preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/', $domain)) {
            return false;
        }

        // Check if TLD is supported
        $tld = $this->extractTld($domain);
        $supportedTlds = array_keys($this->getSupportedTlds());
        
        return in_array($tld, $supportedTlds);
    }

    /**
     * Get supported TLDs
     */
    public function getSupportedTlds(): array
    {
        return [
            'com' => ['name' => '.com', 'price' => 150000, 'popular' => true],
            'net' => ['name' => '.net', 'price' => 160000, 'popular' => true],
            'org' => ['name' => '.org', 'price' => 160000, 'popular' => true],
            'id' => ['name' => '.id', 'price' => 200000, 'popular' => true],
            'co.id' => ['name' => '.co.id', 'price' => 180000, 'popular' => true],
            'biz.id' => ['name' => '.biz.id', 'price' => 190000, 'popular' => false],
            'web.id' => ['name' => '.web.id', 'price' => 190000, 'popular' => false],
            'my.id' => ['name' => '.my.id', 'price' => 190000, 'popular' => false],
        ];
    }
}