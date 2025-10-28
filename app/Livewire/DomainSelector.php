<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\DomainService;
use Iodev\Whois\Factory as WhoisFactory;

class DomainSelector extends Component
{
    public $domainName = '';
    public $selectedDomain = '';
    public $isChecking = false;
    public $domainResult = null;
    public $selectedTld = 'com';
    public $ownDomain = false;

    protected $domainService;

    public function boot(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }

    public function mount()
    {
        // Load from session if available
        $domainData = session('checkout.domain', []);
        if (!empty($domainData)) {
            $this->selectedDomain = $domainData['name'] ?? '';
            $this->ownDomain = $domainData['own_domain'] ?? false;
            
            // Parse domain name and TLD from selected domain
            if ($this->selectedDomain) {
                $parts = explode('.', $this->selectedDomain);
                if (count($parts) >= 2) {
                    $this->domainName = $parts[0];
                    $this->selectedTld = implode('.', array_slice($parts, 1));
                }
            }
        }
        
        // Auto check domain availability on page load if domain name exists
        if (!empty($this->domainName)) {
            $this->checkDomainAvailability();
        }
    }

    public function updatedDomainName()
    {
        $this->reset(['domainResult', 'ownDomain']);
        // Auto check domain availability when domain name is changed (with minimum length)
        if (strlen($this->domainName) >= 3) {
            $this->checkDomainAvailability();
        } else {
            // Clear results if domain name is too short
            $this->domainResult = null;
            $this->updateSession();
        }
    }

    public function updatedSelectedTld()
    {
        $this->reset(['domainResult', 'ownDomain']);
        // Auto check domain availability when TLD is changed
        if (!empty($this->domainName) && strlen($this->domainName) >= 3) {
            $this->checkDomainAvailability();
        } else {
            // Update session even if not checking
            $this->updateSession();
        }
    }

    public function updatedOwnDomain()
    {
        $this->updateSession();
    }

    public function checkDomainAvailability()
    {
        if (empty($this->domainName)) {
            return;
        }

        // Validate domain name format
        if (!preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?$/', $this->domainName)) {
            $this->addError('domainName', 'Nama domain hanya boleh mengandung huruf, angka, dan tanda hubung');
            return;
        }

        $this->resetErrorBag('domainName');
        $this->isChecking = true;
        $fullDomain = $this->domainName . '.' . $this->selectedTld;

        try {
            // Use php-whois to check domain availability
            $whois = WhoisFactory::get()->createWhois();
            $info = $whois->loadDomainInfo($fullDomain);
            
            if ($info) {
                // Domain is registered
                $this->domainResult = [
                    'available' => false,
                    'domain' => $fullDomain
                ];
                $this->selectedDomain = $fullDomain;
            } else {
                // Domain is available
                $this->domainResult = [
                    'available' => true,
                    'domain' => $fullDomain
                ];
                $this->selectedDomain = $fullDomain;
            }
            
            $this->updateSession();
        } catch (\Exception $e) {
            // If whois fails, assume domain is available (fallback)
            $this->domainResult = [
                'available' => true,
                'domain' => $fullDomain,
                'error' => 'Tidak dapat memverifikasi status domain, diasumsikan tersedia'
            ];
            $this->selectedDomain = $fullDomain;
            $this->updateSession();
        }

        $this->isChecking = false;
    }

    public function updateSession()
    {
        $domainName = $this->selectedDomain ?: ($this->domainName ? $this->domainName . '.' . $this->selectedTld : '');
        
        // Determine domain type based on availability and ownership
        $domainType = 'new'; // default
        if ($this->domainResult && !$this->domainResult['available'] && $this->ownDomain) {
            $domainType = 'existing';
        }
        
        $domainData = [
            'type' => $domainType,
            'name' => $domainName,
            'own_domain' => $this->ownDomain,
            'is_available' => $this->domainResult['available'] ?? null,
        ];

        session(['checkout.domain' => $domainData]);
        
        // Dispatch event to notify other components
        \Log::info('Dispatching domainUpdated event', $domainData);
        $this->dispatch('domainUpdated', $domainData);
        
        // Also call JavaScript function directly
        \Log::info('Calling JavaScript updateHiddenInputs with data', $domainData);
        $this->js('console.log("JS call from Livewire:", ' . json_encode($domainData) . '); window.updateHiddenInputs(' . json_encode($domainData) . ')');
    }

    public function render()
    {
        return view('livewire.domain-selector');
    }
}