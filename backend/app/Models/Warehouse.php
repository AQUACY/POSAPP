<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'business_id',
        'address',
        'phone',
        'email',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockRequests()
    {
        return $this->hasMany(StockRequest::class);
    }
} 