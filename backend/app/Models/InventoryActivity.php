<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryActivity extends Model
{
    protected $fillable = [
        'inventory_id',
        'user_id',
        'branch_id',
        'action_type',
        'quantity',
        'old_quantity',
        'new_quantity',
        'unit_price',
        'reference_type',
        'reference_id',
        'notes'
    ];

    /**
     * Get the inventory item associated with this activity.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the user who performed this activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch associated with this activity.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
} 