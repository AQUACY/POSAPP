<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Branch;
use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Inventory;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'address',
        'phone',
        'email',
        'tax_id',
        'receipt_settings',
        'report_settings',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'receipt_settings' => 'array',
        'report_settings' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Branch::class);
    }

    public function staff()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function sales()
    {
        return $this->hasManyThrough(Sale::class, Branch::class);
    }
}
