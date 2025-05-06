<?php

namespace App\Models;

use App\Traits\HasSync;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\Inventory;


class Branch extends Model
{
    use HasFactory, HasSync;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'business_id',
        'manager_id',
        'status',
        'sync_status',
        'last_sync_at',
        'device_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function users()
{
    return $this->hasMany(User::class);
}


    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('stock_level')
            ->withTimestamps();
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
} 