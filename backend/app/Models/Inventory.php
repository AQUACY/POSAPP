<?php

namespace App\Models;

use App\Traits\HasSync;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory, HasSync;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'barcode',
        'category_id',
        'business_id',
        'branch_id',
        'unit_price',
        'selling_price',
        'quantity',
        'reorder_level',
        'status',
        'sync_status',
        'last_sync_at',
        'device_id',
        'expiry_date',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockChanges()
    {
        return $this->hasMany(StockChange::class);
    }

    public function isEligibleForRefund()
    {
        if ($this->is_non_refundable) {
            return false;
        }

        if ($this->refund_restriction_days && $this->created_at->diffInDays(now()) > $this->refund_restriction_days) {
            return false;
        }

        return true;
    }

    public function getRefundRestrictionMessage()
    {
        if ($this->is_non_refundable) {
            return 'This item is not eligible for refunds';
        }

        if ($this->refund_restriction_days && $this->created_at->diffInDays(now()) > $this->refund_restriction_days) {
            return "This item can only be refunded within {$this->refund_restriction_days} days of purchase";
        }

        return null;
    }
} 