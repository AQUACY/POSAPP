<?php

namespace App\Models;

use App\Traits\HasSync;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockChange extends Model
{
    use HasFactory, HasSync;

    protected $fillable = [
        'inventory_id',
        'quantity',
        'change_type',
        'reason',
        'reference_type',
        'reference_id',
        'notes',
        'business_id',
        'branch_id',
        'user_id',
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

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
