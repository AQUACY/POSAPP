<?php

namespace App\Models;

use App\Traits\HasSync;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Branch;
use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Inventory;

class Business extends Model
{
    use HasFactory, HasSync;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'address',
        'whatsapp_contact',
        'logo_path',
        'status',
        'subscription_end_date',
        'email',
        'tax_id',
        'receipt_settings',
        'report_settings',
        'settings',
        'description',
        'logo',
        'phone',
        'website',
        'tax_number',
        'sync_status',
        'last_sync_at',
        'device_id',
    ];

    protected $casts = [
        'subscription_end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
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
        return $this->hasMany(Sale::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
