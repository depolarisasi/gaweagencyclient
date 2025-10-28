<?php

namespace Tests\Feature;

use App\Services\DomainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $domainService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->domainService = new DomainService();
    }

    /** @test */
    public function can_check_domain_availability()
    {
        $result = $this->domainService->checkAvailability('example.com');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('available', $result);
        $this->assertArrayHasKey('domain', $result);
        $this->assertArrayHasKey('price', $result);
        $this->assertEquals('example.com', $result['domain']);
    }

    /** @test */
    public function returns_unavailable_for_common_domains()
    {
        $commonDomains = ['google.com', 'facebook.com', 'youtube.com'];
        
        foreach ($commonDomains as $domain) {
            $result = $this->domainService->checkAvailability($domain);
            $this->assertFalse($result['available'], "Domain {$domain} should be unavailable");
        }
    }

    /** @test */
    public function returns_available_for_random_domains()
    {
        $randomDomain = 'test-' . uniqid() . '.com';
        $result = $this->domainService->checkAvailability($randomDomain);
        
        $this->assertTrue($result['available']);
        $this->assertEquals($randomDomain, $result['domain']);
        $this->assertGreaterThan(0, $result['price']);
    }

    /** @test */
    public function can_get_domain_suggestions()
    {
        $suggestions = $this->domainService->getSuggestions('mywebsite');
        
        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        
        foreach ($suggestions as $suggestion) {
            $this->assertArrayHasKey('domain', $suggestion);
            $this->assertArrayHasKey('available', $suggestion);
            $this->assertArrayHasKey('price', $suggestion);
            $this->assertStringContainsString('mywebsite', $suggestion['domain']);
        }
    }

    /** @test */
    public function can_get_domain_prices()
    {
        $prices = $this->domainService->getDomainPrices();
        
        $this->assertIsArray($prices);
        $this->assertNotEmpty($prices);
        
        foreach ($prices as $tld => $price) {
            $this->assertIsString($tld);
            $this->assertIsNumeric($price);
            $this->assertGreaterThan(0, $price);
        }
    }

    /** @test */
    public function can_register_domain()
    {
        $domainData = [
            'domain' => 'test-domain-' . uniqid() . '.com',
            'registrant' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'address' => '123 Main St',
                'city' => 'Anytown',
                'country' => 'US'
            ]
        ];

        $result = $this->domainService->registerDomain($domainData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('domain', $result);
        $this->assertArrayHasKey('registration_id', $result);
        $this->assertTrue($result['success']);
        $this->assertEquals($domainData['domain'], $result['domain']);
    }

    /** @test */
    public function validates_domain_format()
    {
        $validDomains = [
            'example.com',
            'test-domain.net',
            'my-website.org',
            'subdomain.example.co.uk'
        ];

        $invalidDomains = [
            'invalid',
            'no-tld',
            '.com',
            'space domain.com',
            'special@char.com'
        ];

        foreach ($validDomains as $domain) {
            $this->assertTrue($this->domainService->validateDomainFormat($domain), "Domain {$domain} should be valid");
        }

        foreach ($invalidDomains as $domain) {
            $this->assertFalse($this->domainService->validateDomainFormat($domain), "Domain {$domain} should be invalid");
        }
    }

    /** @test */
    public function can_extract_tld_from_domain()
    {
        $testCases = [
            'example.com' => 'com',
            'test.net' => 'net',
            'website.co.uk' => 'co.uk',
            'subdomain.example.org' => 'org'
        ];

        foreach ($testCases as $domain => $expectedTld) {
            $tld = $this->domainService->extractTld($domain);
            $this->assertEquals($expectedTld, $tld, "TLD extraction failed for {$domain}");
        }
    }

    /** @test */
    public function can_extract_domain_name_without_tld()
    {
        $testCases = [
            'example.com' => 'example',
            'my-website.net' => 'my-website',
            'test.co.uk' => 'test',
            'subdomain.example.org' => 'subdomain.example'
        ];

        foreach ($testCases as $domain => $expectedName) {
            $name = $this->domainService->extractDomainName($domain);
            $this->assertEquals($expectedName, $name, "Domain name extraction failed for {$domain}");
        }
    }

    /** @test */
    public function domain_prices_include_common_tlds()
    {
        $prices = $this->domainService->getDomainPrices();
        
        $commonTlds = ['com', 'net', 'org', 'info', 'biz'];
        
        foreach ($commonTlds as $tld) {
            $this->assertArrayHasKey($tld, $prices, "Price for .{$tld} should be available");
        }
    }

    /** @test */
    public function suggestions_include_different_tlds()
    {
        $suggestions = $this->domainService->getSuggestions('testsite');
        
        $tlds = [];
        foreach ($suggestions as $suggestion) {
            $tld = $this->domainService->extractTld($suggestion['domain']);
            $tlds[] = $tld;
        }
        
        $uniqueTlds = array_unique($tlds);
        $this->assertGreaterThan(1, count($uniqueTlds), 'Suggestions should include multiple TLDs');
    }

    /** @test */
    public function can_handle_international_domains()
    {
        $internationalDomains = [
            'example.co.id',
            'test.com.au',
            'website.co.uk'
        ];

        foreach ($internationalDomains as $domain) {
            $result = $this->domainService->checkAvailability($domain);
            $this->assertIsArray($result);
            $this->assertArrayHasKey('available', $result);
            $this->assertEquals($domain, $result['domain']);
        }
    }

    /** @test */
    public function domain_registration_requires_valid_registrant_data()
    {
        $invalidData = [
            'domain' => 'test.com',
            'registrant' => [
                'name' => '',
                'email' => 'invalid-email',
                'phone' => '',
            ]
        ];

        $result = $this->domainService->registerDomain($invalidData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
    }

    /** @test */
    public function can_get_supported_tlds()
    {
        $prices = $this->domainService->getDomainPrices();
        $supportedTlds = array_keys($prices);
        
        $this->assertIsArray($supportedTlds);
        $this->assertNotEmpty($supportedTlds);
        $this->assertContains('com', $supportedTlds);
        $this->assertContains('net', $supportedTlds);
        $this->assertContains('org', $supportedTlds);
    }

    /** @test */
    public function domain_suggestions_are_relevant()
    {
        $keyword = 'restaurant';
        $suggestions = $this->domainService->getSuggestions($keyword);
        
        foreach ($suggestions as $suggestion) {
            $domain = $suggestion['domain'];
            $this->assertTrue(
                str_contains($domain, $keyword) || 
                str_contains($domain, 'food') || 
                str_contains($domain, 'cafe') ||
                str_contains($domain, 'dining'),
                "Suggestion '{$domain}' should be relevant to '{$keyword}'"
            );
        }
    }
}