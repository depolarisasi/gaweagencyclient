<div class="space-y-6">
    <!-- Domain Input Section -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Domain Anda</label>
        <p class="text-sm text-gray-500 mb-4">Domain sudah termasuk dalam paket subscription</p>
        
        <div class="flex space-x-2">
            <div class="flex-1 relative">
                <input type="text" 
                       wire:model.live.debounce.500ms="domainName"
                       placeholder="namawebsite"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('domainName') border-red-500 @enderror">
                <!-- Loading indicator for domain name input -->
                <div wire:loading wire:target="domainName" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                    <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                @error('domainName')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="w-32 relative">
                <select wire:model.live="selectedTld" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="com">.com</option>
                    <option value="net">.net</option>
                    <option value="org">.org</option>
                    <option value="id">.id</option>
                    <option value="co.id">.co.id</option>
                    <option value="web.id">.web.id</option>
                    <option value="my.id">.my.id</option>
                    <option value="biz.id">.biz.id</option>
                </select>
                <!-- Loading indicator for TLD select -->
                <div wire:loading wire:target="selectedTld" class="absolute right-8 top-1/2 transform -translate-y-1/2">
                    <svg class="animate-spin h-3 w-3 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading indicator for domain checking -->
    <div wire:loading wire:target="checkDomainAvailability" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center space-x-3">
            <svg class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-blue-800 font-medium">Mengecek ketersediaan domain {{ $selectedDomain }}...</span>
        </div>
    </div>

    <!-- Domain Check Result -->
    @if($isChecking)
        <div class="flex items-center space-x-2 text-blue-600">
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm">Mengecek ketersediaan domain...</span>
        </div>
    @endif

    <!-- Domain Availability Result -->
    <div wire:loading.remove wire:target="checkDomainAvailability">
        @if($domainResult && !$isChecking)
            @if($domainResult['available'])
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-green-800 font-medium">{{ $selectedDomain }} tersedia!</span>
                    </div>
                    <div class="mt-2 text-sm text-green-700">
                        Domain ini akan didaftarkan untuk Anda (sudah termasuk dalam paket)
                    </div>
                </div>
            @else
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center space-x-2 mb-3">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <span class="text-yellow-800 font-medium">{{ $selectedDomain }} sudah terdaftar</span>
                    </div>
                    <div class="text-sm text-yellow-700 mb-3">
                        Domain ini sudah dimiliki oleh orang lain. Jika Anda memiliki domain ini, silakan centang kotak di bawah:
                    </div>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" 
                               wire:model.live="ownDomain"
                               wire:change="updateSession"
                               onchange="handleOwnDomainChange(this)"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="text-sm text-yellow-800">Ya, saya memiliki domain ini dan akan mengarahkan nameserver ke server Gawe</span>
                    </label>
                </div>
            @endif
        @endif
    </div>

    <!-- Selected Domain Summary -->
    @if($selectedDomain && $domainResult)
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h4 class="font-medium text-blue-900 mb-2">Domain Terpilih:</h4>
            <div class="text-blue-800">
                {{ $selectedDomain }}
                @if(!$domainResult['available'] && $ownDomain)
                    <span class="text-sm">(Domain Existing - Anda akan mengarahkan nameserver)</span>
                @elseif($domainResult['available'])
                    <span class="text-sm">(Domain Baru - Akan didaftarkan)</span>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
function handleOwnDomainChange(checkbox) {
    console.log('=== handleOwnDomainChange START ===');
    console.log('Checkbox checked:', checkbox.checked);
    console.log('Checkbox element:', checkbox);
    
    if (checkbox.checked) {
        let domainName = '{{ $selectedDomain }}';
        
        // If selectedDomain is empty, construct it from domainName and selectedTld
        if (!domainName) {
            const domainNamePart = '{{ $domainName }}';
            const selectedTld = '{{ $selectedTld }}';
            domainName = domainNamePart + selectedTld;
        }
        
        console.log('Domain name from blade:', domainName);
        
        const domainData = {
            type: 'existing',
            name: domainName
        };
        
        console.log('Domain data to set:', domainData);
        
        // Update hidden inputs immediately (no delay)
        const domainTypeInput = document.getElementById('domain_type_input');
        const domainNameInput = document.getElementById('domain_name_input');
        
        console.log('Found domainTypeInput:', !!domainTypeInput);
        console.log('Found domainNameInput:', !!domainNameInput);
        
        if (domainTypeInput && domainNameInput) {
            domainTypeInput.value = domainData.type;
            domainNameInput.value = domainData.name;
            
            console.log('Hidden inputs updated directly:', {
                type: domainTypeInput.value,
                name: domainNameInput.value
            });
            
            // Also trigger the updateHiddenInputs function if it exists
            if (window.updateHiddenInputs) {
                console.log('Calling window.updateHiddenInputs');
                window.updateHiddenInputs(domainData);
            }
            
            // Dispatch custom event as backup
            console.log('Dispatching domainUpdated event');
            window.dispatchEvent(new CustomEvent('domainUpdated', {
                detail: domainData
            }));
            
            // Set the global flag that domain data is ready
            window.domainDataReady = true;
            window.lastDomainUpdateData = domainData;
            console.log('Set window.domainDataReady = true');
        } else {
            console.error('Could not find hidden input elements!');
        }
    }
    console.log('=== handleOwnDomainChange END ===');
}

// Also try to update immediately when the component loads if checkbox is already checked
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired');
    const checkbox = document.querySelector('input[wire\\:model\\.live="ownDomain"]');
    let domainName = '{{ $selectedDomain }}';
    
    // If selectedDomain is empty, construct it from domainName and selectedTld
     if (!domainName) {
         const domainNamePart = '{{ $domainName }}';
         const selectedTld = '{{ $selectedTld }}';
         domainName = domainNamePart + selectedTld;
     }
    
    console.log('Found checkbox on load:', !!checkbox);
    console.log('Checkbox checked on load:', checkbox ? checkbox.checked : 'N/A');
    console.log('Domain name on load:', domainName);
    
    if (checkbox && checkbox.checked && domainName) {
        console.log('Checkbox already checked on load, updating hidden inputs');
        handleOwnDomainChange(checkbox);
    }
});

// Add event listener for checkbox changes
document.addEventListener('change', function(e) {
    if (e.target && e.target.matches('input[wire\\:model\\.live="ownDomain"]')) {
        console.log('Checkbox change event detected via event delegation');
        handleOwnDomainChange(e.target);
    }
});
    </script>