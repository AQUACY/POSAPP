<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'branch_id',
        'warehouse_id',
        'status', // pending, approved, rejected, fulfilled
        'notes',
        'requested_by',
        'approved_by',
        'approved_at',
        'fulfilled_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(StockRequestItem::class);
    }
} 