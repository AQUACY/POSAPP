<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_request_id',
        'inventory_id',
        'quantity_requested',
        'quantity_approved',
        'quantity_fulfilled',
        'notes'
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