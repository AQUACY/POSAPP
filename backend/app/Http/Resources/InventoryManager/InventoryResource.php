<?php

namespace App\Http\Resources\InventoryManager;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'cost_price' => $this->cost_price,
            'reorder_level' => $this->reorder_level,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'branch' => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ],
            'warehouse' => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ],
            'is_low_stock' => $this->quantity <= $this->reorder_level,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 