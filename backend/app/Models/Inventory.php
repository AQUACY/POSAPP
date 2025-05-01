<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'barcode',
        'quantity',
        'unit_price',
        'cost_price',
        'reorder_level',
        'category_id',
        'business_id',
        'branch_id',
        'is_non_refundable',
        'requires_condition_check',
        'condition_notes',
        'refund_restriction_amount',
        'refund_restriction_days'
    ];

    protected $casts = [
        'is_non_refundable' => 'boolean',
        'requires_condition_check' => 'boolean',
        'refund_restriction_amount' => 'decimal:2',
        'refund_restriction_days' => 'integer'
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