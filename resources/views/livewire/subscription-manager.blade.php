<div class="space-y-6">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Subscriptions List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Langganan Saya</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Kelola semua langganan website Anda</p>
        </div>

        @if($subscriptions->count() > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($subscriptions as $subscription)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($subscription->template && $subscription->template->preview_image)
                                        <img class="h-16 w-16 rounded-lg object-cover" 
                                             src="{{ asset('storage/' . $subscription->template->preview_image) }}" 
                                             alt="{{ $subscription->template->name }}">
                                    @else
                                        <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $subscription->template->name ?? 'Template tidak ditemukan' }}
                                        </p>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusBadgeClass($subscription->status) }}">
                                            {{ $this->getStatusText($subscription->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-sm text-gray-600">
                                            {{ $subscription->subscriptionPlan->name ?? 'Paket tidak ditemukan' }} - 
                                            {{ $subscription->subscriptionPlan->billing_cycle ?? 'N/A' }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Domain: {{ $subscription->domain_name }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Dibuat: {{ $subscription->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="text-right mr-4">
                                    <p class="text-sm font-medium text-gray-900">
                                        Rp {{ number_format($subscription->subscription_amount, 0, ',', '.') }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        per {{ $subscription->billing_cycle }}
                                    </p>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex space-x-2">
                                    @if($subscription->status === 'active')
                                        <button wire:click="openUpgradeModal({{ $subscription->id }})"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Upgrade
                                        </button>
                                        <button wire:click="renewSubscription({{ $subscription->id }})"
                                                wire:confirm="Apakah Anda yakin ingin memperpanjang langganan ini?"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Perpanjang
                                        </button>
                                        <button wire:click="cancelSubscription({{ $subscription->id }})"
                                                wire:confirm="Apakah Anda yakin ingin membatalkan langganan ini?"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Batalkan
                                        </button>
                                    @elseif($subscription->status === 'pending')
                                        <a href="{{ route('checkout.success', ['order' => $subscription->id]) }}"
                                           class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            Bayar Sekarang
                                        </a>
                                    @elseif($subscription->status === 'expired')
                                        <button wire:click="renewSubscription({{ $subscription->id }})"
                                                wire:confirm="Apakah Anda yakin ingin memperpanjang langganan ini?"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Perpanjang
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $subscriptions->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada langganan</h3>
                <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat website pertama Anda.</p>
                <div class="mt-6">
                    <a href="{{ route('checkout.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Buat Website Baru
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Upgrade Modal -->
    @if($showUpgradeModal && $selectedOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeUpgradeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Upgrade Langganan
                                </h3>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Upgrade dari paket <strong>{{ $selectedOrder->subscriptionPlan->name }}</strong> 
                                        ke paket yang lebih tinggi.
                                    </p>

                                    <div class="space-y-3">
                                        @foreach($availablePlans as $plan)
                                            @if($plan->price > $selectedOrder->subscriptionPlan->price)
                                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                                    <input type="radio" 
                                                           wire:model="selectedPlanId" 
                                                           value="{{ $plan->id }}" 
                                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                                    <div class="ml-3 flex-1">
                                                        <div class="flex justify-between items-center">
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-900">{{ $plan->name }}</p>
                                                                <p class="text-xs text-gray-500">{{ $plan->billing_cycle_label }}</p>
                                                            </div>
                                                            <div class="text-right">
                                                                <p class="text-sm font-medium text-gray-900">
                                                                    Rp {{ number_format($plan->price, 0, ',', '.') }}
                                                                </p>
                                                                <p class="text-xs text-green-600">
                                                                    +Rp {{ number_format($plan->price - $selectedOrder->subscriptionPlan->price, 0, ',', '.') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>

                                    @error('selectedPlanId')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                wire:click="upgradeSubscription"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Upgrade Sekarang
                        </button>
                        <button type="button" 
                                wire:click="closeUpgradeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>