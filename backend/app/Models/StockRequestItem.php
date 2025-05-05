<?php

namespace App\Models;

use App\Traits\HasSync;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequestItem extends Model
{
    use HasFactory, HasSync;

    protected $fillable = [
        'stock_request_id',
        'inventory_id',
        'quantity',
        'notes',
        'sync_status',
        'last_sync_at',
        'device_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    public function stockRequest()
    {
        return $this->belongsTo(StockRequest::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
} 