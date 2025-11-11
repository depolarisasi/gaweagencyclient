<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'product_id',
        'subscription_plan_id',
        'template_id',
        'order_type',
        'amount',
        'subscription_amount',
        'addons_amount',
        'setup_fee',
        'billing_cycle',
        'status',
        'next_due_date',
        'order_details',
        'domain_name',
        'domain_type',
        'domain_details',
        'notes',
        'activated_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'subscription_amount' => 'decimal:2',
            'addons_amount' => 'decimal:2',
            'setup_fee' => 'decimal:2',
            'next_due_date' => 'date',
            'order_details' => 'array',
            'domain_details' => 'array',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class)->withTrashed();
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function orderAddons()
    {
        return $this->hasMany(OrderAddon::class);
    }

    public function addons()
    {
        return $this->belongsToMany(ProductAddon::class, 'order_addons')
                    ->withPivot(['price', 'billing_cycle', 'quantity', 'addon_details'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSubscription($query)
    {
        return $query->where('order_type', 'subscription');
    }

    public function scopeAddon($query)
    {
        return $query->where('order_type', 'addon');
    }

    // Helper methods
    public function getTotalAmountAttribute()
    {
        // Total dihitung dari komponen biaya aktual: subscription + addons + setup fee
        // Hindari menjumlahkan kembali $this->amount karena biasanya merepresentasikan subtotal
        $subscription = $this->subscription_amount ?? 0;
        $addons = $this->addons_amount ?? 0;
        $setup = $this->setup_fee ?? 0;
        return $subscription + $addons + $setup;
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedSubscriptionAmountAttribute()
    {
        return 'Rp ' . number_format($this->subscription_amount ?? 0, 0, ',', '.');
    }

    public function getFormattedAddonsAmountAttribute()
    {
        return 'Rp ' . number_format($this->addons_amount ?? 0, 0, ',', '.');
    }

    public function isSubscriptionOrder()
    {
        return $this->order_type === 'subscription';
    }

    public function isAddonOrder()
    {
        return $this->order_type === 'addon';
    }

    public function hasNewDomain()
    {
        return $this->domain_type === 'register_new';
    }

    public function hasExistingDomain()
    {
        return $this->domain_type === 'existing';
    }

    public function getDomainTypeLabelAttribute()
    {
        return match($this->domain_type) {
            'existing' => 'Domain Existing',
            'register_new' => 'Daftar Domain Baru',
            default => 'Tidak Ada Domain',
        };
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    // Computed attributes for view compatibility
    public function getCustomerInfoAttribute()
    {
        $user = $this->user;
        return [
            'full_name' => $user?->name ?? '',
            'name' => $user?->name ?? '',
            'email' => $user?->email ?? '',
            'phone' => $user?->phone ?? '',
            // Keep backward compatibility: map company_name to company
            'company' => $user?->company_name ?? '',
        ];
    }

    public function getDomainInfoAttribute()
    {
        // Normalize domain details for view usage
        $details = $this->domain_details ?? [];
        if (!is_array($details)) {
            return [];
        }
        return $details;
    }

    public function getDomainAmountAttribute()
    {
        $details = $this->domain_details ?? [];
        if (is_array($details) && isset($details['price'])) {
            return (float) $details['price'];
        }
        return 0.0;
    }

    public function calculateNextDueDate()
    {
        if (!$this->activated_at) {
            return null;
        }

        $baseDate = $this->next_due_date ? Carbon::parse($this->next_due_date) : Carbon::parse($this->activated_at);

        // For subscription orders, use subscription plan billing cycle
        if ($this->isSubscriptionOrder() && $this->subscriptionPlan) {
            return $baseDate->addMonths($this->subscriptionPlan->cycle_months);
        }

        // Fallback to order billing cycle
        switch ($this->billing_cycle) {
            case 'monthly':
                return $baseDate->addMonth();
            case 'quarterly':
                return $baseDate->addMonths(3);
            case 'semi_annually':
            case '6_months':
                return $baseDate->addMonths(6);
            case 'annually':
                return $baseDate->addYear();
            case '2_years':
                return $baseDate->addYears(2);
            case '3_years':
                return $baseDate->addYears(3);
            default:
                return null;
        }
    }

    /**
     * Singular accessors for compatibility with views using $order->invoice / $order->project
     */
    public function getInvoiceAttribute()
    {
        if ($this->relationLoaded('invoices')) {
            return $this->invoices->sortByDesc('created_at')->first();
        }
        return $this->invoices()->latest()->first();
    }

    public function getProjectAttribute()
    {
        if ($this->relationLoaded('projects')) {
            return $this->projects->sortByDesc('created_at')->first();
        }
        return $this->projects()->latest()->first();
    }
}
