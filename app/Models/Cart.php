<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'template_id',
        'subscription_plan_id',
        'billing_cycle',
        'configuration',
        'domain_data',
        'template_amount',
        'addons_amount',
        'domain_amount',
        'subtotal',
        'customer_fee',
        'total_amount',
        'expires_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'domain_data' => 'array',
        'template_amount' => 'decimal:2',
        'addons_amount' => 'decimal:2',
        'domain_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'customer_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function cartAddons(): HasMany
    {
        return $this->hasMany(CartAddon::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(ProductAddon::class, 'cart_addons')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Helper methods
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function calculateTotals(): void
    {
        $this->addons_amount = $this->cartAddons->sum('price');

        // Calculate domain amount if applicable (only for new domain registrations)
        $domainAmount = 0.0;
        try {
            $domainData = $this->domain_data ?? [];
            $domainType = $domainData['type'] ?? $domainData['domain_type'] ?? null;
            if ($domainType === 'new') {
                if (isset($domainData['price']) && is_numeric($domainData['price'])) {
                    $domainAmount = (float) $domainData['price'];
                } else {
                    $tld = $domainData['tld'] ?? null;
                    $domainName = $domainData['name'] ?? $domainData['domain_name'] ?? '';
                    if (!$tld && $domainName) {
                        $parts = explode('.', $domainName);
                        if (count($parts) > 1) {
                            $tld = implode('.', array_slice($parts, 1));
                        }
                    }
                    $domainService = app(\App\Services\DomainService::class);
                    $prices = $domainService->getDomainPrices();
                    $domainAmount = $tld && isset($prices[$tld]) ? (float) $prices[$tld] : 150000.0;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Cart::calculateTotals failed to calculate domain amount', [
                'error' => $e->getMessage(),
            ]);
            $domainAmount = 0.0;
        }

        $this->domain_amount = $domainAmount;
        $this->subtotal = ($this->template_amount ?? 0) + ($this->addons_amount ?? 0) + $this->domain_amount;
        // Jangan menambahkan biaya platform ke total yang dikirim ke Tripay.
        // Tripay akan menghitung fee_customer sendiri berdasarkan metode pembayaran.
        $this->customer_fee = 0.0;
        $this->total_amount = $this->subtotal;
    }

    public function addAddon(ProductAddon $addon): void
    {
        $this->cartAddons()->firstOrCreate(
            ['product_addon_id' => $addon->id],
            ['price' => $addon->price]
        );
        $this->calculateTotals();
        $this->save();
    }

    public function removeAddon(ProductAddon $addon): void
    {
        $this->cartAddons()->where('product_addon_id', $addon->id)->delete();
        $this->calculateTotals();
        $this->save();
    }

    public function clearAddons(): void
    {
        $this->cartAddons()->delete();
        $this->calculateTotals();
        $this->save();
    }

    /**
     * Static methods
     */
    public static function findOrCreateForSession(string $sessionId): self
    {
        return static::forSession($sessionId)
                     ->notExpired()
                     ->first() ?: static::create([
                         'session_id' => $sessionId,
                         'expires_at' => now()->addHours(24),
                     ]);
    }

    public static function findOrCreateForUser(int $userId): self
    {
        return static::forUser($userId)
                     ->notExpired()
                     ->first() ?: static::create([
                         'user_id' => $userId,
                         'expires_at' => now()->addDays(7),
                     ]);
    }
}
