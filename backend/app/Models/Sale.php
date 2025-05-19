<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Payment status constants
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_COMPLETED = 'completed';
    const PAYMENT_STATUS_CANCELLED = 'cancelled';

    // Payment method constants
    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    const PAYMENT_METHOD_GHANA_PAYMENT = 'ghana_payment';

    // Sync status constants
    const SYNC_STATUS_PENDING = 'pending';
    const SYNC_STATUS_SYNCED = 'synced';
    const SYNC_STATUS_FAILED = 'failed';

    protected $fillable = [
        'sale_number',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'tax_details',
        'final_amount',
        'payment_method',
        'payment_status',
        'payment_url',
        'status',
        'notes',
        'business_id',
        'branch_id',
        'cashier_id',
        'customer_id',
        'sync_status',
        'last_sync_at',
        'device_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'tax_details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_sync_at' => 'datetime'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPaymentPending()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    public function isPaymentCompleted()
    {
        return $this->payment_status === self::PAYMENT_STATUS_COMPLETED;
    }

    public function isPaymentCancelled()
    {
        return $this->payment_status === self::PAYMENT_STATUS_CANCELLED;
    }

    public function getTotalItems()
    {
        return $this->items()->sum('quantity');
    }

    public function calculateTaxes()
    {
        $business = $this->business;
        $taxes = Tax::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        if ($taxes->isEmpty()) {
            $this->tax_amount = 0;
            $this->tax_details = null;
            $this->save();
            return;
        }

        $subtotal = $this->getSubtotal();
        $taxDetails = [];
        $totalTaxAmount = 0;

        foreach ($taxes as $tax) {
            $taxAmount = $tax->calculateTaxAmount($subtotal);
            $totalTaxAmount += $taxAmount;

            $taxDetails[] = [
                'tax_id' => $tax->id,
                'name' => $tax->name,
                'rate' => $tax->rate,
                'amount' => round($taxAmount, 2)
            ];
        }

        $this->tax_amount = round($totalTaxAmount, 2);
        $this->tax_details = $taxDetails;
        $this->save();
    }

    public function getSubtotal()
    {
        return $this->total_amount - $this->discount_amount;
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_COMPLETED);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PENDING);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForCashier($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }

    // Sync related methods
    public function isSynced()
    {
        return $this->sync_status === self::SYNC_STATUS_SYNCED;
    }

    public function isSyncPending()
    {
        return $this->sync_status === self::SYNC_STATUS_PENDING;
    }

    public function isSyncFailed()
    {
        return $this->sync_status === self::SYNC_STATUS_FAILED;
    }

    public function markAsSynced()
    {
        $this->sync_status = self::SYNC_STATUS_SYNCED;
        $this->last_sync_at = now();
        $this->save();
    }

    public function markAsPending()
    {
        $this->sync_status = self::SYNC_STATUS_PENDING;
        $this->save();
    }

    public function markAsFailed()
    {
        $this->sync_status = self::SYNC_STATUS_FAILED;
        $this->save();
    }

    // Sync scopes
    public function scopePendingSync($query)
    {
        return $query->where('sync_status', self::SYNC_STATUS_PENDING);
    }

    public function scopeSynced($query)
    {
        return $query->where('sync_status', self::SYNC_STATUS_SYNCED);
    }

    public function scopeFailedSync($query)
    {
        return $query->where('sync_status', self::SYNC_STATUS_FAILED);
    }

    public function scopeForDevice($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }
} 